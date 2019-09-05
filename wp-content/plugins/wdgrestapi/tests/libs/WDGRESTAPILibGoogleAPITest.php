<?php
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