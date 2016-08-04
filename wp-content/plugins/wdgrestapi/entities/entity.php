<?php
// Blocks direct access
if ( ! function_exists( 'is_admin' ) && ! class_exists( 'TestCase' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDGRESTAPI_Entity {
	
	public function __construct() {
		
	}
	
}