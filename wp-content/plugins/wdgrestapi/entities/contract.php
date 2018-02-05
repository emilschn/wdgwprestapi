<?php
class WDGRESTAPI_Entity_Contract extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'contract';
	
	public static $contract_entity_types = array( 'user' );
	
	public static $contract_statuses = array( 'init', 'waiting-signature', 'validated' );
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Contract::$entity_type, WDGRESTAPI_Entity_Contract::$db_properties );
	}
	
	public function save() {
		if ( empty( $this->loaded_data->status ) ) {
			$this->loaded_data->status = 'init';
		}
		parent::save();
	}
	
	public static function list_get( $entity_type, $entity_id ) {
		$contract_model = WDGRESTAPI_Entity_ContractModel::get_by_entity_id( $entity_type, $entity_id );
		return WDGRESTAPI_Entity_Contract::list_get_by_model( $contract_model->id );
	}
	
	public static function list_get_by_model( $model_id ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Contract::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE model_id = '" .$model_id;
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
		'model_id'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'entity_type'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'entity_id'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'partner'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'partner_id'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'update_date'			=> array( 'type' => 'datetime', 'other' => '' ),
		'status'				=> array( 'type' => 'varchar', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_Contract::$entity_type, WDGRESTAPI_Entity_Contract::$db_properties );
	}
	
}