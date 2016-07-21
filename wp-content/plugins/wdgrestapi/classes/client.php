<?php
// Vérifie si c'est un appel direct
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDGRESTAPI_Client extends WP_User {
    
	public static $key_authorized_ips = 'authorized_ips';
	public function get_authorized_ips() {
		$buffer = get_user_meta( $this->ID, WDGRESTAPI_Client::$key_authorized_ips, TRUE );
		return $buffer;
	}
	
	public function is_authorized_ip( $client_ip ) {
		$authorized_ips_init = $this->get_authorized_ips();
		
		// Suppression des espaces
		$authorized_ips = str_replace( " ", "", $authorized_ips_init );
		
		// Si tout est autorisé, ok
		if ( $authorized_ips === "*" ) {
			return TRUE;
		}
		
		// Si il n'y a qu'une seule adresse, on la vérifie
		if ( strpos( $authorized_ips, "," ) === FALSE ) {
			return ( $authorized_ips == $client_ip );
			
		} else {
			
			// Parcours des adresses IPs possibles
			$authorized_ips_list = explode( ",", $authorized_ips );
			foreach ( $authorized_ips_list as $authorized_ip ) {
				if ( $authorized_ip == $client_ip ) {
					return TRUE;
				}
			}
			
		}
		
		return FALSE;
	}
	
}