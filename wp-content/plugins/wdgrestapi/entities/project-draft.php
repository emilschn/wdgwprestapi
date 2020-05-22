<?php
class WDGRESTAPI_Entity_Project_Draft extends WDGRESTAPI_Entity {
	public static $entity_type = 'project_draft';
	
	public static $status_preparing = 'preparing';
	public static $status_validated = 'validated';
	public static $status_preview = 'preview';
	public static $status_vote = 'vote';
	public static $status_collecte = 'collecte';
	public static $status_funded = 'funded';
	public static $status_closed = 'closed';
	public static $status_archive = 'archive';
	
	public function __construct( $id = FALSE, $guid = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
		if ( $guid != FALSE ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
			$query = 'SELECT * FROM ' .$table_name. ' WHERE guid=\''.$guid.'\'';
			$this->loaded_data = $wpdb->get_row( $query );

			$this->loaded_data->file_list = WDGRESTAPI_Entity_File::get_list( 'project-draft', $this->loaded_data->id, 'business' );

		}
	}

	/**
	 * Override pour enregistrer la date de création
	 */
	public function save() {
		$current_datetime = new DateTime();
		$this->loaded_data->date_created = $current_datetime->format( 'Y-m-d H:i:s' );
		parent::save();
	}
		
	/**
	 * Retourne la liste de tous les brouillons de projets d'un email
	 * @return array
	 */
	public static function list_get( $email ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE email='" .$email. "'";
		$results = $wpdb->get_results( $query );
		return $results;
	}

	/**
	 * Retourne les données du statut du projet
	 */
	public function get_status() {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT status FROM " .$table_name. " WHERE guid=" .$this->loaded_data->guid;
		$buffer = $wpdb->get_result( $query );
		
		return $buffer;
	}

/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	// Pour les types, voir WDGRESTAPI_Entity::get_mysqltype_from_wdgtype
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT', 'gs_col_index' => 1 ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'guid'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 2 ),
		'date_created'			=> array( 'type' => 'datetime', 'other' => 'NOT NULL', 'gs_col_index' => 3 ),
		'id_user'				=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 4 ),
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 5 ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 6 ),
		'step'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 7 ),
		'authorization'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 8 ),
		'metadata'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 9 )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}
