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
}

if( ! function_exists("wpsf_load_fields_styles") ) {
    function wpsf_load_fields_styles() {
        return;
        //wp_enqueue_script('jquery-ui-dialog');
        //wp_enqueue_script('jquery-ui-sortable');
        //wp_enqueue_script('jquery-ui-accordion');
        //wp_enqueue_script('wp-color-picker');
        //wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('wpsf-plugins');
        wp_enqueue_script('wpsf-fields');
        wp_enqueue_script('wpsf-framework');

        wp_enqueue_style('editor-buttons');
        wp_enqueue_script('wplink');
        //wp_enqueue_style('wp-jquery-ui-dialog');
        //wp_enqueue_style('jquery-datepicker');
        //wp_enqueue_style('wp-color-picker');
        //wp_enqueue_style('font-awesome');
        //wp_enqueue_style('animate-css');
        ///wp_enqueue_style('wpsf-plugins');
        //wp_enqueue_style('wpsf-framework');

        if( is_rtl() ) {
            wp_enqueue_style('wpsf-framework-rtl');
        }
    }
}

if( ! function_exists('wpsf_admin_enqueue_scripts') ) {
    function wpsf_admin_enqueue_scripts() {
        $css_files = array(
            'wpsf-plugins'       => array( '/assets/css/wpsf-plugins.min.css', array(), '1.0.0', 'all' ),
            'wpsf-framework'     => array( '/assets/css/wpsf-framework.min.css', array(), '1.0.0', 'all', ),
            'font-awesome'       => array( '/assets/css/font-awesome.min.css', array(), '4.7.0', 'all', ),
            'wpsf-framework-rtl' => array( '/assets/css/wpsf-framework-rtl.min.css', array(), '1.0.0', 'all', ),
            'animate-css'        => array( '/assets/vendors/animatecss/animate.min.css', array(), '3.5.2', 'all', ),
        );

        $js_files = array(
            'wpsf-plugins'    => array( '/assets/js/wpsf-plugins.min.js', array(), '1.0.0', FALSE, ),
            'wpsf-framework'  => array( '/assets/js/wpsf-framework.min.js', array( 'wpsf-plugins' ), '1.0.0', FALSE, ),
            'wpsf-quick-edit' => array( '/assets/js/wpsf-quick-edit.min.js', NULL, '1.0', '', FALSE, ),
        );

        foreach( $css_files as $id => $file ) {
            wp_register_style($id, WPSF_URI . $file[0], $file[1], $file[2], $file[3]);
        }

        foreach( $js_files as $id => $file ) {
            wp_register_script($id, WPSF_URI . $file[0], $file[1], $file[2], TRUE);
        }

        if( has_action('wpsf_widgets') ) {
            add_action('admin_print_styles-widgets.php', 'wpsf_load_fields_styles');
        }
    }

    //add_action('admin_enqueue_scripts', 'wpsf_admin_enqueue_scripts', 1);
}

final Class WPSFramework_Assets {
    private static $_instance = NULL;
    public $scripts = array();
    public $styles = array();
    private $load_assets = array();
    private $page_hook = NULL;

    public function __construct() {
        $this->init_array();
        add_action('admin_enqueue_scripts', array( &$this, 'register_assets' ));
    }

    public function init_array() {
        $this->styles['fontawesome'] = array( WPSF_URI . '/assets/css/font-awesome.css', array(), '4.7.0' );
        $this->styles['wpsf-plugins'] = array( WPSF_URI . '/assets/css/wpsf-plugins.css', array(), WPSF_VERSION );
        $this->styles['wpsf-framework'] = array( WPSF_URI . '/assets/css/wpsf-framework.css', array(), WPSF_VERSION );
        $this->styles['wpsf-framework-rtl'] = array(
            WPSF_URI . '/assets/css/wpsf-framework-rtl.css',
            array(),
            WPSF_VERSION,
        );

        $this->scripts['wpsf-plugins'] = array( WPSF_URI . '/assets/js/wpsf-plugins.js', NULL, WPSF_VERSION, TRUE );
        $this->scripts['wpsf-framework'] = array(
            WPSF_URI . '/assets/js/wpsf-framework.js',
            NULL,
            WPSF_VERSION,
            TRUE,
        );
        $this->scripts['wpsf-quick-edit'] = array(
            WPSF_URI . '/assets/js/wpsf-quick-edit.js',
            NULL,
            WPSF_VERSION,
            TRUE,
        );

        $this->styles['animatecss'] = array( WPSF_URI . '/assets/vendors/animatecss/animate.css', array(), '3.5.2' );
        $this->styles['wpsf-bootstrap'] = array(
            WPSF_URI . '/assets/vendors/bootstrap/bootstrap.css',
            array(),
            '3.3.7',
        );
        $this->styles['chosen'] = array( WPSF_URI . '/assets/vendors/chosen/chosen.css', array(), WPSF_VERSION, );
        $this->styles['flatpickr'] = array( WPSF_URI . '/assets/vendors/flatpickr/flatpickr.css', array(), '4.3.2' );
        $this->styles['select2'] = array( WPSF_URI . '/assets/vendors/select2/select2.css', array(), '4.0.5' );

        $this->scripts['wpsf-actual'] = array(
            WPSF_URI . '/assets/vendors/actual/jquery.actual.js',
            array(),
            '1.0',
            TRUE,
        );
        $this->scripts['wpsf-bootstrap'] = array(
            WPSF_URI . '/assets/vendors/bootstrap/bootstrap.js',
            array(),
            '3.3.7',
            TRUE,
        );
        $this->scripts['chosen'] = array( WPSF_URI . '/assets/vendors/chosen/chosen.js', array(), WPSF_VERSION, TRUE );
        $this->scripts['flatpickr'] = array(
            WPSF_URI . '/assets/vendors/flatpickr/flatpickr.js',
            array(),
            '4.3.2',
            TRUE,
        );
        $this->scripts['interdependencies'] = array(
            WPSF_URI . '/assets/vendors/interdependencies/jquery.interdependencies.js',
            array(),
            WPSF_VERSION,
            TRUE,
        );
        $this->scripts['select2'] = array(
            WPSF_URI . '/assets/vendors/select2/select2.full.js',
            array(),
            '4.0.5',
            TRUE,
        );
    }

    public static function instance() {
        if( self::$_instance == NULL ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public static function render_framework_styles() {
        wp_enqueue_style('fontawesome');
        wp_enqueue_style('wpsf-plugins');
        wp_enqueue_style('wpsf-framework');
    }

    public function register_assets() {
        foreach( $this->styles as $id => $file ) {
            wp_register_style($id, self::is_debug($file[0], 'css'), $file[1], $file[2], 'all');
        }

        foreach( $this->scripts as $iid => $ffile ) {
            wp_register_script($iid, self::is_debug($ffile[0], 'js'), $ffile[1], $ffile[2], TRUE);
        }
    }

    private static function is_debug($file_name = '', $ext = 'css') {
        $search = '.' . $ext;
        $replace = '.min.' . $ext;
        if( ( defined('WP_DEBUG') && WP_DEBUG ) || ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ) {
            return $file_name;
        }
        return str_replace($search, $replace, $file_name);
    }

    public function hook($hook) {
        $this->page_hook = $hook;
        add_action('admin_print_footer_scripts-' . $hook, array( &$this, 'load_framework_assets' ));
    }

    public function load_framework_assets() {
        $this->load_required_assets();
        wp_enqueue_script('wpsf-plugins');
        wp_enqueue_script('wpsf-framework');
    }

    public function load_required_assets() {
        if( is_array($this->load_assets) && ! empty($this->load_assets) ) {
            foreach( $this->load_assets as $id ) {
                wp_enqueue_style($id);
                wp_enqueue_script($id);
            }
        }
    }

    public function load_dialog() {
        $this->add('jquery-ui-dialog');
        $this->add('wp-jquery-ui-dialog');
        $this->add('chosen');
    }

    public function add($slug = '') {
        if( ! isset($this->load_assets[$slug]) ) {
            $this->load_assets[$slug] = $slug;
            return TRUE;
        }
        return FALSE;
    }

    public function load_media() {
        wp_enqueue_media();
    }

    public function remove($slug = '') {
        if( ! isset($this->load_assets[$slug]) ) {
            unset($this->load_assets[$slug]);
            return TRUE;
        }
        return FALSE;
    }
}


if( ! function_exists('wpsf_assets') ) {
    function wpsf_assets() {
        return WPSFramework_Assets::instance();
    }
}

return wpsf_assets();