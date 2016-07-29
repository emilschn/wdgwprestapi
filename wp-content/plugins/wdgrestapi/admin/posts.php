<?php
// Blocks direct access
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Extends Posts Admin
 */
class WDGRESTAPI_Admin_Posts {
	
	public static $key_export_static = 'export_static';

	public static function add_actions() {
		add_action( 'add_meta_boxes', 'WDGRESTAPI_Admin_Posts::add_meta_boxes' );
		add_action( 'save_post', 'WDGRESTAPI_Admin_Posts::save_meta_boxes', 10, 2 );
	}
	
	public static function add_meta_boxes() {
		global $post;

		if ( ! is_object( $post ) )
			return;
		
		add_meta_box( 'wdgrestapi_posts_export_static', __( 'Contenu statique', 'wdgrestapi' ), 'WDGRESTAPI_Admin_Posts::wdgrestapi_posts_export_static', 'page', 'side', 'low' );
	}
	
	public static function save_meta_boxes( $post_id, $post ) {
		// Checks if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
		
		$saved_value = filter_input( INPUT_POST, WDGRESTAPI_Admin_Posts::$key_export_static );
		update_post_meta( $post_id, WDGRESTAPI_Admin_Posts::$key_export_static, $saved_value ? '1' : '0' );
	}
	
	public static function wdgrestapi_posts_export_static() {
		global $post;
		$is_exported_static = get_post_meta( $post->ID, WDGRESTAPI_Admin_Posts::$key_export_static, TRUE );
		?>  
		<input type="checkbox" name="<?php echo WDGRESTAPI_Admin_Posts::$key_export_static; ?>" <?php checked( $is_exported_static ); ?> />
		<?php _e( 'Exporter cette page en contenu statique', 'wdgrestapi' ); ?>
		<?php
	}
}