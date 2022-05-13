<?php
class WDGRESTAPI_Route_UserConformity extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/user-conformity',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/user-conformity/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_user_conformity = new WDGRESTAPI_Route_UserConformity();
		return $route_user_conformity;
	}
	
	/**
	 * Crée une donnée de conformité investisseur
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$user_conformity_item = new WDGRESTAPI_Entity_UserConformity();
		$this->set_posted_properties( $user_conformity_item, WDGRESTAPI_Entity_UserConformity::$db_properties );
		$user_conformity_item->save();

		$reloaded_data = $user_conformity_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_UserConformity::single_create", json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
	/**
	 * Edite une donnée de conformité investisseur
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$user_conformity_id = FALSE;
		if ( !empty( $request ) ) {
			$user_conformity_id = $request->get_param( 'id' );
		}
		if ( !empty( $user_conformity_id ) ) {
			$user_conformity_item = new WDGRESTAPI_Entity_UserConformity( $user_conformity_id );
			$loaded_data = $user_conformity_item->get_loaded_data();

			if ( !empty( $loaded_data ) ) {
				$this->set_posted_properties( $user_conformity_item, WDGRESTAPI_Entity_UserConformity::$db_properties );
				$user_conformity_item->save();

				$reloaded_data = $user_conformity_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_UserConformity::single_edit::" . $user_conformity_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_UserConformity::single_edit::" . $user_conformity_id, "404 : Invalid user conformity ID" );
				return new WP_Error( '404', "Invalid user conformity ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_UserConformity::single_edit", "404 : Invalid user conformity ID (empty)" );
			return new WP_Error( '404', "Invalid user conformity ID (empty)" );
		}
	}
}