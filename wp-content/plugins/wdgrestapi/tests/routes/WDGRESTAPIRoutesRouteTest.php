<?php
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/plugin.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/rest-api.php';
require_once dirname( __FILE__ ) . '/../../../restapi-user-basic-access/classes/authentication.php';
require_once dirname( __FILE__ ) . '/../../entities/entity.php';
require_once dirname( __FILE__ ) . '/../../entities/log.php';
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/rest-api/endpoints/class-wp-rest-controller.php';
require_once dirname( __FILE__ ) . '/../../routes/route.php';

class WDGRESTAPIRoutesRouteTest extends PHPUnit_Framework_TestCase {

	public function testsetPostedProperties() {
		$db_properties_test = array( 'prop' => 'val' );
		$entityTest = new WDGRESTAPI_Entity( FALSE, FALSE, $db_properties_test );

		if ( !isset( $_POST ) ) {
			$_POST = array();
		}
		$_POST[ 'prop' ] = 'val2';
		
		$routeTest = new WDGRESTAPI_Route();
		$routeTest->set_posted_properties( $entityTest, $db_properties_test );

		$loaded_data = $entityTest->get_loaded_data();
		$this->assertEquals( 'val2', $loaded_data->prop );
	}
	
	public function testisDataForCurrentClient() {
		$db_properties_test = array( 'client_user_id' => '0' );
		$entityTest = new WDGRESTAPI_Entity( FALSE, FALSE, $db_properties_test );
		$loaded_data = $entityTest->get_loaded_data();
		$routeTest = new WDGRESTAPI_Route();
		$this->assertTrue( $routeTest->is_data_for_current_client( loaded_data ) );
		
		$db_properties_test_2 = array( 'client_user_id' => '1' );
		$entityTest_2 = new WDGRESTAPI_Entity( FALSE, FALSE, $db_properties_test_2 );
		$loaded_data_2 = $entityTest_2->get_loaded_data();
		$routeTest_2 = new WDGRESTAPI_Route();
		$this->assertFalse( $routeTest_2->is_data_for_current_client( loaded_data_2 ) );
	}
	
	public function testgetCurrentClientAuthorizedIds() {
		$array_authorized_ids_test = array( '0' );
		$routeTest = new WDGRESTAPI_Route();
		$result_authorized_ids = $routeTest->get_current_client_authorized_ids();
		// assertIsArray non fonctionnel sur codeship ?
		$this->assertCount( 1, $result_authorized_ids );
		$this->assertSame( $array_authorized_ids_test, $result_authorized_ids );
	}
	
	public function testgetCurrentClientAuthorizedIdsString() {
		$array_authorized_ids_test = '(0)';
		$routeTest = new WDGRESTAPI_Route();
		$result_authorized_ids_string = $routeTest->get_current_client_autorized_ids_string();
		// assertIsString non fonctionnel sur codeship ?
		$this->assertStringMatchesFormat( '%s', $result_authorized_ids_string );
		$this->assertSame( $array_authorized_ids_test, $result_authorized_ids_string );
	}

	public function testlog() {
		$routeTest = new WDGRESTAPI_Route();
		$this->assertFalse( $routeTest->log( 'route-test', 'result-test' ) );
	}

	public function testregisterWDG() {
		rest_get_server();
		$result = WDGRESTAPI_Route::register_wdg(
			'/users',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		$this->assertTrue( $result );

		$result_2 = WDGRESTAPI_Route::register_wdg(
			'',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		$this->assertFalse( $result_2 );
		
	}

	public function testregisterExternal() {
		rest_get_server();
		$result = WDGRESTAPI_Route::register_external(
			'/users',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		$this->assertTrue( $result );

		$result_2 = WDGRESTAPI_Route::register_external(
			'',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		$this->assertFalse( $result_2 );
		
	}

}