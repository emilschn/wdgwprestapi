<?php
class WDGRESTAPI_Route_User extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register(
			'/user/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'id' => array( 'default' => 0 ) )
		);
	}
	
	public static function register() {
		$route_organization = new WDGRESTAPI_Route_User();
		return $route_organization;
	}
	
	/**
	 * Retourne un utilisateur par son ID
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Organization
	 */
	public function single_get( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'id' );
		if ( !empty( $user_id ) ) {
			$user_item = new WDGRESTAPI_Entity_User( $user_id );
			$loaded_data = $user_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) ) {
				return $loaded_data;
				
			} else {
				return new WP_Error( '404', "Invalid organization ID" );
				
			}
			
		} else {
			return new WP_Error( '404', "Invalid organization ID (empty)" );
		}
	}
	
}