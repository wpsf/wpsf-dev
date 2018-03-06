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
 * Field: Icon
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class WPSFramework_Option_icon extends WPSFramework_Options {
    /**
     * WPSFramework_Option_icon constructor.
     * @param        $field
     * @param string $value
     * @param string $unique
     */
    public function __construct($field, $value = '', $unique = '') {
        parent::__construct($field, $value, $unique);
    }

    public function output() {
        echo $this->element_before();

        $value  = $this->element_value();
        $hidden = ( empty ($value) ) ? ' hidden' : '';

        $addicon    = ( isset($this->field['add_label']) ) ? $this->field['add_label'] : __("Addon Icon", 'wpsf-framework');
        $removeicon = ( isset($this->field['remove_label']) ) ? $this->field['remove_label'] : __("Remove Icon", 'wpsf-framework');

        echo '<div class="wpsf-icon-select">';
        echo '<span class="wpsf-icon-preview' . $hidden . '"><i class="' . $value . '"></i></span>';
        echo '<a data-id="' . microtime(TRUE) . '" href="javascript:void(0);" class="button button-primary wpsf-icon-add">' . esc_html($addicon) . '</a>';
        echo '<a href="javascript:void(0);" class="button wpsf-warning-primary wpsf-icon-remove' . $hidden . '">' . esc_html($removeicon) . '</a>';
        echo '<input type="text" name="' . $this->element_name() . '" value="' . $value . '"' . $this->element_class('wpsf-icon-value') . $this->element_attributes() . ' />';
        echo '</div>';

        echo $this->element_after();
    }
}
