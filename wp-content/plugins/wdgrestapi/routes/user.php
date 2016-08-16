<?php
class WDGRESTAPI_Route_User extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register(
			'/user/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register(
			'/user',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register(
			'/user/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_project = new WDGRESTAPI_Route_User();
		return $route_project;
	}
	
	/**
	 * Retourne un utilisateur par son ID
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Project
	 */
	public function single_get( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'id' );
		if ( !empty( $user_id ) ) {
			$user_item = new WDGRESTAPI_Entity_User( $user_id );
			$loaded_data = $user_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) ) {
				return $loaded_data;
				
			} else {
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Crée un utilisateur
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$user_item = new WDGRESTAPI_Entity_User();
		$this->set_posted_properties( $user_item, WDGRESTAPI_Entity_User::$db_properties );
		$user_item->save();
		$reloaded_data = $user_item->get_loaded_data();
		return $reloaded_data;
	}
	
	/**
	 * Edite un utilisateur spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'id' );
		if ( !empty( $user_id ) ) {
			$user_item = new WDGRESTAPI_Entity_User( $user_id );
			$loaded_data = $user_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) ) {
				$this->set_posted_properties( $user_item, WDGRESTAPI_Entity_User::$db_properties );
				$user_item->save();
				$reloaded_data = $user_item->get_loaded_data();
				return $reloaded_data;
				
			} else {
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		}
	}
	
}