<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 07-03-2018
 * Time: 07:53 AM
 */

class WPSFramework_Option_model_search extends WPSFramework_Options {
    public static $is_added = FALSE;

    public function __construct(array $field = array(), $value = '', $unique = '') {
        parent::__construct($field, $value, $unique);
        add_action('admin_footer', array( &$this, 'add_footer' ));
    }

    public function output() {
        $label = ( isset($this->field['label']) ) ? $this->field['label'] : __("Open Modal");

        echo $this->element_before();
        wp_enqueue_script('wp-backbone');

        echo '<div class="modal_search_value hidden">';
        echo '<textarea type="hidden" name="' . $this->unique . '[' . $this->field['id'] . ']">' . $this->value . '</textarea>';
        echo '</div>';

        $settings_id               = $this->js_settings_id();
        $this->field['query_args'] = ( isset($this->field['query_args']) && is_array($this->field['query_args']) ) ? $this->field['query_args'] : array();

        $this->localize_field($settings_id, array(
            'ajax'       => 'wpsf_modal_select',
            'query_args' => isset($this->field['query_args']) ? $this->field['query_args'] : array(),
            'settings'   => isset($this->field['settings']) ? $this->field['settings'] : array(),
            'type'       => $this->field['options'],
        ), TRUE);


        $attrs = $this->element_attributes(array(
            'type'            => 'button',
            'class'           => 'button wpsf-modal-search-button modal-' . $this->field['id'],
            'data-id'         => $this->field['id'],
            'data-settingsid' => $settings_id,
        ));

        $value = '<button  ' . $attrs . '>' . $label . '</button>';
        echo $value;
        echo '<div class="wpsf-modal-search-result"></div>';

        echo $this->element_after();
    }

    public function add_footer() {
        if( self::$is_added === TRUE ) {
            return;
        }

        ?>
        <style>
            #wpsf-modal-search-view-close {
                width      : 36px;
                height     : 36px;
                position   : absolute;
                top        : 0px;
                right      : 0px;
                cursor     : pointer;
                text-align : center;
                color      : #666;
            }

            #wpsf-modal-search-view-close::before {
                font           : 400 20px/36px dashicons;
                vertical-align : top;
                content        : "";
            }

            #wpsf-modal-search-view-close:hover {
                color : #00A0D2;
            }

            div.wpsf-modal-search-view.find-box td .wpsf-element {
                padding : 0;
            }
        </style>
        <div id="wpsf-modal-search-view-<?php echo $this->field['id']; ?>" class="find-box wpsf-modal-search-view"
             style="display: none;">
            <div class="find-box-head wpsf-modal-search-view-head">
                <?php echo isset($this->field['title']) ? $this->field['title'] : ''; ?>
                <div id="wpsf-modal-search-view-close"></div>
            </div>
            <div class="find-box-inside wpsf-modal-search-view-inside">
                <div class="find-box-search wpsf-modal-search-view-search">
                    <label class="screen-reader-text" for="#bb-modal-view-input"><?php _e('Search'); ?></label>
                    <input type="text" placeholder="<?php _e("Search Text"); ?>" id="wpsf-modal-search-view-input"
                           value="" autocomplete="off"/>

                    <button id="wpsf-modal-search-view-search"
                            class="button "><?php _e("Search"); ?></button>
                    <span class="spinner is-active"></span>
                    <div class="clear"></div>
                </div>
                <div class="wpsf-modal-search-result"></div>
                <div id="wpsf-modal-search-view-response"></div>
            </div>
            <div class="find-box-buttons bb-modal-view-buttons">
                <?php submit_button(__('Select'), 'button-primary alignright', 'wpsf-modal-search-view-submit', FALSE); ?>
                <div class="clear"></div>
            </div>
        </div>
        <?php
    }

    protected function field_defaults() {
        return array(
            'query_args' => array(),
            'settings'   => array(),
            'options'    => FALSE,
            'label'      => __("Open Modal"),
        );
    }
}