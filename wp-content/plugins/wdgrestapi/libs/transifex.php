<?php
class WDGRESTAPI_Lib_Transifex {

	private static $transifex_url = 'https://www.transifex.com/api/2/';
	private static $transifex_token = '1/12f42653e3efe88c6f2d34d7c94c19a7a569d8b7';
	private static $transifex_project_slug = 'sendinblue-templates-mails';
	private static $transifex_project_resource_slug = 'global';
	
	private static function query( $url, $data ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, WDGRESTAPI_Lib_Transifex::$transifex_token );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );

		$encoded_data = json_encode( $data );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $encoded_data );

		$json = curl_exec( $ch );
		if ( !curl_errno( $ch ) ) {
			print_r($ch);
			print_r(curl_error($ch));
		}

		curl_close( $ch );
	}

	public static function create_empty_resource() {
		$url = WDGRESTAPI_Lib_Transifex::$transifex_url;
		$url .= '/project/' .WDGRESTAPI_Lib_Transifex::$transifex_project_slug. '/resources/';

		$data = array();
		$data[ 'slug' ] = WDGRESTAPI_Lib_Transifex::$transifex_project_slug;
		$data[ 'name' ] = 'Global';
		$data[ 'i18n_type' ] = 'HTML';
		$data[ 'source_language_code' ] = 'fr';
		$data[ 'priority' ] = '2';
		$data[ 'categories' ] = '';

		WDGRESTAPI_Lib_Transifex::query( $url, $data );
	} 

	public static function send_strings_to_resource( $slug, $name ) {
		$url = WDGRESTAPI_Lib_Transifex::$transifex_url;
		$url .= '/project/' .WDGRESTAPI_Lib_Transifex::$transifex_project_slug;
		$url .= '/resource/' .WDGRESTAPI_Lib_Transifex::$transifex_project_resource_slug;
		$url .= 'translation/fr/strings';

		$data = array();
		$data[ 'slug' ] = $slug;
		$data[ 'name' ] = $name;

		WDGRESTAPI_Lib_Transifex::query( $url, $data );
	}
	
}