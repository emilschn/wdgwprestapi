<?php
/**
 * Plugin Name: WDG REST API
 * Description: Gère les fonctions particulières de l'API REST WE DO GOOD
 * Version: 0.0.1
 * Author: Emilien Schneider
 * Author URI: http://www.wedogood.co
*/


/**
 * How-to:
 * 	$url = 'http://WP-URL/wp-json/';
	$route = 'wp/v2/posts/1'; //Route classique wordpress
	$route2 = 'wdg/v1/staticpages'; //Route vers méthodes spécifiques wdg
	$id = ID;
	$pwd = PWD;
	$headers = array( "Authorization" => "Basic " . base64_encode( $id.':'.$pwd ) );
	$result = wp_remote_get( $url . $route, array( 'headers' => $headers ) );
 */

// Vérifie si c'est un appel direct
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDGRESTAPI {
	private $version = '0.0.8223';

    
	/**
	 * Instanciation du singleton
	 */
	protected static $_instance = null;
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function __construct() {
		$this->add_include_libs();
		$this->add_include_entities();
		$this->upgrade_db();
		$this->add_include_routes();
		$this->register_routes();
		
		
		$this->add_include_admin( 'posts' );
		WDGRESTAPI_Admin_Posts::add_actions();
		$this->add_include_admin( 'users' );
		WDGRESTAPI_Admin_Users::add_actions();
	}
	
	
	// Gestion des entités
	public function add_include_libs() {
		$this->add_include_lib( 'logs' );
		$this->add_include_lib( 'geolocation' );
		$this->add_include_lib( 'validator' );
		$this->add_include_lib( 'google-api' );
	}
	public function add_include_lib( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'libs/' . $include_name . '.php');
	}
	
	
	// Gestion des entités
	public function add_include_entities() {
		$this->add_include_entity( 'entity' );
		$this->add_include_entity( 'staticpage' );
		$this->add_include_entity( 'log' );
		$this->add_include_entity( 'cache' );
		$this->add_include_entity( 'email' );
		$this->add_include_entity( 'bill' );
		$this->add_include_entity( 'organization' );
		$this->add_include_entity( 'user' );
		$this->add_include_entity( 'project' );
		$this->add_include_entity( 'project-draft' );
		$this->add_include_entity( 'investment' );
		$this->add_include_entity( 'investment-draft' );
		$this->add_include_entity( 'investment-contract' );
		$this->add_include_entity( 'investment-contract-history' );
		$this->add_include_entity( 'bankinfo' );
		$this->add_include_entity( 'declaration' );
		$this->add_include_entity( 'adjustment' );
		$this->add_include_entity( 'roi' );
		$this->add_include_entity( 'roi-tax' );
		$this->add_include_entity( 'file' );
		$this->add_include_entity( 'poll-answer' );
		$this->add_include_entity( 'contract-model' );
		$this->add_include_entity( 'contract' );
		$this->add_include_entity( 'queued-action' );
		$this->add_include_entity( 'transaction' );
		
		$this->add_include_entity( 'organization-user' );
		$this->add_include_entity( 'project-user' );
		$this->add_include_entity( 'project-organization' );
		$this->add_include_entity( 'adjustment-file' );
		$this->add_include_entity( 'adjustment-declaration' );
	}
	public function add_include_entity( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'entities/' . $include_name . '.php');
	}
	
	
	// Gestion des routes
	public function add_include_routes() {
		$this->add_include_route( 'route' );
		$this->add_include_route( 'staticpage' );
		$this->add_include_route( 'email' );
		$this->add_include_route( 'bill' );
		$this->add_include_route( 'organization' );
		$this->add_include_route( 'user' );
		$this->add_include_route( 'project' );
		$this->add_include_route( 'project-draft' );
		$this->add_include_route( 'investment' );
		$this->add_include_route( 'investment-draft' );
		$this->add_include_route( 'investment-contract' );
		$this->add_include_route( 'investment-contract-history' );
		$this->add_include_route( 'bankinfo' );
		$this->add_include_route( 'declaration' );
		$this->add_include_route( 'adjustment' );
		$this->add_include_route( 'roi' );
		$this->add_include_route( 'roi-tax' );
		$this->add_include_route( 'file' );
		$this->add_include_route( 'poll-answer' );
		$this->add_include_route( 'contract-model' );
		$this->add_include_route( 'contract' );
		$this->add_include_route( 'mail-template' );
		$this->add_include_route( 'organization-user' );
		$this->add_include_route( 'project-user' );
		$this->add_include_route( 'project-organization' );
		$this->add_include_route( 'queued-action' );
	}
	public function add_include_route( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'routes/' . $include_name . '.php');
	}
	public function register_routes() {
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_StaticPage::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_Organization::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_User::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_Project::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_Project_Draft::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_Investment::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_InvestmentDraft::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_InvestmentContract::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_InvestmentContractHistory::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_BankInfo::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_Declaration::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_Adjustment::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_ROI::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_ROITax::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_MailTemplate::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_Email::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_Bill::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_File::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_ContractModel::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_Contract::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_PollAnswer::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_QueuedAction::register');
		
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_OrganizationUser::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_ProjectUser::register');
		add_action( 'rest_api_init', 'WDGRESTAPI_Route_ProjectOrganization::register');
	}
	
	
	// Gestion de l'affichage en admin
	public function add_include_admin( $include_name ) {
		include_once( plugin_dir_path( __FILE__ ) . 'admin/' . $include_name . '.php');
	}
	
	
	// Mise à jour éventuelle de la bdd
	public function upgrade_db() {
		if (get_option('wdgwpapi_version') != $this->version) {
			WDGRESTAPI_Entity_Cache::upgrade_db();
			WDGRESTAPI_Entity_Log::upgrade_db();
			WDGRESTAPI_Entity_Bill::upgrade_db();
			WDGRESTAPI_Entity_Email::upgrade_db();
			WDGRESTAPI_Entity_Organization::upgrade_db();
			WDGRESTAPI_Entity_User::upgrade_db();
			WDGRESTAPI_Entity_Project::upgrade_db();
			WDGRESTAPI_Entity_Project_Draft::upgrade_db();
			WDGRESTAPI_Entity_Investment::upgrade_db();
			WDGRESTAPI_Entity_InvestmentDraft::upgrade_db();
			WDGRESTAPI_Entity_InvestmentContract::upgrade_db();
			WDGRESTAPI_Entity_InvestmentContractHistory::upgrade_db();
			WDGRESTAPI_Entity_BankInfo::upgrade_db();
			WDGRESTAPI_Entity_Declaration::upgrade_db();
			WDGRESTAPI_Entity_Adjustment::upgrade_db();
			WDGRESTAPI_Entity_ROI::upgrade_db();
			WDGRESTAPI_Entity_ROITax::upgrade_db();
			WDGRESTAPI_Entity_File::upgrade_db();
			WDGRESTAPI_Entity_PollAnswer::upgrade_db();
			WDGRESTAPI_Entity_ContractModel::upgrade_db();
			WDGRESTAPI_Entity_Contract::upgrade_db();
			WDGRESTAPI_Entity_QueuedAction::upgrade_db();
			WDGRESTAPI_Entity_Transaction::upgrade_db();
			
			WDGRESTAPI_Entity_OrganizationUser::upgrade_db();
			WDGRESTAPI_Entity_ProjectUser::upgrade_db();
			WDGRESTAPI_Entity_ProjectOrganization::upgrade_db();
			WDGRESTAPI_Entity_AdjustmentFile::upgrade_db();
			WDGRESTAPI_Entity_AdjustmentDeclaration::upgrade_db();
			
			update_option('wdgwpapi_version', $this->version);
		}
	}
}

global $wdgrestapi;
$wdgrestapi = WDGRESTAPI::instance();