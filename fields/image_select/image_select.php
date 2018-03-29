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
 *
 * Field: Image Select
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 */
class WPSFramework_Option_image_select extends WPSFramework_Options {
	/**
	 * WPSFramework_Option_image_select constructor.
	 *
	 * @param        $field
	 * @param string $value
	 * @param string $unique
	 */
	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {
		$input_type = ( true === $this->field ['is_radio'] ) ? 'radio' : 'checkbox';
		$input_attr = ( true === $this->field ['multiple'] ) ? '[]' : '';
		echo $this->element_before();
		echo ( empty( $input_attr ) ) ? '<div class="wpsf-field-image-select">' : '';

		if ( isset( $this->field ['options'] ) ) {
			$options = $this->field ['options'];

			foreach ( $options as $key => $value ) {
				$ex_attr = array();
				$label   = $value;
				if ( is_array( $value ) ) {
					$ex_attr = isset( $value['attributes'] ) ? $value['attributes'] : array();
					$label   = isset( $value['label'] ) ? $value['label'] : '';
				}

				echo '<label>';
				echo '<input type="' . $input_type . '" name="' . $this->element_name( $input_attr ) . '" value="' . $key . '"' . $this->element_class() . $this->element_attributes( $key, $ex_attr ) . $this->checked( $this->element_value(), $key ) . '/>';
				echo '<img src="' . $label . '" alt="' . $key . '" /></label>';
			}
		}

		echo ( empty( $input_attr ) ) ? '</div>' : '';
		echo $this->element_after();
	}

	protected function field_defaults() {
		return array(
			'is_radio' => false,
			'multiple' => false,
			'options'  => array(),
		);
	}
}
