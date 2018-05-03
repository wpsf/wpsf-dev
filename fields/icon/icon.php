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
 * Field: Icon
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 */
class WPSFramework_Option_icon extends WPSFramework_Options {
	/**
	 * WPSFramework_Option_icon constructor.
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
		$value  = $this->element_value();
		$hidden = ( empty( $value ) ) ? ' hidden' : '';
		$ex_C   = 'wpsf-icon-select';
		$ex_C   = ( true === $this->field['show_textbox'] ) ? $ex_C . ' show-textbox ' : $ex_C;

		echo '<div class="' . $ex_C . '">';
		echo '<input type="text" name="' . $this->element_name() . '" value="' . $value . '"' . $this->element_class( 'wpsf-icon-value' ) . $this->element_attributes() . ' />';
		echo '<span class="wpsf-icon-preview' . $hidden . '"><i class="' . $value . '"></i></span>';
		echo '<a data-id="' . microtime( true ) . '" href="javascript:void(0);" class="button button-primary wpsf-icon-add">' . esc_html( $this->field['add_label'] ) . '</a>';
		echo '<a href="javascript:void(0);" class="button wpsf-warning-primary wpsf-icon-remove' . $hidden . '">' . esc_html( $this->field['remove_label'] ) . '</a>';
		echo '</div>';

		echo $this->element_after();
	}

	protected function field_defaults() {
		return array(
			'add_label'    => __( 'Addon Icon' ),
			'remove_label' => __( 'Remove Icon' ),
			'show_textbox' => true,
		);
	}
}
