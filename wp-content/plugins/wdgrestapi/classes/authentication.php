<?php
// Vérifie si c'est un appel direct
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDGRESTAPI_Authentication {
    
	public static function authentication() {
		// Essaie d'identifier l'utilisteur passé en header basic
		$username = $_SERVER[ 'PHP_AUTH_USER' ];
		$password = $_SERVER[ 'PHP_AUTH_PW' ];
		$user_authenticated = wp_authenticate( $username, $password );
		
		if ( is_wp_error( $user_authenticated ) ) {
			return new WP_Error( '401', "Unauthorized access (wrong id or password)" );
		}
		
		// Vérifie l'adresse IP du client
		$client = new WDGRESTAPI_Client( $user_authenticated->ID );
		if ( !$client->is_authorized_ip( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
			return new WP_Error( '401', "Unauthorized access (wrong IP)" );
		}
		
		return true;
	}
	
}