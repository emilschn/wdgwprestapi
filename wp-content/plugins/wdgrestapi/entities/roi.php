<?php
class WDGRESTAPI_Entity_ROI extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'roi';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_ROI::$entity_type, WDGRESTAPI_Entity_ROI::$db_properties );
	}
	
	/**
	 * Retourne la liste de tous les ROIs
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_ROI::$entity_type );
		$query = "SELECT id, id_investment, id_project, id_orga, id_user, id_declaration, date_transfer, amount, id_transfer, status FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
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
		'id_investment'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'id_project'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'id_orga'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'id_user'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'id_declaration'		=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'date_transfer'			=> array( 'type' => 'date', 'other' => 'DEFAULT \'0000-00-00\'' ),
		'amount'				=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'id_transfer'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_ROI::$entity_type, WDGRESTAPI_Entity_ROI::$db_properties );
	}
	
}