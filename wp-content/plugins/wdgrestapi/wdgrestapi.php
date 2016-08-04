<?php
/**
 * Plugin Name: WDG REST API
 * Description: Gère les fonctions particulières de l'API REST WE DO GOOD
 * Version: 0.0.1
 * Author: Emilien Schneider
 * Author URI: http://www.wedogood.co
*/


/**
 * How-to:
 * 	$url = 'http://WP-URL/wp-json/';
	$route = 'wp/v2/posts/1'; //Route classique wordpress
	$route2 = 'wdg/v1/staticpages'; //Route vers méthodes spécifiques wdg
	$id = ID;
	$pwd = PWD;
	$headers = array( "Authorization" => "Basic " . base64_encode( $id.':'.$pwd ) );
	$result = wp_remote_get( $url . $route, array( 'headers' => $headers ) );
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
		$this->add_include_entities( 'entity' );
		$this->add_include_entities( 'staticpage' );
		
		$this->add_include_routes( 'route' );
		$this->add_include_routes( 'staticpage' );
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_StaticPage::register');
		
		$this->add_include_admin( 'posts' );
		WDGRESTAPI_Admin_Posts::add_actions();
	}
	
	public function add_include_admin( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'admin/' . $include_name . '.php');
	}
	
	public function add_include_entities( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'entities/' . $include_name . '.php');
	}
	
	public function add_include_routes( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'routes/' . $include_name . '.php');
	}
}

global $wdgrestapi;
$wdgrestapi = WDGRESTAPI::instance();