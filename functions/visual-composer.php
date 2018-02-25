<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 22-02-2018
 * Time: 01:41 PM
 */

class WPSFramework_VC_Field {
    public    $type        = 'text';
    protected $extra_class = 'wpb_vc_param_value';
    protected $setting     = array();
    protected $value       = '';
    protected $field_arr   = array();
    protected $_vc_keys    = array(
        'type',
        'holder',
        'class',
        'heading',
        'param_name',
        'value',
        'description',
        'admin_label',
        'dependency',
        'edit_field_class',
        'weight',
        'group',
        'vc_single_param_edit_holder_class',
    );
    protected $_unique_key = '';

    public function __construct($settings = array(), $value = array(), $type = '') {
        $this->setting   = $settings;
        $this->field_arr = $settings;
        $this->value     = $value;
        $this->type      = $type;
    }

    public function option($key = '', $default = FALSE) {
        if( isset($this->setting[$key]) ) {
            return $this->setting[$key];
        }
        return $default;
    }

    public function render() {
        return wpsf_add_element($this->field_array(), $this->value, $this->_unique_key);
    }

    public function field_array() {
        $replace_fields = $this->replace_keys();
        $return         = array();

        foreach( $replace_fields as $replace => $base ) {
            if( isset($this->setting[$base]) ) {
                $return[$replace] = $this->setting[$base];
            }
        }

        $return         = array_merge($return, $this->filtered_settings());
        $return['type'] = $this->type;

        $return['class'] = isset($return['class']) ? $return['class'] : '';
        $return['class'] = $return['class'] . ' ' . $this->extra_class($return);

        $return['wrap_attributes']                    = isset($return['wrap_attributes']) ? $return['wrap_attributes'] : array();
        $return['wrap_attributes']['data-param-name'] = $return['id'];
        $return['wrap_attributes']                    = $this->extra_wrap_attributes($return['wrap_attributes'], $return);

        $return['id']   = strtolower($return['id']);
        $return['name'] = strtolower($return['name']);

        $return['default'] = isset($return['default']) ? $return['default'] : NULL;
        $this->value       = ( is_null($this->value($return)) ) ? $return['default'] : $this->value($return);

        return $return;
    }

    private function replace_keys() {
        return array(
            'class'      => 'class',
            'id'         => 'param_name',
            'name'       => 'param_name',
            'dependency' => 'dependency',
            'default'    => 'std',
        );
    }

    public function filtered_settings() {
        $r = $this->setting;
        foreach( $this->vc_keys() as $i ) {
            if( isset($r[$i]) ) {
                unset($r[$i]);
            }
        }
        return $r;
    }

    private function vc_keys() {
        return $this->_vc_keys;
    }

    public function extra_class($return) {
        return $this->extra_class;
    }

    public function extra_wrap_attributes($attr, $return) {
        return $attr;
    }

    public function value($return) {
        return $this->value;
    }

    public function explode_pipeline($value) {
        $return = array();
        $data   = explode('|', $value);

        if( ! empty(array_filter($data)) && count($data) > 0 ) {
            foreach( $data as $val ) {
                $_data = array_filter(explode(":", $val, 2));
                if( count($_data) == 2 ) {
                    if( ! isset($return[$_data[0]]) ) {
                        $return[$_data[0]] = array();
                    }
                    $_data[1] = ( isset($_data[1]) ) ? $_data[1] : '';

                    $is_array = explode(',', $_data[1]);
                    if( is_array($is_array) && count($is_array) > 1 ) {
                        $return[$_data[0]] = array_merge($is_array, $return[$_data[0]]);
                    } else {
                        $return[$_data[0]] = $_data[1];
                    }
                }
            }
        }

        return $return;
    }

    public function decode($value) {
        $v = $this->is_encoded($value);
        if( $v === TRUE ) {
            return json_decode(urldecode($this->base64_val), TRUE);
        }
        return FALSE;
    }

    public function is_encoded($value) {
        if( ! isset($this->base64_val) ) {
            $value = base64_decode($value, TRUE);
            if( $value === FALSE ) {
                return FALSE;
            }
            $this->base64_val = $value;
            return TRUE;
        }
        return TRUE;
    }
}

class WPSFramework_VC_checkbox_Field extends WPSFramework_VC_Field {
    public $type = 'checkbox';

    public function extra_class($return) {
        if( ! isset($return['options']) ) {
            return $this->extra_class;
        }
        return '';
    }

    public function value($return) {
        if( ! isset($return['options']) ) {
            return $this->value;
        }

        $m_data = $this->explode_pipeline($this->value);

        if( ! empty(array_filter($m_data)) ) {
            return $m_data;
        }

        /*$is_m = explode('|', $this->value);

        if( ! empty(array_filter($is_m)) && count($is_m) > 1 ) {
            $r = array();
            foreach( array_filter($is_m) as $value ) {
                $value = array_filter(explode(':', $value));
                if( count($value) == 2 ) {
                    if( ! isset($r[$value[0]]) ) {
                        $r[$value[0]] = array();
                    }
                    $r[$value[0]] = array_merge(explode(',', $value[1]), $r[$value[0]]);
                }
            }
            return $r;
        }*/

        return explode(',', $this->value);
    }
}

class WPSFramework_VC_radio_Field extends WPSFramework_VC_checkbox_Field {
    public $type = 'radio';
}

class WPSFramework_VC_image_select_Field extends WPSFramework_VC_checkbox_Field {
    public $type = 'image_select';

}

class WPSFramework_VC_heading_Field extends WPSFramework_VC_Field {
    public    $type        = 'heading';
    protected $extra_class = '';
}

class WPSFramework_VC_subheading_field extends WPSFramework_VC_Field {
    public    $type        = 'subheading';
    protected $extra_class = '';
}

class WPSFramework_VC_notice_field extends WPSFramework_VC_Field {
    public    $type        = 'notice';
    protected $extra_class = '';
}

class WPSFramework_VC_content_Field extends WPSFramework_VC_Field {
    public    $type        = 'content';
    protected $extra_class = '';
}

class WPSFramework_VC_select_Field extends WPSFramework_VC_Field {
    public $type = 'select';

    public function value($return) {
        return explode(",", $this->value);
    }
}

class WPSFramework_VC_background_Field extends WPSFramework_VC_Field {
    public $type = 'background';

    public function value($return) {
        return $this->explode_pipeline($this->value); // TODO: Change the autogenerated stub
    }
}

class WPSFramework_VC_sorter_Field extends WPSFramework_VC_Field {
    public $type = 'sorter';

    /**
     * @todo check ifits required
     * @return array
     */
    public function __pipe_sep() {
        $values = $this->explode_pipeline($this->value);
        $values = ( is_array($values) ) ? $values : array();
        $_vals  = array();
        foreach( $values as $i => $val ) {
            if( is_array($val) ) {
                $_vals[$i] = array();
                foreach( $val as $e => $v ) {
                    $ep = explode(":", $v);
                    if( ! empty(array_filter($ep)) && count($ep) > 1 ) {
                        $_vals[$i][$ep[0]] = $ep[1];
                    }
                }
            }
        }

        return $_vals; // TODO: Change the autogenerated stub
    }

    public function value($return) {
        $this->is_encoded($this->value);
        $values = $this->decode($this->value);

        if( ! isset($values['disabled']) ) {
            $values['disabled'] = array();
        }

        if( ! isset($values['enabled']) ) {
            $values['enabled'] = array();
        }
        return $values;


    }
}

class WPSFramework_VC_fieldset_Field extends WPSFramework_VC_Field {
    public $type = 'fieldset';

    public function value($return) {
        $this->is_encoded($this->value);
        $values = $this->decode($this->value);
        return $values;
    }
}

class WPSFramework_VC_accordion_Field extends WPSFramework_VC_Field {
    public $type = 'accordion';

    public function value($return) {
        $this->is_encoded($this->value);
        $values = $this->decode($this->value);
        return $values;
    }
}

class WPSFramework_VC_tab_Field extends WPSFramework_VC_Field {
    public $type = 'tab';

    public function value($return) {
        $this->is_encoded($this->value);
        $values = $this->decode($this->value);
        return $values;
    }
}

class WPSFramework_VC_social_icons_Field extends WPSFramework_VC_Field {
    public $type = 'social_icons';

    public function value($return) {
        $this->is_encoded($this->value);
        $values = $this->decode($this->value);
        return $values;
    }
}

class WPSFramework_VC_color_scheme_Field extends WPSFramework_VC_checkbox_Field {
    public $type = 'color_scheme';
}

class WPSFramework_VC_image_size_Field extends WPSFramework_VC_Field {
    public $type = 'image_size';

    public function value($return) {
        return $this->explode_pipeline($this->value);
    }
}

class WPSFramework_VC_css_builder_Field extends WPSFramework_VC_Field {
    public $type = 'css_builder';

    public function value($return) {
        $this->is_encoded($this->value);
        return $this->decode($this->value);
    }
}

/**
 * @todo Group Field Not Working :(
 * Class WPSFramework_VC_group_Field
 */
class WPSFramework_VC_group_Field extends WPSFramework_VC_Field {
    public $type = 'group';

    public function value($return) {
        $this->is_encoded($this->value);
        return $this->decode($this->value);
    }
}

if( ! class_exists('WPSFramework_Visual_Composer_Integration') ) {
    final class WPSFramework_Visual_Composer_Integration {
        private static $_load_fields = array(
            'text',
            'textarea',
            'number',
            'checkbox',
            'radio',
            'switcher',
            'select',
            'image_size',
            'links',
            'animate_css',
            'date_picker',
            'group',

            'icon',
            'upload',
            'background',
            'color_picker',
            'image_select',
            'typography',
            'image',
            'gallery',
            'sorter',
            'color_scheme',
            'social_icons',

            'accordion',
            'fieldset',
            'tab',
            'css_builder',

            'heading',
            'subheading',
            'content',
            'notice',
        );

        private static $js_js = FALSE;

        public static function init() {
            add_action('admin_enqueue_scripts', function() {
                wpsf_assets()->register_assets();
                wp_enqueue_style('wpsf-vc');
                wp_enqueue_script('wpsf-vc');
            });
            self::register_vc_fields();
        }

        public static function register_vc_fields() {
            foreach( self::$_load_fields as $field ) {
                vc_add_shortcode_param('wpsf_' . $field, array( __CLASS__, 'render_field' ), self::get_js());
            }
        }

        public static function get_js() {
            if( self::$js_js === FALSE ) {
                self::$js_js = TRUE;
                return WPSF_URI . '/assets/js/wpsf-vc.js';
            }
            return FALSE;
        }

        public static function render_field($settings, $value, $tag) {
            $output = '<div class="wpsf-framework wpsf-vc-framework wpsf-vc-field-' . self::get_type($settings['type']) . '">';
            $output .= self::render($settings, $value);
            $output .= '</div>';
            return $output;
        }

        public static function get_type($type) {
            return str_replace('wpsf_', '', $type);
        }

        public static function render($settings, $value) {
            $is_exits = TRUE;
            if( ! isset($settings['type']) ) {
                $is_exits = FALSE;
            }

            $class = 'WPSFramework_VC_' . self::get_type($settings['type']) . '_Field';

            if( in_array(self::get_type($settings['type']), self::$_load_fields) ) {
                if( ! class_exists($class, FALSE) ) {
                    $class = 'WPSFramework_VC_Field';
                }
            } else if( ! class_exists($class) ) {
                $is_exits = FALSE;
            }


            if( $is_exits === FALSE ) {
                return '<p>' . sprintf(__("WPSF Field Class %s Not Found !!"), '<strong>' . $settings['type'] . '</strong>') . '</p>';
            }


            $class = new $class($settings, $value, self::get_type($settings['type']));
            return $class->render();
        }
    }
}


WPSFramework_Visual_Composer_Integration::init();