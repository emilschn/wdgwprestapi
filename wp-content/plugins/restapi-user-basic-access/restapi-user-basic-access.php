<?php
/**
 * Plugin Name: REST API User Basic Access
 * Description: API REST access authorization and user accounts management
 * Version: 0.0.1
 * Author: Emilien Schneider
 * Author URI: http://www.wedogood.co
*/


// Blocks direct access
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDG_RESTAPIUserBasicAccess {
	/**
	 * Singleton instanciation
	 */
	protected static $_instance = null;
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function __construct() {
		
		$this->add_class( 'authentication' );
		$this->add_class( 'client' );
		add_filter( 'rest_authentication_errors', 'WDG_RESTAPIUserBasicAccess_Class_Authentication::authentication' );
		
		if (is_admin() ) {
			$this->add_admin( 'users' );
			WDG_RESTAPIUserBasicAccess_Admin_Users::add_actions();
		}
		
	}
	
	public function add_class( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'classes/' . $include_name . '.php');
	}
	
	public function add_admin( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'admin/' . $include_name . '.php');
	}
}

global $wdg_restapiuserbasicaccess;
$wdg_restapiuserbasicaccess = WDG_RESTAPIUserBasicAccess::instance();