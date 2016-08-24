<?php
class WDGRESTAPI_Entity_ProjectOrganization extends WDGRESTAPI_Entity {
	public static $entity_type = 'project_organization';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_ProjectOrganization::$entity_type, WDGRESTAPI_Entity_ProjectOrganization::$db_properties );
	}
	
	/**
	 * Retourne la liste des organisations liées à un projet
	 * @param int $id_project
	 * @return array
	 */
	public static function get_list_by_project_id( $id_project ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_ProjectOrganization::$entity_type );
		$query = "SELECT id_organization, type FROM " . $table_name;
		$query .= " WHERE id_project=" . $id_project;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	/**
	 * Retourne la liste des projets liés à une organisation
	 * @param int $id_organization
	 * @return array
	 */
	public static function get_list_by_organization_id( $id_organization ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_ProjectOrganization::$entity_type );
		$query = "SELECT id_project, type FROM " . $table_name;
		$query .= " WHERE id_organization=" . $id_organization;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	/**
	 * Supprime la liaison entre organization, projet et un role spécifique
	 * @param int $id_project
	 * @param int $id_organization
	 * @param string $type
	 */
	public static function remove( $id_project, $id_organization, $type ) {
		if ( !empty( $id_project ) && !empty( $id_organization ) && !empty( $type ) ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_ProjectOrganization::$entity_type );
			$wpdb->delete(
				$table_name,
				array(
					'id_project'		=> $id_project,
					'id_organization'	=> $id_organization,
					'type'				=> $type
				)
			);
		}
	}




/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	// Pour les types, voir WDGRESTAPI_Entity::get_mysqltype_from_wdgtype
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'id_project'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'id_organization'		=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_ProjectOrganization::$entity_type, WDGRESTAPI_Entity_ProjectOrganization::$db_properties );
	}
	
}