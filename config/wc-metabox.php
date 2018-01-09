<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 08-01-2018
 * Time: 07:25 PM
 */

global $wpsf_wc_metabox;

$fields = wpsf_basic_inputs('wc_inputs');
$fields[] = wpsf_accordion('wc_inputs');

$wpsf_wc_metabox = array(
    'plugin1' => array(
        array(
            'id' => '_wpsf_wc_metabox_1',
            'sections' => array(
                array('group' => 'general','fields' => wpsf_very_basic_inputs(), ),
                array(
                    'name' => 'both',
                    'title' => 'Product + Variations',
                    'is_variation' => true,
                    'fields' => wpsf_very_basic_inputs(''),
                ),
                array(
                    'name' => 'wsproduct',
                    'title' => 'Only Product',
                    'fields' => wpsf_very_basic_inputs('ac'),
                ),
                array(
                    'name' => 'product',
                    'title' => 'Only A Field',
                    'fields' => array(
                        array(
                            'title' => 'Only Product',
                            'type' =>'text',
                            'validate' => 'required',
                            'id' => '_',
                        ),
                        array(
                            'title' => 'Only Variation',
                            'type' => 'text',
                            'is_variation' => true,
                            'only_variation' => true,
                            'validate' => 'required',
                            'id' => '___ACE',
                        ),
                        array(
                            'title' => 'Both',
                            'type' => 'switcher',
                            'is_variation' => 'pricing',
                            'id' => "____",
                        )
                    )
                ),

            )
        ),

    ),
);
$_instance = WPSFramework_WC_Metabox::instance();
$_instance->init($wpsf_wc_metabox);

new WPSFramework_User_Profile(array(
    array(
        'id' => '_custom_wpsf_fields',
        'title' => __("Some Title"),
        'style' => 'modern',
        'fields' => array(
            array(
                'id' => '_switcher',
                'type' => 'switcher',
                'title' => 'Switcher',
            ) ,
            array(
                'id' => '_text',
                'type' => 'text',
                'title' => 'Title',
                'validate' => 'required',
            )

        )
    )
));

new WPSFramework_Settings(array('menu_title' => 'Framework','menu_slug' => 'fuck'),array(
    'general' => array(
        'name' => 'general',
        'title' => 'general',
        'fields' => array(
            array(
                'type' => 'icon',
                'id' => 'icon'
            )
        )
    )
));