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
 * Field: Switcher
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 */
class WPSFramework_Option_switcher extends WPSFramework_Options {
    /**
     * WPSFramework_Option_switcher constructor.
     *
     * @param        $field
     * @param string $value
     * @param string $unique
     */
    public function __construct($field, $value = '', $unique = '') {
        parent::__construct($field, $value, $unique);
    }

    public function output() {
        echo $this->element_before();
        $label = ( ! empty ($this->field ['label']) ) ? '<div class="wpsf-text-desc">' . $this->field ['label'] . '</div>' : '';
        $value = ( isset($this->field['switch_value']) ) ? $this->field['switch_value'] : '1';

        echo '<label><input type="checkbox" name="' . $this->element_name() . '" value="' . $value . '"' . $this->element_class() . $this->element_attributes() . checked($this->element_value(), $value, FALSE) . '/>
		<em data-on="' . esc_html($this->field['on_label']) . '" data-off="' . esc_html($this->field['off_label']) . '"></em><span></span></label>' . $label;
        echo $this->element_after();
    }

    protected function field_defaults() {
        return array(
            'on_label'     => __("On"),
            'off_label'    => __("Off"),
            'label'        => '',
            'switch_value' => '1',
        );
    }
}
