<?php
class WDGRESTAPI_Route_Email extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/emails',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/email',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/sms/clean',
			WP_REST_Server::READABLE,
			array( $this, 'clean_sms_list'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::READABLE )
		);
	}
	
	public static function register() {
		$route_email = new WDGRESTAPI_Route_Email();
		return $route_email;
	}
	
	/**
	 * Retourne la liste des e-mails
	 */
	public function list_get() {
		try {
			return WDGRESTAPI_Entity_Email::list_get( $this->get_current_client_autorized_ids_string() );
			
		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_Email::list_get", $e->getMessage() );
			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Crée un e-mail
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$email_item = new WDGRESTAPI_Entity_Email();
		$this->set_posted_properties( $email_item, WDGRESTAPI_Entity_Email::$db_properties );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$email_item->set_property( 'client_user_id', $current_client->ID );
		$email_item->save();
		$email_item->send();
		$reloaded_data = $email_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_Email::single_create", json_encode( $reloaded_data ) );
		return $reloaded_data;
	}

	public function clean_sms_list() {
		WDGRESTAPI_Entity_Email::clean_sms_list();
	}
	
}