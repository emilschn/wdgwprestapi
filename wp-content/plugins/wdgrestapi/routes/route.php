<?php
// Blocks direct access
if ( ! function_exists( 'is_admin' ) && ! class_exists( 'TestCase' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDGRESTAPI_Route {
	
	public static $namespace = 'wdg/v1';
	
	public static function register( $route, $method, $callback ) {
		
		register_rest_route(
			WDGRESTAPI_Route::$namespace,
			$route,
			array(
				'methods' => $method,
				'callback' => $callback
			)
		);
		
	}
	
}