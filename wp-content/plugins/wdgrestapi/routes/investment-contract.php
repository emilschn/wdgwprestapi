<?php
class WDGRESTAPI_Route_InvestmentContract extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/investment-contracts',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/investment-contract/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/investment-contract',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/investment-contract/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_investment_contract = new WDGRESTAPI_Route_InvestmentContract();
		return $route_investment_contract;
	}
	
	/**
	 * Retourne la liste des contrats d'investissement
	 * @return array
	 */
	public function list_get() {
		try {
			$input_subscription = filter_input( INPUT_GET, 'id_subscription' );
			return WDGRESTAPI_Entity_InvestmentContract::list_get( $this->get_current_client_autorized_ids_string(), $input_subscription );
				
		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_InvestmentContract::list_get", $e->getMessage() );
			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Retourne un contrat d'investissement grâce à son id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Project
	 */
	public function single_get( WP_REST_Request $request ) {
		$investment_contract_id = $request->get_param( 'id' );
		if ( !empty( $investment_contract_id ) ) {
			try {
				$investment_contract_item = new WDGRESTAPI_Entity_InvestmentContract( $investment_contract_id );
				$loaded_data = $investment_contract_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					return $loaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_InvestmentContract::single_get::" . $investment_contract_id, "404 : Invalid investment contract id" );
					return new WP_Error( '404', "Invalid investment contract id" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_InvestmentContract::single_get::" . $investment_contract_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_InvestmentContract::single_get", "404 : Invalid investment contract id (empty)" );
			return new WP_Error( '404', "Invalid investment contract id (empty)" );
		}
	}
	
	/**
	 * Crée un contrat d'investissement
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$this->log( "WDGRESTAPI_Route_InvestmentContract::single_create", json_encode( $_POST ) );
		$investment_contract_item = new WDGRESTAPI_Entity_InvestmentContract();
		$this->set_posted_properties( $investment_contract_item, WDGRESTAPI_Entity_InvestmentContract::$db_properties );
		if ( $investment_contract_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$investment_contract_item->set_property( 'client_user_id', $current_client->ID );
			$save_result = $investment_contract_item->save();
			$reloaded_data = $investment_contract_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_InvestmentContract::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_InvestmentContract::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_InvestmentContract::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $investment_contract_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_InvestmentContract::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_InvestmentContract::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un contrat d'investissement spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$investment_contract_id = $request->get_param( 'id' );
		if ( !empty( $investment_contract_id ) ) {
			$investment_contract_item = new WDGRESTAPI_Entity_InvestmentContract( $investment_contract_id );
			$loaded_data = $investment_contract_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $investment_contract_item, WDGRESTAPI_Entity_InvestmentContract::$db_properties );
				$investment_contract_item->save();
				$reloaded_data = $investment_contract_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_InvestmentContract::single_edit::" . $investment_contract_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_InvestmentContract::single_edit::" . $investment_contract_id, "404 : Invalid investment contract id" );
				return new WP_Error( '404', "Invalid investment contract id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_InvestmentContract::single_edit", "404 : Invalid investment contract id (empty)" );
			return new WP_Error( '404', "Invalid investment contract id (empty)" );
		}
	}
	
}