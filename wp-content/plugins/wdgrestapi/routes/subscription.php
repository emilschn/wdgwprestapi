<?php
class WDGRESTAPI_Route_Subscription extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/subscription/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get')
		);

		WDGRESTAPI_Route::register_wdg(
			'/subscriptions/',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/subscription',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/subscription/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_subscription = new WDGRESTAPI_Route_subscription();
		return $route_subscription;
	}

	/**
	 * Retourne la liste des abonnements
	 * @return array
	 */
	public function list_get() {
		try {
			return WDGRESTAPI_Entity_Subscription::get_active_subscriptions( $this->get_current_client_autorized_ids_string() );
		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_Subscription::list_get", $e->getMessage() );

			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Retourne un abonnement grâce à son id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_subscription
	 */
	public function single_get( WP_REST_Request $request ) {
		$subscription_id = $request->get_param( 'id' );
		if ( !empty( $subscription_id ) ) {
			try {
				$subscription_item = new WDGRESTAPI_Entity_subscription( $subscription_id );
				$loaded_data_temp = $subscription_item->get_loaded_data();
				
				if ( !empty( $loaded_data_temp ) ) {
					return $loaded_data_temp;
					
				} else {
					$this->log( "WDGRESTAPI_Route_subscription::single_get::" . $subscription_id, "404 : Invalid subscription id" );
					return new WP_Error( '404', "Invalid subscription id" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_subscription::single_get::" . $subscription_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_subscription::single_get", "404 : Invalid subscription id (empty)" );
			return new WP_Error( '404', "Invalid subscription id (empty)" );
		}
	}
	
	/**
	 * Crée un abonnement
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$this->log( "WDGRESTAPI_Route_subscription::single_create", json_encode( $_POST ) );
		$subscription_item = new WDGRESTAPI_Entity_subscription();
		$this->set_posted_properties( $subscription_item, WDGRESTAPI_Entity_subscription::$db_properties );
		if ( $subscription_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$subscription_item->set_property( 'client_user_id', $current_client->ID );
			$save_result = $subscription_item->save();
			$reloaded_data = $subscription_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_subscription::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_subscription::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_subscription::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $subscription_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_subscription::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_subscription::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un abonnement spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$subscription_id = $request->get_param( 'id' );
		if ( !empty( $subscription_id ) ) {
			$subscription_item = new WDGRESTAPI_Entity_subscription( $subscription_id );
			$loaded_data = $subscription_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $subscription_item, WDGRESTAPI_Entity_subscription::$db_properties );
				$subscription_item->save();
				$reloaded_data = $subscription_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_subscription::single_edit::" . $subscription_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_subscription::single_edit::" . $subscription_id, "404 : Invalid subscription id" );
				return new WP_Error( '404', "Invalid subscription id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_subscription::single_edit", "404 : Invalid subscription id (empty)" );
			return new WP_Error( '404', "Invalid subscription id (empty)" );
		}
	}
	
}