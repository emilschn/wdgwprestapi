<?php
class WDGRESTAPI_Entity_ContractModel extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'contract_model';
	
	public static $model_entity_types = array( 'project' );
	
	public static $model_types = array( 'investment', 'investment_amendment' );
	
	public static $model_statuses = array( 'draft', 'sent' );
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_ContractModel::$entity_type, WDGRESTAPI_Entity_ContractModel::$db_properties );
	}
	
	public function save() {
		if ( empty( $this->loaded_data->status ) ) {
			$this->loaded_data->status = 'draft';
		}
		$current_datetime = new DateTime();
		$this->loaded_data->update_date = $current_datetime->format( 'Y-m-d h:i:s' );
		parent::save();
	}
	
	public static function get_by_entity_id( $entity_type, $entity_id ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_ContractModel::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE entity_type = '" .$entity_type. "' AND entity_id = " .$entity_id;
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
		'entity_id'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'entity_type'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'model_type'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'model_name'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'model_content'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'update_date'			=> array( 'type' => 'datetime', 'other' => '' ),
		'status'				=> array( 'type' => 'varchar', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_ContractModel::$entity_type, WDGRESTAPI_Entity_ContractModel::$db_properties );
	}
	
}