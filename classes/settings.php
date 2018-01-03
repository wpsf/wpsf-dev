<?php
if (! defined ( 'ABSPATH' )) {
	die ();
} // Cannot access pages directly.

class WPSFramework_Settings extends WPSFramework_Abstract {
	
    public $unique = WPSF_OPTION;
	
    public $settings = array ();
	
    public $options = array ();
	
    public $sections = array ();
	
    public $get_option = array ();
	
    public $settings_page = null;
    
    public $override_location = null;
    
    private function _set_settings_options($settings = array(), $options = array()) {
		if (! empty ( $settings )) {
            $defaults = array(
                'menu_type' => '',
				'menu_parent' => '',
				'menu_title' => '',
				'menu_slug' => '',
				'menu_capability' => 'manage_options',
				'menu_icon' => null,
				'menu_position' => null,
                'ajax_save' => false,
                'buttons' => array(),
                'option_name' => $this->unique,
                'override_location' => '',
                'extra_css' => array(),
                'extra_js' => array(),
                'is_single_page' => false,
                'is_sticky_header' => false,
                'style' => 'modern',
            );
            
            $this->settings = wp_parse_args($settings,$defaults);
            
            $buttons = array(
                'save' => __("Save",'wpsf-wp'),
                'restore' => __("Restore" ,'wpsf-wp'),
                'reset' => __("Reset All Options",'wpsf-wp'),
            );
            
            $this->settings['buttons'] = wp_parse_args($this->settings['buttons'],$buttons);
            
			$this->settings = apply_filters ( 'wpsf_settings', $this->settings );
			if (isset ( $this->settings ['options_name'] )) {
				$this->unique = $this->settings ['options_name'];
			}
            
            if(isset($this->settings['override_location'])){
                $this->override_location = $this->settings['override_location'];
            }
		}
		
		if (! empty ( $options )) {
			$this->options = apply_filters ( 'wpsf_options', $options );
		}
	}
    
    public function __construct($settings = array(), $options = array()) {
		$this->init ( $settings, $options );
	}
    
    public function init($settings = array(), $options = array()) {
		$this->_set_settings_options ( $settings, $options );
		$this->total_fields = 0;
        $this->settings_page = null;
        
		if (! empty ( $this->options )) {
            $this->parent_sectionid = '';
            $this->current_section = '';
            $this->sections = array();
            $this->get_sections();
            $this->cache = get_transient('wpsf_'.$this->get_cache_key());
            $this->find_current_sections();
            $this->get_option = get_option ( $this->unique,true );
            if(!is_array($this->get_option)){$this->get_option = array();}
			$this->addAction('admin_menu', 'admin_menu' );
            $this->addAction('admin_init','register_settings');
            $this->addAction("admin_enqueue_scripts",'load_style_script');
            $this->addAction("update_option_".$this->unique,'on_options_update');
		}
	}
    
    public function on_options_update(){
        do_action("wpsf_options_updated_".$this->unique);
    }
    
    private function _get_first_section_name($is_section = true,$page = null){
        $cs = null;
        
        if(!is_null($page)){
            if(isset($this->options[$page])){
                $cs = $this->options[$page];
            } else {
                return false;
            }
        } else {
            $cs = current($this->options);
        }
        
        if($is_section === true){
            if(isset($cs['sections'])){
                $cs = current($cs['sections']);
                return $cs['name'];
            }
        } else {
            return isset($cs['name']) ? $cs['name'] : false;
        }
    }
    
    public function find_current_sections(){
        $this->current_section = (!empty($this->cache['section_id'])) ? $this->cache['section_id'] : false;
        $this->parent_sectionid = (!empty($this->cache['parent_section_id'])) ? $this->cache['parent_section_id'] : false;
        
        $get_current_section = wpsf_get_var('wpsf-section-id',false);
        $get_parent_sectionid = wpsf_get_var('wpsf-parent-section-id',false);
        
        if($get_current_section === false && $get_parent_sectionid === false){
            $this->_validate_first_section($this->current_section,$this->parent_sectionid);
        } else if($get_current_section === false || $get_parent_sectionid === false){
            $this->_validate_first_section($get_current_section,$get_parent_sectionid);
        } else if($get_current_section !== false || $get_parent_sectionid !== false){
            $this->_validate_first_section($get_current_section,$get_parent_sectionid);
        }
    }
    
    private function _validate_first_section($current_section,$parent_sectionid){
        if($current_section === false && $parent_sectionid === false){
            $this->parent_sectionid = $this->_get_first_section_name(false);
            $this->current_section = $this->_get_first_section_name(true);
        } else if($current_section !== false && $parent_sectionid === false){
            $this->parent_sectionid = $current_section;
            $this->current_section = $this->_get_first_section_name(true,$parent_sectionid);
        } else if($parent_sectionid !== false && $current_section === false ){
            $this->current_section = $this->_get_first_section_name(true,$parent_sectionid);
        } else {
            $this->current_section = $current_section;
            $this->parent_sectionid = $parent_sectionid;
        }
    }
    
    public function load_style_script($hook){
        if($this->settings_page == $hook){
            wpsf_load_fields_styles();
        }
        
        if(isset($this->settings['extra_css']) && is_array($this->settings['extra_css'])){
            foreach($this->settings['extra_css'] as $id){
                wp_enqueue_style($id);
            }
        } 
        
        if(isset($this->settings['extra_js']) && is_array($this->settings['extra_js'])){
            foreach($this->settings['extra_js'] as $id){
                wp_enqueue_script($id);
            }
        } 
    }

    public function admin_menu() {
		$menu_parent = $this->settings['menu_parent'];
        $menu_type = $this->settings['menu_type'];
        $menu_icon = $this->settings['menu_icon'];
        $menu_position = $this->settings['menu_position'];
        $menu_title = $this->settings['menu_title'];
        $menu_capability = $this->settings['menu_capability'];
        $menu_slug = $this->settings['menu_slug'];
        
        switch($menu_type){
            case 'submenu':
                $this->settings_page = add_submenu_page($menu_parent,$menu_title,$menu_title,$menu_capability,$menu_slug,array (&$this,'admin_page'));
                break;
            case 'management':
            case 'dashboard':
            case 'dashboard':
            case 'options':
            case 'plugins':
            case 'theme':
                $fun = 'add_'.$menu_type.'_page';
                if(function_exists($fun)){
                    $this->settings_page = $fun($menu_title,$menu_title,$menu_capability,$menu_slug, array(&$this,'admin_page'), $menu_icon, $menu_position);
                }
                break;
            default:
                $this->settings_page = add_menu_page ( $menu_title, $menu_title, $menu_capability, $menu_slug, array (&$this,'admin_page'), $menu_icon, $menu_position );
                break;
        }
        
	}
    
    private function get_sections() {
        if(!empty($this->sections)){
            return $this->sections;
        }
		$sections = array ();
		foreach ( $this->options as $key => $value ) {
			if (isset ( $value ['sections'] )) {
				foreach ( $value ['sections'] as $section ) {
					if (isset ( $section ['fields'] )) {
                        $section['page_id'] = $value['name'];
						$sections [$value['name'].'/'.$section['name']] = $section;
                        $this->total_fields += count($section['fields']);
					}
				}
			} else {
                if(isset($value['callback_hook'])){
                    $this->options[$key]['fields'] = array();
                    $value['fields'] = array();
                }
                
				if (isset ( $value ['fields'] )) {
                    $value['page_id'] = false;
					$sections [$value['name']] = $value;
                    $this->total_fields += count($value['fields']);
				}
			}
		}
        $this->sections = $sections;
        
        return $sections;
	}
    
    public function register_settings(){
        register_setting ( $this->unique, $this->unique, array (&$this,'validate_save'));
        if(count($this->get_option) < $this->total_fields){
            $this->register_settings_fields();
        }
	}
    
    private function register_settings_fields(){
        $defaults = array();
        
        foreach($this->get_sections() as $section){
            foreach($section['fields'] as $field_key => $field){
                if(isset($field['default']) && ! isset($this->get_option[$field['id']])){
                    $defaults[$field['id']] = $field['default'];
                    $this->get_option[$field['id']] = $field['default'];
                }
            }
        }
        if(!empty($defaults)){
            update_option($this->unique,$this->get_option);
        }
    }
    
    public function validate_save($request){
        $add_errors = array();
        $section_id = wpsf_get_var('wpsf-section-id',false);
        $parent_section_id = wpsf_get_var("wpsf-parent-section-id",false);
        $submitted_val = $request;
        
        if($this->is_single_page() === false){
            $request = array_merge($this->get_option,$request);
        }
        
        if(isset($request['_nounce'])){
            unset ( $request ['_nonce'] );
        }
        
        if (isset ( $request ['import'] ) && ! empty ( $request ['import'] )) {
			$decode_string = wpsf_decode_string ( $request ['import'] );
			if (is_array ( $decode_string )) {
				return $decode_string;
			}
			$add_errors [] = $this->add_settings_error ( esc_html__ ( 'Success. Imported backup options.', 'wpsf-framework' ), 'updated' );
		}
        
        if (isset ( $request ['resetall'] )) {
			$add_errors [] = $this->add_settings_error(esc_html__('Default options restored.', 'wpsf-framework'),'updated');
			return;
		}
        
        if(isset($request['reset']) && !empty($section_id)){
            $this->get_sections();
            if(isset($this->sections[$this->_sec_id($section_id,$parent_section_id)])){
                $section = $this->sections[$this->_sec_id($section_id,$parent_section_id)];
                foreach($section['fields'] as $field){
                    if(isset($field['id'])){
                        if(isset($field['default'])){
                            $request [$field ['id']] = $field ['default'];
                        } else {
                            unset ( $request [$field ['id']] );
                        }
                    }
                }
            }
            $add_errors[] = $this->add_settings_error(esc_html__('Default options restored for only this section.', 'wpsf-framework'),'updated');
        }
        
        foreach($this->get_sections() as $section){
            if($this->is_single_page() === false && ($section_id != $section['name'] && $parent_section_id != $section['page_id'])){
                continue;
            }
            
            foreach($section['fields'] as $field){
                if(isset($field['type']) && ! isset($field['multilang']) && isset($field['id'])){
                    $request_value = isset ($request[$field['id']]) ? $request[$field['id']] : '';
					$sanitize_type = $field ['type'];
                    
                    if (isset($field['sanitize'])) {
                        $sanitize_type = ($field['sanitize'] !== false) ? $field['sanitize'] : false;
                    }
                    
                    if ($sanitize_type !== false && has_filter('wpsf_sanitize_'.$sanitize_type )) {
                        $request [$field ['id']] = apply_filters ('wpsf_sanitize_'.$sanitize_type, $request_value, $field, $section ['fields']);
                    }
                    
                    if (isset ( $field ['validate'] ) && has_filter ( 'wpsf_validate_' . $field ['validate'] )) {
                        $validate = apply_filters('wpsf_validate_' . $field ['validate'], $request_value, $field, $section ['fields']);
                        if (! empty ( $validate )) {
                            $add_errors [] = $this->add_settings_error ( $validate, 'error', $field ['id'] );
                            $request [$field ['id']] = (isset($this->get_option[$field ['id']])) ? $this->get_option [$field ['id']] : '';
                        }
                    }
                    
                    if($this->is_single_page() === false){
                        $request[$field['id']] = isset($submitted_val[$field['id']]) ? $request[$field['id']] : '';
                    }
                }
            }
        }
        
        $request = apply_filters("wpsf_validate_save",$request,$this);
        do_action("wpsf_validate_save_after",$request);
        $time =(wpsf_language_defaults() !== false ) ? 30 : 10;
        set_transient('wpsf_'.$this->get_cache_key(),array('errors' => $add_errors,'section_id' => $section_id,'parent_section_id' => $parent_section_id,),$time);
        return $request;
    }
    
    public function field_callback($field) {
		$value = (isset ( $field ['id'] ) && isset ( $this->get_option [$field ['id']] )) ? $this->get_option [$field ['id']] : '';
        if($field['type'] === 'fieldset' && isset($field['un_array'])){
            $value = array();
            foreach($field['fields'] as $f){
                $value[$f['id']] = (isset($this->get_option[$f['id']])) ? $this->get_option[$f['id']] : '';
            }
        }
        
		return wpsf_add_element ( $field, $value, $this->unique );
	}
    
    public function add_settings_error($message, $type = 'error', $id = 'global') {
		return array ('setting' => 'wpsf-errors','code' => $id,'message' => $message,'type' => $type);
	}

    public function get_settings_fields() {
		$this->catch_output ();
		settings_fields ( $this->unique );
        echo '<input type="hidden" class="wpsf-reset" name="wpsf-section-id" value="'.$this->current_section().'" />';
        echo '<input class="wpsf_parent_section_id" type="hidden" name="wpsf-parent-section-id" value="'.$this->current_section('parent').'" /> ';
		return $this->catch_output ( false );
	}
    
    public function admin_page(){
        $errors_html = '';
        
        if($this->settings['ajax_save'] !== true && !empty($this->cache['errors'])){
            global $wpsf_errors;
            $wpsf_errors = $this->cache['errors'];
            
            foreach ( $wpsf_errors as $error ) {
                if (in_array ( $error ['setting'], array ('general','wpsf-errors'))) {
                    $errors_html .= '<div class="wpsf-settings-error ' . $error ['type'] . '"> <p><strong>' . $error ['message'] . '</strong></p> </div>';
                }
            }
        }
        
        $this->render_html();
        $this->load_template('settings/render.php',array('class' => &$this,'errors' => $errors_html,));
    }
        
    private function _sec_id($section_id ='',$parent_id = false){
        return ($parent_id === false) ? $section_id : $parent_id.'/'.$section_id;
    }
    
    public function is_single_page(){
        return isset($this->settings['is_single_page']) ? $this->settings['is_single_page'] : true;
    }
    
    public function current_section($type = false){
        return ($type == 'parent') ? $this->parent_sectionid : $this->current_section;
    }
    
    public function is_sticky_header(){
        return (isset($this->settings['is_sticky_header']) && $this->settings['is_sticky_header'] === true) ? 'wpsf-sticky-header' : false;
    }
    
    public function has_nav() {
		return (count ( $this->options ) <= 1) ? 'wpsf-show-all' : "";
	}
    
    public function theme() {
		return isset ( $this->settings ['style'] ) ? $this->settings ['style'] : 'modern';
	}
    
    public function is_modern(){
        return ($this->theme() === 'modern' ) ? true : false;
    }
    
    public function is_simple(){
        return ($this->theme() === 'simple' ) ? true : false;
    }
    
    private function get_icon($data){
        return (isset($data['icon']) && !empty($data['icon'])) ?  '<i class="wpsf-icon '.$data['icon'].'"></i>' : '';
    }
    
    private function get_title($data){
        return (isset($data['title']) && !empty($this->has_nav())) ? '<div class="wpsf-section-title"><h3>'.$data['title'].'</h3></div>' : '';
    }
    
    private function is_page_active($status){
        return ($status === true) ? 'style="display:block";' : '';
    }
    
    public function get_settings_buttons() {
		$this->catch_output ('start');
        if($this->settings['buttons']['save'] !== false){
            $text = ($this->settings['buttons']['save'] === true) ? 'Save' : $this->settings['buttons']['save'];
            submit_button ( esc_html($text), 'primary wpsf-save', 'save', false, array ('data-save' => esc_html__ ( 'Saving...', 'wpsf-wp' )));    
        }
        
        if($this->settings['buttons']['restore'] !== false){
            $text = ($this->settings['buttons']['restore'] === true) ? 'Save' : $this->settings['buttons']['restore'];
            submit_button ( esc_html($text), 'secondary wpsf-restore wpsf-reset-confirm', $this->unique . '[reset]', false );
        }
        
        if($this->settings['buttons']['reset'] !== false){
            $text = ($this->settings['buttons']['reset'] === true ) ? "Reset All Options" : $this->settings['buttons']['reset'];
            submit_button ( $text, 'secondary wpsf-restore wpsf-warning-primary wpsf-reset-confirm', $this->unique . '[resetall]', false );
        }
        
		return $this->catch_output ( false );
	}
    
    public function get_tab_url($section = '',$parent = ''){
        if($this->is_single_page() !== true){
            $data = array();
            if(!empty($section)){
                $data['wpsf-section-id'] = $section;
            }
            if(!empty($parent)){
                $data['wpsf-parent-section-id'] = $parent;
            }
            $url = remove_query_arg(array('wpsf-section-id','wpsf-parent-section-id'));
            return add_query_arg($data,$url);            
        }
        return '#';
    }
    
    private function render_fields($data){
        if(isset($data['callback_hook'])){
            $this->catch_output();
            do_action($data['callback_hook'],$this);
            return $this->catch_output('end');
        } else if(isset($data['fields'])){
            $r = '';
            foreach($data['fields'] as $field){
                $r .= $this->field_callback($field);
            }
            return $r;
        }
        return false;
    }

    public function render_html(){
        $main_menu = '';
        $page_html = '';
        $sub_nav = '';
        if($this->is_modern()){
            $main_menu =  '<div class="wpsf-nav"> <ul>';    
        }
        
        foreach ( $this->options as $page_id => $page ) {
            $page_icon = $this->get_icon($page);
            $is_page_active = ($this->current_section('parent') == $page['name']) ? true : false;
            $is_child_active = false;
            $is_callback = false;
            if($this->is_simple()){
                $sub_navs = $l1_html = '';
            }
            
            if(isset($page['sections'])){
                $is_child_active = (isset($page['sections'][$this->current_section()]) && $is_page_active === true) ? true : false;
                
                if($this->is_modern()){
                    $active_li = ($is_page_active === true && $is_child_active === true ) ? ' wpsf-tab-active ' : '';
                    $main_menu .= '<li class="wpsf-sub '.$active_li.'"> <a href="#" class="wpsf-arrow">'.$page_icon.$page['title'].'</a> <ul '.$this->is_page_active(($is_page_active === true && $is_child_active === true )).'>';
                }
                
                if($this->is_simple()){
                    $sub_nav = array();
                    $inner_html = '';
                    $first_section = current($page['sections']);
                    $first_section = $first_section['name'];
                }
                
                foreach($page['sections'] as $section_id => $section){
                    $is_section_active = ($is_child_active === true && $this->current_section() == $section['name']) ? true : false;
                    $sec_icon = $this->get_icon($section);
                    $fields = $this->render_fields($section);

                    if($this->is_simple()){
                        $active_class = ($is_section_active === true) ? 'current' : '';
                        $sub_nav[] = '<li><a  href="#" data-parent-section="'.esc_attr($page['name']).'" data-section="'.esc_attr($section['name']).'" class="'.$active_class.'">'.$sec_icon.' '.$section['title'].'</a>';
                        $inner_html .= '<div id="wpsf-tab-'.$page['name'].'-'.$section['name'].'" class="wpsf-section" '.$this->is_page_active($is_section_active).'>'.$this->get_title($section).$fields.'</div>';
                    }
                    
                    if($this->is_modern()){
                        $active_class = ($is_section_active === true) ? ' wpsf-section-active ' : '';
                        $main_menu .= '<li><a class="'.$active_class.'" href="'.$this->get_tab_url($section['name'],$page['name']).'" data-parent-section="'.$page['name'].'" data-section="'.$section['name'].'">'.$sec_icon.$section['title'] . '</a></li>';
                        $page_html .= '<div '.$this->is_page_active($is_section_active).' id="wpsf-tab-'.$page['name'].'-'.$section['name'].'" class="wpsf-section">'.$this->get_title($section).$fields.'</div>';
                    }
                }
                
                if($this->is_simple()){
                    if($is_child_active === false){
                        $sub_nav[0] =  str_replace('class=""','class="current"',$sub_nav[0]);
                        $inner_html = str_replace('id="wpsf-tab-'.$page['name'].'-'.$first_section.'"','id="wpsf-tab-'.$page['name'].'-'.$first_section.'" style="display:block"',$inner_html);
                    }
                    
                    $sub_nav = implode(' | </li>',$sub_nav);
                    $sub_nav = '<ul class="wpsf-submenus subsubsub" id="wpsf-tab-'.$page['name'].'" >'.$sub_nav.'</ul>';  
                    $l1_html = $inner_html;
                    $sub_navs = $sub_nav;
                }
                
                if($this->is_modern()){
                    $main_menu .= '</ul></li>';
                }
                
            } else if(isset($page['callback_hook'])){
                $is_callback = true;
                $fields = $this->render_fields($page);

                if($this->is_simple()){
                    $l1_html = $fields;
                }
                
                if($this->is_modern()){
                    $active_class = ($is_page_active === true) ? ' wpsf-section-active ' : '';
                    $main_menu .= '<li><a class="'.$active_class.'" href="'.$this->get_tab_url($page['name']).'" data-section="'.$page['name'].'" >'.$page_icon.$page['title'].'</a></li>';
                    
                    $page_html .= '<div '.$this->is_page_active($is_page_active).' id="wpsf-tab-'.$page['name'].'" class="wpsf-section">'.$this->get_title($page).$fields.'</div>';
                }
                
            } else if(isset($page['fields'])){
                $fields = $this->render_fields($page);
                
                if($this->is_simple()){
                    $sub_navs = '<span class="wpsf-submenus" id="wpsf-tab-'.$page['name'].'" data-section="'.esc_attr($page['name']).'">'.$page['title'].'</span>';
                    $l1_html .= $fields;
                }
                
                if($this->is_modern()){
                    $active_class = ($is_page_active === true) ? ' wpsf-section-active ' : '';
                    $main_menu .= '<li><a class="'.$active_class.'" href="'.$this->get_tab_url($page['name']).'" data-section="'.$page['name'].'" >'.$page_icon.$page['title'].'</a></li>';
                    $page_html .= '<div '.$this->is_page_active($is_page_active).' id="wpsf-tab-'.$page['name'].'" class="wpsf-section">'.$this->get_title($page).$fields.'</div>';
                }
            } else {
                if($this->is_modern()){
                    $main_menu .= '<li><div class="wpsf-seperator">'.$page_icon.$page['title'].'</div></li>';
                }
            }
            
            if($this->is_simple()){
                if(!isset($page['fields']) && ! isset($page['sections']) && ! isset($page['callback_hook'])){
                    continue;
                }
                $is_active = ($is_page_active === true) ? ' nav-tab-active ' : '';
                $main_menu .= '<a href="'.$this->get_tab_url($page['name']).'" data-section="'.$page['name'].'" class="nav-tab '.$is_active.'">'.$page_icon.' '.$page['title'].'</a>';
                $page_active = ($is_page_active === true) ? '' : ' style="display:none;" ';
                
                
                $page_html .= '<div id="wpsf-tab-'.$page['name'].'" '.$page_active.'>';
                if($is_callback === true) {
                    $page_html .= $l1_html;
                } else {
                    $page_html .= '<div class="postbox"><h2 class="wpsf-subnav-container hndle">'.$sub_navs.'</h2><div class="inside">'.$l1_html.'</div></div>';
                }                
                $page_html .=  '</div>';
            }
        }
        
        if($this->is_modern()){
            $main_menu .=  '</ul></div>';    
        }
        
        $this->main_menu = $main_menu;
        $this->page_html = $page_html;
        return $main_menu;
    }
    
    public function html_nav_bar(){
        return $this->main_menu;
    }
    
    public function html_content(){
        return $this->page_html;
    }
}