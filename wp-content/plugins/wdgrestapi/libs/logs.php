<?php
class WDGRESTAPI_Lib_Logs {
	
	public static function log( $debug_str, $entity_type = FALSE ) {
		// Ne pas enregistrer dans les fichiers de logs ce qu'on enregistre déjà dans la BDD
		if ( $entity_type == WDGRESTAPI_Entity_Log::$entity_type ) {
			return FALSE;
		}
		
		$filename = dirname ( __FILE__ ) . '/log_'.date("m.d.Y").'.txt';
		$file_handle = fopen($filename, 'a');
		date_default_timezone_set("Europe/Paris");

		$request_uri = "";
		if ( isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
			$request_uri = $_SERVER[ 'REQUEST_URI' ];
		}
		$query_string = "";
		if ( isset( $_SERVER[ 'QUERY_STRING' ] ) ) {
			$query_string = $_SERVER[ 'QUERY_STRING' ];
		}

		fwrite($file_handle, date("m.d.Y H:i:s") . " (".$request_uri."?".$query_string.")\n");
		fwrite($file_handle, " -> " . $debug_str . "\n\n");
		fclose($file_handle);
	}
	
}