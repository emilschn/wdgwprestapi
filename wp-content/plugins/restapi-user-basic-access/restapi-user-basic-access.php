<?php
/**
 * Plugin Name: REST API User Basic Authorization
 * Description: REST API access and user accounts management via basic authorization. Once activated, WP REST API will only be accessible to registered users.
 * Version: 0.5
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
		add_filter( 'rest_pre_dispatch', 'WDG_RESTAPIUserBasicAccess_Class_Authentication::check_authorized_actions', 10, 3 );
		
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