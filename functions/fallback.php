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

if ( ! defined( 'ABSPATH' ) ) {
	die ();
} // Cannot access pages directly.

if ( ! function_exists( 'wpsf_get_term_meta' ) ) {
	/**
	 * A fallback for get term meta
	 * get_term_meta added since WP 4.4
	 *
	 * @since   1.0.2
	 * @version 1.0.0
	 *
	 * @param        $term_id
	 * @param string $key
	 * @param bool   $single
	 *
	 * @return bool|mixed
	 */
	function wpsf_get_term_meta( $term_id, $key = '', $single = false ) {
		if ( function_exists( "get_term_meta" ) ) {
			return get_term_meta( $term_id, $key, $single );
		}
		$terms = get_option( 'wpsf_term_' . $key );
		return ( ! empty( $terms [ $term_id ] ) ) ? $terms [ $term_id ] : false;
	}
}

if ( ! function_exists( 'wpsf_add_term_meta' ) ) {
	/**
	 * A fallback for add term meta
	 * add_term_meta added since WP 4.4
	 *
	 * @param        $term_id
	 * @param string $meta_key
	 * @param        $meta_value
	 * @param bool   $unique
	 *
	 * @since   1.0.2
	 * @version 1.0.0
	 *
	 * @return bool|int|\WP_Error
	 */
	function wpsf_add_term_meta( $term_id, $meta_key = '', $meta_value, $unique = false ) {
		if ( function_exists( "add_term_meta" ) ) {
			return add_term_meta( $term_id, $meta_key, $meta_value, $unique );
		}
		return update_term_meta( $term_id, $meta_key, $meta_value, $unique );
	}
}

if ( ! function_exists( 'wpsf_update_term_meta' ) ) {
	/**
	 *
	 * A fallback for update term meta
	 * update_term_meta added since WP 4.4
	 *
	 * @since   1.0.2
	 * @version 1.0.0
	 *
	 * @param        $term_id
	 * @param        $meta_key
	 * @param        $meta_value
	 * @param string $prev_value
	 *
	 * @return bool|int|\WP_Error
	 */
	function wpsf_update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
		if ( function_exists( "update_term_meta" ) ) {
			return update_term_meta( $term_id, $meta_key, $meta_value, $prev_value );
		}

		if ( ! empty( $term_id ) || ! empty( $meta_key ) || ! empty( $meta_value ) ) {
			$terms              = get_option( 'wpsf_term_' . $meta_key );
			$terms [ $term_id ] = $meta_value;
			update_option( 'wpsf_term_' . $meta_key, $terms );
		}
		return true;
	}
}

if ( ! function_exists( 'wpsf_delete_term_meta' ) ) {
	/**
	 * A fallback for delete term meta
	 * delete_term_meta added since WP 4.4
	 *
	 * @since   1.0.2
	 * @version 1.0.0
	 *
	 * @param        $term_id
	 * @param        $meta_key
	 * @param string $meta_value
	 *
	 * @return bool
	 */
	function wpsf_delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
		if ( function_exists( "delete_term_meta" ) ) {
			return delete_term_meta( $term_id, $meta_key, $meta_value );
		}
		if ( ! empty( $term_id ) || ! empty( $meta_key ) ) {
			$terms = get_option( 'wpsf_term_' . $meta_key );
			unset ( $terms [ $term_id ] );
			update_option( 'wpsf_term_' . $meta_key, $terms );
		}
		return true;
	}
}
