<?php
class WDGRESTAPI_Route_User extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register(
			'/users',
			WP_REST_Server::READABLE,
			'WDGRESTAPI_Entity_User::list_get'
		);
		
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
		
		WDGRESTAPI_Route::register_external(
			'/user/(?P<email>[a-zA-Z0-9\-\@\.]+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit_email'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
		
		WDGRESTAPI_Route::register_external(
			'/user/(?P<email>[a-zA-Z0-9\-\@\.]+)/royalties',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_royalties'),
			array( 'id' => array( 'default' => 0 ) )
		);
	}
	
	public static function register() {
		$route_user = new WDGRESTAPI_Route_User();
		return $route_user;
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
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->log( "WDGRESTAPI_Route_User::single_get::" . $user_id, json_encode( $loaded_data ) );
				return $loaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_User::single_get::" . $user_id, "404 : Invalid user ID" );
				return new WP_Error( '404', "Invalid user ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_User::single_get", "404 : Invalid user ID (empty)" );
			return new WP_Error( '404', "Invalid user ID (empty)" );
		}
	}
	
	/**
	 * Retourne les royalties d'un utilisateur par son ID
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_royalties( WP_REST_Request $request ) {
		$user_email = $request->get_param( 'email' );
		if ( !empty( $user_email ) ) {
			$royalties_data = WDGRESTAPI_Entity_User::get_royalties_data( $user_email );
			$this->log( "WDGRESTAPI_Route_User::single_get_royalties::" . $user_email, json_encode( $royalties_data ) );
			return $royalties_data;
			
		} else {
			$this->log( "WDGRESTAPI_Route_User::single_get_royalties", "404 : Invalid user email (empty)" );
			return new WP_Error( '404', "Invalid user email (empty)" );
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
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$user_item->set_property( 'client_user_id', $current_client->ID );
		$user_item->save();
		$reloaded_data = $user_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_User::single_create", json_encode( $reloaded_data ) );
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
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $user_item, WDGRESTAPI_Entity_User::$db_properties );
				$user_item->save();
				$reloaded_data = $user_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_User::single_edit::" . $user_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_User::single_edit::" . $user_id, "404 : Invalid user ID" );
				return new WP_Error( '404', "Invalid user ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_User::single_edit", "404 : Invalid user ID (empty)" );
			return new WP_Error( '404', "Invalid user ID (empty)" );
		}
	}
	
	/**
	 * Edite un utilisateur spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit_email( WP_REST_Request $request ) {
		$user_email = $request->get_param( 'email' );
		if ( !empty( $user_email ) ) {
			$email_data = WDGRESTAPI_Entity_User::update_email( $user_email, $_POST );
			$this->log( "WDGRESTAPI_Route_User::single_edit_email::" . $user_email, json_encode( $email_data ) );
			return $email_data;
			
		} else {
			$this->log( "WDGRESTAPI_Route_User::single_edit_email", "404 : Invalid user email (empty)" );
			return new WP_Error( '404', "Invalid user email (empty)" );
		}
	}
	
}