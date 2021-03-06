<?php
class WDGRESTAPI_Route_OrganizationUser extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/organization/(?P<id>\d+)/users',
			WP_REST_Server::READABLE,
			array( $this, 'get_userlist_by_organization_id'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/organizations',
			WP_REST_Server::READABLE,
			array( $this, 'get_organizationlist_by_user_id'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/organization/(?P<id>\d+)/users',
			WP_REST_Server::CREATABLE,
			array( $this, 'link_user'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
	}
	
	public static function register() {
		$route_organization_user = new WDGRESTAPI_Route_OrganizationUser();
		return $route_organization_user;
	}
	
	public function get_userlist_by_organization_id( WP_REST_Request $request ) {
		$organization_id = $request->get_param( 'id' );
		if ( !empty( $organization_id ) ) {
			try {
				$user_list = WDGRESTAPI_Entity_OrganizationUser::get_list_by_organization_id( $organization_id );
				return $user_list;
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_OrganizationUser::get_userlist_by_organization_id::" . $organization_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_OrganizationUser::get_userlist_by_organization_id", "404 : Invalid organization ID (empty)" );
			return new WP_Error( '404', "Invalid organization ID (empty)" );
		}
	}
	
	public function get_organizationlist_by_user_id( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'id' );
		if ( !empty( $user_id ) ) {
			try {
				$result = WDGRESTAPI_Entity_OrganizationUser::get_list_by_user_id( $user_id );
				$organization_list = array();
				foreach ( $result as $link_item ) {
					$organization = new WDGRESTAPI_Entity_Organization( $link_item->id_organization );
					$loaded_data = $organization->get_loaded_data();
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
				
				return $organization_list;
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_OrganizationUser::get_organizationlist_by_user_id::" . $user_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_OrganizationUser::get_organizationlist_by_user_id", "404 : Invalid user ID (empty)" );
			return new WP_Error( '404', "Invalid user ID (empty)" );
		}
	}
	
	public function link_user( WP_REST_Request $request ) {
		$organization_id = $request->get_param( 'id' );
		$organizationuser_item = new WDGRESTAPI_Entity_OrganizationUser();
		$this->set_posted_properties( $organizationuser_item, WDGRESTAPI_Entity_OrganizationUser::$db_properties );
		$organizationuser_item->set_property( 'id_organization', $organization_id );
		$organizationuser_item->save();
		$reloaded_data = $organizationuser_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_OrganizationUser::link_user", json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
}