<?php
class WDGRESTAPI_Route_Organization extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register(
			'/organizations',
			WP_REST_Server::READABLE,
			'WDGRESTAPI_Entity_Organization::list_get'
		);
		
		WDGRESTAPI_Route::register(
			'/organization/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register(
			'/organization',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register(
			'/organization/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_organization = new WDGRESTAPI_Route_Organization();
		return $route_organization;
	}
	
	/**
	 * Retourne une organisation par son ID
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Organization
	 */
	public function single_get( WP_REST_Request $request ) {
		$organization_id = $request->get_param( 'id' );
		if ( !empty( $organization_id ) ) {
			$organization_item = new WDGRESTAPI_Entity_Organization( $organization_id );
			$loaded_data = $organization_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) ) {
				return $loaded_data;
				
			} else {
				return new WP_Error( '404', "Invalid organization ID" );
				
			}
			
		} else {
			return new WP_Error( '404', "Invalid organization ID (empty)" );
		}
	}
	
	/**
	 * Crée une organisation
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$organization_item = new WDGRESTAPI_Entity_Organization();
		$this->set_posted_properties( $organization_item, WDGRESTAPI_Entity_Organization::$db_properties );
		$organization_item->save();
		$reloaded_data = $organization_item->get_loaded_data();
		return $reloaded_data;
	}
	
	/**
	 * Edite une organisation spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$organization_id = $request->get_param( 'id' );
		if ( !empty( $organization_id ) ) {
			$organization_item = new WDGRESTAPI_Entity_Organization( $organization_id );
			$loaded_data = $organization_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) ) {
				$this->set_posted_properties( $organization_item, WDGRESTAPI_Entity_Organization::$db_properties );
				$organization_item->save();
				$reloaded_data = $organization_item->get_loaded_data();
				return $reloaded_data;
				
			} else {
				return new WP_Error( '404', "Invalid organization ID" );
				
			}
			
		}
	}
	
}