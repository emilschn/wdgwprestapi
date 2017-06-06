<?php
class WDGRESTAPI_Route extends WP_REST_Controller {
	
	public static $wdg_namespace = 'wdg/v1';
	public static $external_namespace = 'external/v1';
	public static $key_authorized_accounts_access = 'wdg_authorized_accounts_access';
	
	/**
	 * Définit les différentes propriétés d'une entité à partir d'informations postées
	 * @param WDGRESTAPI_Entity $entity
	 * @param array $properties_list
	 */
	public function set_posted_properties( WDGRESTAPI_Entity $entity, array $properties_list ) {
		foreach ( $properties_list as $property_key => $db_property ) {
			$property_new_value = filter_input( INPUT_POST, $property_key );
			if ( $property_new_value !== null && $property_new_value !== FALSE ) {
				$entity->set_property( $property_key, $property_new_value );
			}
		}
	}
	
	/**
	 * Renvoie TRUE si la donnée peut être renvoyée au client qui fait l'appel
	 * @param object $loaded_data
	 * @return boolean
	 */
	public function is_data_for_current_client( $loaded_data ) {
		$authorized_access_list = array();
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		array_push( $authorized_access_list, $current_client->ID );
		
		$access_temp = $current_client->get( WDGRESTAPI_Route::$key_authorized_accounts_access );
		if ( !empty( $access_temp ) ) {
			if ( strpos( $access_temp, ',' ) !== FALSE ) {
				$array_access_temp = explode( ',', $access_temp );
				$authorized_access_list = array_merge( $authorized_access_list, $array_access_temp );
				
			} else {
				array_push( $authorized_access_list, $access_temp );
				
			}
		}
		
		return ( in_array( $loaded_data->client_user_id, $authorized_access_list ) );
	}
	
	/**
	 * Enregistre une nouvelle ligne de log quand nécessaire
	 * @param string $route
	 * @param string $result
	 */
	public function log( $route, $result ) {
		$log_item = new WDGRESTAPI_Entity_Log();
		$log_item->set_property( 'route', $route );
		$log_item->set_property( 'result', $result );
		$log_item->save();
	}
	
	/**
	 * Enregistre une nouvelle route dans l'API REST
	 * @param string $route
	 * @param string $method
	 * @param function $callback
	 * @param array $args
	 */
	public static function register( $route, $method, $callback, $args = array() ) {
		
		register_rest_route(
			WDGRESTAPI_Route::$wdg_namespace,
			$route,
			array(
				'methods'	=> $method,
				'callback'	=> $callback,
				'args'		=> $args
			)
		);
		
	}
	
	/**
	 * Enregistre une nouvelle route externe dans l'API REST
	 * @param string $route
	 * @param string $method
	 * @param function $callback
	 * @param array $args
	 */
	public static function register_external( $route, $method, $callback, $args = array() ) {
		
		register_rest_route(
			WDGRESTAPI_Route::$external_namespace,
			$route,
			array(
				'methods'	=> $method,
				'callback'	=> $callback,
				'args'		=> $args
			)
		);
		
	}
	
}