<?php
require_once dirname( __FILE__ ) . '/../../../restapi-user-basic-access/classes/authentication.php';
require_once dirname( __FILE__ ) . '/../../entities/entity.php';

class WDGRESTAPIEntitiesEntityTest extends PHPUnit_Framework_TestCase {

	public function testgetLoadedData() {
		$db_properties_test = array( 'prop' => 'val' );
		$loaded_data_empty = json_decode( '{}' );

		$entityTest1 = new WDGRESTAPI_Entity( 1, 'test', array() );
		$this->assertEquals( $entityTest1->get_loaded_data(), $loaded_data_empty );

		$entityTest2 = new WDGRESTAPI_Entity( FALSE, FALSE, FALSE );
		$this->assertEquals( $entityTest2->get_loaded_data(), $loaded_data_empty );

		$entityTest3 = new WDGRESTAPI_Entity( FALSE, FALSE, $db_properties_test );
		$loaded_data_3 = $entityTest3->get_loaded_data();
		$this->assertEquals( $loaded_data_3->prop, 'val' );

	}

	public function testsetProperty() {
		$db_properties_test = array( 'prop' => 'val' );

		$entityTest3 = new WDGRESTAPI_Entity( FALSE, FALSE, $db_properties_test );
		$entityTest3->set_property( 'prop', 'val2' );
		$entityTest3->set_property( 'prop2', 'val' );
		$loaded_data_3 = $entityTest3->get_loaded_data();
		$this->assertEquals( $loaded_data_3->prop, 'val2' );
		$this->assertEquals( $loaded_data_3->prop2, 'val' );

	}

	public function testsetMetadata() {
		$db_properties_test = array( 'prop' => 'val' );
		$entityTest = new WDGRESTAPI_Entity( FALSE, FALSE, $db_properties_test );
		$entityTest->set_metadata( 'metaprop', 'metaval' );
		$this->assertEquals( $entityTest->get_metadata( 'metaprop' ), 'metaval' );

	}

	public function testgetPropertiesErrors() {
		$db_properties_test = array( 'prop' => 'val' );

		$entityTest1 = new WDGRESTAPI_Entity( 1, 'test', array() );
		$this->assertEquals( $entityTest1->get_properties_errors(), array() );

		$entityTest2 = new WDGRESTAPI_Entity( FALSE, FALSE, FALSE );
		$this->assertEquals( $entityTest2->get_properties_errors(), array() );

		$entityTest3 = new WDGRESTAPI_Entity( FALSE, FALSE, $db_properties_test );
		$this->assertEquals( $loaded_data_3->get_properties_errors(), array() );

	}

	public function testmakeUID() {
		$test_uid = WDGRESTAPI_Entity::make_uid();
		$this->assertIsString( $test_uid );
	}

	public function testsave() {
		$entityTest = new WDGRESTAPI_Entity( FALSE, FALSE, FALSE );
		$this->assertFalse( $entityTest->save() );
	}

	public function testdelete() {
		$entityTest = new WDGRESTAPI_Entity( FALSE, FALSE, FALSE );
		$this->assertFalse( $entityTest->delete() );
	}

	public function testupgradeEntityDB() {
		$this->assertFalse( WDGRESTAPI_Entity::upgrade_entity_db( FALSE, FALSE ) );
	}

	public function testgetTableName() {
		$this->assertFalse( WDGRESTAPI_Entity::get_table_name( FALSE ) );
	}

	/**
	 * @dataProvider mysqlToWDGProvider
	 */
	public function testgetMySQLTypeFromWDGType( $value, $expected) {
		$this->assertEquals( WDGRESTAPI_Entity::get_mysqltype_from_wdgtype( $value ), $expected );
	}
	
	public function mysqlToWDGProvider() {
		return [
			'id to mediumint(9)'		=> [ 'id', 'mediumint(9)' ],
			'uid to varchar(50)'		=> [ 'uid', 'varchar(50)' ],
			'longtext to longtext'		=> [ 'longtext', 'longtext' ],
			'date to date'				=> [ 'date', 'date' ],
			'bool to int(1)'			=> [ 'bool', 'int(1)' ],
			'int to int(11)'			=> [ 'int', 'int(11)' ],
			'float to float'			=> [ 'float', 'float' ]
		];
	}

}