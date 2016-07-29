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
		$this->add_admin_include( 'posts' );
		WDGRESTAPI_Admin_Posts::add_actions();
	}
	
	public function add_include( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'classes/' . $include_name . '.php');
	}
	
	public function add_admin_include( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'admin/' . $include_name . '.php');
	}
}

global $wdgrestapi;
$wdgrestapi = WDGRESTAPI::instance();