<?php
/**
 * Extends WP_User to manage new data
 */
class WDG_RESTAPIUserBasicAccess_Class_Client extends WP_User {
    
	/**
	 * Meta access keys
	 */
	public static $key_authorized_restapi = 'authorized_restapi';
	public static $key_authorized_ips = 'authorized_ips';
	public static $key_authorized_actions = 'authorized_actions';
	
	/**
	 * Actions list
	 */
	public static $action_get = 'get';
	public static $action_post = 'post';
	public static $action_put = 'put';
	public static $action_delete = 'delete';
	
	/**
	 * Properties
	 */
	private $authorized_ips; // Needs to be set before access
	private $authorized_actions; // Needs to be set before access
	
	/**
	 * Returns true if user can access to REST API
	 * @return boolean
	 */
	public function is_authorized_restapi() {
		$buffer = get_user_meta( $this->ID, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_restapi, TRUE );
		return ($buffer == '1');
	}
	
/*******************************************************************************
 * AUTHORIZED IPs
 ******************************************************************************/
	/**
	 * Set authorized IPs (useful for testing)
	 * @param string $authorized_ips
	 */
	public function set_authorized_ips( $authorized_ips ) {
		$this->authorized_ips = $authorized_ips;
	}
	
	/**
	 * Returns single IP address or the list of IP addresses authorized for the user
	 * @return string
	 */
	public function get_authorized_ips() {
		if ( !isset( $this->authorized_ips ) ) {
			$this->set_authorized_ips( get_user_meta( $this->ID, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_ips, TRUE ) );
		}
		return $this->authorized_ips;
	}
	
	/**
	 * Returns true if the IP is authorized for this client
	 * @param string $client_ip
	 * @return boolean
	 */
	public function is_authorized_ip( $client_ip ) {
		return WDG_RESTAPIUserBasicAccess_Class_Client::get_is_authorized_ip( $this->get_authorized_ips(), $client_ip );
	}
	
	/**
	 * Returns true if the IP is authorized for this client
	 * @param string $authorized_ips_init
	 * @param string $client_ip
	 * @return boolean
	 */
	public static function get_is_authorized_ip( $authorized_ips_init, $client_ip ) {
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
	
/*******************************************************************************
 * AUTHORIZED ACTIONS
 ******************************************************************************/
	/**
	 * Set authorized actions (useful for testing)
	 * @param string $authorized_actions
	 */
	public function set_authorized_actions( $authorized_actions ) {
		$this->authorized_actions = json_decode( $authorized_actions );
	}
	
	/**
	 * Returns a REST API actions array, with authorization for each of them
	 * @return array
	 */
	public function get_authorized_actions() {
		if ( !isset( $this->authorized_actions ) ) {
			$meta_result = get_user_meta( $this->ID, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions, TRUE );
			$this->set_authorized_actions( $meta_result );
		}
		return $this->authorized_actions;
	}
	
	/**
	 * Returns true if the specified action is authorized for this user
	 * @param string $action_init
	 * @return boolean
	 */
	public function is_authorized_action( $action_init ) {
		return WDG_RESTAPIUserBasicAccess_Class_Client::get_is_authorized_action( $this->get_authorized_actions(), $action_init );
	}
	
	/**
	 * Returns true if the specified action is authorized for this user
	 * @param object or string $authorized_actions_object
	 * @param string $action_init
	 * @return boolean
	 */
	public static function get_is_authorized_action( $authorized_actions_object, $action_init ) {
		if ( is_string( $authorized_actions_object ) ) {
			$authorized_actions_object = json_decode( $authorized_actions_object );
		}
		$action = strtolower( $action_init );
		if ( $action == 'patch' ) {
			$action = 'put';
		}
		if ( isset( $authorized_actions_object->$action ) ) {
			return ( $authorized_actions_object->$action == '1' );
			
		} else {
			return FALSE;
		}
	}
	
}