<?php
if (! defined ( 'ABSPATH' )) {
    die ();
} // Cannot access pages directly.
/**
 *
 * Field: Hidden
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class WPSFramework_Option_hidden extends WPSFramework_Options {
    public function __construct($field, $value = '', $unique = '') {
        parent::__construct ( $field, $value, $unique );
    }
    public function output() {
        echo '<input type="hidden" name="' . $this->element_name () . '" value="' . $this->element_value () . '"' . $this->element_class () . $this->element_attributes () . '/>';
    }
}
