<?php
class WDGRESTAPI_Route_Contract extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/contracts',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/contract/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/contract',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/contract/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_contract = new WDGRESTAPI_Route_Contract();
		return $route_contract;
	}
	
	/**
	 * Retourne la liste des contrats
	 * @return array
	 */
	public function list_get() {
		return array(); // TODO
	}
	
	/**
	 * Retourne un contrat grâce à son id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Contract
	 */
	public function single_get( WP_REST_Request $request ) {
		$contract_id = $request->get_param( 'id' );
		if ( !empty( $contract_id ) ) {
			try {
				$contract_item = new WDGRESTAPI_Entity_Contract( $contract_id );
				$loaded_data_temp = $contract_item->get_loaded_data();
				
				if ( !empty( $loaded_data_temp ) && $this->is_data_for_current_client( $loaded_data_temp ) ) {
					$this->log( "WDGRESTAPI_Route_Contract::single_get::" . $contract_id, json_encode( $loaded_data_temp ) );
					return $loaded_data_temp;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Contract::single_get::" . $contract_id, "404 : Invalid contract id" );
					return new WP_Error( '404', "Invalid contract id" );
					
				}

			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Contract::single_get::" . $contract_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Contract::single_get", "404 : Invalid contract id (empty)" );
			return new WP_Error( '404', "Invalid contract id (empty)" );
		}
	}
	
	/**
	 * Crée un contrat
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$contract_item = new WDGRESTAPI_Entity_Contract();
		$this->set_posted_properties( $contract_item, WDGRESTAPI_Entity_Contract::$db_properties );
		if ( $contract_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$contract_item->set_property( 'client_user_id', $current_client->ID );
			$save_result = $contract_item->save();
			$reloaded_data = $contract_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_Contract::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_Contract::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_Contract::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $contract_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_Contract::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_Contract::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un contrat spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$contract_id = $request->get_param( 'id' );
		if ( !empty( $contract_id ) ) {
			$contract_item = new WDGRESTAPI_Entity_Contract( $contract_id );
			$loaded_data = $contract_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $contract_item, WDGRESTAPI_Entity_Contract::$db_properties );
				$result = $contract_item->save();
				if ( $result ) {
					$reloaded_data = $contract_item->get_loaded_data();
					$this->log( "WDGRESTAPI_Route_Contract::single_edit::" . $contract_id, json_encode( $reloaded_data ) );
					return $reloaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Contract::single_edit::" . $contract_id, 'Error invalid data' );
					return new WP_Error( 'cant-edit', "Invalid data" );
				}
				
			} else {
				$this->log( "WDGRESTAPI_Route_Contract::single_edit::" . $contract_id, "404 : Invalid contract id" );
				return new WP_Error( '404', "Invalid contract id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Contract::single_edit", "404 : Invalid contract id (empty)" );
			return new WP_Error( '404', "Invalid contract id (empty)" );
		}
	}
	
}