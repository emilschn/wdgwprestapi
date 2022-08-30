<?php
class WDGRESTAPI_Entity_ProjectUser extends WDGRESTAPI_Entity {
	public static $entity_type = 'project_user';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_ProjectUser::$entity_type, WDGRESTAPI_Entity_ProjectUser::$db_properties );
	}
	
	/**
	 * Retourne la liste des utilisateurs liés à un projet
	 * @param int $id_project
	 * @return array
	 */
	public static function get_list_by_project_id( $id_project ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_ProjectUser::$entity_type );
		$query = "SELECT id_user, type FROM " . $table_name;
		$query .= " WHERE id_project=" . $id_project;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	/**
	 * Retourne la liste des projets liés à un utilisateur
	 * @param int $id_user
	 * @return array
	 */
	public static function get_list_by_user_id( $id_user ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_ProjectUser::$entity_type );
		$query = "SELECT id_project, type FROM " . $table_name;
		$query .= " WHERE id_user=" . $id_user;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	/**
	 * Supprime la liaison entre utilisateur, projet et un role spécifique
	 * @param int $id_project
	 * @param int $id_user
	 * @param string $type
	 */
	public static function remove( $id_project, $id_user, $type ) {
		if ( !empty( $id_project ) && !empty( $id_user ) && !empty( $type ) ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_ProjectUser::$entity_type );
			$wpdb->delete(
				$table_name,
				array(
					'id_project'	=> $id_project,
					'id_user'		=> $id_user,
					'type'			=> $type
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
		'id_user'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'notifications'			=> array( 'type' => 'bool', 'other' => 'DEFAULT 1 NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_ProjectUser::$entity_type, WDGRESTAPI_Entity_ProjectUser::$db_properties );
	}
	
}