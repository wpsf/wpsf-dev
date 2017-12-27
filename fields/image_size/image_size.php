<?php
if (! defined ( 'ABSPATH' )) {
	die ();
} // Cannot access pages directly.
/**
 *
 * Field: Text
 *
 * @since 1.0.0
 * @version 1.0.0
 *         
 */
class WPSFramework_Option_image_size extends WPSFramework_Options {
	public function __construct($field, $value = '', $unique = '') {
		parent::__construct ( $field, $value, $unique );
	}
	public function output() {
		echo $this->element_before ();
        
        echo wpsf_add_element ( array (
                'pseudo' => false,
                'type' => 'text',
                'name' => $this->element_name ( '[width]' ),
                'value' => @$this->value ['width'], 
                'attributes' => array (
                    'placeholder' => __("Width"),
                    'style' => 'width:50px;',
                    'size' => 3,
                ),
                
        ) );
        
        echo ' x ';
        
        echo wpsf_add_element ( array (
                'pseudo' => false,
                'type' => 'text',
                'name' => $this->element_name ( '[height]' ),
                'value' => @$this->value ['height'], 
                'attributes' => array (
                    'placeholder' => __("Height"),
                    'style' => 'width:50px;',
                    'size' => 3,
                ),
                
        ) );
        
        echo wpsf_add_element ( array (
                'pseudo' => false,
                'type' => 'checkbox',
                'name' => $this->element_name ( '[crop]' ),
                'value' => @$this->value ['crop'], 
            'label' => __("Hard Crop ?")
        ) );
        
		echo $this->element_after ();
	}
}
