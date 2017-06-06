<?php
/**
 * Extends User Admin
 */
class WDGRESTAPI_Admin_Users {
    
	/**
	 * Subscribes to standard WP actions
	 */
	public static function add_actions() {
		add_action( 'show_user_profile', 'WDGRESTAPI_Admin_Users::add_user_fields' );
		add_action( 'edit_user_profile', 'WDGRESTAPI_Admin_Users::add_user_fields' );
		add_action( 'personal_options_update', 'WDGRESTAPI_Admin_Users::save_user_fields' );
		add_action( 'edit_user_profile_update', 'WDGRESTAPI_Admin_Users::save_user_fields' );
	}
	
	/**
	 * Adds some new fields to the user account admin form
	 * @param WP_User $user
	 */
	public static function add_user_fields( $user ) {
		if ( current_user_can( 'manage_options' ) ) {
			?>
			<h3><?php _e( "WE DO GOOD Access Parameters", 'restapi-user-basic-access' ); ?></h3>

			<table class="form-table">
				<tr>
					<th><label for="<?php echo WDGRESTAPI_Route::$key_authorized_accounts_access; ?>"><?php _e( "Access to the data of these user IDs", 'wdgrestapi' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo WDGRESTAPI_Route::$key_authorized_accounts_access; ?>" value="<?php echo $user->get( WDGRESTAPI_Route::$key_authorized_accounts_access ); ?>" />
					</td>
				</tr>
			</table>
		<?php
		}
	}
	
	/**
	 * Saves new fields from the user account admin form
	 * @param int $user_id
	 */
	public static function save_user_fields( $user_id ) {
		if ( current_user_can( 'manage_options' ) ) {
			$authorized_accounts_access = filter_input( INPUT_POST, WDGRESTAPI_Route::$key_authorized_accounts_access );
			update_user_meta( $user_id, WDGRESTAPI_Route::$key_authorized_accounts_access, $authorized_accounts_access );
		}
	}
}