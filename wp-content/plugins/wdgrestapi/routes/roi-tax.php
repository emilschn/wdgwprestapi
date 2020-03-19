<?php
class WDGRESTAPI_Route_ROITax extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/roi-taxes',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/roi-tax',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/roi-tax/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_declaration = new WDGRESTAPI_Route_ROITax();
		return $route_declaration;
	}
	
	/**
	 * Retourne la liste des ROITax
	 * @return array
	 */
	public function list_get() {
		try {
			return WDGRESTAPI_Entity_ROITax::list_get( $this->get_current_client_autorized_ids_string() );
			
		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_ROITax::list_get", $e->getMessage() );
			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Crée un ROITax
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$roitax_item = new WDGRESTAPI_Entity_ROITax();
		$this->set_posted_properties( $roitax_item, WDGRESTAPI_Entity_ROITax::$db_properties );
		if ( $roitax_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$roitax_item->set_property( 'client_user_id', $current_client->ID );
			$save_result = $roitax_item->save();
			$reloaded_data = $roitax_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_ROITax::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_ROITax::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_ROITax::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $roitax_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_ROITax::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_ROITax::single_create", $error_buffer );
			$this->log( "WDGRESTAPI_Route_ROITax::single_create", json_encode( $_POST ) );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un ROITax spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$roitax_id = $request->get_param( 'id' );
		if ( !empty( $roitax_id ) ) {
			$roitax_item = new WDGRESTAPI_Entity_ROITax( $roitax_id );
			$loaded_data = $roitax_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $roitax_item, WDGRESTAPI_Entity_ROITax::$db_properties );
				$roitax_item->save();
				$reloaded_data = $roitax_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_ROITax::single_edit::" . $roitax_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_ROITax::single_edit::" . $roi_id, "404 : Invalid ROITax id" );
				return new WP_Error( '404', "Invalid ROITax id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_ROITax::single_edit", "404 : Invalid ROITax id (empty)" );
			return new WP_Error( '404', "Invalid ROITax id (empty)" );
		}
	}
	
}