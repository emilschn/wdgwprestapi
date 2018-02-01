<?php
class WDGRESTAPI_Route_ProjectUser extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)/users',
			WP_REST_Server::READABLE,
			array( $this, 'get_userlist_by_project_id'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/projects',
			WP_REST_Server::READABLE,
			array( $this, 'get_projectlist_by_user_id'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)/users',
			WP_REST_Server::CREATABLE,
			array( $this, 'link_user'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<projectid>\d+)/user/(?P<userid>\d+)/type/(?P<type>[a-z]+)',
			WP_REST_Server::DELETABLE,
			array( $this, 'unlink_user'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE )
		);
	}
	
	public static function register() {
		$route_project_user = new WDGRESTAPI_Route_ProjectUser();
		return $route_project_user;
	}
	
	public function get_userlist_by_project_id( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$result = WDGRESTAPI_Entity_ProjectUser::get_list_by_project_id( $project_id );
			$user_list = array();
			foreach ( $result as $link_item ) {
				$user = new WDGRESTAPI_Entity_User( $link_item->id_user );
				$loaded_data = $user->get_loaded_data();
				array_push( 
					$user_list,
					array( 
						"id"		=> $loaded_data->id,
						"wpref"		=> $loaded_data->wpref,
						"name"		=> $loaded_data->name,
						"surname"	=> $loaded_data->surname,
						"type"		=> $link_item->type
					)
				);
			}
			
			$this->log( "WDGRESTAPI_Route_ProjectUser::get_userlist_by_project_id::" . $project_id, json_encode( $user_list ) );
			return $user_list;
			
		} else {
			$this->log( "WDGRESTAPI_Route_ProjectUser::get_userlist_by_project_id", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	public function get_projectlist_by_user_id( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'id' );
		if ( !empty( $user_id ) ) {
			$result = WDGRESTAPI_Entity_ProjectUser::get_list_by_user_id( $user_id );
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
			
			$this->log( "WDGRESTAPI_Route_ProjectUser::get_projectlist_by_user_id::" . $user_id, json_encode( $project_list ) );
			return $project_list;
			
		} else {
			$this->log( "WDGRESTAPI_Route_ProjectUser::get_projectlist_by_user_id", "404 : Invalid user ID (empty)" );
			return new WP_Error( '404', "Invalid user ID (empty)" );
		}
	}
	
	public function link_user( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		$projectuser_item = new WDGRESTAPI_Entity_ProjectUser();
		$this->set_posted_properties( $projectuser_item, WDGRESTAPI_Entity_ProjectUser::$db_properties );
		$projectuser_item->set_property( 'id_project', $project_id );
		//TODO : vérifier que l'utilisateur et le projet existent ?
		$projectuser_item->save();
		$reloaded_data = $projectuser_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_ProjectUser::link_user::" . $project_id, json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
	public function unlink_user( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'projectid' );
		$user_id = $request->get_param( 'userid' );
		$type = $request->get_param( 'type' );
		WDGRESTAPI_Entity_ProjectUser::remove( $project_id, $user_id, $type );
		$this->log( "WDGRESTAPI_Route_ProjectUser::unlink_user::".$project_id."::".$user_id."::".$type, 'TRUE' );
		return TRUE;
	}
	
}