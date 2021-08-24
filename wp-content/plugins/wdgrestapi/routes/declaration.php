<?php
class WDGRESTAPI_Route_Declaration extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/declarations',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/declarations/stats',
			WP_REST_Server::READABLE,
			array( $this, 'list_get_stats')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/declaration/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'token' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/declaration/token/(?P<token>[a-z0-9]+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_by_token'),
			array( 'token' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/declaration',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/declaration/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/declaration/(?P<id>\d+)/rois',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_rois'),
			array( 'token' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/declaration/(?P<id>\d+)/adjustments',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_adjustments'),
			array( 'token' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/declaration/(?P<id>\d+)/files',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_files'),
			array( 'token' => array( 'default' => 0 ) )
		);
	}
	
	public static function register() {
		$route_declaration = new WDGRESTAPI_Route_Declaration();
		return $route_declaration;
	}
	
	/**
	 * Retourne la liste des déclarations
	 * @return array
	 */
	public function list_get() {
		try {
			$input_start_date = filter_input( INPUT_GET, 'start_date' );
			$input_end_date = filter_input( INPUT_GET, 'end_date' );
			$input_type = filter_input( INPUT_GET, 'type' );
			$input_status = filter_input( INPUT_GET, 'status' );
			$start_date = ( !empty( $input_start_date ) ) ? new DateTime( $input_start_date ) : FALSE;
			$end_date = ( !empty( $input_end_date ) ) ? new DateTime( $input_end_date ) : FALSE;
			$type = ( !empty( $input_type ) ) ? $input_type : FALSE;
			$status = ( !empty( $input_status ) ) ? $input_status : FALSE;
			return WDGRESTAPI_Entity_Declaration::list_get( $this->get_current_client_autorized_ids_string(), $start_date, $end_date, $type, $status );

		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_Declaration::list_get", $e->getMessage() );
			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Retourne des statistiques sur les déclarations
	 */
	public function list_get_stats() {
		return array(); // Rien pour l'instant
	}
	
	/**
	 * Retourne une déclaration grâce à son id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Declaration
	 */
	public function single_get( WP_REST_Request $request ) {
		$declaration_id = $request->get_param( 'id' );
		if ( !empty( $declaration_id ) ) {
			try {
				$declaration_item = new WDGRESTAPI_Entity_Declaration( $declaration_id );
				$loaded_data_temp = $declaration_item->get_loaded_data();
				
				if ( !empty( $loaded_data_temp ) && $this->is_data_for_current_client( $loaded_data_temp ) ) {
					$loaded_data = WDGRESTAPI_Entity_Declaration::complete_single_data( $loaded_data_temp );
					return $loaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Declaration::single_get::" . $declaration_id, "404 : Invalid declaration id" );
					return new WP_Error( '404', "Invalid declaration id" );
					
				}

			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Declaration::single_get::" . $declaration_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Declaration::single_get", "404 : Invalid declaration id (empty)" );
			return new WP_Error( '404', "Invalid declaration id (empty)" );
		}
	}
	
	/**
	 * Retourne une déclaration par le token de paiement
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_get_by_token( WP_REST_Request $request ) {
		$payment_token = $request->get_param( 'token' );
		if ( !empty( $payment_token ) ) {
			try {
				$declaration_item = new WDGRESTAPI_Entity_Declaration( FALSE, $payment_token );
				$loaded_data = $declaration_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					$this->log( "WDGRESTAPI_Route_Declaration::single_get_by_token::" . $payment_token, json_encode( $loaded_data ) );
					return $loaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Declaration::single_get_by_token::" . $payment_token, "404 : Invalid declaration payment token" );
					return new WP_Error( '404', "Invalid declaration payment token" );
					
				}
			
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Declaration::single_get_by_token::" . $payment_token, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Declaration::single_get_by_token", "404 : Invalid declaration payment token (empty)" );
			return new WP_Error( '404', "Invalid declaration payment token (empty)" );
		}
	}
	
	/**
	 * Crée une déclaration
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$this->log( "WDGRESTAPI_Route_Declaration::single_create", json_encode( $_POST ) );
		$declaration_item = new WDGRESTAPI_Entity_Declaration();
		$this->set_posted_properties( $declaration_item, WDGRESTAPI_Entity_Declaration::$db_properties );
		if ( $declaration_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$declaration_item->set_property( 'client_user_id', $current_client->ID );
			$save_result = $declaration_item->save();
			$reloaded_data = $declaration_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_Declaration::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_Declaration::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_Declaration::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $declaration_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_Declaration::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_Declaration::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite une déclaration spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$declaration_id = $request->get_param( 'id' );
		if ( !empty( $declaration_id ) ) {
			$declaration_item = new WDGRESTAPI_Entity_Declaration( $declaration_id );
			$loaded_data = $declaration_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $declaration_item, WDGRESTAPI_Entity_Declaration::$db_properties );
				$declaration_item->save();
				$reloaded_data = $declaration_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_Declaration::single_edit::" . $declaration_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Declaration::single_edit::" . $declaration_id, "404 : Invalid declaration id" );
				return new WP_Error( '404', "Invalid declaration id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Declaration::single_edit", "404 : Invalid declaration id (empty)" );
			return new WP_Error( '404', "Invalid declaration id (empty)" );
		}
	}
	
	/**
	 * Retourne les ROIs liées à une déclaration (par l'ID de la déclaration)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_rois( WP_REST_Request $request ) {
		$declaration_id = $request->get_param( 'id' );
		if ( !empty( $declaration_id ) ) {
			try {
				$declaration_item = new WDGRESTAPI_Entity_Declaration( $declaration_id );
				$loaded_data = $declaration_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					$rois_data = $declaration_item->get_rois();
					return $rois_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Declaration::single_get_rois::" . $declaration_id, "404 : Invalid declaration ID" );
					return new WP_Error( '404', "Invalid declaration ID" );
					
				}
			
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Declaration::single_get_rois::" . $declaration_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Declaration::single_get_rois", "404 : Invalid declaration ID (empty)" );
			return new WP_Error( '404', "Invalid declaration ID (empty)" );
		}
	}
	
	/**
	 * Retourne les ajustements appliqués à une déclaration (par l'ID de la déclaration)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_adjustments( WP_REST_Request $request ) {
		$declaration_id = $request->get_param( 'id' );
		if ( !empty( $declaration_id ) ) {
			try {
				$declaration_item = new WDGRESTAPI_Entity_Declaration( $declaration_id );
				$loaded_data = $declaration_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					$input_linked = filter_input( INPUT_GET, 'linked' );
					if ( empty( $input_linked ) ) {
						$rois_data = $declaration_item->get_adjustments();
					} else {
						$rois_data = $declaration_item->get_linked_adjustments();
					}
					return $rois_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Declaration::single_get_adjustments::" . $declaration_id, "404 : Invalid declaration ID" );
					return new WP_Error( '404', "Invalid declaration ID" );
					
				}
			
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Declaration::single_get_adjustments::" . $declaration_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Declaration::single_get_adjustments", "404 : Invalid declaration ID (empty)" );
			return new WP_Error( '404', "Invalid declaration ID (empty)" );
		}
	}
	
	/**
	 * Retourne les fichiers liés à une déclaration (par l'ID de la déclaration)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_files( WP_REST_Request $request ) {
		$declaration_id = $request->get_param( 'id' );
		if ( !empty( $declaration_id ) ) {
			try {
				$declaration_item = new WDGRESTAPI_Entity_Declaration( $declaration_id );
				$loaded_data = $declaration_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					$input_file_type = filter_input( INPUT_GET, 'file_type' );
					$file_list = $declaration_item->get_files( $input_file_type );
					return $file_list;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Declaration::single_get_files::" . $declaration_id, "404 : Invalid declaration ID" );
					return new WP_Error( '404', "Invalid declaration ID" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Declaration::single_get_files::" . $declaration_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Declaration::single_get_files", "404 : Invalid declaration ID (empty)" );
			return new WP_Error( '404', "Invalid declaration ID (empty)" );
		}
	}
	
}