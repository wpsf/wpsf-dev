<?php
/*
---------------------------------------------------------------------------------------------------
- This file is part of the WPSF package.                                                          -
- This package is Open Source Software. For the full copyright and license                        -
- information, please view the LICENSE file which was distributed with this                       -
- source code.                                                                                    -
-                                                                                                 -
- @package    WPSF                                                                                -
- @author     Varun Sridharan <varunsridharan23@gmail.com>                                        -
---------------------------------------------------------------------------------------------------
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 12-01-2018
 * Time: 07:48 AM
 */

class WPSFramework_Option_date_picker extends WPSFramework_Options {
	/**
	 * WPSFramework_Option_date_picker constructor.
	 *
	 * @param        $field
	 * @param string $value
	 * @param string $unique
	 */
	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {
		echo $this->element_before();
		$this->simple_datepicker( 'simple', $this->settings(), '' );
		echo $this->element_after();
	}

	/**
	 * @param string $type
	 * @param array  $extra_attrs
	 * @param string $title
	 */
	public function simple_datepicker( $type = 'simple', $extra_attrs = array(), $title = '' ) {
		$elem_args = array_filter( array(
			'id'         => $this->field['id'],
			'type'       => 'text',
			'class'      => 'wpsf-datepicker ',
			'wrap_class' => 'horizontal ',
			'title'      => $title,
			'pseudo'     => true,
			'attributes' => array_merge( array(
				'data-datepicker-type'  => $type,
				'data-datepicker-theme' => $this->get_theme(),
			), $extra_attrs ),
		) );
		echo $this->add_field( $elem_args, $this->element_value(), $this->unique );
	}

	public function get_theme() {
		return ( isset( $this->field['theme'] ) ) ? 'flatpickr-' . $this->field['theme'] : '';
	}

	protected function settings() {
		$rand_id = sanitize_key( $this->field['id'] ) . intval( microtime( true ) );
		$rand_id = str_replace( array( '-', '_' ), '', $rand_id );
		if ( ! empty( $this->field['settings'] ) ) {
			$this->localize_field( $rand_id, $this->field['settings'], true );
		}

		return array( 'data-datepicker-id' => $rand_id );
	}

	protected function field_defaults() {
		return array(
			'settings' => array(),
			'theme'    => false,
		);
	}
}
