<?php
class WDGRESTAPI_Route_Project_Draft extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/project-drafts/(?P<email>[a-zA-Z0-9\-\@\.]+)',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project-draft/(?P<guid>[a-zA-Z0-9\-]+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'guid' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project-draft',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project-draft/(?P<guid>[a-zA-Z0-9\-]+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
		
		WDGRESTAPI_Route::register_external(
			'/project-draft/(?P<guid>[a-zA-Z0-9\-]+)/status',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_status'),
			array( 'guid' => array( 'default' => 0 ) )
		);
	}
	
	public static function register() {
		$route_project = new WDGRESTAPI_Route_Project_Draft();
		return $route_project;
	}
	
	/**
	 * Retourne la liste des brouillons de projets correspondant à un email
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function list_get( WP_REST_Request $request ) {
		try {
			$email = $request->get_param( 'email' );

			// Gestion cache
			$cache_name = '/project-drafts';
			$cached_version_entity = new WDGRESTAPI_Entity_Cache( FALSE, $cache_name );
			$cached_value = $cached_version_entity->get_value( 60 );
			
			if ( !empty( $cached_value ) ) {
				WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Route_Project_Draft::use cache');
				$buffer = json_decode( $cached_value );
			} else {
				WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Route_Project_Draft::use request');
				$buffer = WDGRESTAPI_Entity_Project_Draft::list_get( $email );
				$cached_version_entity->save( $cache_name, json_encode( $buffer ) );
			}
			
			return $buffer;
			
		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_Project_Draft::list_get", $e->getMessage() );
			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Retourne un brouillon de projet par son GUID
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Project_Draft
	 */
	public function single_get( WP_REST_Request $request ) {
		
		$project_draft_guid = $request->get_param( 'guid' );
		if ( !empty( $project_draft_guid ) ) {
			try {
				$project_item = new WDGRESTAPI_Entity_Project_Draft( FALSE, $project_draft_guid );
				$loaded_data = $project_item->get_loaded_data( FALSE);
				
				if ( !empty( $loaded_data )) {
					return $loaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Project_Draft::single_get::" . $project_draft_guid, "404 : Invalid project GUID" );
					return new WP_Error( '404', "Invalid project GUID" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Project_Draft::single_get::" . $project_draft_guid, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project_Draft::single_get", "404 : Invalid project GUID (empty)" );
			return new WP_Error( '404', "Invalid project GUID (empty)" );
		}
	}
	
	/**
	 * Retourne le statut d'un projet par son ID WordPress
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_get_status( WP_REST_Request $request ) {
		$project_draft_guid = $request->get_param( 'guid' );
		if ( !empty( $project_draft_guid ) ) {
			try {
				$project_item = new WDGRESTAPI_Entity_Project_Draft( FALSE, $project_draft_guid );
				$loaded_data = $project_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) ) {
					$loaded_data = $project_item->get_status();
					return $loaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Project_Draft::single_get_status::" . $project_draft_guid, "404 : Invalid project GUID" );
					return new WP_Error( '404', "Invalid project GUID" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Project_Draft::single_get_status::" . $project_draft_guid, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project_Draft::single_get_status", "404 : Invalid project GUID (empty)" );
			return new WP_Error( '404', "Invalid project GUID (empty)" );
		}
	}
	
	/**
	 * Crée un brouillon de projet
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$project_item = new WDGRESTAPI_Entity_Project_Draft();
		// TODO : code à vérifier
		$this->set_posted_properties( $project_item, WDGRESTAPI_Entity_Project_Draft::$db_properties, FALSE );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$project_item->set_property( 'client_user_id', $current_client->ID );
		$project_item->save();
		$reloaded_data = $project_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_Project_Draft::single_create", json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
		
	/**
	 * Edite un brouillon de projet spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'guid' );
		// TODO : code à vérifier
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project_Draft( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $project_item, WDGRESTAPI_Entity_Project_Draft::$db_properties );
				$project_item->save();
				$reloaded_data = $project_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_Project_Draft::single_edit::" . $project_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project_Draft::single_edit::" . $project_id, "404 : Invalid project GUID" );
				return new WP_Error( '404', "Invalid project GUID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project_Draft::single_edit", "404 : Invalid project GUID (empty)" );
			return new WP_Error( '404', "Invalid project GUID (empty)" );
		}
	}

	
	
}