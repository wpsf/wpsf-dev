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

add_action("wpsf_framework_loaded",'wpsf_framework_demo');

function wpsf_framework_demo(){
    // configs
    global $wpsf_demo_settings,$wpsf_demo_customizer,$wpsf_demo_metabox,$wpsf_demo_shortcodes,$wpsf_demo_taxonomy;
    wpsf_locate_template ( 'config/framework.config.php' );
    wpsf_locate_template ( 'config/customize.config.php' );
    wpsf_locate_template ( 'config/metabox.config.php' );
    wpsf_locate_template ( 'config/taxonomy.config.php' );
    wpsf_locate_template ( 'config/shortcode.config.php' );
    
    $framework_options = array(
    
        'settings' => array(
            'settings' => array(
                'menu_title' => 'Theme - Style',
                'options_name' => 'wpsf-options-f-names',
                'style' => 'modern',
                'menu_type' => 'menu', // menu, submenu, options, theme, etc.
                'menu_slug' => 'wpsf-framework',
                'ajax_save' => false,
                'is_single_page' => false,
                'show_reset_all' => false,
                'is_sticky_header' => false,
                'framework_title' => 'WP-SF <small>by Varun Sridharan</small>'
            ),
            'options' => $wpsf_demo_settings,
        ),
        
        'customizer' => $wpsf_demo_customizer,
        'metabox' => $wpsf_demo_metabox,
        'shortcode' => $wpsf_demo_shortcodes,
        'taxonomy' => $wpsf_demo_taxonomy,
    );
    
    new WPSFramework($framework_options);
}
