<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 07-02-2018
 * Time: 11:24 AM
 */

final class WPSFramework_Ajax extends WPSFramework_Abstract {
    private static $_instance = NULL;

    public function __construct() {
        add_action('wp_ajax_wpsf-ajax', array( &$this, 'handle_ajax' ));
        add_action('wp_ajax_nopriv_wpsf-ajax', array( &$this, 'handle_ajax' ));
    }

    public static function instance() {
        if( self::$_instance == NULL ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function handle_ajax() {
        if( isset($_REQUEST['wpsf-action']) ) {
            $action = $_REQUEST['wpsf-action'];
            if( method_exists($this, $action) ) {
                $this->$action();
            } else if( has_action('wpsf_ajax_' . $action) ) {
                do_action('wpsf_ajax_' . $action);
            }
        }

        wp_die();
    }

    public function query_select_data() {
        $options = array();
        $query_args = ( isset ($_REQUEST['query_args']) ) ? $_REQUEST['query_args'] : array();

        $data = WPSFramework_Query::query($_REQUEST['options'], $query_args, $_REQUEST['s']);
        echo wp_json_encode($data);
        wp_die();
    }
}

return WPSFramework_Ajax::instance();