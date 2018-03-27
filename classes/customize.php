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
	die;
} // Cannot access pages directly.

/**
 *
 * Customize Class
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 */
class WPSFramework_Customize extends WPSFramework_Abstract {
	/**
	 * sections
	 *
	 * @access public
	 * @var array
	 *
	 */
	public $options = array();
	/**
	 * panel priority
	 *
	 * @access public
	 * @var bool
	 *
	 */
	public $priority = 1;
	/**
	 * unique
	 *
	 * @var null
	 */
	public $unique = null;
	/**
	 * type
	 *
	 * @var string
	 */
	protected $type = 'customize';

	/**
	 * WPSFramework_Customize constructor.
	 *
	 * @param $options
	 * @param $db_slug
	 */
	public function __construct( $options, $db_slug ) {
		$this->unique  = $db_slug;
		$this->options = apply_filters( 'wpsf_customize_options', $options );

		do_action( 'wpsf_customize_options_config', $this->options );

		if ( ! empty( $this->options ) ) {
			$this->addAction( 'customize_register', 'customize_register' );
			add_action( 'customize_controls_enqueue_scripts', 'wpsf_load_customizer_assets' );
		}
	}

	/**
	 * @param $wp_customize
	 */
	public function customize_register( $wp_customize ) {
		wpsf_locate_template( 'functions/customize.php' );
		do_action( 'wpsf_customize_register', $wp_customize );
		$panel_priority = 1;

		foreach ( $this->options as $value ) {
			$this->priority = $panel_priority;

			if ( isset( $value['sections'] ) ) {
				$wp_customize->add_panel( $value['name'], array(
					'title'       => $value['title'],
					'priority'    => ( isset( $value['priority'] ) ) ? $value['priority'] : $panel_priority,
					'description' => ( isset( $value['description'] ) ) ? $value['description'] : '',
				) );

				$this->add_section( $wp_customize, $value, $value['name'] );
			} else {
				$this->add_section( $wp_customize, $value );
			}

			$panel_priority++;
		}
	}

	/**
	 * @param      $wp_customize
	 * @param      $value
	 * @param bool $panel
	 */
	public function add_section( $wp_customize, $value, $panel = false ) {
		$section_priority = ( $panel ) ? 1 : $this->priority;
		$sections         = ( $panel ) ? $value['sections'] : array( 'sections' => $value );

		foreach ( $sections as $section ) {
			$wp_customize->add_section( $section['name'], array(
				'title'       => $section['title'],
				'priority'    => ( isset( $section['priority'] ) ) ? $section['priority'] : $section_priority,
				'description' => ( isset( $section['description'] ) ) ? $section['description'] : '',
				'panel'       => ( $panel ) ? $panel : '',
			) );

			$setting_priority = 1;

			foreach ( $section['settings'] as $setting ) {
				$setting_name = $this->unique . '[' . $setting['name'] . ']';

				$wp_customize->add_setting( $setting_name, wp_parse_args( $setting, array(
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'wpsf_sanitize_clean',
				) ) );

				$control_args = wp_parse_args( $setting['control'], array(
					'unique'   => $this->unique,
					'section'  => $section['name'],
					'settings' => $setting_name,
					'priority' => $setting_priority,
				) );

				if ( 'wpsf_field' === $control_args['type'] ) {
					$call_class = 'WP_Customize_' . $control_args['type'] . '_Control';
					$wp_customize->add_control( new $call_class( $wp_customize, $setting['name'], $control_args ) );

				} else {
					$wp_controls = array( 'color', 'upload', 'image', 'media' );
					$call_class  = 'WP_Customize_' . ucfirst( $control_args['type'] ) . '_Control';
					if ( in_array( $control_args['type'], $wp_controls ) && class_exists( $call_class ) ) {
						$wp_customize->add_control( new $call_class( $wp_customize, $setting['name'], $control_args ) );
					} else {
						$wp_customize->add_control( $setting['name'], $control_args );
					}
				}
				$setting_priority++;
			}
			$section_priority++;
		}
	}
}
