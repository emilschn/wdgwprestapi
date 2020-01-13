<?php
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/load.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/default-constants.php';
wp_initial_constants();

require_once dirname( __FILE__ ) . '/../../../../../wp-load.php';

require_once dirname( __FILE__ ) . '/../../../../../wp-includes/functions.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/plugin.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/cache.php';
wp_start_object_cache();
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/class-wp-error.php';

require_once dirname( __FILE__ ) . '/../../../../../wp-includes/rest-api.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/rest-api/class-wp-rest-request.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/rest-api/class-wp-rest-server.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/rest-api/endpoints/class-wp-rest-controller.php';

require_once dirname( __FILE__ ) . '/../../../restapi-user-basic-access/classes/authentication.php';

require_once dirname( __FILE__ ) . '/../../entities/entity.php';
require_once dirname( __FILE__ ) . '/../../entities/user.php';
require_once dirname( __FILE__ ) . '/../../entities/log.php';
require_once dirname( __FILE__ ) . '/../../entities/cache.php';
require_once dirname( __FILE__ ) . '/../../routes/route.php';
require_once dirname( __FILE__ ) . '/../../routes/user.php';
require_once dirname( __FILE__ ) . '/../../libs/logs.php';

use PHPUnit\Framework\TestCase;
class WDGRESTAPIRoutesUserTest extends TestCase {

	public function testregister() {
		$test_register = WDGRESTAPI_Route_User::register();
		$this->assertInstanceOf( WDGRESTAPI_Route_User, $test_register );
	}

	public function testlistGet() {
		$test_route_user = new WDGRESTAPI_Route_User();
		$result_list_get = $test_route_user->list_get();
		$this->assertFalse( $result_list_get );
	}

	public function testlistGetStats() {
		$test_route_user = new WDGRESTAPI_Route_User();
		$result_list_get_stats = $test_route_user->list_get_stats();
		$this->assertFalse( $result_list_get_stats );
	}

	public function testsingleGet() {
		$test_route_user = new WDGRESTAPI_Route_User();
		$result_single_get = $test_route_user->single_get( FALSE );
		$this->assertTrue( is_wp_error( $result_single_get ) );
	}

	public function testsingleGetRoyalties() {
		$test_route_user = new WDGRESTAPI_Route_User();
		$result_single_get_royalties = $test_route_user->single_get_royalties( FALSE );
		$this->assertTrue( is_wp_error( $result_single_get_royalties ) );
	}

	public function testsingleCreate() {
		$test_route_user = new WDGRESTAPI_Route_User();
		$result_single_create = $test_route_user->single_create( FALSE );
		$this->assertFalse( $result_single_create->wpref );
		$this->assertEquals( '0000-00-00', $result_single_create->birthday_date );
	}

	public function testsingleEdit() {
		$test_route_user = new WDGRESTAPI_Route_User();
		$result_single_edit = $test_route_user->single_edit( FALSE );
		$this->assertTrue( is_wp_error( $result_single_edit ) );
	}

	public function testsingleEditEmail() {
		$test_route_user = new WDGRESTAPI_Route_User();
		$result_single_edit_email = $test_route_user->single_edit_email( FALSE );
		$this->assertTrue( is_wp_error( $result_single_edit_email ) );
	}

	public function testsingleGetInvestmentContracts() {
		$test_route_user = new WDGRESTAPI_Route_User();
		$result_single_get_investment_contracts = $test_route_user->single_get_investment_contracts( FALSE );
		$this->assertTrue( is_wp_error( $result_single_get_investment_contracts ) );
	}

	public function testsingleGetROIS() {
		$test_route_user = new WDGRESTAPI_Route_User();
		$result_single_get_rois = $test_route_user->single_get_rois( FALSE );
		$this->assertTrue( is_wp_error( $result_single_get_rois ) );
	}

	public function testsingleGetActivities() {
		$test_route_user = new WDGRESTAPI_Route_User();
		$result_single_get_activities = $test_route_user->single_get_activities( FALSE );
		$this->assertTrue( is_wp_error( $result_single_get_activities ) );
	}

}