<?php
/*-------------------------------------------------------------------------------------------------
- This file is part of the WPSF package.                                                          -
- This package is Open Source Software. For the full copyright and license                        -
- information, please view the LICENSE file which was distributed with this                       -
- source code.                                                                                    -
-                                                                                                 -
- @package    WPSF                                                                                -
- @author     Varun Sridharan <varunsridharan23@gmail.com>                                        -
 -------------------------------------------------------------------------------------------------*/

/**
 * Created by PhpStorm.
 * Project : wpsf
 * User: varun
 * Date: 12-03-2018
 * Time: 05:20 PM
 */

class WPSFramework_Field_Registry {
	/**
	 * _instance
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * _instances
	 *
	 * @var array
	 */
	private static $_instances = array();

	/**
	 * @return null|\WPSFramework_Field_Registry
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * @param \WPSFramework_Options $instance
	 */
	public function add( WPSFramework_Options &$instance ) {
		if ( ! isset( self::$_instances[ $instance->id ] ) ) {
			self::$_instances[ $instance->id ] = $instance;
		}
	}

	/**
	 * @param string $field_id
	 *
	 * @return bool|mixed
	 */
	public function get( $field_id = '' ) {
		return ( isset( self::$_instances[ $field_id ] ) ) ? self::$_instances[ $field_id ] : false;
	}

	/**
	 * @return array
	 */
	public function all() {
		return self::$_instances;
	}
}

return WPSFramework_Field_Registry::instance();
