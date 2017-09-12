<?php
class WDGRESTAPI_Route_MailTemplate extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register(
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
		$buffer = array(
			'template1',
			'template2',
			'template3'
		);
		return $buffer;
	}
	
}