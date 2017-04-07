<?php
class WDGRESTAPI_Route extends WP_REST_Controller {
	
	public static $wdg_namespace = 'wdg/v1';
	
	/**
	 * Définit les différentes propriétés d'une entité à partir d'informations postées
	 * @param array $properties_list
	 * @param WDGRESTAPI_Entity $entity
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
	
}