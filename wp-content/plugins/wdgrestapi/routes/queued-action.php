<?php
class WDGRESTAPI_Route_QueuedAction extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/queued-actions',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/queued-action',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/queued-action/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_queued_action = new WDGRESTAPI_Route_QueuedAction();
		return $route_queued_action;
	}
	
	/**
	 * Retourne la liste des e-mails
	 */
	public function list_get() {
		try {
			$input_limit = filter_input( INPUT_GET, 'limit' );
			$input_next_to_execute = filter_input( INPUT_GET, 'next_to_execute' );
			$input_entity_id = filter_input( INPUT_GET, 'entity_id' );
			$input_action = filter_input( INPUT_GET, 'action' );
			return WDGRESTAPI_Entity_QueuedAction::list_get( $this->get_current_client_autorized_ids_string(), $input_limit, $input_next_to_execute, $input_entity_id, $input_action );
			
		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_QueuedAction::list_get", $e->getMessage() );
			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Crée une action dans la queue
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$queued_action_item = new WDGRESTAPI_Entity_QueuedAction();
		$this->set_posted_properties( $queued_action_item, WDGRESTAPI_Entity_QueuedAction::$db_properties );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$queued_action_item->set_property( 'client_user_id', $current_client->ID );
		$queued_action_item->save();
		$reloaded_data = $queued_action_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Entity_QueuedAction::single_create", json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
	/**
	 * Edite une action dans la queue
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$queued_action_id = $request->get_param( 'id' );
		if ( !empty( $queued_action_id ) ) {
			$queued_action_item = new WDGRESTAPI_Entity_QueuedAction( $queued_action_id );
			$loaded_data = $queued_action_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $queued_action_item, WDGRESTAPI_Entity_QueuedAction::$db_properties );
				$queued_action_item->save();
				$reloaded_data = $queued_action_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_QueuedAction::single_edit::" . $queued_action_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_QueuedAction::single_edit::" . $queued_action_id, "404 : Invalid queued action ID" );
				return new WP_Error( '404', "Invalid queued action ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_QueuedAction::single_edit", "404 : Invalid queued action ID (empty)" );
			return new WP_Error( '404', "Invalid queued action ID (empty)" );
		}
	}
	
}