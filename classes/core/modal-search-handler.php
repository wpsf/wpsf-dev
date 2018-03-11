<?php

/**
 * Created by PhpStorm.
 * User: varun
 * Date: 07-03-2018
 * Time: 10:01 AM
 */
Class WPSFramework_Modal_Search_Handler {
    public function __construct($type = '', $selected = array(), $query_data = array(), $query_args = array()) {
        $table = new WPSF_Modal_Search_Table($type, $query_args, $query_data, $selected);
        $table->views();
        $table->display();
        echo '<style>td.column-wpsfcbs,th.column-wpsfcbs{width:75px;}</style>';
    }
}

if( ! class_exists('WP_List_Table') ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPSF_Modal_Search_Table extends WP_List_Table {
    public $items    = array();
    public $settings = array();
    public $selected = array();
    public $type     = '';

    public function __construct($type = '', $args = array(), $data = array(), $selected = array()) {
        $this->type     = $type;
        $this->items    = $data;
        $this->selected = $selected;
        $settings       = isset($args['settings']) ? $args['settings'] : array();
        $this->settings = wp_parse_args($settings, array(
            'columns' => array(),
        ));
        parent::__construct(array(
            'plural'   => '',
            'singular' => '',
            'ajax'     => TRUE,
            'screen'   => NULL,
        ));
    }

    public static function is_cpt($type) {
        return in_array($type, array( 'page', 'pages', 'post', 'posts' ));
    }

    public function prepare_items() {
        $this->set_pagination_args(array(
            'total_items' => count($this->items),
            'per_page'    => 3,
        ));
    }

    public function get_columns() {
        $_cols = $this->default_cols($this->type);
        $cols  = array_merge(array( 'wpsfcbs' => '' ), $_cols);

        if( is_array($this->settings['columns']) ) {
            foreach( $this->settings['columns'] as $slug => $d ) {
                $title = $d;
                if( is_array($d) ) {
                    $title = isset($d['title']) ? $d['title'] : '';
                }
                $cols[$slug] = $title;
            }
        }

        return $cols;
    }

    public function default_cols($type = '') {
        if( $this->is_tax($type) ) {
            return array(
                'title'       => __("Name"),
                'description' => __("Description"),
                'slug'        => __("Slug"),
                'post_count'  => __("Count"),
            );
        } else if( $this->is_page($type) ) {
            return array(
                'title'  => __("Name"),
                'author' => __("Author"),
                'date'   => __("Date"),
            );
        }
    }

    public function is_tax($type = '') {
        return in_array($this->get_type($type), array( 'tag', 'tags', 'category', 'categories' ));
    }

    public function get_type($type = '') {
        return empty($type) ? $this->type : $type;
    }

    public function is_page($type = '') {
        return in_array($this->get_type($type), array( 'page', 'pages' ));
    }

    public function column_date($item) {
        if( $this->is_post() || $this->is_page() ) {
            global $mode;

            if( '0000-00-00 00:00:00' === $item->post_date ) {
                $t_time    = $h_time = __('Unpublished');
                $time_diff = 0;
            } else {
                $t_time    = get_the_time(__('Y/m/d g:i:s a'), $item);
                $m_time    = $item->post_date;
                $time      = get_post_time('G', TRUE, $item);
                $time_diff = time() - $time;

                if( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
                    $h_time = sprintf(__('%s ago'), human_time_diff($time));
                } else {
                    $h_time = mysql2date(__('Y/m/d'), $m_time);
                }
            }

            if( 'publish' === $item->post_status ) {
                $status = __('Published');
            } else if( 'future' === $item->post_status ) {
                if( $time_diff > 0 ) {
                    $status = '<strong class="error-message">' . __('Missed schedule') . '</strong>';
                } else {
                    $status = __('Scheduled');
                }
            } else {
                $status = __('Last Modified');
            }

            $status = apply_filters('post_date_column_status', $status, $item, 'date', $mode);
            if( $status ) {
                echo $status . '<br />';
            }
            echo '<abbr title="' . $t_time . '">' . apply_filters('post_date_column_time', $h_time, $item, 'date', $mode) . '</abbr>';
        }
    }

    public function is_post($type = '') {
        return in_array($this->get_type($type), array( 'post', 'posts' ));
    }

    public function column_author($item) {
        if( $this->is_page() || $this->is_post() ) {
            $args = array(
                'post_type' => $item->post_type,
                'author'    => $item->post_author,
            );

            $user = get_user_by('id', $item->post_author);
            if( ! is_wp_error($user) ) {
                return $this->get_edit_link($args, $user->display_name);
            }
            return __("Unknown User");
        }
    }

    /**
     * Helper to create links to edit.php with params.
     *
     * @since 4.4.0
     *
     * @param array  $args  URL parameters for the link.
     * @param string $label Link text.
     * @param string $class Optional. Class attribute. Default empty string.
     *
     * @return string The formatted link string.
     */
    protected function get_edit_link($args, $label, $class = '') {
        $url = add_query_arg($args, 'edit.php');

        $class_html = $aria_current = '';
        if( ! empty($class) ) {
            $class_html = sprintf(' class="%s"', esc_attr($class));

            if( 'current' === $class ) {
                $aria_current = ' aria-current="page"';
            }
        }

        return sprintf('<a href="%s"%s%s>%s</a>', esc_url($url), $class_html, $aria_current, $label);
    }

    /**
     * @param object $item
     * @param string $col_name
     *
     * @return mixed
     */
    public function column_default($item, $col_name) {
        return apply_filters("wpsf_modal_search_column", '', $col_name, $item);
    }

    public function column_title($item) {
        if( $this->is_tax($this->type) ) {
            $edit_Link = get_edit_term_link($item->term_id, $item->taxonomy);
            $title     = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $edit_Link, $item->name);
            return '<strong>' . $title . '</strong>';
        }

        if( $this->is_page() || $this->is_post() ) {
            $can_edit_post = current_user_can('edit_post', $item->ID);

            echo "<strong>";

            $format = get_post_format($item->ID);
            if( $format ) {
                $label        = get_post_format_string($format);
                $format_class = 'post-state-format post-format-icon post-format-' . $format;
                $format_args  = array(
                    'post_format' => $format,
                    'post_type'   => $item->post_type,
                );
                echo $this->get_edit_link($format_args, $label . ':', $format_class);
            }

            $title = empty($item->post_title) ? __("(No Title)") : $item->post_title;


            if( $can_edit_post && $item->post_status != 'trash' ) {
                printf('<a class="row-title" href="%s" aria-label="%s">%s</a>', get_edit_post_link($item->ID), esc_attr(sprintf(__('&#8220;%s&#8221; (Edit)'), $title)), $title);
            } else {
                echo $title;
            }
            _post_states($item);

            echo "</strong>\n";

        }
    }

    public function column_description($item) {
        if( $this->is_tax() ) {
            return '<p>' . $item->description . '</p>';
        }
    }

    public function column_post_count($item) {
        if( $this->is_tax() ) {
            $count = number_format_i18n($item->count);

            $tax = get_taxonomy($item->taxonomy);

            if( $tax->query_var ) {
                $args = array( $tax->query_var => $item->slug );
            } else {
                $args = array( 'taxonomy' => $tax->name, 'term' => $item->slug );
            }

            return "<a href='" . esc_url(add_query_arg($args, 'edit.php')) . "'>$count</a>";
        }
    }

    public function column_slug($item) {
        if( $this->is_tax() ) {
            return $item->slug;
        }
    }

    public function column_wpsfcbs($item) {
        $label = '';
        $value = '';
        if( $this->is_tax() ) {
            $label = $item->name;
            $value = $item->term_id;
        } else if( $this->is_post() || $this->is_page() ) {
            $label = $item->post_title;
            $value = $item->ID;
        }

        return wpsf_add_element(array(
            'type'         => 'switcher',
            'id'           => 'tag',
            'class'        => 'wpsfModalInput',
            'attributes'   => array(
                'data-label' => $label,
            ),
            'switch_value' => $value,
        ), $this->get_selected($item));
    }

    public function get_selected($item = array()) {
        if( $this->is_tax() ) {
            return isset($this->selected[$item->term_id]) ? $item->term_id : FALSE;
        }
    }

    public function no_items() {
        _e('No Result found.');
    }
}