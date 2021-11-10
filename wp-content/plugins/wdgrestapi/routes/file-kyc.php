<?php
class WDGRESTAPI_Route_FileKYC extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/files-kyc/',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);

		WDGRESTAPI_Route::register_wdg(
			'/file-kyc/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/file-kyc/gateway_id/(?P<id>[a-z0-9]+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_by_gateway_id'),
			array( 'token' => array( 'default' => 0 ) )
		);

		WDGRESTAPI_Route::register_wdg(
			'/file-kyc/(?P<id>\d+)/send-to-lemonway',
			WP_REST_Server::READABLE,
			array( $this, 'single_send_to_lemonway')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/file-kyc',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/file-kyc/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_file_kyc = new WDGRESTAPI_Route_FileKYC();
		return $route_file_kyc;
	}

	/**
	 * Retourne une liste de fichiers en fonction de certains paramètres
	 */
	public function list_get( WP_REST_Request $request ) {
		try {
			$input_entity_type = filter_input( INPUT_GET, 'entity_type' );
			$input_user_id = filter_input( INPUT_GET, 'user_id' );
			$input_organization_id = filter_input( INPUT_GET, 'organization_id' );			
			return WDGRESTAPI_Entity_FileKYC::get_list( $input_entity_type, $input_user_id, $input_organization_id );

		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_FileKYC::list_get", $e->getMessage() );
			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Retourne un fichier KYC grâce à son id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_FileKYC
	 */
	public function single_get( WP_REST_Request $request ) {
		$file_kyc_id = $request->get_param( 'id' );
		if ( !empty( $file_kyc_id ) ) {
			try {
				$file_kyc_item = new WDGRESTAPI_Entity_FileKYC( $file_kyc_id );
				$loaded_data_temp = $file_kyc_item->get_loaded_data();
				
				if ( !empty( $loaded_data_temp ) && $this->is_data_for_current_client( $loaded_data_temp ) ) {
					return $loaded_data_temp;
					
				} else {
					$this->log( "WDGRESTAPI_Route_FileKYC::single_get::" . $file_kyc_id, "404 : Invalid file kyc id" );
					return new WP_Error( '404', "Invalid file kyc id" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_FileKYC::single_get::" . $file_kyc_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_FileKYC::single_get", "404 : Invalid file kyc id (empty)" );
			return new WP_Error( '404', "Invalid file kyc id (empty)" );
		}
	}
	
	/**
	 * Retourne un fichier KYC grâce à son gateway_id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_FileKYC
	 */
	public function single_get_by_gateway_id( WP_REST_Request $request ) {
		$gateway_id = $request->get_param( 'id' );
		if ( !empty( $gateway_id ) ) {
			try {
				$file_kyc_item = WDGRESTAPI_Entity_FileKYC::get_single_by_gateway_id( $gateway_id );
				$loaded_data = $file_kyc_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					$this->log( "WDGRESTAPI_Route_FileKYC::single_get_by_gateway_id::" . $gateway_id, json_encode( $loaded_data ) );
					return $loaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_FileKYC::single_get_by_gateway_id::" . $gateway_id, "404 : Invalid file kyc gateway id" );
					return new WP_Error( '404', "Invalid file kyc gateway id" );
					
				}
			
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_FileKYC::single_get_by_gateway_id::" . $gateway_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_FileKYC::single_get_by_gateway_id", "404 : Invalid file kyc gateway id (empty)" );
			return new WP_Error( '404', "Invalid file kyc gateway id (empty)" );
		}
	}

	/**
	 * Envoie un fichier spécifique à Lemonway
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_send_to_lemonway( WP_REST_Request $request ) {
		$file_kyc_id = $request->get_param( 'id' );
		if ( !empty( $file_kyc_id ) ) {
			try {
				$file_kyc_item = new WDGRESTAPI_Entity_FileKYC( $file_kyc_id );
				$send_result = $file_kyc_item->send_to_lw();
				
				if ( !empty( $send_result ) ) {
					return $send_result;
					
				} else {
					$this->log( "WDGRESTAPI_Route_FileKYC::single_send_to_lemonway::" . $file_kyc_id, "404 : Invalid file kyc id" );
					return new WP_Error( '404', "Invalid file kyc id" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_FileKYC::single_send_to_lemonway::" . $file_kyc_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_FileKYC::single_send_to_lemonway", "404 : Invalid file kyc id (empty)" );
			return new WP_Error( '404', "Invalid file kyc id (empty)" );
		}
	}
	
	/**
	 * Crée un fichier KYC
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$file_kyc_item = new WDGRESTAPI_Entity_FileKYC();
		$this->set_posted_properties( $file_kyc_item, WDGRESTAPI_Entity_FileKYC::$db_properties );
		if ( $file_kyc_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$file_kyc_item->set_property( 'client_user_id', $current_client->ID );
			$file_kyc_data = filter_input( INPUT_POST, 'data' );
			$file_kyc_item->set_file_data( $file_kyc_data );
			$save_result = $file_kyc_item->save();
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_FileKYC::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$reloaded_data = $file_kyc_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_FileKYC::single_create", json_encode( $reloaded_data ) );
				$this->log( "WDGRESTAPI_Route_FileKYC::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $file_kyc_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_FileKYC::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_FileKYC::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un fichier KYC spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$file_kyc_id = $request->get_param( 'id' );
		if ( !empty( $file_kyc_id ) ) {
			$file_kyc_item = new WDGRESTAPI_Entity_FileKYC( $file_kyc_id );
			$loaded_data = $file_kyc_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $file_kyc_item, WDGRESTAPI_Entity_FileKYC::$db_properties );
				$file_kyc_data = filter_input( INPUT_POST, 'data' );
				if ( !empty($file_kyc_data) ){
					$file_kyc_item->set_file_data( $file_kyc_data );
				}			
				$result = $file_kyc_item->save();
				if ( $result ) {
					$reloaded_data = $file_kyc_item->get_loaded_data();
					$this->log( "WDGRESTAPI_Route_FileKYC::single_edit::" . $file_kyc_id, json_encode( $reloaded_data ) );
					return $reloaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_FileKYC::single_edit::" . $file_kyc_id, 'Error invalid data' );
					return new WP_Error( 'cant-edit', "Invalid data" );
				}
				
			} else {
				$this->log( "WDGRESTAPI_Route_FileKYC::single_edit::" . $file_kyc_id, "404 : Invalid file KYC id" );
				return new WP_Error( '404', "Invalid file KYC id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_FileKYC::single_edit", "404 : Invalid file KYC id (empty)" );
			return new WP_Error( '404', "Invalid file KYC id (empty)" );
		}
	}
	
}