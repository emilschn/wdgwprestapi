<?php
// Blocks direct access
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDG_RESTAPIUserBasicAccess_Class_Authentication {
	
	/**
	 * @var WDG_RESTAPIUserBasicAccess_Class_Client 
	 */
	private static $current_client;
    
	/**
	 * Authenticates the user with username, password and authorized IP addresses
	 * @return \WP_Error|boolean
	 */
	public static function authentication() {
		// Tries to authentify the user with the id transmitted in basic header
		$username = $_SERVER[ 'PHP_AUTH_USER' ];
		$password = $_SERVER[ 'PHP_AUTH_PW' ];
		$client = wp_authenticate( $username, $password );
		
		if ( is_wp_error( $client ) ) {
			return new WP_Error( '401', "Unauthorized access (wrong id or password)" );
		}
		
		WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client = new WDG_RESTAPIUserBasicAccess_Class_Client( $client->ID );
		
		// If the user can access the REST API
		if ( !WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client->is_authorized_restapi() ) {
			return new WP_Error( '401', "Unauthorized access (user unauthorized)" );
		}
		
		// Checks the client IP address
		if ( !WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client->is_authorized_ip( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
			return new WP_Error( '401', "Unauthorized access (wrong IP)" );
		}
		
		return true;
	}
	
	/**
	 * Checks if the user can call a specific method
	 * @param mixed           $response Current response, either response or `null` to indicate pass-through.
	 * @param WP_REST_Server  $handler  ResponseHandler instance (usually WP_REST_Server).
	 * @param WP_REST_Request $request  The request that was used to make current response.
	 * @return WP_REST_Response Modified response, either response or `null` to indicate pass-through.
	 */
	public static function check_authorized_actions( $response, $handler, $request ) {
		$method = $request->get_method();
		if ( !WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client->is_authorized_action( $method ) ) {
			return new WP_Error( '401', "Unauthorized access (can't call method ".$method.")" );
		}
	}
	
}