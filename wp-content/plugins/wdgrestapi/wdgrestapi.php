<?php
/**
 * Plugin Name: WDG REST API
 * Description: Gère les fonctions particulières de l'API REST WE DO GOOD
 * Version: 0.0.1
 * Author: Emilien Schneider
 * Author URI: http://www.wedogood.co
*/


// Vérifie si c'est un appel direct
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDGRESTAPI {
	private $version = '0.0.1';
    
	/**
	 * Instanciation du singleton
	 */
	protected static $_instance = null;
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function __construct() {
		
		$this->add_include( 'authentication' );
		$this->add_include( 'client' );
		add_filter( 'rest_authentication_errors', 'WDGRESTAPI_Authentication::authentication' );
		
		if (is_admin() ) {
			$this->add_admin( 'users' );
			WDGRESTAPI_Admin_Users::init();
		}
		
	}
	
	public function add_include( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'classes/' . $include_name . '.php');
	}
	
	public function add_admin( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'admin/' . $include_name . '.php');
	}
}

global $wdgrestapi;
$wdgrestapi = WDGRESTAPI::instance();