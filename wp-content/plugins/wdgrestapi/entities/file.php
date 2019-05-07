<?php
class WDGRESTAPI_Entity_File extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'file';
	
	public static $file_entity_types = array( 'user', 'organization', 'project', 'declaration', 'investment', 'investment-draft' );
	
	public static $file_types = array( 'kyc_id', 'kyc_home', 'kyc_rib', 'kyc_kbis', 'kyc_status', 'campaign_bill', 'campaign_certificate', 'campaign_estimated_budget', 'contract', 'amendment', 'picture-check', 'picture-contract' );
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_File::$entity_type, WDGRESTAPI_Entity_File::$db_properties );
	}
	
	/**
	 * Retourne un élément File en fonction de ses paramètres
	 * @param string $entity_type
	 * @param int $entity_id
	 * @param string $file_type
	 * @return WDGRESTAPI_Entity_File
	 */
	public static function get_single( $entity_type, $entity_id, $file_type ) {
		$buffer = FALSE;
		
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_File::$entity_type );
		$query = "SELECT id FROM " .$table_name. " WHERE entity_type='" .$entity_type. "' AND entity_id=" .$entity_id. " AND file_type='" .$file_type. "'";
		$loaded_data = $wpdb->get_row( $query );
		if ( !empty( $loaded_data->id ) ) {
			$buffer = new WDGRESTAPI_Entity_File( $loaded_data->id );
		}
		
		return $buffer;
	}
	
	public function get_loaded_data() {
		$buffer = parent::get_loaded_data();
		$buffer->url = '';
		if ( !empty( $this->loaded_data->file_name ) ) {
			$buffer->url = home_url( '/wp-content/plugins/wdgrestapi/files/' .$this->loaded_data->entity_type. '/' .$this->loaded_data->file_type. '/' .$this->loaded_data->file_name );
		}
		return $buffer;
	}
	
	public function save( $base64_file_data ) {
		if ( in_array( $this->loaded_data->entity_type, WDGRESTAPI_Entity_File::$file_entity_types ) && in_array( $this->loaded_data->file_type, WDGRESTAPI_Entity_File::$file_types ) ) {
			$file_data = base64_decode( $base64_file_data );
			$this->loaded_data->file_signature = md5( $file_data );
			$path = $this->get_path();
			$random_filename = $this->get_random_filename( $path, $this->loaded_data->file_extension );
			file_put_contents( $path . $random_filename, $file_data );
			$current_datetime = new DateTime();
			$this->loaded_data->file_name = $random_filename;
			$this->loaded_data->update = $current_datetime->format( 'Y-m-d H:i:s' );
			$this->loaded_data->status = 'uploaded';
			parent::save();
			
		} else {
			return FALSE;
		}
	}
	
	private function get_path() {
		$buffer = __DIR__. '/../files/' .$this->loaded_data->entity_type. '/' .$this->loaded_data->file_type. '/';
		if ( !is_dir( $buffer ) ) {
			mkdir( $buffer, 0777, true );
		}
		return $buffer;
	}
	
	private function get_random_filename( $path, $ext ) {
		$buffer = $this->loaded_data->entity_id. '-';
		
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$size = strlen( $chars );
		for( $i = 0; $i < 15; $i++ ) {
			$buffer .= $chars[ rand( 0, $size - 1 ) ];
		}
		
		while ( file_exists( $path . $buffer . '.' . $ext ) ) {
			$buffer .= $chars[ rand( 0, $size - 1 ) ];
		}
		
		$buffer = $buffer . '.' . $ext;
		return $buffer;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'entity_id'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'entity_type'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'file_type'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'file_extension'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'file_name'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'file_signature'		=> array( 'type' => 'longtext', 'other' => '' ),
		'update_date'			=> array( 'type' => 'datetime', 'other' => '' ),
		'status'				=> array( 'type' => 'varchar', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_File::$entity_type, WDGRESTAPI_Entity_File::$db_properties );
	}
	
}