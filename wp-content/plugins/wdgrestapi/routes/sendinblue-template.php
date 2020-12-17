<?php
class WDGRESTAPI_Route_SendinblueTemplate extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/sendinblue-template',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create_or_update'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
	}
	
	public static function register() {
		return new WDGRESTAPI_Route_SendinblueTemplate();
	}
	
	/**
	 * Crée un template de mail SendInBlue
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create_or_update( WP_REST_Request $request ) {
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Route_SendinblueTemplate > $_post : ' . print_r($_POST, true) );
		$sendinblue_template_slug = filter_input( INPUT_POST, 'slug' );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Route_SendinblueTemplate > $sendinblue_template_slug : ' . print_r($sendinblue_template_slug, true) );
		$sendinblue_template_item = new WDGRESTAPI_Entity_SendinblueTemplate( $sendinblue_template_slug );
		$this->set_posted_properties( $sendinblue_template_item, WDGRESTAPI_Entity_SendinblueTemplate::$db_properties );
		$sendinblue_template_item->save();

		$reloaded_data = $sendinblue_template_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_SendinblueTemplate::single_create_or_update", json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
}