<?php
class WDGRESTAPI_Lib_Logs {
	
	public static function log( $debug_str ) {
		$filename = dirname ( __FILE__ ) . '/log_'.date("m.d.Y").'.txt';
		$file_handle = fopen($filename, 'a');
		date_default_timezone_set("Europe/Paris");
		fwrite($file_handle, date("m.d.Y H:i:s") . " (".$_SERVER['REQUEST_URI']."?".$_SERVER['QUERY_STRING'].")\n");
		fwrite($file_handle, " -> " . $debug_str . "\n\n");
		fclose($file_handle);
	}
	
}