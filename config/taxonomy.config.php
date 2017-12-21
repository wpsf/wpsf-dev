<?php
if (! defined ( 'ABSPATH' )) {
die ();
} // Cannot access pages directly.
// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// TAXONOMY OPTIONS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
global $wpsf_demo_taxonomy;
$wpsf_demo_taxonomy = array ();

// -----------------------------------------
// Taxonomy Options -
// -----------------------------------------
$wpsf_demo_taxonomy [] = array (
    'id' => '_custom_taxonomy_options',
    'taxonomy' => 'category', // category, post_tag or your custom taxonomy name
    'fields' => array (
        array (
            'id' => 'section_1_text',
            'type' => 'text',
            'title' => 'Text Field' 
        ),
        array (
            'id' => 'section_1_textarea',
            'type' => 'textarea',
            'title' => 'Textarea Field' 
        )
    )
);

$wpsf_demo_taxonomy [] = array (
    'id' => '_custom_taxonomy_options',
    'taxonomy' => 'cpt-tag', // category, post_tag or your custom taxonomy name
    'fields' => array (
        array (
            'id' => 'section_1_text',
            'type' => 'text',
            'title' => 'Text Field' 
        ) 
    ) 
);