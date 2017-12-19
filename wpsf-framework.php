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

// ------------------------------------------------------------------------------------------------
require_once plugin_dir_path ( __FILE__ ) . '/wpsf-framework-path.php';
// ------------------------------------------------------------------------------------------------

if (! function_exists ( "wpsf_template" )) {
	function wpsf_template($template_name, $args = array()) {
		if (file_exists ( WPSF_DIR . '/templates/' . $template_name )) {
			extract ( $args );
			include (WPSF_DIR . '/templates/' . $template_name);
		}
	}
}

if (! function_exists ( 'wpsf_framework_init' ) && ! class_exists ( 'WPSFramework' )) {
	function wpsf_framework_init() {
		
		// active modules
		defined ( 'WPSF_ACTIVE_FRAMEWORK' ) or define ( 'WPSF_ACTIVE_FRAMEWORK', true );
		defined ( 'WPSF_ACTIVE_METABOX' ) or define ( 'WPSF_ACTIVE_METABOX', true );
		defined ( 'WPSF_ACTIVE_TAXONOMY' ) or define ( 'WPSF_ACTIVE_TAXONOMY', true );
		defined ( 'WPSF_ACTIVE_SHORTCODE' ) or define ( 'WPSF_ACTIVE_SHORTCODE', true );
		defined ( 'WPSF_ACTIVE_CUSTOMIZE' ) or define ( 'WPSF_ACTIVE_CUSTOMIZE', true );
		defined ( 'WPSF_ACTIVE_LIGHT_THEME' ) or define ( 'WPSF_ACTIVE_LIGHT_THEME', false );
		
		// helpers
		wpsf_locate_template ( 'functions/deprecated.php' );
		wpsf_locate_template ( 'functions/fallback.php' );
		wpsf_locate_template ( 'functions/helpers.php' );
		wpsf_locate_template ( 'functions/actions.php' );
		wpsf_locate_template ( 'functions/enqueue.php' );
		wpsf_locate_template ( 'functions/sanitize.php' );
		wpsf_locate_template ( 'functions/validate.php' );
		
		// classes
		wpsf_locate_template ( 'classes/abstract.class.php' );
		wpsf_locate_template ( 'classes/options.class.php' );
		wpsf_locate_template ( 'classes/framework.class.php' );
		wpsf_locate_template ( 'classes/settings.class.php' );
		wpsf_locate_template ( 'classes/metabox.class.php' );
		wpsf_locate_template ( 'classes/taxonomy.class.php' );
		wpsf_locate_template ( 'classes/shortcode.class.php' );
		wpsf_locate_template ( 'classes/customize.class.php' );
		
		// configs
		wpsf_locate_template ( 'config/framework.config.php' );
		wpsf_locate_template ( 'config/metabox.config.php' );
		wpsf_locate_template ( 'config/taxonomy.config.php' );
		wpsf_locate_template ( 'config/shortcode.config.php' );
		wpsf_locate_template ( 'config/customize.config.php' );
	}
	add_action ( 'init', 'wpsf_framework_init', 10 );
}
