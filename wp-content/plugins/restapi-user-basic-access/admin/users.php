<?php
/**
 * Extends User Admin
 */
class WDG_RESTAPIUserBasicAccess_Admin_Users {
    
	/**
	 * Subscribes to standard WP actions
	 */
	public static function add_actions() {
		add_action( 'show_user_profile', 'WDG_RESTAPIUserBasicAccess_Admin_Users::add_user_fields' );
		add_action( 'edit_user_profile', 'WDG_RESTAPIUserBasicAccess_Admin_Users::add_user_fields' );
		add_action( 'personal_options_update', 'WDG_RESTAPIUserBasicAccess_Admin_Users::save_user_fields' );
		add_action( 'edit_user_profile_update', 'WDG_RESTAPIUserBasicAccess_Admin_Users::save_user_fields' );
	}
	
	/**
	 * Adds some new fields to the user account admin form
	 * @param WP_User $user
	 */
	public static function add_user_fields( $user ) {
		$client_user = new WDG_RESTAPIUserBasicAccess_Class_Client( $user->ID );
	?>
		<h3><?php _e( "REST API Access Parameters", 'restapi-user-basic-access' ); ?></h3>

		<table class="form-table">
		    <tr>
				<th><label for="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_restapi; ?>"><?php _e( "REST API Access", 'restapi-user-basic-access' ); ?></label></th>
				<td>
					<input type="checkbox" name="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_restapi; ?>" <?php checked( $client_user->is_authorized_restapi() ); ?> />
					<?php _e( "This user can access to the REST API.", 'restapi-user-basic-access' ); ?>
				</td>
		    </tr>
		</table>
		
		<table class="form-table">
		    <tr>
				<th><label for="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_ips; ?>"><?php _e( "Authorized IP addresses", 'restapi-user-basic-access' ); ?></label></th>
				<td>
					<input type="text" name="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_ips; ?>" value="<?php echo $client_user->get_authorized_ips(); ?>" />
				</td>
		    </tr>
		</table>
		
		<table class="form-table">
		    <tr>
				<th><label><?php _e( "Authorized actions", 'restapi-user-basic-access' ); ?></label></th>
				<td>
					<label for="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_get; ?>">
						<input type="checkbox" name="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_get; ?>" <?php checked( $client_user->is_authorized_action( WDG_RESTAPIUserBasicAccess_Class_Client::$action_get ) ); ?> />
						<?php _e( "Read data (GET)", 'restapi-user-basic-access' ); ?>
					</label><br />
					
					<label for="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_post; ?>">
						<input type="checkbox" name="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_post; ?>" <?php checked( $client_user->is_authorized_action( WDG_RESTAPIUserBasicAccess_Class_Client::$action_post ) ); ?> />
						<?php _e( "Add data (POST)", 'restapi-user-basic-access' ); ?>
					</label><br />
					
					<label for="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_put; ?>">
						<input type="checkbox" name="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_put; ?>" <?php checked( $client_user->is_authorized_action( WDG_RESTAPIUserBasicAccess_Class_Client::$action_put ) ); ?> />
						<?php _e( "Modify data (PUT / PATCH)", 'restapi-user-basic-access' ); ?>
					</label><br />
					
					<label for="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_delete; ?>">
						<input type="checkbox" name="<?php echo WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_delete; ?>" <?php checked( $client_user->is_authorized_action( WDG_RESTAPIUserBasicAccess_Class_Client::$action_delete ) ); ?> />
						<?php _e( "Remove data (DELETE)", 'restapi-user-basic-access' ); ?>
					</label><br />
				</td>
		    </tr>
		</table>
	<?php
	}
	
	/**
	 * Saves new fields from the user account admin form
	 * @param int $user_id
	 */
	public static function save_user_fields( $user_id ) {
		$is_authorized_restapi = filter_input( INPUT_POST, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_restapi );
		update_user_meta( $user_id, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_restapi, ($is_authorized_restapi ? '1' : '0') );
		
		$new_ips = filter_input( INPUT_POST, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_ips );
		update_user_meta( $user_id, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_ips, $new_ips );
		
		$action_array = array();
		$posted_get = filter_input( INPUT_POST, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_get );
		$action_array[WDG_RESTAPIUserBasicAccess_Class_Client::$action_get] = ($posted_get ? '1' : '0');
		$posted_post = filter_input( INPUT_POST, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_post );
		$action_array[WDG_RESTAPIUserBasicAccess_Class_Client::$action_post] = ($posted_post ? '1' : '0');
		$posted_put = filter_input( INPUT_POST, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_put );
		$action_array[WDG_RESTAPIUserBasicAccess_Class_Client::$action_put] = ($posted_put ? '1' : '0');
		$posted_delete = filter_input( INPUT_POST, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions . WDG_RESTAPIUserBasicAccess_Class_Client::$action_delete );
		$action_array[WDG_RESTAPIUserBasicAccess_Class_Client::$action_delete] = ($posted_delete ? '1' : '0');
		update_user_meta( $user_id, WDG_RESTAPIUserBasicAccess_Class_Client::$key_authorized_actions, json_encode($action_array) );
	}
}