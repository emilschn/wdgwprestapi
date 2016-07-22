<?php
// Blocks direct access
if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Extends User Admin
 */
class WDG_RESTAPIUserBasicAccess_Admin_Users {
    
	public static function add_actions() {
		add_action( 'show_user_profile', 'WDG_RESTAPIUserBasicAccess_Admin_Users::add_user_fields' );
		add_action( 'edit_user_profile', 'WDG_RESTAPIUserBasicAccess_Admin_Users::add_user_fields' );
		add_action( 'personal_options_update', 'WDG_RESTAPIUserBasicAccess_Admin_Users::save_user_fields' );
		add_action( 'edit_user_profile_update', 'WDG_RESTAPIUserBasicAccess_Admin_Users::save_user_fields' );
	}
	
	
	public static function add_user_fields( $user ) {
		$client_user = new WDG_RESTAPIUserBasicAccess_Class_Client( $user->ID );
	?>
		<h3><?php _e( "REST API Access Parameters", 'restapi-user-basic-access' ); ?></h3>

		<table class="form-table">
		    <tr>
				<th><label for="validation_status"><?php _e( "REST API Access", 'restapi-user-basic-access' ); ?></label></th>
				<td>
					<input type="checkbox" name="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_restapi; ?>" <?php checked( $client_user->is_authorized_restapi() ); ?> />
					<?php _e( "This user can access to the REST API.", 'restapi-user-basic-access' ); ?>
				</td>
		    </tr>
		</table>
		
		<table class="form-table">
		    <tr>
				<th><label for="validation_status"><?php _e( "Authorized IP addresses", 'restapi-user-basic-access' ); ?></label></th>
				<td>
					<input type="text" name="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_ips; ?>" value="<?php echo $client_user->get_authorized_ips(); ?>" />
				</td>
		    </tr>
		</table>
	<?php
	}
	
	public static function save_user_fields( $user_id ) {
		$is_authorized_restapi = filter_input( INPUT_POST, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_restapi );
		update_user_meta( $user_id, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_restapi, ($is_authorized_restapi ? '1' : '0') );
		
		$new_ips = filter_input( INPUT_POST, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_ips );
		update_user_meta( $user_id, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_ips, $new_ips );
	}
}