<?php
class WDGRESTAPI_Route_Investment extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register(
			'/investments',
			WP_REST_Server::READABLE,
			'WDGRESTAPI_Entity_Investment::list_get'
		);
		
		WDGRESTAPI_Route::register_external(
			'/investment/(?P<token>[a-z0-9]+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'token' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_external(
			'/investment',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register(
			'/investment/(?P<token>[a-z0-9]+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_investment = new WDGRESTAPI_Route_Investment();
		return $route_investment;
	}
	
	/**
	 * Retourne un investissement grâce à son token
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Project
	 */
	public function single_get( WP_REST_Request $request ) {
		$investment_token = $request->get_param( 'token' );
		if ( !empty( $investment_token ) ) {
			$investment_item = new WDGRESTAPI_Entity_Investment( '', $investment_token );
			$loaded_data = $investment_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->log( "WDGRESTAPI_Route_Investment::single_get::" . $investment_token, json_encode( $loaded_data ) );
				return $loaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Investment::single_get::" . $investment_token, "404 : Invalid investment token" );
				return new WP_Error( '404', "Invalid investment token" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Investment::single_get", "404 : Invalid investment token (empty)" );
			return new WP_Error( '404', "Invalid investment token (empty)" );
		}
	}
	
	/**
	 * Crée un investissement
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$this->log( "WDGRESTAPI_Route_Investment::single_create", json_encode( $_POST ) );
		$investment_item = new WDGRESTAPI_Entity_Investment();
		$this->set_posted_properties( $investment_item, WDGRESTAPI_Entity_Investment::$db_properties );
		if ( $investment_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$investment_item->set_property( 'client_user_id', $current_client->ID );
			$investment_item->set_property( 'token', $investment_item->make_uid() );
			$investment_item->set_property( 'status', WDGRESTAPI_Entity_Investment::$status_init );
			$date_expiration = new DateTime();
			$date_expiration->add( new DateInterval( 'PT1H' ) );
			$investment_item->set_property( 'token_expiration', $date_expiration->format('Y-m-d H:i:s') );
			$save_result = $investment_item->save();
			$reloaded_data = $investment_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_Investment::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_Investment::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_Investment::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $investment_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_Investment::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_Investment::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un investissement spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$investment_token = $request->get_param( 'token' );
		if ( !empty( $investment_token ) ) {
			$investment_item = new WDGRESTAPI_Entity_Investment( '', $investment_token );
			$loaded_data = $investment_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $investment_item, WDGRESTAPI_Entity_Investment::$db_properties );
				$investment_item->save();
				$reloaded_data = $investment_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_Investment::single_edit::" . $investment_token, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Investment::single_edit::" . $investment_token, "404 : Invalid investment token" );
				return new WP_Error( '404', "Invalid investment token" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Investment::single_edit", "404 : Invalid investment token (empty)" );
			return new WP_Error( '404', "Invalid investment token (empty)" );
		}
	}
	
}