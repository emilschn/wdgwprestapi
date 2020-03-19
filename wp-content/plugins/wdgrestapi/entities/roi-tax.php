<?php
class WDGRESTAPI_Entity_ROITax extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'roi_tax';
	
	public static $recipient_type_user = 'user';
	public static $recipient_type_orga = 'orga';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	/**
	 * Retourne la liste de tous les ROITax
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'id_roi'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'id_recipient'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'date_transfer'			=> array( 'type' => 'date', 'other' => 'DEFAULT \'0000-00-00\'' ),
		'amount_taxed_in_cents'	=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'amount_tax_in_cents'	=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'percent_tax'			=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'tax_country'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'recipient_type'		=> array( 'type' => 'varchar', 'other' => 'DEFAULT \'user\'' ),
		'has_tax_exemption'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}