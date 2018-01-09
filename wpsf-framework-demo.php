<?php
if (! defined ( 'ABSPATH' )) {
	die ();
} // Cannot access pages directly.
/**
 * ------------------------------------------------------------------------------------------------
 *
 * WordPress-Settings-Framework Framework
 * A Lightweight and easy-to-use WordPress Options Framework
 *
 * Plugin Name: WordPress-Settings-Framework Framework
 * Plugin URI: http://codestarframework.com/
 * Author: WordPress-Settings-Framework
 * Author URI: http://codestarlive.com/
 * Version: 1.0.2
 * Description: A Lightweight and easy-to-use WordPress Options Framework
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wpsf-framework
 *
 * ------------------------------------------------------------------------------------------------
 *
 * Copyright 2015 WordPress-Settings-Framework <info@codestarlive.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * ------------------------------------------------------------------------------------------------
 */

require_once plugin_dir_path ( __FILE__ ) . '/wpsf-framework.php';

function wpsf_accordion($id_prefix = ''){
    return array(
        'id' => $id_prefix.'_accordion',
        'type' => 'accordion',
        'fields' => wpsf_basic_inputs($id_prefix.'_accordion'),
        'name' => 'HELP',
    );
}
function wpsf_fieldsset($id_prefix = ''){
    return array(
        'id' => $id_prefix.'_field_set',
        'type' => 'fieldset',
        'fields' => wpsf_basic_inputs($id_prefix),
        'title' => 'Fieldset',
    );
}

function wpsf_group_fields($id_prefix = ''){
    return array(
        'id' => $id_prefix.'_group',
        'type' => 'group',
        'title' => "Group Title",
        'button_title' => 'Custom button',
        'accordion_title' => 'accordion_title',
        'fields' => wpsf_basic_inputs($id_prefix.'_group'),
    );
}

function wpsf_very_basic_inputs($id_prefix = ''){
    $options = array('option1' => __("Option1"),'option2' => __("Option2"),'option3' => __("Option3"));
    $img_select =  array(
        'value-1' => 'http://codestarframework.com/assets/images/placeholder/100x80-2ecc71.gif',
        'value-2' => 'http://codestarframework.com/assets/images/placeholder/100x80-e74c3c.gif',
        'value-3' => 'http://codestarframework.com/assets/images/placeholder/100x80-ffbc00.gif',
        'value-4' => 'http://codestarframework.com/assets/images/placeholder/100x80-3498db.gif',
        'value-5' => 'http://codestarframework.com/assets/images/placeholder/100x80-555555.gif',
    );

    return array(
        array( 'id' => $id_prefix.'_textfield', 'type' => 'text', 'title' => __("Text Field"), ),
        array('id' => $id_prefix.'_textarea','type' =>'textarea','title' => __("Textarea"),),
        array('id' => $id_prefix.'_select','type' => 'select','title' => __("Select"),'options' => $options),
        array('id' => $id_prefix.'_mselect','type' => 'select','title' => 'MSelect','options' => $options,'attributes' => array('multiple' => 'multiple')),
        array('id' => $id_prefix.'_checkbox','type' =>'checkbox','title' => __("Single Checkbox")),
        array('id' =>$id_prefix.'_checkbox_group','type' => 'checkbox','title' => __("Group Checkbox")),
        array('id' => $id_prefix.'_radio','type' => 'radio','title' => __("Radio"),'options' => $options),
        array('id' => $id_prefix.'_switcher','type' => 'switcher','title' => __("Switcher")),
        array('id' =>$id_prefix.'_number','type' => 'number','title' => __("Number")),
        array('id' => $id_prefix.'_icon','type' => 'icon','title' => __("icon")),
        array('id' =>$id_prefix.'_colorpicker','type' => 'color_picker','title'=>__("Color Picker")),
        array('id' =>$id_prefix.'_image_select','type' =>'image_select','title' => __("Image Select"),'options' => $img_select),
        array( 'type'    => 'heading', 'content' => 'Heading Field', ),
        array( 'type'    => 'subheading', 'content' => 'Sub Heading Field',),
        array( 'type'    => 'content','content' => 'Lorem ipsum dollar.'),
        array( 'type'    => 'notice', 'class'   => 'success', 'content' => 'Success: Lorem Ipsum Dollar.', ),
        array( 'type'    => 'notice', 'class'   => 'warning', 'content' => 'Warning: Lorem Ipsum Dollar.', ),
        array( 'type'    => 'notice', 'class'   => 'danger', 'content' => 'Danger: Lorem Ipsum Dollar.', ),
        array( 'type'    => 'notice', 'class'   => 'info', 'content' => 'Info: Lorem Ipsum Dollar.', ),
    );
}


function wpsf_basic_inputs($id_prefix = ''){
    $options = array('option1' => __("Option1"),'option2' => __("Option2"),'option3' => __("Option3"));
    $img_select =  array(
        'value-1' => 'http://codestarframework.com/assets/images/placeholder/100x80-2ecc71.gif',
        'value-2' => 'http://codestarframework.com/assets/images/placeholder/100x80-e74c3c.gif',
        'value-3' => 'http://codestarframework.com/assets/images/placeholder/100x80-ffbc00.gif',
        'value-4' => 'http://codestarframework.com/assets/images/placeholder/100x80-3498db.gif',
        'value-5' => 'http://codestarframework.com/assets/images/placeholder/100x80-555555.gif',
    );

    return array(
        array( 'id' => $id_prefix.'_textfield', 'type' => 'text', 'title' => __("Text Field"), ),
        array('id' => $id_prefix.'_textarea','type' =>'textarea','title' => __("Textarea"),),
        array('id' => $id_prefix.'_select','type' => 'select','title' => __("Select"),'options' => $options),
        array('id' => $id_prefix.'_mselect','type' => 'select','title' => 'MSelect','options' => $options,'attributes' => array('multiple' => 'multiple')),
        array('id' => $id_prefix.'_checkbox','type' =>'checkbox','title' => __("Single Checkbox")),
        array('id' =>$id_prefix.'_checkbox_group','type' => 'checkbox','title' => __("Group Checkbox")),
        array('id' => $id_prefix.'_radio','type' => 'radio','title' => __("Radio"),'options' => $options),
        array('id' => $id_prefix.'_switcher','type' => 'switcher','title' => __("Switcher")),
        array('id' =>$id_prefix.'_number','type' => 'number','title' => __("Number")),
        array('id' => $id_prefix.'_icon','type' => 'icon','title' => __("icon")),
        array('id' =>$id_prefix.'_upload','type' => 'upload','title' => __("Upload")),
        array('id' => $id_prefix.'_background','type' =>'background','title' => __("Background")),
        array('id' =>$id_prefix.'_colorpicker','type' => 'color_picker','title'=>__("Color Picker")),
        array('id' =>$id_prefix.'_image_select','type' =>'image_select','title' => __("Image Select"),'options' => $img_select),
        array( 'id' => $id_prefix.'_typo', 'type' => 'typography', 'title'     => 'Typography Field',),
        array( 'type'    => 'heading', 'content' => 'Heading Field', ),
        array( 'type'    => 'subheading', 'content' => 'Sub Heading Field',),
        array( 'type'    => 'content','content' => 'Lorem ipsum dollar.'),
        array( 'id'    => $id_prefix.'_image', 'type'  => 'image', 'title' => 'Image',),
        array( 'id'    => $id_prefix.'_gallery', 'type'  => 'gallery', 'title' => 'Image Galler',),
        array( 'id'             => 'sorter_2', 'type'           => 'sorter', 'title'          => 'Sorter',
            'default'        => array(
                'enabled'      => array(
                    'blue'       => 'Blue',
                    'green'      => 'Green',
                    'red'        => 'Red',
                    'yellow'     => 'Yellow',
                    'orange'     => 'Orange',
                    'ocean'      => 'Ocean',
                ),
                'disabled'     => array(
                    'black'      => 'Black',
                    'white'      => 'White',
                ),
            ),
            'enabled_title'  => 'Active Colors',
            'disabled_title' => 'Deactive Colors',
        ),
        array( 'type'    => 'notice', 'class'   => 'success', 'content' => 'Success: Lorem Ipsum Dollar.', ),
        array( 'type'    => 'notice', 'class'   => 'warning', 'content' => 'Warning: Lorem Ipsum Dollar.', ),
        array( 'type'    => 'notice', 'class'   => 'danger', 'content' => 'Danger: Lorem Ipsum Dollar.', ),
        array( 'type'    => 'notice', 'class'   => 'info', 'content' => 'Info: Lorem Ipsum Dollar.', ),



    );
}

add_action("wpsf_framework_loaded",'wpsf_framework_demo');

function wpsf_framework_demo(){
    require_once (__DIR__.'/config/wc-metabox.php');
}
