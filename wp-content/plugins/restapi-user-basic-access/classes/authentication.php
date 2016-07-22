<?php
// Blocks direct access
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDG_RESTAPIUserBasicAccess_Class_Authentication {
    
	public static function authentication() {
		// Tries to authentify the user with the id transmitted in basic header
		$username = $_SERVER[ 'PHP_AUTH_USER' ];
		$password = $_SERVER[ 'PHP_AUTH_PW' ];
		$user_authenticated = wp_authenticate( $username, $password );
		
		if ( is_wp_error( $user_authenticated ) ) {
			return new WP_Error( '401', "Unauthorized access (wrong id or password)" );
		}
		
		$client = new WDG_RESTAPIUserBasicAccess_Class_Client( $user_authenticated->ID );
		
		// If the user can access the REST API
		if ( !$client->is_authorized_restapi() ) {
			return new WP_Error( '401', "Unauthorized access (user unauthorized)" );
		}
		
		// Checks the client IP address
		if ( !$client->is_authorized_ip( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
			return new WP_Error( '401', "Unauthorized access (wrong IP)" );
		}
		
		return true;
	}
	
}