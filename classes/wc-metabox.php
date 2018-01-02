<?php
if(!defined("ABSPATH")){exit;}

if(!class_exists("WPSFramework_WC_Metabox")){
    class WPSFramework_WC_Metabox extends WPSFramework_Abstract {

        public $options = null;

        public $default_wc_tabs = null;

        public $fields = array();

        public $group_fields = array();

        public $groups_to_add = array();

        public function __construct($options) {
            $this->default_wc_tabs = apply_filters('wpsf_wc_default_tabs',array(
                'general' => 'general_product_data',
                'inventory' => 'inventory_product_data',
                'shipping' => 'shipping_product_data',
                'linked_product' => 'linked_product_data',
                'attribute' => 'product_attributes',
                'variations' => 'variable_product_options',
                'advanced' => 'advanced_product_data',
            ));
            $this->options = apply_filters ( 'wpsf_wc_metabox_options', $options );
            
            if(!empty($this->options)){
                $this->addAction('load-post.php','handle_options');
                $this->addAction('load-post-new.php','handle_options');
                $this->addAction('woocommerce_product_data_tabs','add_wc_tabs');
                $this->addAction("woocommerce_product_data_panels",'add_wc_fields',99);
                $this->addAction("admin_enqueue_scripts",'load_style_script');
                $this->addAction('woocommerce_admin_process_product_object','save_product_data');
                $this->addAction('woocommerce_product_options_advanced','advanced_page');
                $this->addAction('woocommerce_product_options_general_product_data','general_page');
                $this->addAction('woocommerce_product_options_inventory_product_data','stock_page');
                $this->addAction('woocommerce_product_options_related','linked_page');
                $this->addAction('woocommerce_product_options_shipping','shipping_page');
            }
        }

        public function advanced_page(){ echo $this->_render_group_page('advanced'); }

        public function general_page(){ echo $this->_render_group_page('general'); }

        public function stock_page(){ echo $this->_render_group_page('inventory'); }

        public function linked_page(){ echo $this->_render_group_page('linked_product'); }

        public function shipping_page(){ echo $this->_render_group_page('shipping'); }

        private function _render_group_page($key = ''){
            if(!isset($this->group_fields[$key])) {
                return;
            }

            foreach($this->group_fields[$key] as $group_field){
                $wc_class = (isset($group_field['wc_style']) && $group_field['wc_style'] === true) ? ' wpsf-wc-style wpsf-wc-metabox-fields ' : ' wpsf-wc-metabox-fields ';
                echo '<div class="'.$wc_class.'">';
                    echo $this->render_fields($group_field,$group_field['id']);
                echo '</div>';
            }
        }

        public function handle_options(){
            foreach($this->options as $key => $plugin) {
                foreach($plugin as $sections){
                    if(!isset($this->fields[$sections['id']])){
                        $this->fields[$sections['id']] = array();
                    }
                    if(isset($sections['sections'])){
                        foreach($sections['sections'] as $section){
                            $this->fields[$sections['id']] = array_merge($this->fields[$sections['id']],$section['fields']);
                            if(isset($section['group'])){
                                if(!isset($this->group_fields[$section['group']])){
                                    $this->group_fields[$section['group']] = array();
                                }
                                $this->group_fields[$section['group']][] = array_merge(array('id' => $sections['id']),$section);
                            } else {
                                $this->groups_to_add[] = array_merge(array('id' => $sections['id']),$section);
                            }
                        }
                    } else {
                        $this->fields[$sections['id']] = array_merge($this->fields[$sections['id']],$sections['fields']);
                        $this->groups_to_add[] = $sections;
                    }
                }
            }
        }

        public function save_product_data(){
            global $post;

            if(wp_verify_nonce ( wpsf_get_var ( 'wpsf-framework-wc-metabox-nonce' ), 'wpsf-framework-wc-metabox' )){
                foreach($this->fields as $db_id => $fields){
                    $errors = array();
                    $transient = array();
                    $request = wpsf_get_var($db_id);
                    $ex_value = $this->_post_data('get',$db_id);
                    foreach($fields as $field){
                        if(isset($field['type']) && isset($field['id'])){
                            $field_value = wpsf_get_vars($db_id,$field['id']);
                            $sanitize_type = $field['type'];

                            if(isset($field['sanitize']) && $field['sanitize'] !== false){
                                $sanitize_type = $field['sanitize'];
                            }

                            if(has_filter('wpsf_sanitize_'.$sanitize_type)){
                                $request[$field['id']] = apply_filters('wpsf_sanitize_'.$sanitize_type,$field_value,$field,$fields);
                            }

                            if(isset($field['validate']) && has_filter('wpsf_validate_'.$field['validate'])){
                                $validate = apply_filters('wpsf_validate_'.$field['validate'],$field_value,$field,$fields);

                                if(!empty($validate)){
                                    $default = isset($field['default']) ? $field['default'] : '';
                                    $old_val = isset($ex_value[$field['id']]) ? $ex_value[$field['id']] : $default;
                                    $request[$field['id']] = $old_val;
                                    $errors[$field['id']] = array(
                                        'code' => $field['id'],
                                        'message' => $validate,
                                        'type' => 'error',
                                    );
                                }
                            }
                        }
                    }

                    $request = apply_filters('wpsf_wc_metabox_save',$request,$db_id,$post);
                    if(empty($request)){
                        delete_post_meta($post->ID,$db_id);
                    } else {
                        update_post_meta($post->ID,$db_id,$request);
                    }
                    $transient['errors'] = $errors;
                    set_transient('wpsf-wc-mt'.$db_id,$transient,30);
                }
            }
        }

        public function load_style_script(){
            global $typenow;
            if($typenow === 'product'){
                wpsf_load_fields_styles();
            }
        }
        
        public function add_wc_tabs($tabs = array()){
            foreach($this->groups_to_add as $group){
                $defaults = array(
                    'class' => array(),
                    'priority' =>9999,
                    'title' => '',
                    'name' => '',
                    'show' => '',
                    'hide' => '',
                );
                $group = wp_parse_args($group,$defaults);
                $default_class = array('wpsf-wc-tab');
                $default_class = is_array($group['class']) ? array_merge($group['class'],$default_class) : array_merge(array($group['class']),$default_class);
                $default_class = array_merge($default_class,$this->show_hide_class($group['show'],$group['hide']));
                $tabs[$group['name']] = array(
                    'label' => $group['title'],
                    'target' => apply_filters('wpsf_sanitize_title','wpsf_'.$group['name'].'_wctab'),
                    'class' => $default_class,
                    'priority' => $group['priority'],
                );
            }
            return $tabs;
        }

        private function __sh_class($data,$key){
            $return = array();
            if(!empty($data)){
                foreach(explode('|',$data) as $c){
                    $return[] = $key.$c;
                }
            }
            return $return;
        }

        private function show_hide_class($show = '',$hide = '',$_r = 'array'){
            $return = array();
            $return = array_merge($return,$this->__sh_class($show,'show_if_'));
            $return = array_merge($return,$this->__sh_class($hide,'hide_if_'));

            if($_r == 'array'){
                return $return;
            }

            return implode(' ',$return);
        }
        
        private function _merge_wrap_class($old_classes = '',$new_class = array()){
            if(empty($old_class)){
                return implode(' ',$new_class);
            }
            
            $ex_class = explode(' ',$old_classes);
            foreach($new_class as $c) {
                if (!in_array($c, $ex_class)) {
                    $ex_class[] = $c;
                }
            }
            return implode(' ',$ex_class);
        }

        private function _post_data($type = 'get',$key = '',$update_value = '',$post_id = ''){
            if(empty($post_id)){
                global $post;
                $post_id = isset($post->ID) ? $post->ID : false;
            }

            if($type == 'get'){
                return get_post_meta($post_id,apply_filters('wpsf_sanitize_title','wpsf_'.$key.'_wctabs'),true);
            }

            return update_post_meta($post_id,apply_filters('wpsf_sanatize_title','wpsf_'.$key.'_wctabs'),$update_value);
        }

        private function render_fields($option,$db_key = ''){
            global $post,$wpsf_errors;
            $html = '';
            $values = get_post_meta($post->ID,$db_key,true);
            $transient = get_transient('wpsf-wc-mt'.$db_key);
            $wpsf_errors = isset($transient['errors']) ? $transient['errors'] : array();

            if(!is_array($values)){
                $values = array();
            }
            if(isset($option['fields'])){
                foreach($option['fields'] as $field){
                    $defaults = array('show' => '','hide' => '','wrap_class' => '');
                    $field = wp_parse_args($field,$defaults);
                    $value = isset($values[$field['id']]) ? $values[$field['id']] : '';
                    $WrapClass = $this->show_hide_class($field['show'],$field['hide']);
                    $field['wrap_class'] = $this->_merge_wrap_class($field['wrap_class'],$WrapClass);
                    $html .= wpsf_add_element ( $field, $value, $db_key);
                }
            }
            return $html;
        }

        public function add_wc_fields(){
            wp_nonce_field ( 'wpsf-framework-wc-metabox', 'wpsf-framework-wc-metabox-nonce' );

            foreach($this->groups_to_add as $group){
                $default = array(
                    'fields' => '',
                    'name' => '',
                    'title' => '',
                );
                $group = wp_parse_args($group,$default);
                $id = apply_filters('wpsf_sanitize_title','wpsf_'.$group['name'].'_wctab');

                $wc_class = (isset($group['wc_style']) && $group['wc_style'] === true) ? ' wpsf-wc-style ' : '';

                echo '<div id="'.$id.'" class="panel woocommerce_options_panel hidden  wpsf-wc-metabox-fields'.$wc_class.'">';
                echo $this->render_fields($group,$group['id']);
                echo '</div>';
            }
        }
    }
}