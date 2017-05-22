<?php
class WDGRESTAPI_Route_BankInfo extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_external(
			'/bankinfo/(?P<email>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'email' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_external(
			'/bankinfo',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_external(
			'/bankinfo/(?P<email>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
		
		WDGRESTAPI_Route::register_external(
			'/bankinfo/(?P<email>\d+)',
			WP_REST_Server::DELETABLE,
			array( $this, 'single_delete'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE )
		);
	}
	
	public static function register() {
		$route_bankinfo = new WDGRESTAPI_Route_BankInfo();
		return $route_bankinfo;
	}
	
	/**
	 * Retourne les infos bancaires grâce à un e-mail
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Project
	 */
	public function single_get( WP_REST_Request $request ) {
		$bankinfo_email = $request->get_param( 'email' );
		if ( !empty( $bankinfo_email ) ) {
			$bankinfo_item = new WDGRESTAPI_Route_BankInfo( '', $bankinfo_email );
			$loaded_data = $bankinfo_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->log( "WDGRESTAPI_Route_BankInfo::single_get::" . $bankinfo_email, json_encode( $loaded_data ) );
				return $loaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_BankInfo::single_get::" . $bankinfo_email, "404 : Invalid bank info e-mail" );
				return new WP_Error( '404', "Invalid bank info e-mail" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_BankInfo::single_get", "404 : Invalid bank info e-mail (empty)" );
			return new WP_Error( '404', "Invalid bank info e-mail (empty)" );
		}
	}
	
	/**
	 * Crée un item d'informations bancaires
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$this->log( "WDGRESTAPI_Route_BankInfo::single_create", json_encode( $_POST ) );
		$bankinfo_item = new WDGRESTAPI_Entity_BankInfo();
		$this->set_posted_properties( $bankinfo_item, WDGRESTAPI_Entity_BankInfo::$db_properties );
		if ( $bankinfo_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$bankinfo_item->set_property( 'client_user_id', $current_client->ID );
			$date_creation = new DateTime();
			$bankinfo_item->set_property( 'date_update', $date_creation->format('Y-m-d H:i:s') );
			$save_result = $bankinfo_item->save();
			$reloaded_data = $bankinfo_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_BankInfo::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_BankInfo::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_BankInfo::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $bankinfo_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_BankInfo::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_BankInfo::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite des informations bancaires correspondantes à un email
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$bankinfo_email = $request->get_param( 'email' );
		if ( !empty( $bankinfo_email ) ) {
			$bankinfo_item = new WDGRESTAPI_Entity_BankInfo( '', $bankinfo_email );
			$loaded_data = $bankinfo_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $bankinfo_item, WDGRESTAPI_Entity_BankInfo::$db_properties );
				$bankinfo_item->save();
				$reloaded_data = $bankinfo_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_BankInfo::single_edit::" . $bankinfo_email, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_BankInfo::single_edit::" . $bankinfo_email, "404 : Invalid bank info e-mail" );
				return new WP_Error( '404', "Invalid bank info e-mail" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_BankInfo::single_edit", "404 : Invalid bank info e-mail (empty)" );
			return new WP_Error( '404', "Invalid bank info e-mail (empty)" );
		}
	}
	
	/**
	 * Supprime les informations bancaires correspondantes à un email
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_delete( WP_REST_Request $request ) {
		$bankinfo_email = $request->get_param( 'email' );
		if ( !empty( $bankinfo_email ) ) {
			$bankinfo_item = new WDGRESTAPI_Entity_BankInfo( '', $bankinfo_email );
			$loaded_data = $bankinfo_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$bankinfo_item->delete();
				$reloaded_data = TRUE;
				$this->log( "WDGRESTAPI_Route_BankInfo::single_delete::" . $bankinfo_email, 'deleted' );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_BankInfo::single_delete::" . $bankinfo_email, "404 : Invalid bank info e-mail" );
				return new WP_Error( '404', "Invalid bank info e-mail" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_BankInfo::single_delete", "404 : Invalid bank info e-mail (empty)" );
			return new WP_Error( '404', "Invalid bank info e-mail (empty)" );
		}
	}
	
}