<?php
require_once dirname( __FILE__ ) . '/../../entities/entity.php';
require_once dirname( __FILE__ ) . '/../../entities/log.php';
require_once dirname( __FILE__ ) . '/../../libs/logs.php';

class WDGRESTAPILibLogsTest extends PHPUnit_Framework_TestCase {

	public function testlog() {
		$this->assertFalse( WDGRESTAPI_Lib_Logs::log( 'coucou', WDGRESTAPI_Entity_Log::$entity_type ) );

		WDGRESTAPI_Lib_Logs::log( 'coucou' );
		$this->assertFileExists( dirname ( __FILE__ ) . '/../../libs/log_'.date("m.d.Y").'.txt' );
	}
	
}