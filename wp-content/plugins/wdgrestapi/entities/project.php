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
	 * Retourne la liste des déclarations liées à un projet
	 * @return array
	 */
	public function get_declarations() {
		$buffer = WDGRESTAPI_Entity_Declaration::list_get_by_project_id( $this->loaded_data->wpref );
		return $buffer;
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
	public static function list_get( $authorized_client_id_string ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Project::$entity_type );
		$query = "SELECT id, wpref, name FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	/**
	 * Retourne les statistiques qui concernent les projets
	 */
	public static function get_stats() {
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site( 'get_projects_stats' );
		return $buffer;
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