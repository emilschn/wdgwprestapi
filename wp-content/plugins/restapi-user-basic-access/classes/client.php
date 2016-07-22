<?php
// Blocks direct access
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDG_RESTAPIUserBasicAccess_Class_Client extends WP_User {
    
	/**
	 * Meta access keys
	 */
	public static $key_authorized_restapi = 'authorized_restapi';
	public static $key_authorized_ips = 'authorized_ips';
	
	/**
	 * Returns true if user can access to REST API
	 * @return boolean
	 */
	public function is_authorized_restapi() {
		$buffer = get_user_meta( $this->ID, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_restapi, TRUE );
		return ($buffer == '1');
	}
	
	/**
	 * Returns single IP address or the list of IP addresses authorized for the user
	 * @return string
	 */
	public function get_authorized_ips() {
		$buffer = get_user_meta( $this->ID, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_ips, TRUE );
		return $buffer;
	}
	
	/**
	 * Returns true if the IP is authorized for this client
	 * @param string $client_ip
	 * @return boolean
	 */
	public function is_authorized_ip( $client_ip ) {
		$authorized_ips_init = $this->get_authorized_ips();
		
		// Clear spaces
		$authorized_ips = str_replace( " ", "", $authorized_ips_init );
		
		// If everything is authorized, ok
		if ( $authorized_ips === "*" ) {
			return TRUE;
		}
		
		// If only one IP address specified, check this one
		if ( strpos( $authorized_ips, "," ) === FALSE ) {
			return ( $authorized_ips == $client_ip );
			
		} else {
			
			// If it's a list, check each IP address
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