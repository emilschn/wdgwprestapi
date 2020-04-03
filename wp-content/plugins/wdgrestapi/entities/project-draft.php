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
		}
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
		'id_user'				=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 3 ),
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 4 ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 5 ),
		'step'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 6 ),
		'authorization'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 7 ),
		'metadata'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 8 )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}
