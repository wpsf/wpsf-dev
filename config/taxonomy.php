<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 16-01-2018
 * Time: 01:53 PM
 */

new WPSFramework_Taxonomy(array(
    array(
        'id'       => '_custom_taxonomy_options',
        'taxonomy' => 'category',
        // category, post_tag or your custom taxonomy name
        'fields'   => array(
            array(
                'id'    => 'section_1_text',
                'type'  => 'text',
                'title' => 'Text Field',
                'validate' => 'required',

            ),
            array(
                'id'    => 'section_1_textarea',
                'type'  => 'textarea',
                'title' => 'Textarea Field',
                'validate' => 'required',
            ),
        ),
    ),

    array(
        'id'       => '_custom_taxonomy_options',
        'taxonomy' => 'post_tag',
        // category, post_tag or your custom taxonomy name
        'fields'   => array(
            array(
                'id'    => 'section_1_text',
                'type'  => 'text',
                'title' => 'Text Field',
            ),
        ),
    ),
));