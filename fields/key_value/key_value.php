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
} // Cannot access pages directly.

/**
 * Class WPSFramework_Option_key_value
 * Field Key Value.
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class WPSFramework_Option_key_value extends WPSFramework_Options {
	/**
	 * WPSFramework_Option_image_size constructor.
	 *
	 * @param        $field
	 * @param string $value
	 * @param string $unique
	 */
	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function final_output() {
		echo $this->output();
	}

	public function output() {
		$field          = $this->field;
		$field['clone'] = true;
		$field['type']  = 'key_options';
		return wpsf_add_element( $field, $this->value, $this->unique );
	}

	public function field_defaults() {
		return array(
			'clone_max'  => -1,
			'clone_sort' => false,
		);
	}
}

/**
 * Class WPSFramework_Option_key_options
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class WPSFramework_Option_key_options extends WPSFramework_Options {
	/**
	 * WPSFramework_Option_image_size constructor.
	 *
	 * @param        $field
	 * @param string $value
	 * @param string $unique
	 */
	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function final_output() {
		echo $this->output();
	}

	public function output() {

		$key_field                                = $this->get_field_args( '[key]' );
		$key_field['attributes']['placeholder']   = $this->field['key_placeholder'];
		$value_field                              = $this->get_field_args( '[value]' );
		$value_field['attributes']['placeholder'] = $this->field['value_placeholder'];

		echo wpsf_add_element( $key_field, $this->value( 'key' ), $this->unique );
		echo wpsf_add_element( $value_field, $this->value( 'value' ), $this->unique );
	}

	public function get_field_args( $after ) {
		$f               = $this->field;
		$f['type']       = 'text';
		$f['only_field'] = true;
		$f['name_after'] = $f['name_after'] . $after;
		return $f;
	}

	public function value( $type = 'key' ) {
		if ( is_array( $this->value ) ) {
			return ( isset( $this->value[ $type ] ) ) ? $this->value[ $type ] : false;
		}
		return false;
	}

	public function field_defaults() {
		return array(
			'key_placeholder'   => __( 'Key', 'wpsf-framework' ),
			'value_placeholder' => __( 'Value', 'wpsf-framework' ),
		);
	}
}
