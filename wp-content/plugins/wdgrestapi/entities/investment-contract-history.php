<?php
class WDGRESTAPI_Entity_InvestmentContractHistory extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'investment_contract_history';
	
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		
		'date'					=> array( 'type' => 'datetime', 'other' => 'NOT NULL' ),
		'data_modified'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'new_value'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		
		'list_new_contracts'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'comment'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}