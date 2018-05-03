<?php
/**
 *
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * Initial version created 02-05-2018 / 04:42 PM
 * @version 1.0
 * @since 1.0
 * @package
 * @link
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WPSFramework_Field_Cloner' ) ) {
	/**
	 * Class WPSFramework_Field_Cloner
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class WPSFramework_Field_Cloner extends WPSFramework_Options {

		/**
		 * WPSFramework_Field_Cloner constructor.
		 *
		 * @param array  $field
		 * @param string $value
		 * @param string $unique
		 */
		public function __construct( array $field = array(), $value = '', $unique = '' ) {
			parent::__construct( $field, $value, $unique );
		}

		public function get_field_args( $i ) {
			$delete = array( 'clone', 'clone_max', 'title', 'before', 'after', 'clone_sort' );
			$fields = $this->field;
			foreach ( $delete as $d ) {
				if ( isset( $fields[ $d ] ) ) {
					unset( $fields[ $d ] );
				}
			}
			$fields['name_after'] = '[' . $i . ']';
			return $fields;
		}

		public function html_template( $i = '{clone_count}', $value = '' ) {
			$html = '<div class="clone_element">';
			if ( isset( $this->field['clone_sort'] ) ) {
				$html .= '<div class="clone_sorter"><i class="fa fa-bars" aria-hidden="true"></i></div>';
			}
			$html .= wpsf_add_element( $this->get_field_args( $i ), $value, $this->unique );
			$html .= '<div class="clone_remove"><button class="clone_remove button button-secondary" type="button">' . $this->field['clone_remove_label'] . '</button> </div>';
			$html .= '</div>';
			return $html;
		}

		/**
		 * @return mixed
		 */
		public function output() {
			echo $this->element_before();
			$excass = 'cloner_wrap';
			if ( true === $this->field['clone_sort'] ) {
				$excass .= ' clone_sortable ';
			}
			echo '<div class="' . $excass . '">';
			echo $this->output_fields();
			echo '</div>';
			echo '<button class="wpsf_cloner_add button button-primary" type="button">' . $this->field['clone_addmore_label'] . '</button>';
			echo $this->element_after();
		}

		public function output_fields() {
			if ( is_array( $this->value ) ) {
				$html = '';
				foreach ( $this->value as $i => $v ) {
					$html .= $this->html_template( $i, $v );
				}
				return $html;
			}
			return false;
		}

		/**
		 *
		 */
		public function final_output() {
			echo $this->element_wrapper();
			echo $this->output();
			echo $this->element_wrapper( false );
		}

		/**
		 * @param bool $is_start
		 */
		public function element_wrapper( $is_start = true ) {
			if ( true === $is_start ) {
				$main_id         = $this->js_settings();
				$this->row_after = '';
				$sub             = ( isset( $this->field['sub'] ) ) ? 'sub-' : '';
				$languages       = wpsf_language_defaults();
				$wrap_class      = 'wpsf-element wpsf-element-' . $this->element_type() . ' wpsf-field-cloner ';

				$wrap_class .= ( ! empty( $this->field['wrap_class'] ) ) ? ' ' . $this->field['wrap_class'] : '';
				$wrap_class .= ( ! empty( $this->field['title'] ) ) ? ' wpsf-element-' . sanitize_title( $this->field ['title'] ) : ' no-title ';
				$wrap_class .= ( isset( $this->field ['pseudo'] ) ) ? ' wpsf-pseudo-field' : '';

				$is_hidden = ( isset( $this->field ['show_only_language'] ) && ( $this->field ['show_only_language'] != $languages ['current'] ) ) ? ' hidden ' : '';

				$wrap_attr = ( isset( $this->field['wrap_attributes'] ) && is_array( $this->field['wrap_attributes'] ) ) ? $this->field['wrap_attributes'] : array();
				if ( is_array( $this->field['dependency'] ) && false !== $this->field['dependency'] ) {
					$is_hidden                                  = ' hidden';
					$wrap_attr[ 'data-' . $sub . 'controller' ] = $this->field ['dependency'] [0];
					$wrap_attr[ 'data-' . $sub . 'condition' ]  = $this->field ['dependency'] [1];
					$wrap_attr[ 'data-' . $sub . 'value' ]      = $this->field ['dependency'] [2];
				}
				$wrap_attr['data-cloner_id'] = $main_id;
				$wrap_attr                   = $this->array_to_html_attrs( $wrap_attr );

				if ( isset( $this->field['columns'] ) ) {
					$wrap_class .= ' wpsf-column wpsf-column-' . $this->field['columns'] . ' ';

					if ( 0 == self::$total_cols ) {
						$wrap_class .= ' wpsf-column-first ';
						echo '<div class="wpsf-element wpsf-row">';
					}

					self::$total_cols += $this->field['columns'];

					if ( 12 == self::$total_cols ) {
						$wrap_class .= ' wpsf-column-last ';

						$this->row_after  = '</div>';
						self::$total_cols = 0;
					}
				}
				$wrap_class .= ' ' . $is_hidden;
				echo '<div class="' . $wrap_class . '" ' . $wrap_attr . ' >';
				$this->element_title();
				echo $this->element_title_before();
			} else {
				echo $this->element_title_after();
				echo '<div class="clear"></div>';
				echo '</div>';
				echo $this->row_after;
			}
		}

		/**
		 * @return array
		 */
		public function get_defaults() {
			return array(
				'dependency'          => false,
				'clone_sort'          => false,
				'clone_addmore_label' => __( 'Add More' ),
				'clone_remove_label'  => '<i class="fa fa-trash" aria-hidden="true"></i>',
			);
		}

		/**
		 * @return mixed|string
		 */
		public function js_settings() {
			$rand_id = $this->js_settings_id();
			$this->localize_field( $rand_id, array( 'html' => $this->html_template() ), true );
			return $rand_id;
		}
	}
}
