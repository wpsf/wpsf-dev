<?php
/**
 * Created by PhpStorm.
 * Project : wpsf
 * User: varun
 * Date: 13-03-2018
 * Time: 02:39 PM
 */

class WPSFramework_Settings extends WPSFramework_Abstract {
    protected $type               = 'settings';
    protected $framework_defaults = array(
        'menu_type'         => '',
        'menu_parent'       => '',
        'menu_title'        => 'WPSF Settings',
        'menu_slug'         => 'wpsf',
        'menu_capability'   => 'manage_options',
        'menu_icon'         => NULL,
        'menu_position'     => NULL,
        'show_submenus'     => FALSE,
        'ajax_save'         => FALSE,
        'buttons'           => array(),
        'option_name'       => FALSE,
        'override_location' => '',
        'extra_css'         => array(),
        'extra_js'          => array(),
        'is_single_page'    => FALSE,
        'is_sticky_header'  => FALSE,
        'style'             => 'modern',
        'help_tabs'         => array(),
    );
    protected $fields_md5         = NULL;
    protected $page_hook          = NULL;
    protected $menus              = array();
    private   $active_menu        = array();

    /**
     * WPSFramework_Settings constructor.
     *
     * @param array  $settings
     * @param array  $fields
     * @param string $plugin_id
     */
    public function __construct($settings = array(), $fields = array(), $plugin_id = '') {
        if( ! empty($fields) && ! empty($settings) && $this->is_not_ajax() ) {
            $this->raw_options                       = $fields;
            $this->framework_defaults['option_name'] = $this->unique;
            $settings                                = wp_parse_args($settings, $this->framework_defaults);
            $settings['buttons']                     = wp_parse_args($settings['buttons'], array(
                'save'    => __("Save", 'wpsf-wp'),
                'restore' => __("Restore", 'wpsf-wp'),
                'reset'   => __("Reset All Options", 'wpsf-wp'),
            ));

            $this->plugin_id         = empty($plugin_id) ? $settings['option_name'] : $plugin_id;
            $this->settings          = $this->_filter("settings", $settings);
            $this->options           = $this->_filter('fields', $fields);
            $this->unique            = ( ! empty($this->settings['option_name']) ) ? $this->settings['option_name'] : $this->unique;
            $this->override_location = ( isset($settings['override_location']) ) ? $settings['override_location'] : FALSE;
            $this->addAction('admin_init', 'register_settings');
            $this->addAction('admin_menu', 'admin_menu');
            wpsf_registry()->add($this);
        }
    }

    /**
     * Register Settings To WP
     *
     * @todo add SetDefaults Function
     */
    public function register_settings() {
        $cache = $this->get_cache();
        register_setting($this->unique, $this->unique, array(
            'sanitize_callback' => array( &$this, 'validate_save' ),
        ));

        if( ! isset($cache['md5']) || ( isset($cache['md5']) && $cache['md5'] !== $this->fields_md5() ) ) {
            $this->set_defaults();
        }
    }

    /**
     * Retrives Cache From DB and returns it
     *
     * @return array
     */
    public function get_cache() {
        if( empty($this->cache) ) {
            $cache       = get_option($this->unique . '-transient', array());
            $this->cache = ( is_array($cache) ) ? $cache : array();
        }
        return $this->cache;
    }

    /**
     * Encodes raw_options array and converts into MD5 to get an unique ID for settings fields
     *
     * @return null|string
     */
    protected function fields_md5() {
        if( empty($this->fields_md5) ) {
            $this->fields_md5 = md5(json_encode($this->raw_options));
        }
        return $this->fields_md5;
    }

    public function set_defaults() {
        $defaults = array();
        $this->get_db_options();
        foreach( $this->get_sections() as $section ) {
            foreach( $section['fields'] as $field_key => $field ) {
                if( isset($field['default']) && ! isset($this->get_option[$field['id']]) ) {
                    $defaults[$field['id']]         = $field['default'];
                    $this->db_options[$field['id']] = $field['default'];
                }
            }
        }
        if( ! empty($defaults) ) {
            update_option($this->unique, $this->db_options);
        }
        $this->cache['md5'] = $this->fields_md5();
        $this->set_cache($this->cache);
    }

    /**
     * Retrives Stored Options From DB
     *
     * @return array|mixed
     */
    public function get_db_options() {
        if( empty($this->db_options) ) {
            $this->db_options = get_option($this->unique, TRUE);
            $this->db_options = ( empty($this->db_options) || $this->db_options === TRUE ) ? array() : $this->db_options;
        }
        return $this->db_options;
    }

    /**
     * @return array
     */
    protected function get_sections() {
        $sections = array();
        foreach( $this->options as $key => $page ) {
            $page_id = $page['name'];
            if( isset($page['sections']) ) {
                foreach( $page['sections'] as $_key => $section ) {
                    $section_id = $section['name'];
                    if( isset($section['fields']) ) {
                        $section['page_id']                     = $page_id;
                        $sections[$page_id . '/' . $section_id] = $section;
                    }
                }

            } else {
                if( isset($page['callback_hook']) ) {
                    $page['fields'] = array();
                }

                if( isset($page['fields']) ) {
                    $page['page_id']    = FALSE;
                    $sections[$page_id] = $page;
                }
            }

        }

        return $sections;
    }

    /**
     * @param array $data
     */
    public function set_cache($data = array()) {
        update_option($this->unique . '-transient', $data);
        $this->cache = $data;
    }

    /**
     * @param $request
     *
     * @return array
     */
    public function validate_save($request) {
        $this->options = $this->map_error_id($this->options);
        $this->find_active_menu();

        if( isset($request['_nonce']) ) {
            unset ($request ['_nonce']);
        }

        $save_handler = new WPSFramework_DB_Save_Handler();
        $request      = $save_handler->handle_settings_page(array(
            'is_single_page'     => $this->is('single_page'),
            'current_section_id' => $this->active(FALSE),
            'current_parent_id'  => $this->active(TRUE),
            'db_key'             => $this->unique,
            'posted_values'      => $request,
        ), $this->get_sections());


        $add_errors = $save_handler->get_errors();
        unset($this->cache['parent_section_id']);
        $this->cache['errors']     = $add_errors;
        $this->cache['section_id'] = $this->active(FALSE);
        $this->cache['parent_id']  = $this->active(TRUE);
        $this->set_cache($this->cache);
        return $request;
    }

    /**
     * Finds Active Menu for the given options
     */
    private function find_active_menu() {
        $cache  = $this->get_cache();
        $_cache = array(
            'section_id' => ( ! empty($cache['section_id']) ) ? $cache['section_id'] : FALSE,
            'parent_id'  => ( ! empty($cache['parent_id']) ) ? $cache['parent_id'] : FALSE,
        );
        $_url   = array(
            'section_id' => wpsf_get_var('wpsf-section-id', FALSE),
            'parent_id'  => wpsf_get_var('wpsf-parent-id', FALSE),
        );

        $_cache_v = $this->validate_section_ids($_cache);
        $_url_v   = $this->validate_section_ids($_url);

        if( $_cache_v !== FALSE ) {
            $default = $this->validate_sections($_cache_v['parent_id'], $_cache_v['section_id']);

            $this->cache['section_id'] = FALSE;
            $this->cache['parent_id']  = FALSE;
            $this->set_cache($this->cache);
        } else if( $_url_v != FALSE ) {
            $default = $this->validate_sections($_url_v['parent_id'], $_url_v['section_id']);
        } else {
            $default = $this->validate_sections(FALSE, FALSE);
        }

        if( ( is_null($default['section_id']) || $default['section_id'] === FALSE ) && $default['parent_id'] ) {
            $default['section_id'] = $default['parent_id'];
        }
        $this->active_menu = $default;
    }

    /**
     * Validate Given Section IDS
     *
     * @param array $ids
     *
     * @return array|bool
     */
    public function validate_section_ids($ids = array()) {
        if( empty(array_filter($ids)) ) {
            return FALSE;
        } else if( empty($ids['section_id']) && ! empty($ids['parent_id']) ) {
            return array( 'section_id' => FALSE, 'parent_id' => $ids['parent_id'] );
        } else if( ! empty($ids['section_id']) && empty($ids['parent_id']) ) {
            return array( 'section_id' => FALSE, 'parent_id' => $ids['section_id'] );
        } else {
            return array( 'section_id' => $ids['section_id'], 'parent_id' => $ids['parent_id'] );
        }
    }

    /**
     * @param string $parent_id
     * @param string $section_id
     *
     * @return array
     */
    public function validate_sections($parent_id = '', $section_id = '') {
        $parent_id  = $this->is_page_section_exists($parent_id, $section_id);
        $section_id = $this->is_page_section_exists($parent_id, $section_id, TRUE);
        return array( 'section_id' => $section_id, 'parent_id' => $parent_id );
    }

    /**
     * @param string $page_id
     * @param string $section_id
     * @param bool   $is_section
     *
     * @return bool|null|string
     */
    public function is_page_section_exists($page_id = '', $section_id = '', $is_section = FALSE) {
        foreach( $this->options as $option ) {
            if( $option['name'] === $page_id && $is_section === FALSE ) {
                return $page_id;
            } else if( $option['name'] === $page_id && isset($option['sections']) ) {
                foreach( $option['sections'] as $section ) {
                    if( $section['name'] === $section_id ) {
                        return $section_id;
                    }
                }
            }
        }

        $page_id = ( $is_section === TRUE ) ? $page_id : NULL;
        return $this->get_page_section_id($is_section, $page_id);
    }

    /**
     * @param bool $is_section
     * @param null $page
     *
     * @return bool|null
     */
    private function get_page_section_id($is_section = TRUE, $page = NULL) {
        if( $page !== NULL ) {
            foreach( $this->options as $option ) {
                if( $option['name'] === $page && $is_section === FALSE ) {
                    return $option['name'];
                } else if( $option['name'] === $page && $is_section === TRUE && isset($option['sections']) ) {
                    $cs = current($option['sections']);
                    return $cs['name'];
                }
            }
        } else {
            $cs = current($this->options);
            if( $is_section === TRUE && isset($cs['sections']) ) {
                $cs = current($cs['sections']);
                return $cs['name'];
            }

            return isset($cs['name']) ? $cs['name'] : FALSE;
        }
        return FALSE;
    }

    /**
     * @param string $type
     * @param bool   $status
     *
     * @return bool|mixed|string
     */
    public function is($type = '', $status = FALSE) {
        switch( $type ) {
            case 'single_page':
            case 'sp':
                return ( $this->_option('is_single_page') === TRUE ) ? TRUE : FALSE;
            break;

            case 'sticky_header':
            case 'sticky_head':
                return ( $this->_option('is_sticky_header') === TRUE ) ? TRUE : FALSE;
            break;
            case'ajax_save':
                return $this->_option('ajax_save');
            break;
            case'has_nav':
                return ( count($this->options) <= 1 ) ? FALSE : TRUE;
            break;
            case 'page_active' :
                return ( $status === TRUE ) ? 'style="display:block;"' : '';
            default:
                return FALSE;
            break;
        }
    }

    /**
     * Returns Current active Menu
     *
     * @param bool $is_parent
     *
     * @return bool|mixed
     */
    public function active($is_parent = TRUE) {
        if( $is_parent === TRUE ) {
            return ( isset($this->active_menu['parent_id']) ) ? $this->active_menu['parent_id'] : FALSE;
        }
        return ( isset($this->active_menu['section_id']) ) ? $this->active_menu['section_id'] : FALSE;
    }

    /**
     * Adds Admin Menu
     */
    public function admin_menu() {
        $pm        = $this->settings['menu_parent'];
        $type      = $this->settings['menu_type'];
        $i         = $this->settings['menu_icon'];
        $p         = $this->settings['menu_position'];
        $_t        = $this->settings['menu_title'];
        $ac        = $this->settings['menu_capability'];
        $slug      = $this->settings['menu_slug'];
        $menu_type = 'parent';

        switch( $type ) {
            case 'submenu':
                $menu_type       = 'submenu';
                $this->page_hook = add_submenu_page($pm, $_t, $_t, $ac, $slug, array( &$this, 'render_page' ));
            break;
            case 'management':
            case 'dashboard':
            case 'options':
            case 'plugins':
            case 'theme':
                $menu_type = 'submenu';
                $f         = 'add_' . $type . '_page';
                if( function_exists($f) ) {
                    $this->page_hook = $f($_t, $_t, $ac, $slug, array( &$this, 'render_page', ), $i, $p);
                }
            break;
            default:
                $this->page_hook = add_menu_page($_t, $_t, $ac, $slug, array( &$this, 'render_page', ), $i, $p);
            break;
        }

        $this->_action('settings_menu', $this->page_hook, $menu_type, $this);
        $this->addAction('load-' . $this->page_hook, 'init_page');
    }

    /**
     * Renders HTML Source
     */
    public function render_page() {
        wpsf_template($this->override_location, 'settings.php', array( 'class' => $this ));
    }

    /**
     * Runs @ load-{$page_hook} action
     */
    public function init_page() {
        global $wpsf_errors;
        $this->addAction("admin_enqueue_scripts", 'load_assets');
        $this->options = $this->map_error_id($this->options);
        $this->get_db_options();
        $this->find_active_menu();
        $this->menus = $this->filter("menus", $this->extract_menus(), $this);
        $errors      = ( isset($this->cache['errors']) ) ? $this->cache['errors'] : array();
        wpsf_add_errors($errors);
    }

    /**
     * Extract Menus From the options array
     *
     * @param array       $ops
     * @param string|bool $parent
     *
     * @return array
     */
    public function extract_menus($ops = array(), $parent = FALSE) {
        $output = array();
        $array  = ( empty($ops) ) ? $this->options : $ops;

        foreach( $array as $option ) {
            $name = isset($option['name']) ? $option['name'] : '';
            if( isset($option['sections']) ) {
                $is_active     = ( $this->active(TRUE) === $option['name'] ) ? TRUE : FALSE;
                $output[$name] = array(
                    'name'         => $name,
                    'title'        => ( isset($option['title']) ) ? $option['title'] : '',
                    'icon'         => ( isset($option['icon']) ) ? $option['icon'] : '',
                    'is_separator' => FALSE,
                    'is_active'    => $is_active,
                    'href'         => ( isset($option['href']) ) ? $option['href'] : $this->get_tab_url($name, NULL),
                    'submenus'     => $this->_filter('submenu', $this->extract_menus($option['sections'], $name), $name),
                );
            } else {
                $is_active = ( $this->active(FALSE) === $option['name'] ) ? TRUE : FALSE;

                $output[$name] = array(
                    'name'         => $name,
                    'title'        => ( isset($option['title']) ) ? $option['title'] : '',
                    'icon'         => ( isset($option['icon']) ) ? $option['icon'] : '',
                    'href'         => ( isset($option['href']) ) ? $option['href'] : $this->get_tab_url($name, $parent),
                    'submenus'     => array(),
                    'is_active'    => $is_active,
                    'is_separator' => ( isset($option['fields']) || isset($option['callback_hook']) || isset($option['href']) ) ? FALSE : TRUE,
                );
            }
        }
        return $output;
    }

    /**
     * @param string $section
     * @param string $parent
     *
     * @return string
     */
    public function get_tab_url($section = '', $parent = '') {
        if( $this->is('single_page') !== TRUE ) {
            $data = array( 'wpsf-section-id' => $section, 'wpsf-parent-id' => $parent );
            $url  = remove_query_arg(array_keys($data));
            return add_query_arg(array_filter($data), $url);
        }
        return '#';
    }


    /**
     * Register & Loads Required WPSF Assets
     */
    public function load_assets() {
        wpsf_assets()->render_framework_style_scripts();

        if( isset($this->settings['extra_css']) && is_array($this->settings['extra_css']) ) {
            foreach( $this->settings['extra_css'] as $id ) {
                wp_enqueue_style($id);
            }
        }

        if( isset($this->settings['extra_js']) && is_array($this->settings['extra_js']) ) {
            foreach( $this->settings['extra_js'] as $id ) {
                wp_enqueue_script($id);
            }
        }
    }

    /**
     * Returns Active Theme Name
     *
     * @return bool|mixed|string
     */
    public function theme() {
        return ( ! empty($this->_option('style')) ) ? $this->_option('style') : 'modern';
    }

    /**
     * Returns Settings Button
     *
     * @return string
     */
    public function get_settings_buttons() {
        $this->catch_output('start');
        if( $this->settings['buttons']['save'] !== FALSE ) {
            $text = ( $this->settings['buttons']['save'] === TRUE ) ? 'Save' : $this->settings['buttons']['save'];
            submit_button(esc_html($text), 'primary wpsf-save', 'save', FALSE, array( 'data-save' => esc_html__('Saving...', 'wpsf-wp') ));
        }

        if( $this->settings['buttons']['restore'] !== FALSE ) {
            $text = ( $this->settings['buttons']['restore'] === TRUE ) ? 'Save' : $this->settings['buttons']['restore'];
            submit_button(esc_html($text), 'secondary wpsf-restore wpsf-reset-confirm', $this->unique . '[reset]', FALSE);
        }

        if( $this->settings['buttons']['reset'] !== FALSE ) {
            $text = ( $this->settings['buttons']['reset'] === TRUE ) ? "Reset All Options" : $this->settings['buttons']['reset'];
            submit_button($text, 'secondary wpsf-restore wpsf-warning-primary wpsf-reset-confirm', $this->unique . '[resetall]', FALSE);
        }

        return $this->catch_output(FALSE);
    }

    /**
     * Returns Menus List
     *
     * @return array
     */
    public function navs() {
        return $this->menus;
    }

    /**
     * Renders Icon HTML
     *
     * @param $data
     *
     * @return string
     */
    public function icon($data) {
        return ( isset($data['icon']) && ! empty($data['icon']) ) ? '<i class="wpsf-icon ' . $data['icon'] . '"></i>' : '';
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function get_title($data) {
        return ( isset($data['title']) && ! empty($this->is('has_nav')) ) ? '<div class="wpsf-section-title"><h3>' . $data['title'] . '</h3></div>' : '';
    }

    /**
     * @param $data
     *
     * @return bool|string
     */
    public function render_fields($data) {
        if( isset($data['callback_hook']) ) {
            $this->catch_output();
            do_action($data['callback_hook'], $this);
            return $this->catch_output('end');
        } else if( isset($data['fields']) ) {
            $r = '';
            foreach( $data['fields'] as $field ) {
                $r .= $this->field_callback($field);
            }
            return $r;
        }
        return FALSE;
    }

    /**
     * @param $field
     *
     * @return string
     */
    public function field_callback($field) {
        $value = $this->get_field_values($field, $this->get_db_options());
        return wpsf_add_element($field, $value, $this->unique);
    }
}