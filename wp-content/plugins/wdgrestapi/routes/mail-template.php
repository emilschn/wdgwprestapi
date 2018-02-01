<?php
class WDGRESTAPI_Route_MailTemplate extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/mail-templates',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
	}
	
	public static function register() {
		$route_mail_templates = new WDGRESTAPI_Route_MailTemplate();
		return $route_mail_templates;
	}
	
	/**
	 * Retourne la liste des templates de mails exploitables
	 */
	public function list_get() {
		$buffer = array();
		
		include_once( plugin_dir_path( __FILE__ ) . '../libs/sendinblue/mailin.php');
		$mailin = new Mailin( 'https://api.sendinblue.com/v2.0', WDG_SENDINBLUE_API_KEY, 5000 );
		$data = array(
			"type"		=> "template",
			"status"	=> "temp_active"
		);
		$sendinblue_result = $mailin->get_campaigns_v2( $data );
		
		$sendinblue_list = $sendinblue_result[ 'data' ][ 'campaign_records' ];
		foreach ( $sendinblue_list as $sendinblue_template ) {
			$item = array(
				'id'	=> $sendinblue_template[ 'id' ],
				'name'	=> $sendinblue_template[ 'campaign_name' ],
			);
			array_push( $buffer, $item );
		}
		
		return $buffer;
	}
	
}