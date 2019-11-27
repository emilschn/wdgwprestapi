<?php
class WDGRESTAPI_Route_File extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/files',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/file/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/file',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/file/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_file = new WDGRESTAPI_Route_File();
		return $route_file;
	}
	
	/**
	 * Retourne la liste des fichiers
	 * @return array
	 */
	public function list_get() {
		return array(); // TODO
	}
	
	/**
	 * Retourne un fichier grâce à son id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_File
	 */
	public function single_get( WP_REST_Request $request ) {
		$file_id = $request->get_param( 'id' );
		if ( !empty( $file_id ) ) {
			$file_item = new WDGRESTAPI_Entity_File( $file_id );
			$loaded_data_temp = $file_item->get_loaded_data();
			
			if ( !empty( $loaded_data_temp ) && $this->is_data_for_current_client( $loaded_data_temp ) ) {
				$this->log( "WDGRESTAPI_Route_File::single_get::" . $file_id, json_encode( $loaded_data_temp ) );
				return $loaded_data_temp;
				
			} else {
				$this->log( "WDGRESTAPI_Route_File::single_get::" . $file_id, "404 : Invalid file id" );
				return new WP_Error( '404', "Invalid file id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_File::single_get", "404 : Invalid file id (empty)" );
			return new WP_Error( '404', "Invalid file id (empty)" );
		}
	}
	
	/**
	 * Crée un fichier
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$file_item = new WDGRESTAPI_Entity_File();
		$this->set_posted_properties( $file_item, WDGRESTAPI_Entity_File::$db_properties );
		if ( $file_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$file_item->set_property( 'client_user_id', $current_client->ID );
			$file_data = filter_input( INPUT_POST, 'data' );
			$file_item->set_file_data( $file_data );
			$save_result = $file_item->save();
			$reloaded_data = $file_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_File::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_File::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_File::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $file_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_File::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_File::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un fichier spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$file_id = $request->get_param( 'id' );
		if ( !empty( $file_id ) ) {
			$file_item = new WDGRESTAPI_Entity_File( $file_id );
			$loaded_data = $file_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $file_item, WDGRESTAPI_Entity_File::$db_properties );
				$file_data = filter_input( INPUT_POST, 'data' );
				$result = $file_item->save( $file_data );
				if ( $result ) {
					$reloaded_data = $file_item->get_loaded_data();
					$this->log( "WDGRESTAPI_Route_File::single_edit::" . $file_id, json_encode( $reloaded_data ) );
					return $reloaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_File::single_edit::" . $file_id, 'Error invalid data' );
					return new WP_Error( 'cant-edit', "Invalid data" );
				}
				
			} else {
				$this->log( "WDGRESTAPI_Route_File::single_edit::" . $file_id, "404 : Invalid file id" );
				return new WP_Error( '404', "Invalid file id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_File::single_edit", "404 : Invalid file id (empty)" );
			return new WP_Error( '404', "Invalid file id (empty)" );
		}
	}
	
}