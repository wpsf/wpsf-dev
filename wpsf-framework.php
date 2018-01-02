<?php
if (! defined ( 'ABSPATH' )) { die (); }
/**
 * ------------------------------------------------------------------------------------------------
 * WordPress-Settings-Framework Framework
 * A Lightweight and easy-to-use WordPress Options Framework
 *
 * Copyright 2015 WordPress-Settings-Framework <info@codestarlive.com>
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * ------------------------------------------------------------------------------------------------
 */

require_once plugin_dir_path ( __FILE__ ) . '/wpsf-framework-path.php';

if (! function_exists ( "wpsf_template" )) {
    function wpsf_template($override_location,$template_name,$args = array()){
        if(file_exists($override_location.'/'.$template_name)){
            $path = $override_location.'/'.$template_name;
        } else if(file_exists(WPSF_DIR.'/templates/'.$template_name)){
            $path = WPSF_DIR.'/templates/'.$template_name;
        } else {
            return false;
        }
        
        extract($args);
        include($path);
    }
}

if (! function_exists ( 'wpsf_framework_init' ) && ! class_exists ( 'WPSFramework' )) {
	function wpsf_framework_init() {
		defined ( 'WPSF_ACTIVE_LIGHT_THEME' ) or define ( 'WPSF_ACTIVE_LIGHT_THEME', false );
		// helpers
		wpsf_locate_template ( 'functions/fallback.php' );
		wpsf_locate_template ( 'functions/helpers.php' );
		wpsf_locate_template ( 'functions/actions.php' );
		wpsf_locate_template ( 'functions/enqueue.php' );
		wpsf_locate_template ( 'functions/sanitize.php' );
		wpsf_locate_template ( 'functions/validate.php' );
		
		// classes
		wpsf_locate_template ( 'classes/abstract.php' );
		wpsf_locate_template ( 'classes/options.php' );
		wpsf_locate_template ( 'classes/framework.php' );
        
        function wpsf_autoloader($class,$check= false){
            if($class === true && class_exists($class) === true){
                return true;
            }
            
            if ( 0 === strpos( $class, 'WPSFramework_Option_' )  ) {
                $path = strtolower(substr( $class, 20 ));
                wpsf_locate_template ('fields/'.$path.'/'.$path.'.php' );
            } else if ( 0 === strpos( $class, 'WPSFramework_' )  ) {
                $path = strtolower(substr( str_replace( '_', '-', $class ), 13 ));
                wpsf_locate_template ('classes/'.$path.'.php' );
            }
        }
        
		spl_autoload_register('wpsf_autoloader');
        do_action("wpsf_framework_loaded");
	}
    add_action('init','wpsf_framework_init',1);
}