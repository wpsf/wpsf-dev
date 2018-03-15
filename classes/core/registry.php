<?php
/**
 * Created by PhpStorm.
 * Project : wpsf
 * User: varun
 * Date: 12-03-2018
 * Time: 04:48 PM
 */

class WPSFramework_Registry {
    private static $_instance  = NULL;
    private static $_instances = array();

    /**
     * @return \WPSFramework_Registry
     */
    public static function instance() {
        if( self::$_instance === NULL ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param \WPSFramework_Abstract $instance
     */
    public function add(WPSFramework_Abstract &$instance) {
        if( ! isset(self::$_instances[$instance->id()]) ) {
            self::$_instances[$instance->id()] = $instance;
        }
    }

    /**
     * @param string $plugin_id
     *
     * @return bool|mixed
     */
    public function get($plugin_id = '') {
        return ( isset(self::$_instances[$plugin_id]) ) ? self::$_instances[$plugin_id] : FALSE;
    }

    /**
     * @return array
     */
    public function all() {
        return self::$_instances;
    }
}

return WPSFramework_Registry::instance();