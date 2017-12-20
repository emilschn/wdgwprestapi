<?php
include_once( plugin_dir_path( __FILE__ ) . '../libs/quickbooks/src/config.php');
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;

class WDGRESTAPI_Entity_Bill extends WDGRESTAPI_Entity {
	public static $entity_type = 'bill';
	
	public static $tool_quickbooks = 'quickbooks';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Bill::$entity_type, WDGRESTAPI_Entity_Bill::$db_properties );
	}
	
	public static function list_get() {
		$quickbooks_service = WDGRESTAPI_Entity_Bill::get_quickbooks_service();
		if ( !is_wp_error( $quickbooks_service ) ) {
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
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$this->set_property( 'caller', $current_client->ID );
		}
		
		if ( $this->loaded_data->tool == WDGRESTAPI_Entity_Bill::$tool_quickbooks ) {
			$quickbook_invoice_id = $this->save_on_quickbooks( json_decode( $this->loaded_data->options ) );
			if ( !empty( $quickbook_invoice_id ) ) {
				parent::save();
				$this->set_property( 'object_id', $quickbook_invoice_id );
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	private function save_on_quickbooks( $options ) {
		$quickbooks_service = WDGRESTAPI_Entity_Bill::get_quickbooks_service();

		//Add a new Invoice
		$bill_object = Invoice::create( [
			"Line"	=> [
				[
					"Amount"		=> $options->itemvalue,
					"Description"	=> $options->itemdescription,
					"DetailType"	=> "SalesItemLineDetail",
					"SalesItemLineDetail" => [
						"TaxCodeRef"	=>	$options->itemtaxid,
						"ItemRef"		=>	$options->itemtitle
					]
				]
			],
			"CustomerRef"	=> [
				"value"	=> $options->customerid
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
	
	/**
	 * @return DataService
	 */
	private static function get_quickbooks_service() {
		try {
			$quickbooks_service = DataService::Configure(array(
				'auth_mode'			=> 'oauth2',
				'ClientID'			=> WDG_QUICKBOOKS_CLIENT_ID,
				'ClientSecret'		=> WDG_QUICKBOOKS_CLIENT_SECRET,
				'accessTokenKey'	=> WDG_QUICKBOOKS_ACCESS_TOKEN_KEY,
				'refreshTokenKey'	=> WDG_QUICKBOOKS_REFRESH_TOKEN_KEY,
				'QBORealmID'		=> WDG_QUICKBOOKS_REALM_ID,
				'baseUrl'			=> WDG_QUICKBOOKS_BASE_URL
			));
			$OAuth2LoginHelper = $quickbooks_service->getOAuth2LoginHelper();

			$accessToken = $OAuth2LoginHelper->refreshToken();
			$error = $OAuth2LoginHelper->getLastError();
			if ($error != null) {
				echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
				echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
				echo "The Response message is: " . $error->getResponseBody() . "\n";
				return;
			}
			$quickbooks_service->updateOAuth2Token( $accessToken );
			
		} catch ( Exception $e ) {
			return new WP_Error( 'error', $e->getMessage() );
		}
		
		return $quickbooks_service;
	}


/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'date'					=> array( 'type' => 'datetime', 'other' => '' ),
		'caller'				=> array( 'type' => 'id', 'other' => '' ),
		'tool'					=> array( 'type' => 'varchar', 'other' => '' ),
		'object'				=> array( 'type' => 'varchar', 'other' => '' ),
		'object_id'				=> array( 'type' => 'id', 'other' => '' ),
		'options'				=> array( 'type' => 'longtext', 'other' => '' ),
		'result'				=> array( 'type' => 'longtext', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_Bill::$entity_type, WDGRESTAPI_Entity_Bill::$db_properties );
	}
	
}