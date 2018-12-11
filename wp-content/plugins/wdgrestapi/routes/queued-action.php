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
	}
	
	public static function register() {
		$route_queued_action = new WDGRESTAPI_Route_QueuedAction();
		return $route_queued_action;
	}
	
	/**
	 * Retourne la liste des e-mails
	 */
	public function list_get() {
		$input_limit = filter_input( INPUT_GET, 'limit' );
		$input_entity_id = filter_input( INPUT_GET, 'entity_id' );
		$input_action = filter_input( INPUT_GET, 'action' );
		return WDGRESTAPI_Entity_QueuedAction::list_get( $this->get_current_client_autorized_ids_string(), $input_limit, $input_entity_id, $input_action );
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
	
}