<?php
class WDGRESTAPI_Route_ContractModel extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/contract-models',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/contract-model/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/contract-model',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/contract-model/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_contract_model = new WDGRESTAPI_Route_ContractModel();
		return $route_contract_model;
	}
	
	/**
	 * Retourne la liste des modèles de contrat
	 * @return array
	 */
	public function list_get() {
		return array(); // TODO
	}
	
	/**
	 * Retourne un modèle de contrat grâce à son id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_ContractModel
	 */
	public function single_get( WP_REST_Request $request ) {
		$contract_model_id = $request->get_param( 'id' );
		if ( !empty( $contract_model_id ) ) {
			$contract_model_item = new WDGRESTAPI_Entity_ContractModel( $contract_model_id );
			$loaded_data_temp = $contract_model_item->get_loaded_data();
			
			if ( !empty( $loaded_data_temp ) && $this->is_data_for_current_client( $loaded_data_temp ) ) {
				$this->log( "WDGRESTAPI_Route_ContractModel::single_get::" . $contract_model_id, json_encode( $loaded_data_temp ) );
				return $loaded_data_temp;
				
			} else {
				$this->log( "WDGRESTAPI_Route_ContractModel::single_get::" . $contract_model_id, "404 : Invalid contract model id" );
				return new WP_Error( '404', "Invalid contract model id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_ContractModel::single_get", "404 : Invalid contract model id (empty)" );
			return new WP_Error( '404', "Invalid contract model id (empty)" );
		}
	}
	
	/**
	 * Crée un modèle de contrat
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$contract_model_item = new WDGRESTAPI_Entity_ContractModel();
		$this->set_posted_properties( $contract_model_item, WDGRESTAPI_Entity_ContractModel::$db_properties );
		if ( $contract_model_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$contract_model_item->set_property( 'client_user_id', $current_client->ID );
			$save_result = $contract_model_item->save();
			$reloaded_data = $contract_model_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_ContractModel::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_ContractModel::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_ContractModel::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $contract_model_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_ContractModel::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_ContractModel::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un modèle de contrat spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$contract_model_id = $request->get_param( 'id' );
		if ( !empty( $contract_model_id ) ) {
			$contract_model_item = new WDGRESTAPI_Entity_ContractModel( $contract_model_id );
			$loaded_data = $contract_model_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $contract_model_item, WDGRESTAPI_Entity_ContractModel::$db_properties );
				$result = $contract_model_item->save();
				if ( $result ) {
					$reloaded_data = $contract_model_item->get_loaded_data();
					$this->log( "WDGRESTAPI_Route_ContractModel::single_edit::" . $contract_model_id, json_encode( $reloaded_data ) );
					return $reloaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_ContractModel::single_edit::" . $contract_model_id, 'Error invalid data' );
					return new WP_Error( 'cant-edit', "Invalid data" );
				}
				
			} else {
				$this->log( "WDGRESTAPI_Route_ContractModel::single_edit::" . $contract_model_id, "404 : Invalid contract model id" );
				return new WP_Error( '404', "Invalid contract model id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_ContractModel::single_edit", "404 : Invalid contract model id (empty)" );
			return new WP_Error( '404', "Invalid contract model id (empty)" );
		}
	}
	
}