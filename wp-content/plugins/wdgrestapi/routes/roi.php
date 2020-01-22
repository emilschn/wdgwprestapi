<?php
class WDGRESTAPI_Route_ROI extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/rois',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/rois/stats',
			WP_REST_Server::READABLE,
			array( $this, 'list_get_stats')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/roi/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'token' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/roi',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/roi/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_declaration = new WDGRESTAPI_Route_ROI();
		return $route_declaration;
	}
	
	/**
	 * Retourne la liste des ROIs
	 * @return array
	 */
	public function list_get() {
		try {
			return WDGRESTAPI_Entity_ROI::list_get( $this->get_current_client_autorized_ids_string() );
			
		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_ROI::list_get", $e->getMessage() );
			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Retourne des statistiques sur les ROIs
	 */
	public function list_get_stats() {
		return array(); // Rien pour l'instant
	}
	
	/**
	 * Retourne un ROI grâce à son id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_ROI
	 */
	public function single_get( WP_REST_Request $request ) {
		$roi_id = $request->get_param( 'id' );
		if ( !empty( $roi_id ) ) {
			try {
				$declaration_item = new WDGRESTAPI_Entity_ROI( $roi_id );
				$loaded_data = $declaration_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					return $loaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_ROI::single_get::" . $roi_id, "404 : Invalid ROI id" );
					return new WP_Error( '404', "Invalid ROI id" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_ROI::single_get::" . $roi_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_ROI::single_get", "404 : Invalid ROI id (empty)" );
			return new WP_Error( '404', "Invalid ROI id (empty)" );
		}
	}
	
	/**
	 * Crée un ROI
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$this->log( "WDGRESTAPI_Entity_ROI::single_create", json_encode( $_POST ) );
		$roi_item = new WDGRESTAPI_Entity_ROI();
		$this->set_posted_properties( $roi_item, WDGRESTAPI_Entity_ROI::$db_properties );
		if ( $roi_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$roi_item->set_property( 'client_user_id', $current_client->ID );
			$save_result = $roi_item->save();
			$reloaded_data = $roi_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_ROI::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_ROI::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_ROI::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $roi_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_ROI::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_ROI::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un ROI spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$roi_id = $request->get_param( 'id' );
		if ( !empty( $roi_id ) ) {
			$roi_item = new WDGRESTAPI_Entity_ROI( $roi_id );
			$loaded_data = $roi_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $roi_item, WDGRESTAPI_Entity_ROI::$db_properties );
				$roi_item->save();
				$reloaded_data = $roi_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_ROI::single_edit::" . $roi_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_ROI::single_edit::" . $roi_id, "404 : Invalid ROI id" );
				return new WP_Error( '404', "Invalid declaration id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_ROI::single_edit", "404 : Invalid ROI id (empty)" );
			return new WP_Error( '404', "Invalid ROI id (empty)" );
		}
	}
	
}