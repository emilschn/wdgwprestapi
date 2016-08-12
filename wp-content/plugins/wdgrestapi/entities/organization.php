<?php
class WDGRESTAPI_Entity_Organization extends WDGRESTAPI_Entity {
	public static $entity_type = 'organization';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Organization::$entity_type, WDGRESTAPI_Entity_Organization::$db_properties );
	}
	
	public function set_name( $new_name ) {
		$this->loaded_data->name = $new_name;
	}
	
	/**
	 * Retourne la liste de toutes les organisations
	 * @return array
	 */
	public static function list_get() {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Organization::$entity_type );
		$query = "SELECT id, wpref, name FROM " .$table_name;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	/**
	 * Requete de transfert SQL :
	 * INSERT INTO `wp_entity_organization` (`id`, `wpref`, `name`, `creation_date`, `strong_authentication`, `type`, `legalform`, `idnumber`, `rcs`, `capital`, `address`, `postalcode`, `city`, `country`, `ape`, `bank_owner`, `bank_address`, `bank_iban`, `bank_bic`, `website_url`, `twitter_url`, `facebook_url`, `linkedin_url`, `viadeo_url`) VALUES
(6, 0, 'BLI', '2014-12-16', 0, 'society', 'BLI', 'BLI', 'BLI', 400, 'BLI', 300, 'BLI', 'BH', 'BLI', '', '', '', '', '---', '---', '---', '---', '---');
	 */
	
	// Pour les types, voir WDGRESTAPI_Entity::get_mysqltype_from_wdgtype
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'wpref'					=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'name'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'creation_date'			=> array( 'type' => 'date', 'other' => '' ),
		'strong_authentication'	=> array( 'type' => 'bool', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'legalform'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'idnumber'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'rcs'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'capital'				=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'ape'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'address'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'postalcode'			=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'city'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'country'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_owner'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_address'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'bank_iban'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_bic'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'website_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'twitter_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'facebook_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'linkedin_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'viadeo_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_Organization::$entity_type, WDGRESTAPI_Entity_Organization::$db_properties );
	}
	
}