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

/**
 *
 * Field: WP List Table.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 */
class WPSFramework_Option_wp_list_table extends WPSFramework_Options {
	/**
	 * WPSFramework_Option_text constructor.
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

		if ( ! empty( $this->field['items'] ) ) {
			$items = $this->field['items'];
		} else {
			$items = WPSFramework_Query::query( $this->field['options'], $this->field['query_args'], '' );
		}

		$table = new WPSFramework_List_Table( $this->field['options'], array(
			'query_args' => $this->field['query_args'],
			'settings'   => $this->field['settings'],
		), $items );

		$table->display();

		echo $this->element_after();
	}

	protected function field_defaults() {
		return array(
			'query_args' => array(),
			'items'      => array(),
			'option'     => '',
			'settings'   => array(),
			'options'    => false,
			'label'      => __( "Open Modal", 'wpsf-framework' ),
		);
	}
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPSFramework_List_Table extends WP_List_Table {

	/**
	 * items
	 *
	 * @var array|mixed|void
	 */
	public $items = array();

	/**
	 * settings
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 * type
	 *
	 * @var string
	 */
	public $type = '';

	/**
	 * WPSFramework_List_Table constructor.
	 *
	 * @param string $type
	 * @param array  $args
	 * @param array  $data
	 */
	public function __construct( $type = '', $args = array(), $data = array() ) {
		$this->type     = $type;
		$this->items    = $data;
		$settings       = isset( $args['settings'] ) ? $args['settings'] : array();
		$this->settings = wp_parse_args( $settings, array(
			'id'             => false,
			'remove_columns' => array(),
			'columns'        => array(),
		) );
		parent::__construct( array(
			'plural'   => '',
			'singular' => '',
			'ajax'     => false,
			'screen'   => 'post',
		) );
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	public static function is_cpt( $type ) {
		return in_array( $type, array( 'page', 'pages', 'post', 'posts' ) );
	}

	/**
	 * @uses prepare_items
	 */
	public function prepare_items() {
		$this->set_pagination_args( array(
			'total_items' => count( $this->items ),
			'per_page'    => 9999,
		) );
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		$cols = $this->default_cols( $this->type );

		if ( is_array( $this->settings['columns'] ) ) {
			foreach ( $this->settings['columns'] as $slug => $d ) {
				$title = $d;
				if ( is_array( $d ) ) {
					$title = isset( $d['title'] ) ? $d['title'] : '';
				}
				$cols[ $slug ] = $title;
			}
		}

		if ( is_array( $this->settings['remove_columns'] ) ) {
			foreach ( $this->settings['remove_columns'] as $slug ) {
				if ( isset( $cols[ $slug ] ) ) {
					unset( $cols[ $slug ] );
				}
			}
		}

		return $cols;
	}

	/**
	 * @param string $type
	 *
	 * @return array
	 */
	public function default_cols( $type = '' ) {
		if ( $this->is_tax( $type ) ) {
			return array(
				'title'       => __( "Name" ),
				'description' => __( "Description" ),
				'slug'        => __( "Slug" ),
				'post_count'  => __( "Count" ),
			);
		} elseif ( $this->is_page( $type ) || $this->is_post( $type ) ) {
			return array(
				'thumbnail' => __( "Image" ),
				'title'     => __( "Name" ),
				'author'    => __( "Author" ),
				'date'      => __( "Date" ),

			);
		} else {
			return array(
				'thumbnail' => __( "Image" ),
				'title'     => __( "Title" ),

			);
		}
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public function is_tax( $type = '' ) {
		return in_array( $this->get_type( $type ), array( 'tag', 'tags', 'category', 'categories' ) );
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_type( $type = '' ) {
		return empty( $type ) ? $this->type : $type;
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public function is_page( $type = '' ) {
		return in_array( $this->get_type( $type ), array( 'page', 'pages' ) );
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public function is_post( $type = '' ) {
		return in_array( $this->get_type( $type ), array( 'post', 'posts' ) );
	}

	/**
	 * @param $item
	 */
	public function column_date( $item ) {
		if ( $this->is_post() || $this->is_page() ) {
			global $mode;

			if ( '0000-00-00 00:00:00' === $item->post_date ) {
				$t_time    = $h_time = __( 'Unpublished' );
				$time_diff = 0;
			} else {
				$t_time    = get_the_time( __( 'Y/m/d g:i:s a' ), $item );
				$m_time    = $item->post_date;
				$time      = get_post_time( 'G', true, $item );
				$time_diff = time() - $time;

				if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
					$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
				} else {
					$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
				}
			}

			if ( 'publish' === $item->post_status ) {
				$status = __( 'Published' );
			} elseif ( 'future' === $item->post_status ) {
				if ( $time_diff > 0 ) {
					$status = '<strong class="error-message">' . __( 'Missed schedule' ) . '</strong>';
				} else {
					$status = __( 'Scheduled' );
				}
			} else {
				$status = __( 'Last Modified' );
			}

			$status = apply_filters( 'post_date_column_status', $status, $item, 'date', $mode );
			if ( $status ) {
				echo $status . '<br />';
			}
			echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $item, 'date', $mode ) . '</abbr>';
		} else {
			$this->column_default( $item, 'date' );
		}
	}

	/**
	 * @param object $item
	 * @param string $col_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $col_name ) {
		if ( isset( $this->settings['columns'][ $col_name ] ) ) {
			$_col = $this->settings['columns'][ $col_name ];
			if ( isset( $_col['callback'] ) && is_callable( $_col['callback'] ) ) {
				$func  = ( is_array( $_col['callback'] ) ) ? 'call_user_func_array' : 'call_user_func';
				$param = ( is_array( $_col['callback'] ) ) ? array( $item ) : $item;
				return $func( $_col['callback'], $param );
			}
		}
		if ( isset( $this->settings['id'] ) ) {
			return apply_filters( 'wpsf_modal_search_column_' . $this->settings['id'], '', $col_name, $item );
		}
		return apply_filters( "wpsf_modal_search_column", '', $col_name, $item );
	}

	/**
	 * @param $item
	 *
	 * @return string|void
	 */
	public function column_author( $item ) {
		if ( $this->is_page() || $this->is_post() ) {
			$args = array(
				'post_type' => $item->post_type,
				'author'    => $item->post_author,
			);

			$user = get_user_by( 'id', $item->post_author );
			if ( ! is_wp_error( $user ) ) {
				return $this->get_edit_link( $args, $user->display_name );
			}
			return __( "Unknown User" );
		} else {
			$this->column_default( $item, 'author' );
		}
	}

	/**
	 * Helper to create links to edit.php with params.
	 *
	 * @since 4.4.0
	 *
	 * @param array  $args URL parameters for the link.
	 * @param string $label Link text.
	 * @param string $class Optional. Class attribute. Default empty string.
	 *
	 * @return string The formatted link string.
	 */
	protected function get_edit_link( $args, $label, $class = '' ) {
		$url = add_query_arg( $args, 'edit.php' );

		$class_html = $aria_current = '';
		if ( ! empty( $class ) ) {
			$class_html = sprintf( ' class="%s"', esc_attr( $class ) );

			if ( 'current' === $class ) {
				$aria_current = ' aria-current="page"';
			}
		}

		return sprintf( '<a href="%s"%s%s>%s</a>', esc_url( $url ), $class_html, $aria_current, $label );
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_title( $item ) {
		if ( $this->is_tax( $this->type ) ) {
			$edit_Link = get_edit_term_link( $item->term_id, $item->taxonomy );
			$title     = sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', $edit_Link, $item->name );
			return '<strong>' . $title . '</strong>';
		} elseif ( $this->is_page() || $this->is_post() ) {

			$can_edit_post = current_user_can( 'edit_post', $item->ID );

			echo "<strong>";

			$format = get_post_format( $item->ID );
			if ( $format ) {
				$label        = get_post_format_string( $format );
				$format_class = 'post-state-format post-format-icon post-format-' . $format;
				$format_args  = array(
					'post_format' => $format,
					'post_type'   => $item->post_type,
				);
				echo $this->get_edit_link( $format_args, $label . ':', $format_class );
			}

			$title = empty( $item->post_title ) ? __( "(No Title)" ) : $item->post_title;


			if ( $can_edit_post && $item->post_status != 'trash' ) {
				printf( '<a class="row-title" href="%s" aria-label="%s">%s</a>', get_edit_post_link( $item->ID ), esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $title ) ), $title );
			} else {
				echo $title;
			}
			_post_states( $item );

			echo "</strong>\n";
		} else {
			$this->column_default( $item, 'title' );
		}
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_description( $item ) {
		if ( $this->is_tax() ) {
			return '<p>' . $item->description . '</p>';
		} else {
			$this->column_default( $item, 'description' );
		}
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_post_count( $item ) {
		if ( $this->is_tax() ) {
			$count = number_format_i18n( $item->count );

			$tax = get_taxonomy( $item->taxonomy );

			if ( $tax->query_var ) {
				$args = array( $tax->query_var => $item->slug );
			} else {
				$args = array( 'taxonomy' => $tax->name, 'term' => $item->slug );
			}

			return "<a href='" . esc_url( add_query_arg( $args, 'edit.php' ) ) . "'>$count</a>";
		} else {
			$this->column_default( $item, 'post_count' );
		}
	}

	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	public function column_slug( $item ) {
		if ( $this->is_tax() ) {
			return $item->slug;
		} else {
			$this->column_default( $item, 'slug' );
		}
	}


	/**
	 * @param array $item
	 *
	 * @return bool
	 */
	public function get_selected( $item = array() ) {
		if ( $this->is_tax() ) {
			return isset( $this->selected[ $item->term_id ] ) ? $item->term_id : false;
		} elseif ( $this->is_post() === true || $this->is_page() === true ) {
			return isset( $this->selected[ $item->ID ] ) ? $item->ID : false;
		}
	}

	public function column_thumbnail( $item = array() ) {
		$image_arr = ( isset( $item->image_args ) ) ? $item->image_args : array();
		echo get_the_post_thumbnail( $item->ID, 'thumbnail', $image_arr );
	}

	/**
	 *
	 */
	public function no_items() {
		_e( 'No Result found.' );
	}
}