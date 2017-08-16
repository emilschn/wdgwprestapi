<?php
class WDGRESTAPI_Entity_Project extends WDGRESTAPI_Entity {
	public static $entity_type = 'project';
	
	public function __construct( $id = FALSE, $wpref = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Project::$entity_type, WDGRESTAPI_Entity_Project::$db_properties );
		if ( $wpref != FALSE ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Project::$entity_type );
			$query = 'SELECT * FROM ' .$table_name. ' WHERE wpref='.$wpref;
			$this->loaded_data = $wpdb->get_row( $query );
		}
	}
	
	/**
	 * Récupération des données de royalties concernant un projet
	 * @return string
	 */
	public function get_royalties_data() {
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site( 'get_royalties_by_project', $this->loaded_data->wpref );
		return $buffer;
	}
	
	/**
	 * Retourne la liste de tous les projets
	 * @return array
	 */
	public static function list_get() {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Project::$entity_type );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$query = "SELECT id, wpref, name FROM " .$table_name. " WHERE client_user_id=" .$current_client->ID;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	// Pour les types, voir WDGRESTAPI_Entity::get_mysqltype_from_wdgtype
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'wpref'					=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'creation_date'			=> array( 'type' => 'date', 'other' => '' ),
		'name'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_Project::$entity_type, WDGRESTAPI_Entity_Project::$db_properties );
	}
	
}