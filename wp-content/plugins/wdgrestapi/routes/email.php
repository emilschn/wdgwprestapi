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
			'/email/(?P<id>\d+)/send',
			WP_REST_Server::READABLE,
			array( $this, 'single_send')
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
			$input_id_template = filter_input( INPUT_GET, 'id_template' );
			$input_recipient_email = filter_input( INPUT_GET, 'recipient_email' );
			return WDGRESTAPI_Entity_Email::list_get( $input_id_template, $input_recipient_email );
			
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
	
	public function single_send( WP_REST_Request $request ) {
		$email_id = $request->get_param( 'id' );
		if ( !empty( $email_id ) ) {
			try {
				$email_item = new WDGRESTAPI_Entity_Email( $email_id );
				$send_result = $email_item->send();
				
				if ( !empty( $send_result ) ) {
					return $send_result;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Email::single_send::" . $email_id, "404 : Invalid email id" );
					return new WP_Error( '404', "Invalid email id" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Email::single_send::" . $email_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Email::single_send", "404 : Invalid email id (empty)" );
			return new WP_Error( '404', "Invalid email id (empty)" );
		}
	}
}