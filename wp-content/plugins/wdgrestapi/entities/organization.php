<?php
class WDGRESTAPI_Entity_Organization extends WDGRESTAPI_Entity {
	public static $entity_type = 'organization';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Organization::$entity_type, WDGRESTAPI_Entity_Organization::$db_properties );
	}

	/**
	* Surcharge la fonction de récupération de données
	 * @return object
	*/
	public function get_loaded_data() {
		$this->check_geolocation_data();
		return parent::get_loaded_data();
	}

	/**
	* Se charge des vérifications de données de géolocalisation actualisées
	*/
	private function check_geolocation_data() {
		$loaded_data = $this->loaded_data;

		// Définition de l'adresse et du MD5 de l'adresse
		$geolocation_address = $loaded_data->address . ' ' . $loaded_data->postalcode . ' ' . $loaded_data->city;
		$geolocation_addr_md5 = md5( $geolocation_address );
		$current_geolocation_addr_md5 = $this->get_metadata( 'geolocation_addr_md5' );
		WDGRESTAPI_Lib_Logs::log( 'check_geolocation_data > ' . $geolocation_addr_md5 . ' | ' . $current_geolocation_addr_md5 );

		// Si la géolocalisation de l'organisation n'a pas été récupérée
			// ou si le nouveau MD5 de l'adresse est différent de celui qui a déjà été enregistré
		if ( empty( $loaded_data->geolocation ) || $current_geolocation_addr_md5 != $geolocation_addr_md5 ) {

			// Récupération des données de géolocalisation
			WDGRESTAPI_Lib_Logs::log( 'check_geolocation_data >> get_geolocation_data' );
			$geolocation_data = WDGRESTAPI_Lib_Geolocation::get_geolocation_data( $geolocation_address );

			// Si l'API a retourné des valeurs exploitables, on les enregistre
			if ( $geolocation_data != false && !is_wp_error( $geolocation_data ) ) {
				$this->set_metadata( 'geolocation_addr_md5', $geolocation_addr_md5 );
				$this->set_property( 'geolocation', $geolocation_data['lat'] . ',' . $geolocation_data['long'] );
				$this->save();
			}

		}
	}
	
	/**
	* Définit le nom de l'organisation
	 * @param string $new_name
	*/
	public function set_name( $new_name ) {
		$this->loaded_data->name = $new_name;
	}
	
	/**
	 * Retourne la liste de toutes les organisations
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Organization::$entity_type );
		$query = "SELECT id, wpref, name, email FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
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
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'name'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'representative_function'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'creation_date'			=> array( 'type' => 'date', 'other' => '' ),
		'strong_authentication'	=> array( 'type' => 'bool', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'legalform'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'idnumber'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'rcs'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'capital'				=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'ape'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'vat'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'fiscal_year_end_month'	=> array( 'type' => 'varchar', 'other' => '' ),
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
		'viadeo_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'metadata'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'geolocation'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_Organization::$entity_type, WDGRESTAPI_Entity_Organization::$db_properties );
	}
	
}