<?php
class WDGRESTAPI_Entity_OrganizationUser extends WDGRESTAPI_Entity {
	public static $entity_type = 'organization_user';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_OrganizationUser::$entity_type, WDGRESTAPI_Entity_OrganizationUser::$db_properties );
	}
	
	/**
	 * Retourne la liste des utilisateurs liés à une organisation
	 * @param int $id_organization
	 * @return array
	 */
	public static function get_list_by_organization_id( $id_organization ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_OrganizationUser::$entity_type );
		$query = "SELECT id_user, type FROM " . $table_name;
		$query .= " WHERE id_organization=" . $id_organization;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	/**
	 * Retourne la liste des organisations liées à un utilisateur
	 * @param int $id_user
	 * @return array
	 */
	public static function get_list_by_user_id( $id_user ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_OrganizationUser::$entity_type );
		$query = "SELECT id_organization, type FROM " . $table_name;
		$query .= " WHERE id_user=" . $id_user;
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
		'id_organization'		=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'id_user'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_OrganizationUser::$entity_type, WDGRESTAPI_Entity_OrganizationUser::$db_properties );
	}
	
}