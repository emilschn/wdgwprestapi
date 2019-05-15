<?php
class WDGRESTAPI_Entity_InvestmentDraft extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'investment_draft';
	
	public static $status_draft = 'draft';
	public static $status_validated = 'validated';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	public function list_get( $authorized_client_id_string, $project_id ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		
		$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		if ( !empty( $project_id ) ) {
			$query .= " AND project_id = " . $project_id;
		}
		
		$results = $wpdb->get_results( $query );
		return self::complete_data( $results );
	}
	
	/**
	 * Parcourt les données pour ajouter celles qu'il manque
	 * @param array $results
	 * @return array
	 */
	private static function complete_data( $results ) {
		$buffer = array();
		
		foreach ( $results as $result ) {
			$buffer_item = self::complete_single_data( $result );
			array_push( $buffer, $buffer_item );
		}
		
		return $buffer;
	}
	
	/**
	 * Ajoute les données manquantes
	 * @param object $result
	 * @return array
	 */
	public static function complete_single_data( $result ) {
		// Fichiers
		$result->check = '';
		$check_file_entity = WDGRESTAPI_Entity_File::get_single( 'investment-draft', $result->id, 'picture-check' );
		if ( !empty( $check_file_entity ) ) {
			$check_loaded_data = $check_file_entity->get_loaded_data();
			$result->check = $check_loaded_data->url;
		}
		$result->contract = '';
		$contract_file_entity = WDGRESTAPI_Entity_File::get_single( 'investment-draft', $result->id, 'picture-contract' );
		if ( !empty( $contract_file_entity ) ) {
			$contract_loaded_data = $contract_file_entity->get_loaded_data();
			$result->contract = $contract_loaded_data->url;
		}
		return $result;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'project_id'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'data'					=> array( 'type' => 'longtext', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}