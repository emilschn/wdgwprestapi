<?php
class WDGRESTAPI_Route_Bill extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register(
			'/bills',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register(
			'/bill',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
	}
	
	public static function register() {
		$route_bill = new WDGRESTAPI_Route_Bill();
		return $route_bill;
	}
	
	/**
	 * Retourne la liste des e-mails
	 */
	public function list_get() {
		return WDGRESTAPI_Entity_Bill::list_get( $this->get_current_client_autorized_ids_string() );
	}
	
	/**
	 * Crée un e-mail
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$bill_item = new WDGRESTAPI_Entity_Bill();
		$this->set_posted_properties( $bill_item, WDGRESTAPI_Entity_Bill::$db_properties );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$bill_item->set_property( 'client_user_id', $current_client->ID );
		$bill_item->save();
		$reloaded_data = $bill_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Entity_Bill::single_create", json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
}