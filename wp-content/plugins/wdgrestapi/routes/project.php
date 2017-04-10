<?php
class WDGRESTAPI_Route_Project extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register(
			'/project/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register(
			'/project',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register(
			'/project/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_project = new WDGRESTAPI_Route_Project();
		return $route_project;
	}
	
	/**
	 * Retourne un projet par son ID
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Project
	 */
	public function single_get( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->log( "WDGRESTAPI_Route_Project::single_get::" . $project_id, json_encode( $loaded_data ) );
				return $loaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_get::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_get", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Crée un projet
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$project_item = new WDGRESTAPI_Entity_Project();
		$this->set_posted_properties( $project_item, WDGRESTAPI_Entity_Project::$db_properties );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$project_item->set_property( 'client_user_id', $current_client->ID );
		$project_item->save();
		$reloaded_data = $project_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_Project::single_create", json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
	/**
	 * Edite un projet spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $project_item, WDGRESTAPI_Entity_Project::$db_properties );
				$project_item->save();
				$reloaded_data = $project_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_Project::single_edit::" . $project_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_edit::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_edit", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
}