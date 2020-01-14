<?php
include_once( plugin_dir_path( __FILE__ ) . '../libs/quickbooks/src/config.php');
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;

class WDGRESTAPI_Entity_Bill extends WDGRESTAPI_Entity {
	public static $entity_type = 'bill';
	
	public static $tool_quickbooks = 'quickbooks';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Bill::$entity_type, WDGRESTAPI_Entity_Bill::$db_properties );
	}
	
	public static function list_get() {
		$quickbooks_service = WDGRESTAPI_Entity_Bill::get_quickbooks_service();
		if ( is_wp_error( $quickbooks_service ) ) {
			//$this->properties_errors = $quickbooks_service->get_error_message();
			
		} elseif ( !empty( $quickbooks_service ) ) {
			return $quickbooks_service->FindAll( 'Invoice', 1, 5 );
		}
		return FALSE;
	}
	
	/**
	 * 
	 */
	public function save() {
		if ( empty( $this->loaded_data->id ) ) {
			date_default_timezone_set( 'Europe/Paris' );
			$current_date = new DateTime();
			$this->set_property( 'date', $current_date->format( 'Y-m-d H:i:s' ) );
		}
		
		if ( $this->loaded_data->tool == WDGRESTAPI_Entity_Bill::$tool_quickbooks ) {
			$quickbook_invoice_id = $this->save_on_quickbooks( json_decode( $this->loaded_data->options ) );
			if ( !empty( $quickbook_invoice_id ) ) {
				parent::save();
				$this->set_property( 'tool_id', $quickbook_invoice_id );
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	private function save_on_quickbooks( $options ) {
		$quickbooks_service = WDGRESTAPI_Entity_Bill::get_quickbooks_service();

		if ( is_wp_error( $quickbooks_service ) ) {
			$this->properties_errors = $quickbooks_service->get_error_message();
			
		} elseif ( !empty( $quickbooks_service ) ) {
			//Add a new Invoice
			$id_class = isset( $options->classid ) ? $options->classid : '';
			$id_location = isset( $options->locationid ) ? $options->locationid : '';

			$bill_object = Invoice::create( [
				"Line"	=> [
					[
						"Amount"		=> $options->itemvalue,
						"Description"	=> $options->itemdescription,
						"DetailType"	=> "SalesItemLineDetail",
						"SalesItemLineDetail" => [
							"TaxCodeRef"	=> $options->itemtaxid,
							"ItemRef"		=> $options->itemtitle,
							"ClassRef"		=> $id_class
						]
					]
				],
				"CustomerRef"	=> [
					"value"	=> $options->customerid
				],
				"DepartmentRef"	=> [
					"value"	=> $id_location
				],
				"CustomerMemo"	=> [
					"value"	=> $options->billdescription
				],
				"BillEmail"	=> [
					"Address"	=> $options->customeremail
				],
				"BillEmailBcc"	=> [
					"Address"	=> "admin@wedogood.co"
				]
			] );
			$resultingObj = $quickbooks_service->Add( $bill_object );

			$error = $quickbooks_service->getLastError();
			if ($error != null) {
				$this->properties_errors = $error->getResponseBody();
				return FALSE;
			}

			return $resultingObj->Id;
			
		}
		return FALSE;
	}
	
	/**
	 * @return DataService
	 */
	private static function get_quickbooks_service() {
		try {
			$token_keys = self::get_token_keys();
			if ( is_wp_error( $token_keys ) ) {
				return $token_keys;
			}

			$quickbooks_service = DataService::Configure(array(
				'auth_mode'			=> 'oauth2',
				'ClientID'			=> WDG_QUICKBOOKS_CLIENT_ID,
				'ClientSecret'		=> WDG_QUICKBOOKS_CLIENT_SECRET,
				'accessTokenKey'	=> $token_keys->getAccessToken(),
				'refreshTokenKey'	=> $token_keys->getRefreshToken(),
				'QBORealmID'		=> WDG_QUICKBOOKS_REALM_ID,
				'baseUrl'			=> WDG_QUICKBOOKS_BASE_URL
			));
			$OAuth2LoginHelper = $quickbooks_service->getOAuth2LoginHelper();

			$accessToken = $OAuth2LoginHelper->refreshToken();
			$error = $OAuth2LoginHelper->getLastError();
			if ($error != null) {
				return new WP_Error( 'error', $error->getHttpStatusCode() . ' - ' . $error->getOAuthHelperError() . ' - ' .$error->getResponseBody() );
			}
			$quickbooks_service->updateOAuth2Token( $accessToken );
			
		} catch ( Exception $e ) {
			return new WP_Error( 'error', $e->getMessage() );
		}
		
		return $quickbooks_service;
	}

	private static function get_token_keys() {
		try {
			$previous_refresh_token = get_option( 'quickbooks_refresh_token' );
			if ( empty( $previous_refresh_token ) ) {
				$previous_refresh_token = WDG_QUICKBOOKS_REFRESH_TOKEN_KEY;
			}
			$oauth2LoginHelper = new OAuth2LoginHelper( WDG_QUICKBOOKS_CLIENT_ID, WDG_QUICKBOOKS_CLIENT_SECRET );
			$accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken( $previous_refresh_token );
			$refreshTokenValue = $accessTokenObj->getRefreshToken();
			if ( !empty( $refreshTokenValue ) ) {
				update_option( 'quickbooks_refresh_token', $refreshTokenValue );
			}
			
		} catch ( Exception $e ) {
			return new WP_Error( 'error', $e->getMessage() );
		}
		
		return $accessTokenObj;
	}


/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => '' ),
		'date'					=> array( 'type' => 'datetime', 'other' => '' ),
		'tool'					=> array( 'type' => 'varchar', 'other' => '' ),
		'tool_id'				=> array( 'type' => 'id', 'other' => '' ),
		'object'				=> array( 'type' => 'varchar', 'other' => '' ),
		'object_id'				=> array( 'type' => 'id', 'other' => '' ),
		'options'				=> array( 'type' => 'longtext', 'other' => '' ),
		'result'				=> array( 'type' => 'longtext', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_Bill::$entity_type, WDGRESTAPI_Entity_Bill::$db_properties );
	}
	
}