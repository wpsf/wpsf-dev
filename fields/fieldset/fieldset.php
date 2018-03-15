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
 * Field: Fieldset
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 */
class WPSFramework_Option_fieldset extends WPSFramework_Options {

    public function __construct($field, $value = '', $unique = '') {
        parent::__construct($field, $value, $unique);
    }

    public function output() {
        echo $this->element_before();

        echo '<div class="wpsf-inner">';

        foreach( $this->field ['fields'] as $field ) {
            $field_value = ( isset($field['default']) ) ? $field['default'] : '';
            if( isset($field ['id']) && isset($this->value[$field ['id']]) ) {
                $field_value = $this->value[$field ['id']];
            }
            $db_slug = ( $this->field['un_array'] === TRUE ) ? $this->unique : $this->get_unique($this->field['id']);
            echo $this->add_field($field, $field_value, $db_slug);
        }

        echo '</div>';

        echo $this->element_after();
    }

    protected function field_defaults() {
        return array( 'un_array' => FALSE, 'default' => array(), 'fields' => array() );
    }
}
