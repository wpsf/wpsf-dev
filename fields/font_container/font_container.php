<?php

/**
 * Created by PhpStorm.
 * User: varun
 * Date: 26-02-2018
 * Time: 03:42 PM
 */
class WPSFramework_Option_font_container extends WPSFramework_Options {

	public function __construct( array $field = array(), $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {
		$fields        = $this->font_fields();
		$is_inline_css = ( $this->field['inline'] === true ) ? 'font_container_inline' : '';
		echo '<div class="wpsf_font_field ' . $is_inline_css . '" data-id="' . $this->field['id'] . '">';

		foreach ( $fields as $field ) {
			echo $this->add_field( $field, $this->is_value( $field['id'] ), $this->get_unique( $this->field['id'] ) );
		}


		/**
		 * Font Preview
		 */
		if ( $this->field['preview'] == true ) {
			$preview_text = $this->field['preview_text'];
			echo '<div id="preview-' . $this->field['id'] . '" style="font-family:;" class="wpsf-font-preview" contenteditable="true">' . $preview_text . '</div>';
		}
		echo '</div>';
	}

	public function font_fields() {
		$is_select2 = ( isset( $this->field['select2'] ) && $this->field['select2'] === true ) ? 'select2' : '';
		$is_chosen  = ( isset( $this->field['chosen'] ) && $this->field['chosen'] === true ) ? 'chosen' : '';

		$default = array(
			'font_family'    => array(
				'title'   => __( "Font Family" ),
				'desc'    => __( "Select Font Family" ),
				'type'    => 'typography',
				'id'      => 'font_family',
				'variant' => isset( $this->field['variant'] ) ? $this->field['variant'] : true,
				'chosen'  => isset( $this->field['chosen'] ) ? $this->field['chosen'] : null,
				'select2' => isset( $this->field['select2'] ) ? $this->field['select2'] : null,
			),
			'tag'            => array(
				'id'      => 'tag',
				'type'    => 'select',
				'class'   => $is_select2 . ' ' . $is_chosen,
				'title'   => __( "Tag" ),
				'desc'    => __( "Select element tag." ),
				'options' => apply_filters( 'wpsf_font_container_tags', array(
					'h1'   => 'h1',
					'h2'   => 'h2',
					'h3'   => 'h3',
					'h4'   => 'h4',
					'h5'   => 'h5',
					'h6'   => 'h6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				) ),
			),
			'text_align'     => array(
				'id'      => 'text_align',
				'type'    => 'select',
				'class'   => $is_select2 . ' ' . $is_chosen,
				'desc'    => __( "Select text alignment." ),
				'options' => array(
					'left'    => __( "Left" ),
					'right'   => __( "Right" ),
					'center'  => __( "Center" ),
					'justify' => __( "Justify" ),
				),
				'title'   => __( 'Text align' ),
			),
			'font_size'      => array(
				'id'    => 'font_size',
				'type'  => 'text',
				'desc'  => __( "Enter font size." ),
				'title' => __( 'Font Size' ),
			),
			'letter_height'  => array(
				'id'    => 'letter_height',
				'type'  => 'text',
				'desc'  => __( "Enter line height" ),
				'title' => __( 'Line Height' ),
			),
			'letter_spacing' => array(
				'id'    => 'letter_spacing',
				'type'  => 'text',
				'desc'  => __( "Enter Letter Spacing" ),
				'title' => __( 'Letter Spacing' ),
			),
			'color'          => array(
				'title' => __( "Color" ),
				'id'    => 'color',
				'type'  => 'color_picker',
				'rgba'  => ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
			),
		);

		$_fields = ( isset( $this->field['fields'] ) && is_array( $this->field['fields'] ) ) ? $this->field['fields'] : array();

		$fields = $_fields;

		foreach ( $fields as $i => $f ) {
			if ( $f === false ) {
				continue;
			}

			if ( is_bool( $f ) && isset( $default[ $i ] ) ) {
				$fields[ $i ] = $default[ $i ];
			} elseif ( is_string( $f ) && isset( $default[ $i ] ) ) {
				$fields[ $i ]          = $default[ $i ];
				$fields[ $i ]['title'] = $f;
			} elseif ( is_array( $f ) && isset( $default[ $i ] ) ) {
				$fields[ $i ] = wp_parse_args( $f, $default[ $i ] );
			}

			if ( $is_inline === true ) {
				$fields[ $i ]['pseudo'] = true;
				if ( ! isset( $fields[ $i ]['wrap_attributes'] ) ) {
					$fields[ $i ]['wrap_attributes'] = array();
				}
				$fields[ $i ]['wrap_attributes']['data-toggle'] = 'wpsftooltip';
				$fields[ $i ]['wrap_attributes']['title']       = $fields[ $i ]['title'];
			}

		}

		foreach ( $default as $i => $v ) {
			if ( isset( $_fields[ $i ] ) && $_fields[ $i ] === false ) {
				continue;
			} elseif ( ! isset( $_fields[ $i ] ) ) {
				$fields[ $i ] = $v;
			}

			if ( $this->field['inline'] === true ) {
				$fields[ $i ]['pseudo'] = true;
				if ( ! isset( $fields[ $i ]['wrap_attributes'] ) ) {
					$fields[ $i ]['wrap_attributes'] = array();
				}
				$fields[ $i ]['wrap_attributes']['data-toggle'] = 'wpsftooltip';
				$fields[ $i ]['wrap_attributes']['title']       = $fields[ $i ]['title'];
			}
		}

		return $fields;
	}

	public function is_value( $val ) {
		return isset( $this->value[ $val ] ) ? $this->value[ $val ] : false;
	}

	protected function field_defaults() {
		return array(
			'inline'       => false,
			'preview'      => true,
			'select2'      => false,
			'chosen'       => false,
			'preview_text' => 'Lorem ipsum dolor sit amet, pro ad sanctus admodum, vim at insolens appellantur. Eum veri adipiscing an, probo nonumy an vis.',
		);
	}
}