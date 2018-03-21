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

if( ! defined('ABSPATH') ) {
    die ();
} // Cannot access pages directly.

/**
 *
 * Taxonomy Class
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 */
class WPSFramework_Taxonomy extends WPSFramework_Abstract {
    /**
     *
     * instance
     *
     * @access private
     * @var class
     *
     */
    private static $instance = NULL;
    /**
     *
     * taxonomy options
     *
     * @access public
     * @var array
     *
     */
    public    $options = array();
    protected $type    = 'taxonomy';

    /**
     * WPSFramework_Taxonomy constructor.
     *
     * @param $options
     */
    public function __construct($options) {
        $this->options = apply_filters('wpsf_taxonomy_options', $options);
        $this->taxes   = array();
        if( ! empty ($this->options) ) {
            $this->addAction('admin_init', 'add_taxonomy_fields');
            $this->addAction("admin_enqueue_scripts", 'load_style_script');
        }
    }

    /**
     * @param array $options
     *
     * @return \class|\WPSFramework_Taxonomy
     */
    public static function instance($options = array()) {
        if( is_null(self::$instance) ) {
            self::$instance = new self ($options);
        }
        return self::$instance;
    }

    public function load_style_script() {
        global $pagenow, $taxnow;

        if( $pagenow === 'term.php' || $pagenow === 'edit-tags.php' ) {
            if( isset($this->taxes[$taxnow]) ) {
                wpsf_assets()->render_framework_style_scripts();
            }
        }
    }

    public function add_taxonomy_fields() {
        foreach( $this->options as $option ) {
            $opt_taxonomy = $option ['taxonomy'];
            $get_taxonomy = wpsf_get_var('taxonomy');
            if( $get_taxonomy == $opt_taxonomy ) {
                $this->addAction($opt_taxonomy . '_add_form_fields', 'render_taxonomy_form_fields');
                $this->addAction($opt_taxonomy . '_edit_form', 'render_taxonomy_form_fields');
                $this->addAction('created_' . $opt_taxonomy, 'save_taxonomy');
                $this->addAction('edited_' . $opt_taxonomy, 'save_taxonomy');
                $this->addAction('delete_' . $opt_taxonomy, 'delete_taxonomy');
                $this->taxes[$opt_taxonomy] = $opt_taxonomy;
            }
        }
    }

    /**
     * @param $term
     */
    public function render_taxonomy_form_fields($term) {

        $form_edit = ( is_object($term) && isset ($term->taxonomy) ) ? TRUE : FALSE;
        $taxonomy  = ( $form_edit ) ? $term->taxonomy : $term;
        $classname = ( $form_edit ) ? 'edit' : 'add';
        wp_nonce_field('wpsf-taxonomy', 'wpsf-taxonomy-nonce');
        echo '<div class="wpsf-framework wpsf-taxonomy wpsf-taxonomy-' . $classname . '-fields">';

        foreach( $this->options as $option ) {
            if( $taxonomy == $option ['taxonomy'] ) {
                $wpsf_errors = get_transient(wpsf_sanitize_title('wpsf-tt-' . $this->get_cache_key($option)));
                $wpsf_errors = is_array($wpsf_errors) ? $wpsf_errors : array();
                wpsf_add_errors($wpsf_errors);
                $tax_value = ( $form_edit ) ? wpsf_get_term_meta($term->term_id, $option ['id'], TRUE) : '';

                foreach( $option ['fields'] as $field ) {
                    $elem_value = $this->get_field_values($field, $tax_value);
                    echo wpsf_add_element($field, $elem_value, $option ['id']);
                }
            }
        }
        echo '</div>';
    }

    /**
     * @param $term_id
     */
    public function save_taxonomy($term_id) {
        if( wp_verify_nonce(wpsf_get_var('wpsf-taxonomy-nonce'), 'wpsf-taxonomy') ) {
            $taxonomy  = wpsf_get_var('taxonomy');
            $validator = new WPSFramework_DB_Save_Handler;
            foreach( $this->options as $request_value ) {
                if( $taxonomy == $request_value ['taxonomy'] ) {
                    $request_key = $request_value ['id'];
                    $request     = wpsf_get_var($request_key, array());

                    if( isset ($request ['_nonce']) ) {
                        unset ($request ['_nonce']);
                    }

                    if( isset ($request_value ['fields']) ) {
                        $meta_value = wpsf_get_term_meta($term_id, $request_key, TRUE);
                        $request    = $validator->loop_fields($request_value, $request, $meta_value);
                    }

                    $request = apply_filters('wpsf_save_taxonomy', $request, $request_key, $term_id);

                    if( empty ($request) ) {
                        wpsf_delete_term_meta($term_id, $request_key);
                    } else {
                        if( wpsf_get_term_meta($term_id, $request_key, TRUE) ) {
                            wpsf_update_term_meta($term_id, $request_key, $request);
                        } else {
                            wpsf_add_term_meta($term_id, $request_key, $request);
                        }
                    }
                    do_action("wpsf_taxonomy_saved", $term_id, $request_key, $request, $taxonomy);
                    set_transient(wpsf_sanitize_title('wpsf-tt-' . $this->get_cache_key($request_value)), $validator->get_errors(), 20);
                }

            }
        }
    }

    /**
     * @param $term_id
     */
    public function delete_taxonomy($term_id) {
        $taxonomy = wpsf_get_var('taxonomy');
        if( ! empty ($taxonomy) ) {
            foreach( $this->options as $request_value ) {
                if( $taxonomy == $request_value ['taxonomy'] ) {
                    $request_key = $request_value ['id'];
                    wpsf_delete_term_meta($term_id, $request_key);
                    do_action("wpsf_taxonomy_deleted", $term_id, $request_key, $taxonomy);
                }
            }
        }
    }
}
