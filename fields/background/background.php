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
 * Field: Background
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 */
class WPSFramework_Option_background extends WPSFramework_Options {
	/**
	 * WPSFramework_Option_background constructor.
	 *
	 * @param        $field
	 * @param string $value
	 * @param string $unique
	 */
	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	/**
	 * @return mixed|void
	 */
	public function output() {
		echo $this->element_before();

		$value_defaults = array(
			'image'      => '',
			'repeat'     => '',
			'position'   => '',
			'attachment' => '',
			'size'       => '',
			'color'      => '',
		);

		$this->value = wp_parse_args( $this->element_value(), $value_defaults );

		if ( isset( $this->field ['settings'] ) ) {
			extract( $this->field ['settings'] );
		}

		$upload_type  = ( isset( $upload_type ) ) ? $upload_type : 'image';
		$button_title = ( isset( $button_title ) ) ? $button_title : esc_html__( 'Upload', 'wpsf-framework' );
		$frame_title  = ( isset( $frame_title ) ) ? $frame_title : esc_html__( 'Upload', 'wpsf-framework' );
		$insert_title = ( isset( $insert_title ) ) ? $insert_title : esc_html__( 'Use Image', 'wpsf-framework' );

		echo '<div class="wpsf-field-upload">';
		echo '<input type="text" name="' . $this->element_name( '[image]' ) . '" value="' . $this->value ['image'] . '"' . $this->element_class() . $this->element_attributes() . '/>';
		echo '<a href="#" class="button wpsf-add" data-frame-title="' . $frame_title . '" data-upload-type="' . $upload_type . '" data-insert-title="' . $insert_title . '">' . $button_title . '</a>';
		echo '</div>';

		// background attributes
		echo '<fieldset>';
		echo $this->add_field( array(
			'pseudo'     => true,
			'id'         => $this->field ['id'] . '_repeat',
			'type'       => 'select',
			'name'       => $this->element_name( '[repeat]' ),
			'options'    => array(
				''          => 'repeat',
				'repeat-x'  => 'repeat-x',
				'repeat-y'  => 'repeat-y',
				'no-repeat' => 'no-repeat',
				'inherit'   => 'inherit',
			),
			'attributes' => array(
				'data-atts' => 'repeat',
			),

		), $this->value['repeat'] );
		echo $this->add_field( array(
			'pseudo'     => true,
			'type'       => 'select',
			'id'         => $this->field ['id'] . '_position',
			'name'       => $this->element_name( '[position]' ),
			'options'    => array(
				''              => 'left top',
				'left center'   => 'left center',
				'left bottom'   => 'left bottom',
				'right top'     => 'right top',
				'right center'  => 'right center',
				'right bottom'  => 'right bottom',
				'center top'    => 'center top',
				'center center' => 'center center',
				'center bottom' => 'center bottom',
			),
			'attributes' => array(
				'data-atts' => 'position',
			),
		), $this->value ['position'] );
		echo $this->add_field( array(
			'pseudo'     => true,
			'type'       => 'select',
			'name'       => $this->element_name( '[attachment]' ),
			'id'         => $this->field ['id'] . '_attachment',
			'options'    => array(
				''      => 'scroll',
				'fixed' => 'fixed',
			),
			'attributes' => array(
				'data-atts' => 'attachment',
			),
		), $this->value ['attachment'] );
		echo $this->add_field( array(
			'pseudo'     => true,
			'type'       => 'select',
			'id'         => $this->field ['id'] . '_size',
			'name'       => $this->element_name( '[size]' ),
			'options'    => array(
				''        => 'size',
				'cover'   => 'cover',
				'contain' => 'contain',
				'inherit' => 'inherit',
				'initial' => 'initial',
			),
			'attributes' => array(
				'data-atts' => 'size',
			),

		), $this->value ['size'] );
		echo $this->add_field( array(
			'pseudo'     => true,
			'id'         => $this->field ['id'] . '_color',
			'type'       => 'color_picker',
			'name'       => $this->element_name( '[color]' ),
			'attributes' => array(
				'data-atts' => 'bgcolor',
			),
			'default'    => ( isset( $this->field ['default'] ['color'] ) ) ? $this->field ['default'] ['color'] : '',
			'rgba'       => ( isset( $this->field ['rgba'] ) && false === $this->field ['rgba'] ) ? false : '',
		), $this->value ['color'] );
		echo '</fieldset>';

		echo $this->element_after();
	}

	/**
	 * @return array
	 */
	protected function field_defaults() {
		return array(
			'settings' => array(),
		);
	}
}
