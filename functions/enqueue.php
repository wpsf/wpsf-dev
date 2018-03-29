<?php
/*-------------------------------------------------------------------------------------------------
- This file is part of the WPSF package.                                                          -
- This package is Open Source Software. For the full copyright and license                        -
- information, please view the LICENSE file which was distributed with this                       -
- source code.                                                                                    -
-                                                                                                 -
- @package    WPSF                                                                                -
- @author     Varun Sridharan <varunsridharan23@gmail.com>                                        -
 -------------------------------------------------------------------------------------------------*/


if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'WPSFramework_Assets' ) ) {
	/**
	 * Class WPSFramework_Assets
	 */
	final class WPSFramework_Assets {
		/**
		 * _instance
		 *
		 * @var null
		 */
		private static $_instance = null;

		/**
		 * scripts
		 *
		 * @var array
		 */
		public $scripts = array();

		/**
		 * styles
		 *
		 * @var array
		 */
		public $styles = array();

		/**
		 * WPSFramework_Assets constructor.
		 */
		public function __construct() {
			$this->init_array();
			add_action( 'admin_enqueue_scripts', array( &$this, 'register_assets' ) );
		}

		/**
		 * Stores All default WPSF Assets Into A Array
		 *
		 * @uses $this->styles
		 * @uses $this->scripts
		 */
		public function init_array() {
			$this->styles['wpsf-fontawesome']   = array(
				self::is_debug( WPSF_URI . '/assets/css/font-awesome.css', 'css' ),
				array(),
				'4.7.0',
			);
			$this->styles['wpsf-plugins']       = array(
				self::is_debug( WPSF_URI . '/assets/css/wpsf-plugins.css', 'css' ),
				array(),
				WPSF_VERSION,
			);
			$this->styles['wpsf-framework']     = array(
				self::is_debug( WPSF_URI . '/assets/css/wpsf-framework.css', 'css' ),
				array(),
				WPSF_VERSION,
			);
			$this->styles['wpsf-framework-rtl'] = array(
				self::is_debug( WPSF_URI . '/assets/css/wpsf-framework-rtl.css', 'css' ),
				array(),
				WPSF_VERSION,
			);
			$this->styles['wpsf-vc']            = array(
				self::is_debug( WPSF_URI . '/assets/css/wpsf-vc.css', 'css' ),
				array( 'wpsf-framework' ),
				WPSF_VERSION,
			);

			$this->scripts['wpsf-plugins']    = array(
				self::is_debug( WPSF_URI . '/assets/js/wpsf-plugins.js', 'js' ),
				null,
				WPSF_VERSION,
				true,
			);
			$this->scripts['wpsf-framework']  = array(
				self::is_debug( WPSF_URI . '/assets/js/wpsf-framework.js', 'js' ),
				null,
				WPSF_VERSION,
				true,
			);
			$this->scripts['wpsf-vc']         = array(
				self::is_debug( WPSF_URI . '/assets/js/wpsf-vc.js', 'js' ),
				array( 'wpsf-framework' ),
				WPSF_VERSION,
				true,
			);
			$this->scripts['wpsf-quick-edit'] = array(
				self::is_debug( WPSF_URI . '/assets/js/wpsf-quick-edit.js', 'js' ),
				null,
				WPSF_VERSION,
				true,
			);
			$this->scripts['wp-js-hooks']     = array(
				WPSF_URI . '/assets/vendors/wp-js-hooks/wp-js-hooks.min.js',
				array(),
				'1.0',
			);

			$this->styles['wpsf-animatecss'] = array(
				self::is_debug( WPSF_URI . '/assets/vendors/animatecss/animate.css', 'css' ),
				array(),
				'3.5.2',
			);
			$this->styles['wpsf-bootstrap']  = array(
				self::is_debug( WPSF_URI . '/assets/vendors/bootstrap/bootstrap.css', 'css' ),
				array(),
				'3.3.7',
			);
			$this->styles['wpsf-chosen']     = array(
				self::is_debug( WPSF_URI . '/assets/vendors/chosen/chosen.css', 'css' ),
				array(),
				WPSF_VERSION,
			);
			$this->styles['wpsf-flatpickr']  = array(
				self::is_debug( WPSF_URI . '/assets/vendors/flatpickr/flatpickr.css', 'css' ),
				array(),
				'4.3.2',
			);
			$this->styles['wpsf-select2']    = array(
				self::is_debug( WPSF_URI . '/assets/vendors/select2/select2.css', 'css' ),
				array(),
				'4.0.5',
			);

			$this->scripts['wpsf-actual']            = array(
				self::is_debug( WPSF_URI . '/assets/vendors/actual/jquery.actual.js', 'js' ),
				array(),
				'1.0',
				true,
			);
			$this->scripts['wpsf-bootstrap']         = array(
				self::is_debug( WPSF_URI . '/assets/vendors/bootstrap/bootstrap.js', 'js' ),
				array(),
				'3.3.7',
				true,
			);
			$this->scripts['wpsf-chosen']            = array(
				self::is_debug( WPSF_URI . '/assets/vendors/chosen/chosen.js', 'js' ),
				array(),
				WPSF_VERSION,
				true,
			);
			$this->scripts['wpsf-flatpickr']         = array(
				self::is_debug( WPSF_URI . '/assets/vendors/flatpickr/flatpickr.js', 'js' ),
				array(),
				'4.3.2',
				true,
			);
			$this->scripts['wpsf-interdependencies'] = array(
				self::is_debug( WPSF_URI . '/assets/vendors/interdependencies/jquery.interdependencies.js', 'js' ),
				array(),
				WPSF_VERSION,
				true,
			);
			$this->scripts['wpsf-select2']           = array(
				self::is_debug( WPSF_URI . '/assets/vendors/select2/select2.full.js', 'js' ),
				array(),
				'4.0.5',
				true,
			);
		}

		/**
		 * Creates A Instance for WPSFramework_Assets.
		 *
		 * @return null|\WPSFramework_Assets
		 * @static
		 */
		public static function instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * Loads All Default Styles & Assets.
		 */
		public function render_framework_style_scripts() {
			wp_enqueue_media();

			wp_enqueue_script( 'wp-js-hooks' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-accordion' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'wpsf-plugins' );
			wp_enqueue_script( 'wpsf-framework' );
			wp_enqueue_script( 'wplink' );

			wp_enqueue_style( 'editor-buttons' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_style( 'jquery-datepicker' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'font-awesome' );
			wp_enqueue_style( 'animate-css' );
			wp_enqueue_style( 'wpsf-fontawesome' );
			wp_enqueue_style( 'wpsf-plugins' );
			wp_enqueue_style( 'wpsf-framework' );
		}

		/**
		 * Registers Assets With WordPress
		 */
		public function register_assets() {
			foreach ( $this->styles as $id => $file ) {
				wp_register_style( $id, $file[0], $file[1], $file[2], 'all' );
			}

			foreach ( $this->scripts as $iid => $ffile ) {
				wp_register_script( $iid, $ffile[0], $ffile[1], $ffile[2], true );
			}
		}

		/**
		 * Check if WP_DEBUG & SCRIPT_DEBUG Is enabled.
		 *
		 * @param string $file_name
		 * @param string $ext
		 *
		 * @return mixed|string
		 * @static
		 */
		private static function is_debug( $file_name = '', $ext = 'css' ) {
			$search  = '.' . $ext;
			$replace = '.min.' . $ext;
			if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
				return $file_name;
			}
			return str_replace( $search, $replace, $file_name );
		}
	}
}

if ( ! function_exists( 'wpsf_assets' ) ) {
	/**
	 * @return null|\WPSFramework_Assets
	 */
	function wpsf_assets() {
		return WPSFramework_Assets::instance();
	}
}

if ( ! function_exists( 'wpsf_load_customizer_assets' ) ) {
	/**
	 * Loads WPSF Assets on customizer page.
	 */
	function wpsf_load_customizer_assets() {
		wpsf_assets()->render_framework_style_scripts();
	}


	if ( has_action( 'wpsf_widgets' ) ) {
		add_action( 'admin_print_styles-widgets.php', 'wpsf_load_customizer_assets' );
	}
}

return wpsf_assets();
