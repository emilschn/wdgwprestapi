<?php
class WDGRESTAPI_Route_Adjustment extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/adjustment/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/adjustment',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/adjustment/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/adjustment/(?P<id>\d+)/files',
			WP_REST_Server::READABLE,
			array( $this, 'get_filelist_by_adjustment_id'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/adjustment/(?P<id>\d+)/files',
			WP_REST_Server::CREATABLE,
			array( $this, 'link_file'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/adjustment/(?P<adjustmentid>\d+)/file/(?P<fileid>\d+))',
			WP_REST_Server::DELETABLE,
			array( $this, 'unlink_file'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/adjustment/(?P<id>\d+)/declarations',
			WP_REST_Server::READABLE,
			array( $this, 'get_declarationlist_by_adjustment_id'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/adjustment/(?P<id>\d+)/declarations',
			WP_REST_Server::CREATABLE,
			array( $this, 'link_declaration'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/adjustment/(?P<adjustmentid>\d+)/declaration/(?P<fileid>\d+))',
			WP_REST_Server::DELETABLE,
			array( $this, 'unlink_declaration'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE )
		);
	}
	
	public static function register() {
		$route_adjustment = new WDGRESTAPI_Route_Adjustment();
		return $route_adjustment;
	}
	
	/**
	 * Retourne un ajustement grâce à son id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Adjustment
	 */
	public function single_get( WP_REST_Request $request ) {
		$adjustment_id = $request->get_param( 'id' );
		if ( !empty( $adjustment_id ) ) {
			$adjustment_item = new WDGRESTAPI_Entity_Adjustment( $adjustment_id );
			$loaded_data_temp = $adjustment_item->get_loaded_data();
			
			if ( !empty( $loaded_data_temp ) && $this->is_data_for_current_client( $loaded_data_temp ) ) {
				$this->log( "WDGRESTAPI_Route_Adjustment::single_get::" . $adjustment_id, json_encode( $loaded_data_temp ) );
				return $loaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Adjustment::single_get::" . $adjustment_id, "404 : Invalid adjustment id" );
				return new WP_Error( '404', "Invalid adjustment id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Adjustment::single_get", "404 : Invalid adjustment id (empty)" );
			return new WP_Error( '404', "Invalid adjustment id (empty)" );
		}
	}
	
	/**
	 * Crée un ajustement
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$this->log( "WDGRESTAPI_Route_Adjustment::single_create", json_encode( $_POST ) );
		$adjustment_item = new WDGRESTAPI_Entity_Adjustment();
		$this->set_posted_properties( $adjustment_item, WDGRESTAPI_Entity_Adjustment::$db_properties );
		if ( $adjustment_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$adjustment_item->set_property( 'client_user_id', $current_client->ID );
			$save_result = $adjustment_item->save();
			$reloaded_data = $adjustment_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_Adjustment::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_Adjustment::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_Adjustment::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $adjustment_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_Adjustment::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_Adjustment::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un ajustement spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$adjustment_id = $request->get_param( 'id' );
		if ( !empty( $adjustment_id ) ) {
			$adjustment_item = new WDGRESTAPI_Entity_Adjustment( $adjustment_id );
			$loaded_data = $adjustment_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $adjustment_item, WDGRESTAPI_Entity_Adjustment::$db_properties );
				$adjustment_item->save();
				$reloaded_data = $adjustment_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_Adjustment::single_edit::" . $adjustment_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Adjustment::single_edit::" . $adjustment_id, "404 : Invalid adjustment id" );
				return new WP_Error( '404', "Invalid adjustment id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Adjustment::single_edit", "404 : Invalid adjustment id (empty)" );
			return new WP_Error( '404', "Invalid adjustment id (empty)" );
		}
	}
	
	public function get_filelist_by_adjustment_id( WP_REST_Request $request ) {
		$adjustment_id = $request->get_param( 'id' );
		if ( !empty( $adjustment_id ) ) {
			$file_list = WDGRESTAPI_Entity_AdjustmentFile::get_list_by_adjustment_id( $adjustment_id );
			$this->log( "WDGRESTAPI_Route_Adjustment::get_filelist_by_adjustment_id::" . $adjustment_id, json_encode( $file_list ) );
			return $file_list;
			
		} else {
			$this->log( "WDGRESTAPI_Route_Adjustment::get_filelist_by_adjustment_id", "404 : Invalid adjustment ID (empty)" );
			return new WP_Error( '404', "Invalid adjustment ID (empty)" );
		}
	}
	
	public function link_file( WP_REST_Request $request ) {
		$adjustment_id = $request->get_param( 'id' );
		$adjustmentfile_item = new WDGRESTAPI_Entity_AdjustmentFile();
		$this->set_posted_properties( $adjustmentfile_item, WDGRESTAPI_Entity_AdjustmentFile::$db_properties );
		$adjustmentfile_item->set_property( 'id_adjustment', $adjustment_id );
		$adjustmentfile_item->save();
		$reloaded_data = $adjustmentfile_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_Adjustment::link_file::" . $adjustment_id, json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
	public function unlink_file( WP_REST_Request $request ) {
		$adjustment_id = $request->get_param( 'adjustmentid' );
		$file_id = $request->get_param( 'fileid' );
		$type = $request->get_param( 'type' );
		WDGRESTAPI_Entity_AdjustmentFile::remove( $adjustment_id, $file_id, $type );
		$this->log( "WDGRESTAPI_Route_Adjustment::unlink_file::".$adjustment_id."::".$file_id."::".$type, 'TRUE' );
		return TRUE;
	}
	
	public function get_declarationlist_by_adjustment_id( WP_REST_Request $request ) {
		$adjustment_id = $request->get_param( 'id' );
		if ( !empty( $adjustment_id ) ) {
			$declaration_list = WDGRESTAPI_Entity_AdjustmentDeclaration::get_list_by_adjustment_id( $adjustment_id );
			$this->log( "WDGRESTAPI_Route_Adjustment::get_declarationlist_by_adjustment_id::" . $adjustment_id, json_encode( $declaration_list ) );
			return $declaration_list;
			
		} else {
			$this->log( "WDGRESTAPI_Route_Adjustment::get_declarationlist_by_adjustment_id", "404 : Invalid adjustment ID (empty)" );
			return new WP_Error( '404', "Invalid adjustment ID (empty)" );
		}
	}
	
	public function link_declaration( WP_REST_Request $request ) {
		$adjustment_id = $request->get_param( 'id' );
		$adjustmentdeclaration_item = new WDGRESTAPI_Entity_AdjustmentDeclaration();
		$this->set_posted_properties( $adjustmentdeclaration_item, WDGRESTAPI_Entity_AdjustmentDeclaration::$db_properties );
		$adjustmentdeclaration_item->set_property( 'id_adjustment', $adjustment_id );
		$adjustmentdeclaration_item->save();
		$reloaded_data = $adjustmentdeclaration_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_Adjustment::link_declaration::" . $adjustment_id, json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
	public function unlink_declaration( WP_REST_Request $request ) {
		$adjustment_id = $request->get_param( 'adjustmentid' );
		$declaration_id = $request->get_param( 'declarationid' );
		$type = $request->get_param( 'type' );
		WDGRESTAPI_Entity_AdjustmentDeclaration::remove( $adjustment_id, $declaration_id, $type );
		$this->log( "WDGRESTAPI_Route_Adjustment::unlink_declaration::".$adjustment_id."::".$declaration_id."::".$type, 'TRUE' );
		return TRUE;
	}
	
}