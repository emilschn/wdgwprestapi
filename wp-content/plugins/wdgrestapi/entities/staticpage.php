<?php
class WDGRESTAPI_Entity_StaticPage extends WDGRESTAPI_Entity {
	
	public static $key_export_static = 'export_static';
	
	public static function list_get() {
		$staticpages_list = get_pages( array(
			'meta_key'		=> WDGRESTAPI_Entity_StaticPage::$key_export_static,
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
	
	public static function save_exported_static( $post_id, $new_value ) {
		update_post_meta( $post_id, WDGRESTAPI_Entity_StaticPage::$key_export_static, $new_value ? '1' : '0' );
	}

	public static function is_exported_static( $post_id ) {
		return get_post_meta( $post_id, WDGRESTAPI_Entity_StaticPage::$key_export_static, TRUE );
	}
	
}