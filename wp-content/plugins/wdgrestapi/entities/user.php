<?php
class WDGRESTAPI_Entity_User extends WDGRESTAPI_Entity {
	public static $entity_type = 'user';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_User::$entity_type, WDGRESTAPI_Entity_User::$db_properties );
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
		'gender'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'name'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'surname'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'username'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'birthday_date'			=> array( 'type' => 'date', 'other' => '' ),
		'birthday_city'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'address'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'postalcode'			=> array( 'type' => 'int', 'other' => '' ),
		'city'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'picture_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'website_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'twitter_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'facebook_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'linkedin_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'viadeo_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'activation_key'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'password'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'signup_date'			=> array( 'type' => 'date', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_User::$entity_type, WDGRESTAPI_Entity_User::$db_properties );
	}
	
}