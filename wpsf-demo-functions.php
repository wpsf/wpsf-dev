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

/**
 * Created by PhpStorm.
 * User: varun
 * Date: 09-01-2018
 * Time: 07:54 PM
 */


function _wpsf_before($elem, $before = '<p> Here its the element before text</p>') {
    return array_merge(array( 'before' => $before ), $elem);
}

function _wpsf_after($elem, $after = '<p> Here its the element after text</p>') {
    return array_merge(array( 'after' => $after ), $elem);
}

function _wpsf_style($elem, $style = 'width: 175px; height: 40px; border-color: #93C054;') {
    return array_merge(array( 'attributes' => array( 'style' => $style ) ), $elem);
}

function _wpsf_desc($elem, $style = 'Sample Information') {
    return array_merge(array( 'desc' => $style ), $elem);
}

function _wpsf_help($elem, $style = 'Sample Information') {
    return array_merge(array( 'help' => $style ), $elem);
}

function _wpsf_extra($elem) {
    return array(
        _wpsf_before($elem),
        _wpsf_after($elem),
        _wpsf_style($elem),
        _wpsf_desc($elem),
        _wpsf_help($elem),
    );
}

function _wpsf_options($option = '') {
    $option1 = array(
        'option1' => 'Option1',
        'option2' => 'Option2',
        'option3' => 'Option3',
    );
    $option_g = array(
        'Group1'  => $option1,
        'Group 2' => $option1,
    );
    $option_img = array(
        'value-1' => 'http://codestarframework.com/assets/images/placeholder/100x80-2ecc71.gif',
        'value-2' => 'http://codestarframework.com/assets/images/placeholder/100x80-e74c3c.gif',
        'value-3' => 'http://codestarframework.com/assets/images/placeholder/100x80-ffbc00.gif',
        'value-4' => 'http://codestarframework.com/assets/images/placeholder/100x80-3498db.gif',
        'value-5' => 'http://codestarframework.com/assets/images/placeholder/100x80-555555.gif',
    );
    $return = array(
        $option1,
        $option_g,
        $option_img,
    );
    if( isset($return[$option]) ) {
        return $return[$option];
    }
    return ( $option === 'all' ) ? $return : $option;
}

function _wpsf_field($id = '', $type = '', $title = '', $data = array()) {
    return array_filter(array_merge($data, array(
        'id'    => $id,
        'type'  => $type,
        'title' => $title,
    )));
}

function _wpsf_select_checkbox_radio($id = '', $title = '', $options = '', $data = array(), $type = '') {
    $op = _wpsf_options($options);
    $ex_attr = array_merge(array( 'options' => $op ), $data);
    return _wpsf_field($id, $type, $title, $ex_attr);
}

function _wpsf_select($id = '', $title = '', $options = '', $data = array()) {
    return _wpsf_select_checkbox_radio($id, $title, $options, $data, 'select');
}

function _wpsf_radio($id = '', $title = '', $options = '', $data = array()) {
    return _wpsf_select_checkbox_radio($id, $title, $options, $data, 'radio');
}

function _wpsf_checkbox($id = '', $title = '', $options = '', $data = array()) {
    return _wpsf_select_checkbox_radio($id, $title, $options, $data, 'checkbox');
}

function _wpsf_image_select($id = '', $title = '', $options = '', $data = array()) {
    return _wpsf_select_checkbox_radio($id, $title, $options, $data, 'image_select');
}

function _wpsf_heading($content = '') {
    return _wpsf_field('', 'heading', '', array( 'content' => $content ));
}

function _wpsf_subheading($content = '') {
    return _wpsf_field('', 'subheading', '', array( 'content' => $content ));
}

function _wpsf_content($content = '') {
    return _wpsf_field('', 'content', '', array( 'content' => $content ));
}

function _wpsf_notice($content = '', $class = 'info') {
    return _wpsf_field('', 'notice', '', array(
        'content' => $content,
        'class'   => $class,
    ));
}

function _wpsf_accordion($id = '', $title = '', $fields) {
    return _wpsf_field($id . '_accordion', 'accordion', '', array(
        'content' => $title,
        'fields'  => $fields,
    ));
}

function wpsf_icheck_field($id = '', $type = '') {
    $themes = array(
        'minimal' => array(
            'minimal',
            'minimal-red',
            'minimal-green',
            'minimal-blue',
            'minimal-aero',
            'minimal-grey',
            'minimal-orange',
            'minimal-yellow',
            'minimal-pink',
            'minimal-purple',
        ),
        'square'  => array(
            'square',
            'square-red',
            'square-green',
            'square-blue',
            'square-aero',
            'square-grey',
            'square-orange',
            'square-yellow',
            'square-pink',
            'square-purple',
        ),
        'flat'    => array(
            'flat',
            'flat-red',
            'flat-green',
            'flat-blue',
            'flat-aero',
            'flat-grey',
            'flat-orange',
            'flat-yellow',
            'flat-pink',
            'flat-purple',
        ),
        'line'    => array(
            'line',
            'line-red',
            'line-green',
            'line-blue',
            'line-aero',
            'line-grey',
            'line-orange',
            'line-yellow',
            'line-pink',
            'line-purple',
        ),
    );
    $r = array();

    foreach( $themes as $name => $theme ) {
        $s = array();
        foreach( $theme as $t ) {
            $s[] = _wpsf_select_checkbox_radio($id . '_' . $type, 'iCheck With ' . $t, 0, array(
                'class'      => 'icheck',
                'attributes' => array( 'data-theme' => $t ),
            ), $type);
        }
        $r[] = _wpsf_accordion($id . '_icheck_' . $name, "iCheck Theme : " . $name, $s);
    }

    return $r;
}

function wpsf_post_types($id = '', $type = '') {
    return array(
        _wpsf_select_checkbox_radio($id . '_' . $type . '_pages', $type . ' With Pages', 'page', array(), $type),
        _wpsf_select_checkbox_radio($id . '_' . $type . '_posts', $type . ' With Posts', 'posts', array(), $type),
        _wpsf_select_checkbox_radio($id . '_' . $type . '_categories', $type . ' With Categorires', 'categories', array(), $type),
        _wpsf_select_checkbox_radio($id . '_' . $type . '_tags', $type . ' With Tags', 'tags', array(), $type),
        _wpsf_select_checkbox_radio($id . '_' . $type . '_product_tag', $type . ' Custom Post Types posts', 'posts', array(
            'query_args' => array(
                'post_type'      => 'product',
                'posts_per_page' => -1,
            ),
        ), $type),
        _wpsf_select_checkbox_radio($id . '_' . $type . '_product_tag', $type . 'Custom Post Types  tags', 'tags', array(
            'query_args' => array(
                'post_type'      => 'product',
                'taxonomy'       => 'product_tag',
                'posts_per_page' => -1,
            ),
        ), $type),
        _wpsf_select_checkbox_radio($id . '_' . $type . '_product_cat', $type . ' Custom Post Types categoires', 'categories', array(
            'query_args' => array(
                'post_type'      => 'product',
                'taxonomy'       => 'product_cat',
                'posts_per_page' => -1,
            ),
        ), $type),
    );

}

function _wpsf_radios($id = '') {
    $return = array();
    $elem = array( _wpsf_radio($id . '_radio', 'Radio', 0) );
    $final_arr = array_merge($elem, _wpsf_extra($elem[0]));
    $return[] = _wpsf_radio($id . '_radio', 'Radio', 0);
    $return = array_merge($return, $final_arr);
    $return = array_merge($return, wpsf_icheck_field($id . '_radio', 'radio'));
    $return = array_merge($return, wpsf_post_types($id . '_radio', 'radio'));
    return $return;
}

function _wpsf_checkboxs($id = '') {
    $return = array();
    $elem = array( _wpsf_checkbox($id . '_checkbox', 'checkbox', 0) );
    $final_arr = array_merge($elem, _wpsf_extra($elem[0]));
    $return[] = _wpsf_checkbox($id . '_checkbox', 'checkbox', '', array( 'label' => 'Hello' ));
    $return[] = _wpsf_checkbox($id . '_checkbox', 'checkbox', 0);
    $return = array_merge($return, $final_arr);
    $return = array_merge($return, wpsf_icheck_field($id . '_checkbox', 'checkbox'));
    $return = array_merge($return, wpsf_post_types($id . '_checkbox', 'checkbox'));
    return $return;
}

function _wpsf_selects($id = '') {
    $_select = array( _wpsf_select($id . '_select', 'Select', 0, array()), );
    $_mselect = array( _wpsf_select($id . '_mselect_group', 'Select With Group Multiple', 1, array()), );
    $_select = array_merge($_select, _wpsf_extra($_select[0]));
    $_mselect = array_merge($_mselect, _wpsf_extra($_mselect[0]));
    $basic_options = array_merge($_select, $_mselect);

    return array(
        _wpsf_select($id . '_select', 'Select', 0, array()),
        _wpsf_select($id . '_select_group', 'Select With Group', 1, array()),
        _wpsf_select($id . '_mselect', 'Select Multiple', 0, array( 'multiple' => TRUE, )),
        _wpsf_select($id . '_mselect_group', 'Select With Group Multiple', 1, array( 'multiple' => TRUE, )),
        _wpsf_accordion($id . 'basic_options_select', 'Basic Options', $basic_options),
        _wpsf_accordion($id . '_select2_select', 'Select With Select2 Style', array(
            _wpsf_select($id . '_select', 'Select', 0, array( 'class' => 'select2' )),
            _wpsf_select($id . '_select_group', 'Select With Group', 1, array( 'class' => 'select2' )),
            _wpsf_select($id . '_mselect', 'Select Multiple', 0, array(
                'multiple' => TRUE,
                'class'    => 'select2',
            )),
            _wpsf_select($id . '_mselect_group', 'Select With Group Multiple', 1, array(
                'multiple' => TRUE,
                'class'    => 'select2',
            )),
        )),
        _wpsf_accordion($id . '_select2_select', 'Select With chosen Style', array(
            _wpsf_select($id . '_select', 'Select', 0, array( 'class' => 'chosen' )),
            _wpsf_select($id . '_select_group', 'Select With Group', 1, array( 'class' => 'chosen' )),
            _wpsf_select($id . '_mselect', 'Select Multiple', 0, array(
                'multiple' => TRUE,
                'class'    => 'chosen',
            )),
            _wpsf_select($id . '_mselect_group', 'Select With Group Multiple', 1, array(
                'multiple' => TRUE,
                'class'    => 'chosen',
            )),
        )),
        _wpsf_accordion($id . '_select_post_types', 'Select Post Types', wpsf_post_types($id . '_select_', 'select')),
    );
}

function _wpsf_textfield($id = '') {
    $elem = array( _wpsf_field($id . '_text', 'text', 'TextField'), );
    return array_merge($elem, _wpsf_extra($elem[0]));
}

function _wpsf_switcher($id = '') {
    $elem = array(
        _wpsf_field($id . '_switcher', 'switcher', 'Switcher'),
        _wpsf_field($id . '_switcher', 'switcher', 'Switcher With Text', array(
            'on_label'  => 'wow',
            'off_label' => 'omg',
        )),
    );
    return array_merge($elem, _wpsf_extra($elem[0]));
}

function _wpsf_icon($id = '') {
    $elem = array( _wpsf_field($id . '_icon', 'icon', 'icon') );
    return array_merge($elem, _wpsf_extra($elem[0]));
}

function _wpsf_textarea($id = '') {
    $elem = array( _wpsf_field($id . '_textarea', 'text', 'textarea') );
    return array_merge($elem, _wpsf_extra($elem[0]));
}

function _wpsf_upload($id = '') {
    $elem = array(
        _wpsf_field($id . '_upload', 'upload', 'Upload'),
        _wpsf_field($id . '_uploads', 'upload', 'Uploads', array(
            'settings' => array(
                'upload_type'  => 'video',
                'button_title' => 'Video',
                'frame_title'  => 'Select a video',
                'insert_title' => 'Use this video',
            ),
        )),
    );

    return array_merge($elem, _wpsf_extra($elem[0]));
}

function _wpsf_background($id = '') {
    $elem = array( _wpsf_field($id . '_background', 'background', 'Background') );
    return array_merge($elem, _wpsf_extra($elem[0]));
}

function _wpsf_colorpicker($id = '') {
    $elem = array( _wpsf_field($id . '_color_picker', 'color_picker', 'color picker') );
    return array_merge($elem, _wpsf_extra($elem[0]));
}

function _wpsf_imageselect($id = '') {
    $elem = array(
        _wpsf_image_select($id . '_image_select', 'Image Select', 2),
        _wpsf_image_select($id . '_image_select', 'Image Select (Radio)', 2, array( 'radio' => TRUE, )),
        _wpsf_image_select($id . '_image_select', 'Image Select (Multi)', 2, array( 'multi_select' => TRUE, )),
    );
    return array_merge($elem, _wpsf_extra($elem[0]));
}

function _wpsf_typography($id = '') {
    $elem = array(
        _wpsf_field($id . '_typography', 'typography', 'Typography'),
        _wpsf_field($id . '_typographyvs', 'typography', 'Typography Without Variant', array( 'variant' => FALSE )),
        _wpsf_field($id . '_typographycs', 'typography', 'Typography Chosen', array( 'chosen' => FALSE )),
        _wpsf_field($id . '_typographyses', 'typography', 'Typography Select2', array( 'class' => 'select2' )),
    );
    return array_merge($elem, _wpsf_extra($elem[0]));
}


function wpsf_basic_fields($id = '') {
    return array(
        _wpsf_heading('Sample Heading'),
        _wpsf_field($id . '_text', 'text', 'TextField'),
        _wpsf_field($id . '_textarea', 'textarea', 'Textarea'),
        _wpsf_field($id . '_number', 'number', 'Number'),
        _wpsf_notice("Sample Notice info <strong> Supported : Classes info,warning,danger,success</strong>", 'info'),
        _wpsf_field($id . '_text', 'text', 'TextField'),
        _wpsf_field($id . '_colorpicker', 'color_picker', 'Color Picker'),
        _wpsf_subheading('Sample Sub-Heading'),
        _wpsf_select($id . '_select', 'Select', 0),
        _wpsf_radio($id . '_radio', 'Radio', 0),
        _wpsf_checkbox($id . '_checkbox', 'Checkbox', 0),
        _wpsf_notice("Sample Notice warning <strong> Supported : Classes info,warning,danger,success</strong>", 'warning'),
        _wpsf_checkbox($id . '_checkboxs', 'Checkbox', '', array( 'label' => 'Hello' )),
        _wpsf_content('Sample Content'),
        _wpsf_image_select($id . '_image_select', 'Image Select', 2),
        _wpsf_notice("Sample Notice danger <strong> Supported : Classes info,warning,danger,success</strong>", 'danger'),
        _wpsf_field($id . '_icon', 'icon', 'Icon Picker'),
        _wpsf_field($id . '_background', 'background', 'Background Field'),
        _wpsf_notice("Sample Notice success <strong> Supported : Classes info,warning,danger,success</strong>", 'success'),
        _wpsf_field($id . '_imagesize', 'image_size', 'Image Size'),
    );
}


function wpsf_select_options($id = '') {
    return array();
}

function wpsf_textbox($id = '') {
    return array();
}

function wpsf_radio_options($id = '') {
    return array();
}

function wpsf_checkbox_options($id = '') {
    return array();
}

function wpsf_advancedFields_options($id = '') {
    return array();
}

