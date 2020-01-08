<?php

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
require_once dirname( __FILE__ ) . '/../../libs/geolocation.php';

use PHPUnit\Framework\TestCase;

class WDGRESTAPILibGeolocationTest extends TestCase {

	public function testgetGeolocationData() {
		$this->assertEmpty( WDGRESTAPI_Lib_Geolocation::get_geolocation_data( '' ) );
		
		$geoloc_rubbish = WDGRESTAPI_Lib_Geolocation::get_geolocation_data( 'blabliblou blabliblou' );
		$this->assertTrue( is_wp_error( $geoloc_rubbish ) );

		$geoloc_existing = WDGRESTAPI_Lib_Geolocation::get_geolocation_data( '1 place de la bourse 44000 Nantes' );
		$this->assertTrue( ( isset( $geoloc_existing[ 'lat' ] ) && isset( $geoloc_existing[ 'long' ] ) ) );
	}
	
}