<?php
class WDGRESTAPI_Route_Organization extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/organizations',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/organizations/stats',
			WP_REST_Server::READABLE,
			array( $this, 'list_get_stats')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/organization/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/organization/(?P<id>\d+)/investment-contracts',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_investment_contracts'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/organization/(?P<id>\d+)/rois',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_rois'),
			array( 'token' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/organization',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/organization/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/organization/(?P<id>\d+)/virtual-iban',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_virtual_iban'),
			array( 'token' => array( 'default' => 0 ) )
		);
	}
	
	public static function register() {
		$route_organization = new WDGRESTAPI_Route_Organization();
		return $route_organization;
	}
	
	/**
	 * Retourne la liste des organisations
	 * @return array
	 */
	public function list_get() {
		try {
			return WDGRESTAPI_Entity_Organization::list_get( $this->get_current_client_autorized_ids_string() );
			
		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_Organization::list_get", $e->getMessage() );
			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Retourne des statistiques sur les organisations
	 */
	public function list_get_stats() {
		return array(); // Rien pour l'instant
	}
	
	/**
	 * Retourne une organisation par son ID
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Organization
	 */
	public function single_get( WP_REST_Request $request ) {
		$organization_id = $request->get_param( 'id' );
		if ( !empty( $organization_id ) ) {
			try {
				$organization_item = new WDGRESTAPI_Entity_Organization( $organization_id );
				$loaded_data = $organization_item->get_loaded_data();
				if ( isset( $loaded_data->metadata ) ) {
					unset( $loaded_data->metadata );
				}
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					return $loaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Organization::single_get::" . $organization_id, "404 : Invalid organization ID" );
					return new WP_Error( '404', "Invalid organization ID" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Organization::single_get::" . $organization_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Organization::single_get", "404 : Invalid organization ID (empty)" );
			return new WP_Error( '404', "Invalid organization ID (empty)" );
		}
	}
	
	/**
	 * Retourne les contrats d'investissement liés à une organisation (par l'ID de l'organisation)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_investment_contracts( WP_REST_Request $request ) {
		$organization_id = $request->get_param( 'id' );
		if ( !empty( $organization_id ) ) {
			try {
				$organization_item = new WDGRESTAPI_Entity_Organization( $organization_id );
				$loaded_data = $organization_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					$rois_data = $organization_item->get_investment_contracts();
					return $rois_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Organization::single_get_investment_contracts::" . $organization_id, "404 : Invalid organization ID" );
					return new WP_Error( '404', "Invalid organization ID" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Organization::single_get_investment_contracts::" . $organization_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Organization::single_get_investment_contracts", "404 : Invalid organization ID (empty)" );
			return new WP_Error( '404', "Invalid organization ID (empty)" );
		}
	}
	
	/**
	 * Retourne les ROIs liées à une organisation (par l'ID de l'organisation)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_rois( WP_REST_Request $request ) {
		$organization_id = $request->get_param( 'id' );
		if ( !empty( $organization_id ) ) {
			try {
				$organization_item = new WDGRESTAPI_Entity_Organization( $organization_id );
				$loaded_data = $organization_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					$rois_data = $organization_item->get_rois();
					return $rois_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Organization::single_get_rois::" . $organization_id, "404 : Invalid organization ID" );
					return new WP_Error( '404', "Invalid organization ID" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Organization::single_get_rois::" . $organization_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Organization::single_get_rois", "404 : Invalid organization ID (empty)" );
			return new WP_Error( '404', "Invalid organization ID (empty)" );
		}
	}
	
	/**
	 * Retourne les transactions liées à une organisation (par l'ID de l'organisation)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_transactions( WP_REST_Request $request ) {
		$organization_id = FALSE;
		if ( !empty( $request ) ) {
			$organization_id = $request->get_param( 'id' );
		}
		if ( !empty( $organization_id ) ) {
			try {
				$organization_item = new WDGRESTAPI_Entity_Organization( $organization_id );
				$loaded_data = $organization_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					$transactions_data = $organization_item->get_transactions();
					return $transactions_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Organization::single_get_transactions::" . $organization_id, "404 : Invalid organization ID" );
					return new WP_Error( '404', "Invalid organization ID" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Organization::single_get_transactions::" . $organization_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Organization::single_get_transactions", "404 : Invalid organization ID (empty)" );
			return new WP_Error( '404', "Invalid organization ID (empty)" );
		}
	}
	
	/**
	 * Retourne un vIBAN associé à une organisation (en le créant si nécessaire)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_virtual_iban( WP_REST_Request $request ) {
		$organization_id = FALSE;
		if ( !empty( $request ) ) {
			$organization_id = $request->get_param( 'id' );
		}
		
		if ( !empty( $organization_id ) ) {
			try {
				$organization_item = new WDGRESTAPI_Entity_Organization( $organization_id );
				$loaded_data = $organization_item->get_loaded_data();
				
				if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
					$viban_data = $organization_item->get_viban();
					return $viban_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_Organization::single_get_virtual_iban::" . $organization_id, "404 : Invalid organization ID" );
					return new WP_Error( '404', "Invalid organization ID" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_Organization::single_get_virtual_iban::" . $organization_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Organization::single_get_virtual_iban", "404 : Invalid organization ID (empty)" );
			return new WP_Error( '404', "Invalid organization ID (empty)" );
		}
	}
	
	/**
	 * Crée une organisation
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$organization_item = new WDGRESTAPI_Entity_Organization();
		$this->set_posted_properties( $organization_item, WDGRESTAPI_Entity_Organization::$db_properties );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$organization_item->set_property( 'client_user_id', $current_client->ID );
		$organization_item->save();
		$reloaded_data = $organization_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_Organization::single_create", json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
	/**
	 * Edite une organisation spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$organization_id = $request->get_param( 'id' );
		if ( !empty( $organization_id ) ) {
			$organization_item = new WDGRESTAPI_Entity_Organization( $organization_id );
			$loaded_data = $organization_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $organization_item, WDGRESTAPI_Entity_Organization::$db_properties );
				$organization_item->save();
				$reloaded_data = $organization_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_Organization::single_edit::" . $organization_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Organization::single_edit::" . $organization_id, "404 : Invalid organization ID" );
				return new WP_Error( '404', "Invalid organization ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Organization::single_edit", "404 : Invalid organization ID (empty)" );
			return new WP_Error( '404', "Invalid organization ID (empty)" );
		}
	}
	
}