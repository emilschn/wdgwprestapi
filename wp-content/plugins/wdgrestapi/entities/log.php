<?php
class WDGRESTAPI_Entity_Log extends WDGRESTAPI_Entity {
	public static $entity_type = 'log';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Log::$entity_type, WDGRESTAPI_Entity_Log::$db_properties );
	}
	
	
	public function save() {
		date_default_timezone_set( 'Europe/Paris' );
		$current_date = new DateTime();
		$this->set_property( 'date', $current_date->format( 'Y-m-d H:i:s' ) );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$current_client_id = '0';
		if ( !empty( $current_client ) ) {
			$current_client_id = $current_client->ID;
		}
		$this->set_property( 'caller', $current_client_id );
		return parent::save();
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'date'					=> array( 'type' => 'datetime', 'other' => '' ),
		'route'					=> array( 'type' => 'varchar', 'other' => '' ),
		'result'				=> array( 'type' => 'longtext', 'other' => '' ),
		'caller'				=> array( 'type' => 'id', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_Log::$entity_type, WDGRESTAPI_Entity_Log::$db_properties );
	}
	
}