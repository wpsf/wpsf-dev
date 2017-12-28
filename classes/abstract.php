<?php
if (! defined ( 'ABSPATH' )) {
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
    public function __construct() {}

    public function addAction($hook, $function_to_add, $priority = 30, $accepted_args = 1) {
        add_action ( $hook, array (&$this,$function_to_add), $priority, $accepted_args );
    }

    public function addFilter($tag, $function_to_add, $priority = 30, $accepted_args = 1) {
        add_action ( $tag, array (&$this,$function_to_add ), $priority, $accepted_args );
    }

    protected function catch_output($status = 'start') {
        $data ='';
        if ($status == 'start') {
            ob_start ();
        } else{
            $data = ob_get_clean();
            ob_flush ();
        }

        return $data;
    }

    protected function get_cache_key($data = array()){
        if(empty($data)){
            $data = $this->settings;
        }

        if(isset($data['uid'])){
            return $data['uid'];
        } else if(isset($data['id'])){
            return $data['id'];
        } else if(isset($data['title'])){
            return sanitize_title($data['title']);
        } else if(isset($data['menu_title'])){
            return sanitize_title($data['menu_title']);
        }
        return false;
    }
    
    public function load_template($template_name,$args = array()){
        wpsf_template($this->override_location,$template_name,$args);
    }
}
