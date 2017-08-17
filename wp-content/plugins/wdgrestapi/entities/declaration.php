<?php
class WDGRESTAPI_Entity_Declaration extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'declaration';
	
	public function __construct( $id ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Declaration::$entity_type, WDGRESTAPI_Entity_Declaration::$db_properties );
	}
	
	/**
	 * Retourne la liste de toutes les déclarations
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Declaration::$entity_type );
		$query = "SELECT id FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_Declaration::$entity_type, WDGRESTAPI_Entity_Declaration::$db_properties );
	}
	
}