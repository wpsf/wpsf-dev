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

if( ! defined('ABSPATH') ) {
    die ();
}

/**
 * ------------------------------------------------------------------------------------------------
 * WordPress-Settings-Framework Framework
 * A Lightweight and easy-to-use WordPress Options Framework
 *
 * Copyright 2015 WordPress-Settings-Framework <info@wpsf.com>
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
add_action('after_setup_theme', 'wpsf_framework_init');
require_once plugin_dir_path(__FILE__) . '/wpsf-framework-path.php';


if( ! function_exists('wpsf_registry') ) {
    /**
     * @return null|\WPSFramework_Registry
     */
    function wpsf_registry() {
        return WPSFramework_Registry::instance();
    }
}

if( ! function_exists('wpsf_field_registry') ) {
    /**
     * @return null|\WPSFramework_Field_Registry
     */
    function wpsf_field_registry() {
        return WPSFramework_Field_Registry::instance();
    }
}

if( ! function_exists("wpsf_template") ) {
    /**
     * @param       $override_location
     * @param       $template_name
     * @param array $args
     *
     * @return bool
     */
    function wpsf_template($override_location, $template_name, $args = array()) {
        if( file_exists($override_location . '/' . $template_name) ) {
            $path = $override_location . '/' . $template_name;
        } else if( file_exists(WPSF_DIR . '/templates/' . $template_name) ) {
            $path = WPSF_DIR . '/templates/' . $template_name;
        } else {
            return FALSE;
        }

        extract($args);
        include( $path );
        return TRUE;
    }
}

if( ! function_exists("wpsf_autoloader") ) {
    /**
     * @param      $class
     * @param bool $check
     *
     * @return bool
     */
    function wpsf_autoloader($class, $check = FALSE) {
        if( $class === TRUE && class_exists($class, FALSE) === TRUE ) {
            return TRUE;
        }

        if( 0 === strpos($class, 'WPSFramework_Option_') ) {
            $path = strtolower(substr($class, 20));
            wpsf_locate_template('fields/' . $path . '/' . $path . '.php');
        } else if( 0 === strpos($class, 'WPSFramework_') ) {
            $path  = strtolower(substr(str_replace('_', '-', $class), 13));
            $path1 = WPSF_DIR . '/classes/' . $path . '.php';
            $path2 = WPSF_DIR . '/classes/core/' . $path . '.php';

            if( file_exists($path1) ) {
                include( $path1 );
            } else if( file_exists($path2) ) {
                include( $path2 );
            }
        }
        return TRUE;
    }
}

if( ! function_exists('wpsf_framework_init') ) {
    function wpsf_framework_init() {
        if( class_exists('WPSFramework') ) {
            return;
        }

        /**
         * Include WPSF Required Default Functions
         */
        require_once( WPSF_DIR . '/functions/fallback.php' );
        require_once( WPSF_DIR . '/functions/helpers.php' );
        require_once( WPSF_DIR . '/functions/actions.php' );
        require_once( WPSF_DIR . '/functions/enqueue.php' );
        require_once( WPSF_DIR . '/functions/sanitize.php' );
        require_once( WPSF_DIR . '/functions/validate.php' );

        if( defined('WPSF_VC') && WPSF_VC === TRUE ) {
            require_once( WPSF_DIR . '/functions/visual-composer.php' );
        }

        /**
         * Include WPSF Required Default Classes
         */
        require_once( WPSF_DIR . '/classes/core/registry.php' );
        require_once( WPSF_DIR . '/classes/core/field_registry.php' );
        require_once( WPSF_DIR . '/classes/core/abstract.php' );
        require_once( WPSF_DIR . '/classes/core/fields.php' );
        require_once( WPSF_DIR . '/classes/core/wpsf-ajax.php' );
        require_once( WPSF_DIR . '/classes/core/wpsf-query.php' );
        require_once( WPSF_DIR . '/classes/core/options.php' );

        spl_autoload_register('wpsf_autoloader');
        wpsf_registry();
        wpsf_field_registry();

        add_action('widgets_init', 'wpsf_framework_widgets', 10);
        do_action("wpsf_framework_loaded");
    }
}

if( ! function_exists('wpsf_framework_widgets') ) {
    function wpsf_framework_widgets() {
        wpsf_locate_template('classes/widget.php');
        do_action('wpsf_widgets');
    }
}
