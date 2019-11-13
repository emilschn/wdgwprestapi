<?php
require_once dirname( __FILE__ ) . '/../../../restapi-user-basic-access/classes/authentication.php';
require_once dirname( __FILE__ ) . '/../../libs/google-api.php';
require_once dirname( __FILE__ ) . '/../../libs/logs.php';
require_once dirname( __FILE__ ) . '/../../entities/entity.php';
require_once dirname( __FILE__ ) . '/../../entities/user.php';
require_once dirname( __FILE__ ) . '/../../entities/investment-contract.php';
require_once dirname( __FILE__ ) . '/../../entities/roi.php';
require_once dirname( __FILE__ ) . '/../../entities/cache.php';
require_once dirname( __FILE__ ) . '/../../entities/log.php';

class WDGRESTAPIEntitiesUserTest extends PHPUnit_Framework_TestCase {

	public function testSave() {
		$user_entity_test = new WDGRESTAPI_Entity_User();
		$result_save = $user_entity_test->save();
		$this->assertFalse( $result_save );
	}

	public function testgetLoadedData() {
		$user_entity_test = new WDGRESTAPI_Entity_User();
		$user_entity_loaded_data = $user_entity_test->get_loaded_data();
		$this->assertFalse( $user_entity_loaded_data->wpref );
		$this->assertEquals( '0000-00-00', $user_entity_loaded_data->birthday_date );
	}

	public function teststandardizeData() {
		$user_entity_test = new WDGRESTAPI_Entity_User();
		// standardize_data est appelé dans get_loaded_data
		$user_entity_loaded_data = $user_entity_test->get_loaded_data();
		$this->assertEquals( '0000-00-00', $user_entity_loaded_data->birthday_date );
	}

	public function testgetTnvestmentContracts() {
		$user_entity_test = new WDGRESTAPI_Entity_User();
		$list_investment_contracts = $user_entity_test->get_investment_contracts();
		$this->assertEmpty( $list_investment_contracts );
	}

	public function testgetROIS() {
		$user_entity_test = new WDGRESTAPI_Entity_User();
		$list_rois = $user_entity_test->get_rois();
		$this->assertEmpty( $list_rois );
	}

	public function testgetActivities() {
		$user_entity_test = new WDGRESTAPI_Entity_User();
		$list_activities = $user_entity_test->get_activities();
		$this->assertArrayHasKey( 'projects', $list_activities );
		$this->assertCount( 2, $list_activities[ 'projects' ] );
		$this->assertArrayHasKey( 'id', $list_activities[ 'projects' ][ 0 ] );
		$this->assertEquals( 1, $list_activities[ 'projects' ][ 0 ][ 'id' ] );
	}

	public function testgetRoyaltiesData() {
		$list_royalties = WDGRESTAPI_Entity_User::get_royalties_data( 'a@a.a' );
		$this->assertFalse( $list_royalties );
	}

	public function testupdateEmail() {
		$posted_array = array();
		$posted_array[ 'new_email' ] = 'a@a.a';
		$result_update_email = WDGRESTAPI_Entity_User::update_email( 'a@a.a', $posted_array );
		$this->assertArrayHasKey( 'error', $result_update_email );
		$this->assertEquals( 'Invalid new email', $result_update_email[ 'error-message' ] );

		$posted_array[ 'new_email' ] = 'b@b.b';
		$result_update_email = WDGRESTAPI_Entity_User::update_email( 'a@a.a', $posted_array );
		$this->assertArrayHasKey( 'error', $result_update_email );
		$this->assertEquals( '404', $result_update_email[ 'error' ] );
	}

	public function testlistGet() {
		$list_user = WDGRESTAPI_Entity_User::list_get( FALSE );
		$this->assertFalse( $list_user );
	}

	public function testgetStats() {
		$list_stats = WDGRESTAPI_Entity_User::get_stats( FALSE );
		$this->assertFalse( $list_stats );
	}

	public function testupgradeDB() {
		$upgrade_result = WDGRESTAPI_Entity_User::upgrade_db( FALSE );
		$this->assertFalse( $upgrade_result );
	}

}