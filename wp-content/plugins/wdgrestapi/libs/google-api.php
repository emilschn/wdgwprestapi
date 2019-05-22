<?php
class WDGRESTAPI_Lib_GoogleAPI {
	
	private static $client;
	
	public static function init_client() {
		if ( is_null( self::$client ) ) {
			require_once 'google-api-php-client/src/Google/autoload.php';
			$private_key = file_get_contents( __DIR__ . '/../../../../' . WDG_GOOGLEDOCS_KEY_FILE );
			$data = json_decode( $private_key );

			$scopes = array( Google_Service_Sheets::SPREADSHEETS );
			$credentials = new Google_Auth_AssertionCredentials(
				$data->client_email,
				$scopes,
				$data->private_key
			);

			self::$client = new Google_Client();
			self::$client->setAssertionCredentials($credentials);
			if ( self::$client->getAuth()->isAccessTokenExpired() ) {
				self::$client->getAuth()->refreshTokenWithAssertion();
			}
		}
	}
	
	/*public static function get_test_values() {
		self::init_client();
		$service = new Google_Service_Sheets( self::$client );
		$spreadsheetid = WDG_SPREADSHEETS_STATS_ID;
		$range = "A1:I350";
		$response = $service->spreadsheets_values->get( $spreadsheetid, $range );
		$values = $response->getValues();
		print_r( $values );
	}*/
	
	
	public static function set_user_values( $id, $data ) {
		$row_data = array();
		for ( $i = 0; $i < 40; $i++ ) { array_push( $row_data, '' ); }
		foreach ( $data as $data_name => $data_value ) {
			if ( isset( WDGRESTAPI_Entity_User::$db_properties[ $data_name ] ) && isset( WDGRESTAPI_Entity_User::$db_properties[ $data_name ][ 'gs_col_index' ] ) ) {
				$index = WDGRESTAPI_Entity_User::$db_properties[ $data_name ][ 'gs_col_index' ];
				$row_data[ $index - 1 ] = $data_value;
			}
		}
		self::set_values( 'USERS', $id + 1, $row_data );
	}
	
	public static function set_project_values( $id, $data ) {
		$row_data = array();
		for ( $i = 0; $i < 50; $i++ ) { array_push( $row_data, '' ); }
		foreach ( $data as $data_name => $data_value ) {
			if ( isset( WDGRESTAPI_Entity_Project::$db_properties[ $data_name ] ) && isset( WDGRESTAPI_Entity_Project::$db_properties[ $data_name ][ 'gs_col_index' ] ) ) {
				$index = WDGRESTAPI_Entity_Project::$db_properties[ $data_name ][ 'gs_col_index' ];
				$row_data[ $index - 1 ] = $data_value;
			}
		}
		self::set_values( 'PROJECTS', $id + 1, $row_data );
	}

	public static function set_values( $sheet_id, $row_index, $row_data ) {
		self::init_client();
		$service = new Google_Service_Sheets( self::$client );
		$spreadsheetid = WDG_SPREADSHEETS_STATS_ID;
		$range = $sheet_id . "!A" .$row_index. ":BZ" .$row_index;
		WDGRESTAPI_Lib_Logs::log( 'set_values > $range : ' . $range );
		
		$values = [ $row_data ];
		WDGRESTAPI_Lib_Logs::log( 'set_values > $values : ' . print_r( $values, true ) );
		$body = new Google_Service_Sheets_ValueRange( [
			'values' => $values
		] );
		$params = array(
			'valueInputOption' => "USER_ENTERED"
		);
		$result = $service->spreadsheets_values->update( $spreadsheetid, $range, $body, $params );
		WDGRESTAPI_Lib_Logs::log( 'set_values > $result : ' . print_r( $result, true ) );
	}
	
}