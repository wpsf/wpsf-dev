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

global $wpsf_errors;
$wpsf_errors = array();
if ( ! defined( 'ABSPATH' ) ) {
	die();
} // Cannot access pages directly.

if ( ! function_exists( 'wpsf_init_element' ) ) {
	/**
	 * Creates A Fields Instance
	 *
	 * @param array  $field
	 * @param string $value
	 * @param string $unique
	 *
	 * @return array
	 */
	function wpsf_init_element( $field = array(), $value = '', $unique = '' ) {
		$class = 'WPSFramework_Option_' . $field ['type'];
		wpsf_autoloader( $class );
		if ( class_exists( $class ) ) {
			$element              = new $class( $field, $value, $unique );
			$instance_id          = $element->id;
			$field['instance_id'] = $instance_id;
		}
		return $field;
	}
}

if ( ! function_exists( 'wpsf_add_element' ) ) {
	/**
	 * Adds A WPSF Field & Renders it.
	 *
	 * @param array  $field
	 * @param string $value
	 * @param string $unique
	 * @param bool   $force
	 *
	 * @return string
	 */
	function wpsf_add_element( $field = array(), $value = '', $unique = '', $force = false ) {
		$output = '';

		if ( isset( $field['instance_id'] ) && false === $force ) {
			$_instance = wpsf_field_registry()->get( $field['instance_id'] );
			if ( $_instance instanceof WPSFramework_Options ) {
				ob_start();
				$_instance->final_output();
				return ob_get_clean();
			}
			return wpsf_add_element( $field, $value, $unique, true );
		} else {
			$class = 'WPSFramework_Option_' . $field ['type'];
			wpsf_autoloader( $class );
			if ( class_exists( $class ) ) {
				ob_start();
				$element = new $class( $field, $value, $unique );
				$element->final_output();
				$output .= ob_get_clean();
			} else {
				$output .= '<p>' . sprintf( esc_html__( 'This field class is not available! %s', 'wpsf-framework' ), '<strong>' . $class . '</strong>' ) . ' </p > ';
			}
		}
		return $output;
	}
}

if ( ! function_exists( 'wpsf_unarray_fields' ) ) {
	/**
	 * Returns all field types that can be unarrayed.
	 *
	 * @return array
	 */
	function wpsf_unarray_fields() {
		return apply_filters( 'wpsf_unarray_fields_types', array( 'tab', 'group', 'fieldset', 'accordion' ) );
	}
}

if ( ! function_exists( 'wpsf_is_unarray_field' ) ) {
	/**
	 * Checks if field type is unarray.
	 *
	 * @param mixed $type .
	 *
	 * @return bool
	 */
	function wpsf_is_unarray_field( $type ) {
		if ( is_array( $type ) && isset( $type['type'] ) ) {
			return in_array( $type['type'], wpsf_unarray_fields() );
		}
		return in_array( $type, wpsf_unarray_fields() );
	}
}

if ( ! function_exists( 'wpsf_is_unarrayed' ) ) {
	/**
	 * Checks if field is unarray.
	 *
	 * @param mixed $field .
	 *
	 * @return bool
	 */
	function wpsf_is_unarrayed( $field = array() ) {
		if ( wpsf_is_unarray_field( $field ) ) {
			if ( isset( $field['un_array'] ) && true === $field['un_array'] ) {
				return true;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'wpsf_encode_string' ) ) {
	/**
	 * Encode string for backup options
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param $string
	 *
	 * @return string
	 */
	function wpsf_encode_string( $string ) {
		return serialize( $string );
	}
}

if ( ! function_exists( 'wpsf_decode_string' ) ) {
	/**
	 * Decode string for backup options
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	function wpsf_decode_string( $string ) {
		return unserialize( $string );
	}
}

if ( ! function_exists( 'wpsf_get_google_fonts' ) ) {
	/**
	 * Get google font from json file
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @return array|mixed|object
	 */
	function wpsf_get_google_fonts() {
		global $wpsf_google_fonts;

		if ( ! empty( $wpsf_google_fonts ) ) {
			return $wpsf_google_fonts;
		} else {
			ob_start();
			wpsf_locate_template( 'fields / typography / google - fonts . json' );
			$json              = ob_get_clean();
			$wpsf_google_fonts = json_decode( $json );
			return $wpsf_google_fonts;
		}
	}
}

if ( ! function_exists( 'wpsf_get_icon_fonts' ) ) {
	/**
	 * Get icon fonts from json file
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param $file
	 *
	 * @return array|mixed|object
	 */
	function wpsf_get_icon_fonts( $file ) {
		ob_start();
		wpsf_locate_template( $file );
		$json = ob_get_clean();
		return json_decode( $json );
	}
}

if ( ! function_exists( 'wpsf_array_search' ) ) {
	/**
	 * Array search key & value
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param $array
	 * @param $key
	 * @param $value
	 *
	 * @return array
	 */
	function wpsf_array_search( $array, $key, $value ) {
		$results = array();

		if ( is_array( $array ) ) {
			if ( isset( $array[ $key ] ) && $value === $array[ $key ] ) {
				$results [] = $array;
			}

			foreach ( $array as $sub_array ) {
				$results = array_merge( $results, wpsf_array_search( $sub_array, $key, $value ) );
			}
		}

		return $results;
	}
}

if ( ! function_exists( 'wpsf_get_var' ) ) {
	/**
	 * Getting POST Var
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param        $var
	 * @param string $default
	 *
	 * @return string
	 */
	function wpsf_get_var( $var, $default = '' ) {
		if ( isset( $_POST[ $var ] ) ) {
			return $_POST[ $var ];
		}

		if ( isset( $_GET[ $var ] ) ) {
			return $_GET[ $var ];
		}

		return $default;
	}
}

if ( ! function_exists( 'wpsf_get_vars' ) ) {
	/**
	 * Getting POST Vars
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param        $var
	 * @param        $depth
	 * @param string $default
	 *
	 * @return string
	 */
	function wpsf_get_vars( $var, $depth, $default = '' ) {
		if ( isset( $_POST[ $var ][ $depth ] ) ) {
			return $_POST[ $var ][ $depth ];
		}

		if ( isset( $_GET[ $var ][ $depth ] ) ) {
			return $_GET[ $var ][ $depth ];
		}

		return $default;
	}
}

if ( ! function_exists( 'wpsf_js_vars' ) ) {
	/**
	 * Converts PHP Array into JS JSON String with script tag and returns it.
	 *
	 * @param      $object_name
	 * @param      $l10n
	 * @param bool $with_script_tag
	 *
	 * @return string
	 */
	function wpsf_js_vars( $object_name = '', $l10n, $with_script_tag = true ) {
		foreach ( (array) $l10n as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}
		$script = null;
		if ( ! empty( $object_name ) ) {
			$script = "var $object_name = " . wp_json_encode( $l10n ) . ';';
		} else {
			$script = wp_json_encode( $l10n );
		}

		if ( ! empty( $after ) ) {
			$script .= "\n$after;";
		}

		if ( $with_script_tag ) {
			return '<script type="text/javascript" >' . $script . '</script>';
		}
		return $script;
	}
}

if ( ! function_exists( 'wpsf_add_errors' ) ) {
	/**
	 * Adds Error to global $wpsf_error array.
	 *
	 * @param $errs
	 *
	 */
	function wpsf_add_errors( $errs ) {
		global $wpsf_errors;
		if ( is_array( $wpsf_errors ) && is_array( $errs ) ) {
			$wpsf_errors = array_merge( $wpsf_errors, $errs );
		} else {
			$wpsf_errors = $errs;
		}
	}
}

if ( ! function_exists( 'wpsf_get_errors' ) ) {
	/**
	 * Returns gloabl $wpsf_errors.
	 *
	 * @return array
	 */
	function wpsf_get_errors() {
		global $wpsf_errors;
		return $wpsf_errors;
	}
}

if ( ! function_exists( 'wpsf_modern_navs' ) ) {
	/**
	 * Renders Modern Theme Menu
	 *
	 * @param      $navs
	 * @param      $class
	 * @param null $parent
	 */
	function wpsf_modern_navs( $navs, $class, $parent = null ) {
		$parent = ( null === $parent ) ? '' : 'data-parent-section="' . $parent . '"';
		foreach ( $navs as $i => $nav ) :
			$title = ( isset( $nav['title'] ) ) ? $nav['title'] : '';
			$href  = ( isset( $nav['href'] ) && false !== $nav['href'] ) ? $nav['href'] : '#';
			if ( ! empty( $nav['submenus'] ) ) {
				$is_active    = ( isset( $nav['is_active'] ) && true === $nav['is_active'] ) ? ' style="display: block;"' : '';
				$is_active_li = ( isset( $nav['is_active'] ) && true === $nav['is_active'] ) ? ' wpsf-tab-active ' : '';
				echo '<li class="wpsf-sub ' . $is_active_li . '">';
				echo '<a href="#" class="wpsf-arrow">' . $class->icon( $nav ) . ' ' . $title . '</a>';
				echo '<ul ' . $is_active . '>';
				wpsf_modern_navs( $nav['submenus'], $class, $nav['name'] );
				echo '</ul>';
				echo '</li>';
			} else {
				if ( isset( $nav['is_separator'] ) && true === $nav['is_separator'] ) {
					echo '<li><div class="wpsf-seperator">' . $class->icon( $nav ) . ' ' . $title . '</div></li>';
				} else {
					$is_active = ( isset( $nav['is_active'] ) && true === $nav['is_active'] ) ? "class='wpsf-section-active'" : '';
					echo '<li>';
					echo '<a ' . $is_active . ' href="' . $href . '" ' . $parent . ' data-section="' . $nav['name'] . '">' . $class->icon( $nav ) . ' ' . $title . '</a>';
					echo '</li>';
				}
			}

		endforeach;
	}
}

if ( ! function_exists( 'wpsf_simple_render_submenus' ) ) {

	/**
	 * @param array $menus
	 * @param null  $parent_name
	 * @param array $class
	 */
	function wpsf_simple_render_submenus( $menus = array(), $parent_name = null, $class = array() ) {
		global $wpsf_submenus;
		$return = array();
		$first  = current( $menus );
		$first  = isset( $first['name'] ) ? $first['name'] : false;

		foreach ( $menus as $nav ) {
			if ( isset( $nav['is_separator'] ) && true === $nav['is_separator'] ) {
				continue;
			}
			$title     = ( isset( $nav['title'] ) ) ? $nav['title'] : '';
			$is_active = ( isset( $nav['is_active'] ) && true === $nav['is_active'] ) ? ' current ' : '';

			if ( empty( $is_active ) ) {
				$is_active = ( $parent_name !== $class->active() && $first === $nav['name'] ) ? 'current' : $is_active;
			}

			$href = '#';

			if ( isset( $nav['href'] ) && ( false !== $nav['href'] && '#' !== $nav['href'] && true !== $nav['is_internal_url'] ) ) {
				$href = $nav['href'];

				$is_active .= ' has-link ';
			}

			if ( isset( $nav['query_args'] ) && is_array( $nav['query_args'] ) ) {
				$url  = remove_query_arg( array_keys( $nav['query_args'] ) );
				$href = add_query_arg( array_filter( $nav['query_args'] ), $url );

				$is_active .= ' has-link ';
			}

			$icon     = $class->icon( $nav );
			$return[] = '<li> <a href="' . $href . '" class="' . $is_active . '" data-parent-section="' . $parent_name . '" data-section="' . $nav['name'] . '">' . $icon . ' ' . $title . '</a>';
		}
		$wpsf_submenus[ $parent_name ] = implode( '|</li>', $return );
	}
}