<?php
// Blocks direct access
if ( ! function_exists( 'is_admin' ) && ! class_exists( 'TestCase' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDGRESTAPI_Route_StaticPage extends WDGRESTAPI_Route {
	
	public static function register() {
		WDGRESTAPI_Route::register_wdg( '/staticpages', 'GET', 'WDGRESTAPI_Entity_StaticPage::list_get' );
	}
	
}