<?php
class WDGRESTAPI_Entity_Organization extends WDGRESTAPI_Entity {
	public static $entity_type = 'organization';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Organization::$entity_type, WDGRESTAPI_Entity_Organization::$db_properties );
	}
	
	public function save() {
		parent::save();
		WDGRESTAPI_Lib_GoogleAPI::set_organization_values( $this->loaded_data->id, $this->loaded_data );
		WDGRESTAPI_Entity_Cache::delete_by_name_like( '/organizations' );
		WDGRESTAPI_Entity_Cache::delete_by_name_like( '/projects' );
	}

	/**
	* Surcharge la fonction de récupération de données
	 * @return object
	*/
	public function get_loaded_data() {
		$this->check_geolocation_data();
		$buffer = parent::get_loaded_data();
		$buffer = WDGRESTAPI_Entity_Organization::expand_data( $buffer );
		return $buffer;
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
//		WDGRESTAPI_Lib_Logs::log( 'check_geolocation_data > ' . $geolocation_addr_md5 . ' | ' . $current_geolocation_addr_md5 );

		// Si la géolocalisation de l'organisation n'a pas été récupérée
			// ou si le nouveau MD5 de l'adresse est différent de celui qui a déjà été enregistré
		if ( empty( $loaded_data->geolocation ) || $current_geolocation_addr_md5 != $geolocation_addr_md5 ) {

			// Récupération des données de géolocalisation
//			WDGRESTAPI_Lib_Logs::log( 'check_geolocation_data >> get_geolocation_data' );
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
	 * Retourne la liste des contrats d'investissement de cette organisation
	 * @return array
	 */
	public function get_investment_contracts() {
		$buffer = WDGRESTAPI_Entity_InvestmentContract::list_get_by_investor( $this->loaded_data->id, 'organization' );
		return $buffer;
	}
	
	/**
	 * Retourne la liste des ROIs de cette organisation
	 * @return array
	 */
	public function get_rois() {
		$buffer = WDGRESTAPI_Entity_ROI::list_get_by_recipient_id( $this->loaded_data->id, WDGRESTAPI_Entity_ROI::$recipient_type_orga );
		return $buffer;
	}
	
	/**
	 * Retourne la liste de toutes les organisations
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string, $offset = 0, $limit = FALSE, $input_link_to_project = FALSE ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Organization::$entity_type );
		if ( !empty( $input_link_to_project ) ) {
			// TODO : changer requete pour faire liaison avec table votes et table investissements
			$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		} else {
			$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		}
		
		// Gestion offset et limite
		if ( $offset > 0 || !empty( $limit ) ) {
			$query .= " LIMIT ";
			
			if ( $offset > 0 ) {
				$query .= $offset . ", ";
				if ( empty( $limit ) ) {
					$query .= "0";
				}
			}
			if ( !empty( $limit ) ) {
				$query .= $limit;
			}
		}
		
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	public static function expand_data( $item ) {
		$rand_project_manager = rand( 0, 20 );
		$item->is_project_manager = ( $rand_project_manager > 17 ); // TODO
		$item->representative_firstname = 'John'; // TODO
		$item->representative_lastname = 'Doe'; // TODO
		$item->accounting_contact = 'TODO'; // TODO
		$item->invest_count = rand( 0, 30 ); //TODO
		$item->invest_amount = rand( 0, 20000 ); //TODO
		$item->invest_amount_royalties = rand( 0, 200 ); //TODO
		$item->royalties_amount_received = rand( 0, 700 ); //TODO
		$item->lw_amount_wallet = rand( 0, 500 ); //TODO
		$item->lw_wallet_authentication = 'todo'; //TODO
		$item->lw_iban_authentication = 'todo'; //TODO
		
		return $item;
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
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT', 'gs_col_index' => 1 ),
		'wpref'					=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 2 ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'name'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 3 ),
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 4 ),
		'description'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 5 ),
		'representative_function'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 6 ),
		'creation_date'			=> array( 'type' => 'date', 'other' => '' ),
		'strong_authentication'	=> array( 'type' => 'bool', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'legalform'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 7 ),
		'idnumber'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 8 ),
		'rcs'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 9 ),
		'capital'				=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 10 ),
		'ape'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 11 ),
		'vat'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 12 ),
		'fiscal_year_end_month'	=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 13 ),
		'address_number'		=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 14 ),
		'address_number_comp'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 15 ),
		'address'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 16 ),
		'postalcode'			=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 17 ),
		'city'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 18 ),
		'country'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 19 ),
		'bank_owner'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 20 ),
		'bank_address'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 21 ),
		'bank_address2'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 22 ),
		'bank_iban'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 23 ),
		'bank_bic'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 24 ),
		'document_id'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'document_home'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'document_rib'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'document_kbis'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'document_status'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'id_quickbooks'			=> array( 'type' => 'id', 'other' => '', 'gs_col_index' => 25 ),
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
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_Organization::$entity_type, WDGRESTAPI_Entity_Organization::$db_properties );
	}
	
}