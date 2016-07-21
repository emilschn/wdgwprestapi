<?php
class WDGRESTAPI_Admin_Users {
    
	public static $option_name = 'wdgrestapi';
	
	
	public static function init() {
		//Initialisation des champs à rajouter dans les comptes utilisateur du back-office
		add_action( 'show_user_profile', 'WDGRESTAPI_Admin_Users::add_user_fields' );
		add_action( 'edit_user_profile', 'WDGRESTAPI_Admin_Users::add_user_fields' );
		add_action( 'personal_options_update', 'WDGRESTAPI_Admin_Users::save_user_fields' );
		add_action( 'edit_user_profile_update', 'WDGRESTAPI_Admin_Users::save_user_fields' );
	}
	
	
	public static function add_user_fields( $user ) {
		$client_user = new WDGRESTAPI_Client( $user->ID );
	?>
		<h3>Paramètres WDGRESTAPI</h3>

		<table class="form-table">
		    <tr>
				<th><label for="validation_status">Adresses IP autorisées</label></th>
				<td>
					<input type="text" name="<?php echo WDGRESTAPI_Client::$key_authorized_ips; ?>" value="<?php echo $client_user->get_authorized_ips(); ?>" />
				</td>
		    </tr>
		</table>
	<?php
	}
	
	public static function save_user_fields( $user_id ) {
		$new_ips = filter_input( INPUT_POST, WDGRESTAPI_Client::$key_authorized_ips );
		update_user_meta( $user_id, WDGRESTAPI_Client::$key_authorized_ips, $new_ips );
	}
}