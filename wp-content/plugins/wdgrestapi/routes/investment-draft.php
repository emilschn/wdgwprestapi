<?php
class WDGRESTAPI_Route_InvestmentDraft extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/investment-drafts',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/investment-draft/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/investment-draft',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/investment-draft/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_investment_draft = new WDGRESTAPI_Route_InvestmentDraft();
		return $route_investment_draft;
	}
	
	/**
	 * Retourne la liste des brouillons investissements
	 * @return array
	 */
	public function list_get() {
		$input_project_id = filter_input( INPUT_GET, 'project_id' );
		return WDGRESTAPI_Entity_InvestmentDraft::list_get( $this->get_current_client_autorized_ids_string(), $input_project_id );
	}
	
	/**
	 * Retourne un brouillon investissement grâce à son token
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Project
	 */
	public function single_get( WP_REST_Request $request ) {
		$investment_draft_id = $request->get_param( 'id' );
		if ( !empty( $investment_draft_id ) ) {
			$investment_draft_item = new WDGRESTAPI_Entity_InvestmentDraft( $investment_draft_id );
			$loaded_data = $investment_draft_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_get::" . $investment_draft_id, json_encode( $loaded_data ) );
				return $loaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_get::" . $investment_draft_id, "404 : Invalid investment draft id" );
				return new WP_Error( '404', "Invalid investment draft id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_get", "404 : Invalid investment draft id (empty)" );
			return new WP_Error( '404', "Invalid investment draft id (empty)" );
		}
	}
	
	/**
	 * Crée un brouillon d'investissement
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_create", json_encode( $_POST ) );
		$investment_draft_item = new WDGRESTAPI_Entity_InvestmentDraft();
		$this->set_posted_properties( $investment_draft_item, WDGRESTAPI_Entity_InvestmentDraft::$db_properties );
		if ( $investment_draft_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$investment_draft_item->set_property( 'client_user_id', $current_client->ID );
			$save_result = $investment_draft_item->save();
			$reloaded_data = $investment_draft_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $investment_draft_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un brouillon d'investissement spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$investment_draft_id = $request->get_param( 'id' );
		if ( !empty( $investment_draft_id ) ) {
			$investment_draft_item = new WDGRESTAPI_Entity_InvestmentDraft( $investment_draft_id );
			$loaded_data = $investment_draft_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $investment_draft_item, WDGRESTAPI_Entity_InvestmentDraft::$db_properties );
				$investment_draft_item->save();
				$reloaded_data = $investment_draft_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_edit::" . $investment_draft_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_edit::" . $investment_draft_id, "404 : Invalid investment draft id" );
				return new WP_Error( '404', "Invalid investment draft id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_InvestmentDraft::single_edit", "404 : Invalid investment draft id (empty)" );
			return new WP_Error( '404', "Invalid investment draft id (empty)" );
		}
	}
	
}