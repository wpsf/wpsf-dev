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

/**
 * Created by PhpStorm.
 * User: varun
 * Date: 05-01-2018
 * Time: 07:14 AM
 */

if ( ! defined( "ABSPATH" ) ) {
	die;
}

/**
 * Class WPSFramework_DB_Save_Handler
 */
class WPSFramework_DB_Save_Handler extends WPSFramework_Abstract {
	public static $_instance      = null;
	public        $errors         = array();
	public        $fields         = array();
	public        $db_values      = array();
	public        $posted         = array();
	public        $cur_posted     = array();
	public        $is_settings    = false;
	public        $return_values  = array();
	public        $field_ids      = array();
	public        $is_single_page = null;

	public function __construct() {
	}

	/**
	 * @return null|\WPSFramework_DB_Save_Handler
	 */
	public static function instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * @param array $posted_values
	 * @param array $ex_values
	 * @param array $fields
	 *
	 * @return array|mixed
	 */
	public function general_save_handler( $posted_values = array(), $ex_values = array(), $fields = array() ) {
		$this->is_settings   = false;
		$this->return_values = $this->_remove_nonce( $posted_values );
		$this->posted        = $this->return_values;
		$this->db_values     = $ex_values;
		$this->return_values = $this->loop_fields( $fields, $this->return_values, $this->db_values );
		return $this->return_values;
	}

	/**
	 * @param $values
	 *
	 * @return mixed
	 */
	private function _remove_nonce( $values ) {
		foreach ( $values as $id => $value ) {
			if ( $id === '_nonce' ) {
				unset( $values[ $id ] );
			}

			if ( isset( $value['_nonce'] ) ) {
				unset( $values[ $id ]['_nonce'] );
			}

			if ( is_array( $values[ $id ] ) ) {
				$values[ $id ] = $this->_remove_nonce( $values[ $id ] );
			}
		}
		return $values;
	}

	/**
	 * @param array $current_fields
	 * @param array $values
	 * @param array $db_value
	 *
	 * @return array
	 */
	public function loop_fields( $current_fields = array(), $values = array(), $db_value = array() ) {
		if ( isset( $current_fields['fields'] ) ) {
			foreach ( $current_fields['fields'] as $field ) {
				if ( isset( $field['type'] ) && ! isset( $field['multilang'] ) && isset( $field['id'] ) ) {
					$fid = $field['id'];

					if ( isset( $db_value[ $fid ] ) ) {
						$db_val             = $db_value[ $fid ];
						$field['pre_value'] = $db_val;
					} else {
						$db_val             = $db_value;
						$field['pre_value'] = null;
					}

					if ( isset( $field['sections'] ) && $field['type'] === 'tab' ) {
						$f_val = $this->get_field_value( $values, $fid );
						$value = $this->loop_fields( $field['sections'], $f_val, $db_value );
					} elseif ( isset( $field['fields'] ) && $field['type'] !== 'group' ) {
						$f_val = $this->get_field_value( $values, $fid );
						$value = $this->_handle_single_field( $field, $f_val );
						$value = $this->loop_fields( $field, $f_val, $db_val );
					} else {
						$f_val = $this->get_field_value( $values, $fid );
						$value = $this->_handle_single_field( $field, $f_val );
					}

					$values = $this->_manage_data( $values, $value, $fid );
				}
			}
		} else {
			foreach ( $current_fields as $section ) {
				if ( isset( $section['fields'] ) ) {
					$f_val  = $this->get_field_value( $values, $section['name'] );
					$f_val  = ( $f_val === false ) ? $values : $f_val;
					$value  = $this->loop_fields( $section, $f_val, $db_value );
					$values = $this->_manage_data( $values, $value, $section['name'] );
				}
			}
		}

		return $values;
	}

	/**
	 * @param $values
	 * @param $id
	 *
	 * @return bool|mixed
	 */
	private function get_field_value( $values, $id ) {
		if ( isset( $this->posted[ $id ] ) ) {
			return $this->posted[ $id ];
		}

		if ( isset( $values[ $id ] ) ) {
			return $values[ $id ];
		}

		return false;
	}

	/**
	 * @param       $field
	 * @param array $values
	 * @param       $fields
	 *
	 * @return array|bool|mixed
	 */
	public function _handle_single_field( $field, $values = array() ) {
		$value = ( is_array( $values ) && isset( $values[ $field['id'] ] ) ) ? $values[ $field['id'] ] : $values;
		$value = $this->_sanitize_field( $field, $value );
		$value = $this->_validate_field( $field, $value );
		return $value;
	}

	/**
	 * @param $field
	 * @param $value
	 * @param $fields
	 *
	 * @return mixed
	 */
	public function _sanitize_field( $field, $value ) {
		$type = $field['type'];

		if ( isset( $field['sanitize'] ) ) {
			$type = ( $field['sanitize'] !== false ) ? $field['sanitize'] : false;
		}

		if ( $type !== false && has_filter( 'wpsf_sanitize_' . $type ) ) {
			$value = apply_filters( 'wpsf_sanitize_' . $type, $value, $field );
		}

		return $value;
	}

	/**
	 * @param $field
	 * @param $value
	 * @param $fields
	 *
	 * @return bool
	 */
	public function _validate_field( $field, $value ) {
		if ( isset( $field['validate'] ) && has_filter( 'wpsf_validate_' . $field['validate'] ) ) {
			$validate = apply_filters( 'wpsf_validate_' . $field['validate'], $value, $field );
			if ( ! empty( $validate ) ) {
				$fid            = isset( $field['error_id'] ) ? $field['error_id'] : $field['id'];
				$this->errors[] = $this->_error( $validate, 'error', $fid );

				if ( isset( $field['pre_value'] ) && $field['pre_value'] !== null ) {
					return $field['pre_value'];
				}

				if ( isset( $field['default'] ) ) {
					return $field['default'];
				}
				return false;
			}
		}
		return $value;
	}

	/**
	 * @param        $message
	 * @param string $type
	 * @param string $id
	 *
	 * @return array
	 */
	private function _error( $message, $type = 'error', $id = 'global' ) {
		return array(
			'setting' => 'wpsf-errors',
			'code'    => $id,
			'message' => $message,
			'type'    => $type,
		);
	}

	/**
	 * @param $orginal_data
	 * @param $_new
	 * @param $field_id
	 *
	 * @return array
	 */
	private function _manage_data( $orginal_data, $_new, $field_id ) {
		if ( is_array( $orginal_data ) ) {
			if ( ! is_array( $_new ) ) {
				$orginal_data[ $field_id ] = $_new;
			} elseif ( is_array( $_new ) && ( count( array_keys( $_new ) ) !== count( array_keys( $orginal_data ) ) ) ) {
				$orginal_data[ $field_id ] = $_new;
			}
		} elseif ( ! is_array( $orginal_data ) && is_array( $_new ) ) {
			$orginal_data = $_new;
		}

		return $orginal_data;
	}

	/**
	 * @param array $options
	 * @param array $fields
	 *
	 * @return array
	 */
	public function handle_settings_page( $options = array(), $fields = array() ) {
		$this->is_settings = true;

		$defaults = array(
			'is_single_page'     => false,
			'current_section_id' => false,
			'current_parent_id'  => false,
			'db_key'             => false,
			'posted_values'      => array(),
		);

		$options              = wp_parse_args( $options, $defaults );
		$csid                 = $options['current_section_id'];
		$cpid                 = $options['current_parent_id'];
		$isp                  = $options['is_single_page'];
		$this->is_single_page = $isp;
		$this->db_values      = get_option( $options['db_key'], true );
		$this->db_values      = ( $this->db_values === true || empty( $this->db_values ) ) ? array() : $this->db_values;
		$this->posted         = $options['posted_values'];
		$this->posted         = $this->_remove_nonce( $this->posted );
		$this->return_values  = $this->posted;


		$this->fields = $fields;

		foreach ( $this->fields as $section ) {
			if ( $this->is_single_page === false && ( $csid != $section['name'] && $cpid != $section['page_id'] ) ) {
				continue;
			}
			$this->return_values = $this->loop_fields( $section, $this->return_values, $this->db_values );
		}
		if ( $this->is_single_page === false ) {
			//$this->return_values = array_merge($this->db_values, $this->return_values);
			$this->return_values = $this->array_merge( $this->return_values, $this->db_values );
		}

		$this->remove_unknown_fields();
		return $this->return_values;
	}


	/**
	 * @param array $new_values
	 * @param array $old_values
	 *
	 * @return array
	 */
	public function array_merge( $new_values = array(), $old_values = array() ) {
		foreach ( $old_values as $key => $value ) {
			if ( ! isset( $new_values[ $key ] ) ) {
				$new_values[ $key ] = $old_values[ $key ];
			}

		}
		return $new_values;
	}

	public function remove_unknown_fields() {
		if ( $this->is_single_page === false ) {
			$this->field_ids = array();

			foreach ( $this->fields as $section ) {
				$this->extract_field_ids( $section );
			}

			$delete_keys = array_diff_key( $this->return_values, $this->field_ids );
			$delete_keys = array_keys( $delete_keys );

			foreach ( $delete_keys as $d ) {
				unset( $this->field_ids[ $d ] );
				unset( $this->return_values[ $d ] );
			}
		}
	}

	/**
	 * @param $fields
	 */
	public function extract_field_ids( $fields ) {
		if ( isset( $fields['fields'] ) ) {
			foreach ( $fields['fields'] as $field ) {
				if ( isset( $field['un_array'] ) && $field['un_array'] === true ) {
					$this->extract_field_ids( $field );
				} else {
					if ( isset( $field['id'] ) ) {
						$this->field_ids[ $field['id'] ] = $field['id'];
					}
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public function get_errors() {
		$errors       = $this->errors;
		$this->errors = array();
		return $errors;
	}
}