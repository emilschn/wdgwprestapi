<?php
/*
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/load.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/default-constants.php';
wp_initial_constants();

require_once dirname( __FILE__ ) . '/../../../../../wp-load.php';

require_once dirname( __FILE__ ) . '/../../../../../wp-includes/functions.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/plugin.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/cache.php';
wp_start_object_cache();

require_once dirname( __FILE__ ) . '/../../../../../wp-includes/option.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/http.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/class-wp-error.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/class-wp-hook.php';
*/
require_once dirname( __FILE__ ) . '/../../libs/google-api.php';

class WDGRESTAPILibGoogleAPITest extends PHPUnit_Framework_TestCase {

	public function testinitClient() {
		$this->assertFalse( WDGRESTAPI_Lib_GoogleAPI::init_client() );
	}

	public function testsetValues() {
		$this->assertFalse( WDGRESTAPI_Lib_GoogleAPI::set_values( 'USER', 2, array() ) );
	}

	public function testsetProjectValues() {
		$this->assertFalse( WDGRESTAPI_Lib_GoogleAPI::set_project_values( 1, array() ) );
	}

	public function testsetUserValues() {
		$this->assertFalse( WDGRESTAPI_Lib_GoogleAPI::set_user_values( 1, array() ) );
	}

	public function testsetOrganizationValues() {
		$this->assertFalse( WDGRESTAPI_Lib_GoogleAPI::set_organization_values( 1, array() ) );
	}

	public function testsetContractValues() {
		$this->assertFalse( WDGRESTAPI_Lib_GoogleAPI::set_investment_contract_values( 1, array() ) );
	}

	public function testsetDeclarationValues() {
		$this->assertFalse( WDGRESTAPI_Lib_GoogleAPI::set_declaration_values( 1, array() ) );
	}

	public function testsetPollValues() {
		$this->assertFalse( WDGRESTAPI_Lib_GoogleAPI::set_poll_values( 1, array() ) );
	}

}