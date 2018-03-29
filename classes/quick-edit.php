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

/**
 * Created by PhpStorm.
 * User: varun
 * Date: 31-12-2017
 * Time: 02:44 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class WPSFramework_Quick_Edit
 */
class WPSFramework_Quick_Edit extends WPSFramework_Abstract {

	/**
	 * options
	 *
	 * @var array|mixed|void
	 */
	public $options = array();

	/**
	 * post_types
	 *
	 * @var array
	 */
	public $post_types = array();

	/**
	 * formatted
	 *
	 * @var array
	 */
	public $formatted = array();

	/**
	 * only_ids
	 *
	 * @var array
	 */
	public $only_ids = array();

	/**
	 * type
	 *
	 * @var string
	 */
	protected $type = 'quickedit';

	/**
	 * added_ids
	 *
	 * @var array
	 */
	private $added_ids = array();

	/**
	 * is_nonce_rendered
	 *
	 * @var bool
	 */
	private $is_nonce_rendered = false;

	/**
	 * WPSFramework_Quick_Edit constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {
		$this->options = apply_filters( 'wpsf_quick_edit_options', $options );
		$this->hook_post_types();
		$this->addAction( 'admin_enqueue_scripts', 'load_style_script' );
	}

	public function hook_post_types() {
		$this->post_types = wp_list_pluck( $this->options, 'post_type', 'post_type' );
		foreach ( $this->options as $option ) {
			$this->only_ids[] = $option['id'];
			if ( ! isset( $this->formatted[ $option['post_type'] ] ) ) {
				$this->formatted[ $option['post_type'] ] = array();
			}

			if ( ! isset( $this->formatted[ $option['post_type'] ][ $option['column'] ] ) ) {
				$this->formatted[ $option['post_type'] ][ $option['column'] ] = array();
			}

			$this->formatted[ $option['post_type'] ][ $option['column'] ][] = $option;
		}
		foreach ( $this->post_types as $post_type ) {
			$this->addAction( 'manage_' . $post_type . '_posts_custom_column', 'render_hidden_data', 99, 100 );
		}
		$this->addAction( 'quick_edit_custom_box', 'render_quick_edit', 10, 99 );
		$this->addAction( 'save_post', 'save_quick_edit', 10, 2 );
		$this->only_ids = array_filter( array_unique( $this->only_ids ) );
	}

	/**
	 * Load_style_script
	 */
	public function load_style_script() {
		global $pagenow, $typenow;

		if ( 'edit.php' === $pagenow && isset( $this->post_types[ $typenow ] ) ) {
			wpsf_assets()->render_framework_style_scripts();
			wp_enqueue_script( 'wpsf-quick-edit' );
		}
	}

	/**
	 * @param $column
	 * @param $post_id
	 */
	public function render_hidden_data( $column, $post_id ) {
		if ( isset( $this->added_ids[ $post_id ] ) || empty( $this->only_ids ) ) {
			return;
		}
		echo '<div id="wpsf_quick_edit_' . $post_id . '" class="hidden">';
		foreach ( $this->only_ids as $id ) {
			echo '<div id="' . $id . '"> ' . json_encode( get_post_meta( $post_id, $id, true ) ) . '</div>';
		}
		echo '</div>';
		$this->added_ids[ $post_id ] = $post_id;
	}

	/**
	 * @param $column
	 * @param $post_type
	 */
	public function render_quick_edit( $column, $post_type ) {
		if ( ! isset( $this->formatted[ $post_type ][ $column ] ) ) {
			return;
		}

		if ( false === $this->is_nonce_rendered ) {
			wp_nonce_field( 'wpsf-quick-edit', 'wpsf-quick-edit-nonce' );
			$this->is_nonce_rendered = true;
		}

		$options = $this->formatted[ $post_type ][ $column ];
		echo '<fieldset class="inline-edit-col-left"> <div class="wpsf_quick_edit_fields inline-edit-col"> ';
		foreach ( $options as $option ) {
			echo $this->render_fields( $option, $option['id'] );
		}
		echo '</div></fieldset>';
	}

	/**
	 * @param        $option
	 * @param string $db_key
	 *
	 * @return bool|string
	 */
	private function render_fields( $option, $db_key = '' ) {
		if ( ! isset( $option['fields'] ) ) {
			return true;
		}
		$html = '';
		foreach ( $option['fields'] as $field ) {
			$html .= wpsf_add_element( $field, '', $db_key );
		}
		return $html;
	}

	/**
	 * @param $post_id
	 * @param $post
	 */
	public function save_quick_edit( $post_id, $post ) {
		if ( wp_verify_nonce( wpsf_get_var( 'wpsf-quick-edit-nonce' ), 'wpsf-quick-edit' ) ) {
			$post_type = wpsf_get_var( 'post_type' );
			$validator = new WPSFramework_DB_Save_Handler;
			if ( isset( $this->post_types[ $post_type ] ) && isset( $this->formatted[ $post_type ] ) ) {
				foreach ( $this->formatted[ $post_type ] as $data ) {
					foreach ( $data as $section ) {
						$request_key   = $section['id'];
						$submitted_val = wpsf_get_var( $request_key, array() );
						$db_value      = get_post_meta( $post_id, $request_key, true );

						if ( ! is_array( $db_value ) ) {
							$db_value = array();
						}

						$request = array_merge( $db_value, $submitted_val );

						if ( isset( $request['_nonce'] ) ) {
							unset( $request['_nonce'] );
						}

						$request = $validator->loop_fields( array( 'fields' => $section['fields'] ), $request, $db_value );
						$request = apply_filters( 'wpsf_save_post', $request, $request_key, $post );

						if ( empty( $request ) ) {
							delete_post_meta( $post_id, $request_key );
						} else {
							update_post_meta( $post_id, $request_key, $request );
						}
					}
				}
			}
		}
	}
}
