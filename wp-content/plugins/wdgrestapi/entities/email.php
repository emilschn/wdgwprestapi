<?php
class WDGRESTAPI_Entity_Email extends WDGRESTAPI_Entity {
	public static $entity_type = 'email';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Email::$entity_type, WDGRESTAPI_Entity_Email::$db_properties );
	}
	
	/**
	 * 
	 */
	public function save() {
		date_default_timezone_set( 'Europe/Paris' );
		$current_date = new DateTime();
		$this->set_property( 'date', $current_date->format( 'Y-m-d H:i:s' ) );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$this->set_property( 'caller', $current_client->ID );
		parent::save();
	}
	
	/**
	 * Mail sending procedure
	 */
	public function send() {
		
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'date'					=> array( 'type' => 'datetime', 'other' => '' ),
		'caller'				=> array( 'type' => 'id', 'other' => '' ),
		'tool'					=> array( 'type' => 'varchar', 'other' => '' ),
		'template'				=> array( 'type' => 'varchar', 'other' => '' ),
		'recipient'				=> array( 'type' => 'longtext', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_Email::$entity_type, WDGRESTAPI_Entity_Email::$db_properties );
	}
	
}