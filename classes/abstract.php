<?php
/*-------------------------------------------------------------------------------------------------
 - This file is part of the WPSF package.                                                         -
 - This package is Open Source Software. For the full copyright and license                       -
 - information, please view the LICENSE file which was distributed with this                      -
 - source code.                                                                                   -
 -                                                                                                -
 - @package    WPSF                                                                               -
 - @author     Varun Sridharan <varunsridharan23@gmail.com>                                       -
 -------------------------------------------------------------------------------------------------*/

if( ! defined('ABSPATH') ) {
    die ();
} // Cannot access pages directly.

/**
 *
 * Abstract Class
 * A helper class for action and filter hooks
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
abstract class WPSFramework_Abstract {
    public function __construct() {
    }

    public function addAction($hook, $function_to_add, $priority = 30, $accepted_args = 1) {
        add_action($hook, array(
            &$this,
            $function_to_add,
        ), $priority, $accepted_args);
    }

    public function addFilter($tag, $function_to_add, $priority = 30, $accepted_args = 1) {
        add_action($tag, array(
            &$this,
            $function_to_add,
        ), $priority, $accepted_args);
    }

    public function load_template($template_name, $args = array()) {
        wpsf_template($this->override_location, $template_name, $args);
    }

    protected function map_error_id($array = array(), $parent_id = '') {
        $s = empty($array) ? $this->options : $array;

        if( isset($s['sections']) ) {
            foreach( $s['sections'] as $b => $a ) {
                if( isset($a['fields']) ) {

                    $s['sections'][$b] = $this->map_error_id($a, $a['name']);
                }
            }
        } else if( isset($s['fields']) ) {
            foreach( $s['fields'] as $f => $e ) {
                $field_id = isset($e['id']) ? $e['id'] : '';
                $pid = $parent_id . '_' . $field_id;
                $s['fields'][$f]['error_id'] = $pid;

                if( isset($e['fields']) ) {
                    $s['fields'][$f] = $this->map_error_id($e, $pid);
                }
            }
        } else {
            foreach( $s as $i => $v ) {
                if( isset($v['fields']) || isset($v['sections']) ) {
                    $s[$i] = $this->map_error_id($v, '');
                }
            }
        }
        return $s;
    }

    protected function get_field_values($field,$values){
        $value = (isset($field['id']) && isset($values[$field['id']])) ? $values[$field['id']] : '';
        $value = (empty($value) && isset($field['default'])) ? $field['default'] : $value;

        if(in_array($field['type'],array('fieldset','accordion'))&& (isset($field['un_array']) && $field['un_array'] === true)){
            $value = array();
            foreach($field['fields'] as $_field){
                $value[$_field['id']] = $this->get_field_values($_field,$values);
            }
        } else if($field['type'] == 'tab'){
            $value = array();
            $_tab_values = array();
            $_tab_vals = (isset($field['id']) && isset($values[$field['id']])) ? $values[$field['id']] : '';
            if((isset($field['un_array']) && $field['un_array'] === true)){
                $_tab_vals = $values;
            }

            foreach($field['sections'] as $section){
                $_section_vals = (isset($section['name']) && isset($_tab_vals[$section['name']])) ? $_tab_vals[$section['name']] : $_tab_vals;

                $_section_values = array();
                foreach($section['fields'] as $_field){
                    $_section_values[$_field['id']] = $this->get_field_values($_field,$_section_vals);
                }

                if(isset($section['un_array']) && $section['un_array'] === true){
                    $_tab_values = array_merge($_section_values,$_tab_values);
                } else {
                    $_tab_values[$section['name']] = $_section_values;
                }
            }

            if(isset($field['un_array']) && $field['un_array'] === true){
                $value = $_tab_values;
            } else {
                $value[$field['id']] = $_tab_values;
            }
        }
        //var_dump($value);
        return $value;
    }

    protected function _get_field_values($field,$values){
        $value = ( isset ($field ['id']) && isset ($values[$field ['id']]) ) ? $values[$field ['id']] : '';
        if( in_array($field['type'],array('fieldset','accordion')) && (isset($field['un_array']) && $field['un_array'] === true) ) {
            $value = array();
            foreach( $field['fields'] as $f ) {
                $value[$f['id']] = ( isset($values[$f['id']]) ) ? $values[$f['id']] : '';
            }
        } else if( in_array($field['type'],array('tab')) && (isset($field['un_array']) && $field['un_array'] === true) ) {
            $value = array();
            foreach( $field['sections'] as $section ) {
                foreach( $section['fields'] as $f ) {
                    if(isset($section['un_array']) && $section['un_array'] === true){
                        $value[$f['id']] = ( isset($values[$f['id']]) ) ? $values[$f['id']] : '';
                    } else {
                        if(!isset($value[$section['id']])){
                            $value[$section['id']] = array();
                        }
                        $value[$section['id']][$f['id']] = ( isset($values[$section['id']][$f['id']]) ) ? $values[$section['id']][$f['id']] : '';
                    }
                }


            }
        }
        var_dump($field);
        return $value;
    }

    protected function catch_output($status = 'start') {
        $data = '';
        if( $status == 'start' ) {
            ob_start();
        } else {
            $data = ob_get_clean();
            ob_flush();
        }

        return $data;
    }

    protected function get_cache_key($data = array()) {
        if( empty($data) ) {
            $data = $this->settings;
        }

        if( isset($data['uid']) ) {
            return $data['uid'];
        } else if( isset($data['id']) ) {
            return $data['id'];
        } else if( isset($data['title']) ) {
            return sanitize_title($data['title']);
        } else if( isset($data['menu_title']) ) {
            return sanitize_title($data['menu_title']);
        }
        return FALSE;
    }
}
