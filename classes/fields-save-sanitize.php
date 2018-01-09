<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 05-01-2018
 * Time: 07:14 AM
 */

if(!defined("ABSPATH")){die;}

class WPSFramework_Fields_Save_Sanitize extends WPSFramework_Abstract {
    public static $_instance = null;
    public $errors = array();
    public $fields = array();
    public $db_values = array();
    public $posted = array();
    public $cur_posted = array();
    public $is_settings = false;
    public $return_values = array();
    public $field_ids = array();

    private function _error($message , $type = 'error' , $id = 'global') {
        return array( 'setting' => 'wpsf-errors' , 'code' => $id , 'message' => $message , 'type' => $type );
    }

    public static function instance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {}

    public function general_save_handler($options = array(),$fields = array()){
        $this->is_settings = false;
        $this->return_values = $options;
        $this->return_values = $this->loop_fields($fields,$this->return_values);
        return $this->return_values;
    }

    public function handle_settings_page($options = array(),$fields = array()){
        $this->is_settings = true;
        $defaults = array(
            'is_single_page' => false,
            'current_section_id' => false,
            'current_parent_id' => false,
            'db_key' => false,
            'posted_values' => array(),
        );

        $options = wp_parse_args($options,$defaults);
        $csid = $options['current_section_id'];
        $cpid = $options['current_parent_id'];
        $isp = $options['is_single_page'];
        $this->is_single_page = $isp;
        $this->db_values = get_option($options['db_key'],true);
        $this->db_values = ($this->db_values === true || empty($this->db_values)) ? array() : $this->db_values;
        $this->posted = $options['posted_values'];
        $this->return_values = $options['posted_values'];

        $this->fields = $fields;


        foreach($this->fields as $section){
            if($this->is_single_page === false && ($csid != $section['name'] && $cpid != $section['page_id'])){
                continue;
            }
            $this->return_values = $this->loop_fields($section,$this->return_values,$this->db_values);
        }

        if($this->is_single_page === false){
            $this->return_values = array_merge($this->db_values,$this->return_values);
        }
        return $this->return_values;
    }

    public function loop_fields($is_current_fields = false,$values = array(),$db_val = array(),$validate_arr = true){
        $fields = ($is_current_fields === false) ? $this->fields : $is_current_fields;

        if(isset($fields['fields'])){
            foreach($fields['fields'] as $field){
                if(isset($field['type']) && !isset($field['multilang']) && isset($field['id'])){
                    $value = isset($values[$field['id']]) ? $values[$field['id']] : $values;
                    $ex_val = isset($db_val[$field['id']]) ? $db_val[$field['id']] : null;
                    $field['pre_value'] = $ex_val;
                    $value = $this->_handle_single_field($field,$value,$fields);

                    if(isset($field['fields'])){
                        $value = $this->loop_fields($field,$value,$ex_val,false);
                    }

                    $values = $this->_manage_data($values,$value,$field['id']);

                    if($this->is_settings === true && $this->is_single_page === false && $validate_arr === true ){
                        if(!isset($this->posted[$field['id']]) && isset($values[$field['id']])){
                            $values[$field['id']] = '';
                        }
                    }
                }
            }
        } else {
            foreach($fields as $section){
                if(isset($section['fields'])){
                    $values = $this->loop_fields($section,$values,$db_val);
                }
            }
        }

        return $values;
    }

    public function _handle_single_field($field,$values =array(),$fields){
        $value = isset($values[$field['id']]) ? $values[$field['id']] : $values;
        $value = $this->_sanitize_field($field,$value,$fields);
        $value = $this->_validate_field($field,$value,$fields);
        $values = $this->_manage_data($values,$value,$field['id']);
        return $values;
    }

    private function _manage_data($array_1,$data,$id){
        if(isset($array_1[$id])) {
            $array_1[$id] = $data;
        } else {
            $array_1 = $data;
        }
        return $array_1;
    }

    public function _sanitize_field($field,$value,$fields) {
        $type = $field['type'];

        if ( isset($field['sanitize']) ) {
            $type = ( $field['sanitize'] !== FALSE ) ? $field['sanitize'] : FALSE;
        }

        if ( $type !== FALSE && has_filter('wpsf_sanitize_' . $type) ) {
            $value = apply_filters('wpsf_sanitize_' . $type ,$value,$field,$fields);
        }

        return $value;
    }

    public function _validate_field($field,$value,$fields){
        if(isset($field['validate']) && has_filter('wpsf_validate_'.$field['validate'])){
            $validate = apply_filters('wpsf_validate_'.$field['validate'],$value,$field,$fields);
            if(!empty($validate)){
                $fid = isset($field['error_id']) ? $field['error_id'] : $field['id'];
                $this->errors[] = $this->_error($validate,'error',$fid);

                if(isset($field['pre_value'])){
                    return $field['pre_value'];
                }

                if(isset($field['default'])){
                    return $field['default'];
                }
                return FALSE;
            }
        }
        return $value;
    }

    public function get_errors(){
        $errors = $this->errors;
        $this->errors = array();
        return $errors;
    }
}