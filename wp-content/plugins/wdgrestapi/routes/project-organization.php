<?php
class WDGRESTAPI_Route_ProjectOrganization extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register(
			'/project/(?P<id>\d+)/organizations',
			WP_REST_Server::READABLE,
			array( $this, 'get_organizationlist_by_project_id'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register(
			'/organization/(?P<id>\d+)/projects',
			WP_REST_Server::READABLE,
			array( $this, 'get_projectlist_by_organization_id'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register(
			'/project/(?P<id>\d+)/organizations',
			WP_REST_Server::CREATABLE,
			array( $this, 'link_organization'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register(
			'/project/(?P<projectid>\d+)/organization/(?P<organizationid>\d+)/type/(?P<type>[a-z]+)',
			WP_REST_Server::DELETABLE,
			array( $this, 'unlink_organization'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE )
		);
	}
	
	public static function register() {
		$route_project_organization = new WDGRESTAPI_Route_ProjectOrganization();
		return $route_project_organization;
	}
	
	public function get_organizationlist_by_project_id( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$result = WDGRESTAPI_Entity_ProjectOrganization::get_list_by_project_id( $project_id );
			$organization_list = array();
			foreach ( $result as $link_item ) {
				$project = new WDGRESTAPI_Entity_Organization( $link_item->id_organization );
				$loaded_data = $project->get_loaded_data();
				array_push( 
					$organization_list,
					array( 
						"id"	=> $loaded_data->id,
						"wpref"	=> $loaded_data->wpref,
						"name"	=> $loaded_data->name,
						"type"	=> $link_item->type
					)
				);
			}
			
			$this->log( "WDGRESTAPI_Route_ProjectOrganization::get_organizationlist_by_project_id::" . $project_id, json_encode( $organization_list ) );
			return $organization_list;
			
		} else {
			$this->log( "WDGRESTAPI_Route_ProjectOrganization::get_organizationlist_by_project_id", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	public function get_projectlist_by_organization_id( WP_REST_Request $request ) {
		$organization_id = $request->get_param( 'id' );
		if ( !empty( $organization_id ) ) {
			$result = WDGRESTAPI_Entity_ProjectUser::get_list_by_organization_id( $organization_id );
			$project_list = array();
			foreach ( $result as $link_item ) {
				$project = new WDGRESTAPI_Entity_Project( $link_item->id_project );
				$loaded_data = $project->get_loaded_data();
				array_push( 
					$project_list,
					array( 
						"id"	=> $loaded_data->id,
						"wpref"	=> $loaded_data->wpref,
						"name"	=> $loaded_data->name,
						"type"	=> $link_item->type
					)
				);
			}
			
			$this->log( "WDGRESTAPI_Route_ProjectOrganization::get_projectlist_by_organization_id::" . $organization_id, json_encode( $project_list ) );
			return $project_list;
			
		} else {
			$this->log( "WDGRESTAPI_Route_ProjectOrganization::get_projectlist_by_organization_id", "404 : Invalid organization ID (empty)" );
			return new WP_Error( '404', "Invalid organization ID (empty)" );
		}
	}
	
	public function link_organization( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		$projectorganization_item = new WDGRESTAPI_Entity_ProjectOrganization();
		$this->set_posted_properties( $projectorganization_item, WDGRESTAPI_Entity_ProjectOrganization::$db_properties );
		$projectorganization_item->set_property( 'id_project', $project_id );
		//TODO : vérifier que l'organisation et le projet existent ?
		$projectorganization_item->save();
		$reloaded_data = $projectorganization_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_ProjectOrganization::link_organization::" . $project_id, json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
	public function unlink_organization( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'projectid' );
		$organization_id = $request->get_param( 'organizationid' );
		$type = $request->get_param( 'type' );
		WDGRESTAPI_Entity_ProjectOrganization::remove( $project_id, $organization_id, $type );
		$this->log( "WDGRESTAPI_Route_ProjectOrganization::unlink_organization::".$project_id."::".$organization_id."::".$type, 'TRUE' );
		return TRUE;
	}
	
}