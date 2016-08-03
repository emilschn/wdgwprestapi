<?php
// Blocks direct access
if ( ! function_exists( 'is_admin' ) && ! class_exists( 'TestCase' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class WDGRESTAPI_Routes {
	
	public static $namespace = 'wdg/v1';
	
	public static function register() {
		register_rest_route(
			WDGRESTAPI_Routes::$namespace,
			'/staticpages',
			array(
				'methods' => 'GET',
				'callback' => 'WDGRESTAPI_Routes::staticpages_list_get'
			)
		);
	}
	
	public static function staticpages_list_get() {
		$staticpages_list = get_pages( array(
			'meta_key'		=> WDGRESTAPI_Admin_Posts::$key_export_static,
			'meta_value'	=> '1'
		));
		
		$buffer = array();
		foreach ( $staticpages_list as $staticpage ) {
			$staticpage_item = array(
				'ID'		=> $staticpage->ID,
				'title'		=> $staticpage->post_title,
				'update'	=> $staticpage->post_modified,
			);
			array_push( $buffer, $staticpage_item );
		}
		
		return $buffer;
	}
	
}