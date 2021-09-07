<?php
class WDGRESTAPI_Entity_Organization extends WDGRESTAPI_Entity {
	public static $entity_type = 'organization';

	public function __construct($id = FALSE) {
		parent::__construct( $id, WDGRESTAPI_Entity_Organization::$entity_type, WDGRESTAPI_Entity_Organization::$db_properties );
	}

	/**
	 * Récupère un utilisateur à partir de son id WP
	 */
	public static function get_by_wpref($wpref) {
		global $wpdb;
		if ( empty( $wpdb ) ) {
			return FALSE;
		}
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = 'SELECT * FROM ' .$table_name. ' WHERE wpref='.$wpref;
		$result = $wpdb->get_row( $query );
		$orga = new WDGRESTAPI_Entity_Organization( $result->id );

		return $orga;
	}

	public function save() {
		parent::save();
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
	public function set_name($new_name) {
		$this->loaded_data->name = $new_name;
	}

	/**
	 * Retourne la liste des investissements de cette organisation
	 * @return array
	 */
	public function get_investments() {
		$buffer = WDGRESTAPI_Entity_Investment::get_list_by_user( $this->loaded_data->id, TRUE );

		return $buffer;
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
	 * Retourne la liste des transactions de cette organisation
	 */
	public function get_transactions() {
		if ( !empty( $this->loaded_data->gateway_list ) ) {
			return WDGRESTAPI_Entity_Transaction::list_get_by_organization_id( $this->loaded_data->id, json_decode( $this->loaded_data->gateway_list ) );
		}

		return FALSE;
	}

	/**
	 * Recherche un vIBAN existant
	 * Si pas trouvé, en crée un et le retourne
	 */
	public function get_viban() {
		$buffer = FALSE;
		$wdgrestapi = WDGRESTAPI::instance();
		$wdgrestapi->add_include_lib( 'gateways/lemonway' );
		$lw = WDGRESTAPI_Lib_Lemonway::instance();
		$gateway_list_decoded = json_decode( $this->loaded_data->gateway_list );
		if ( isset( $gateway_list_decoded->lemonway ) ) {
			$lw_wallet_id = $gateway_list_decoded->lemonway;
			$buffer = $lw->get_viban( $lw_wallet_id );
			if ( empty( $buffer ) ) {
				$create_result = $lw->create_viban( $lw_wallet_id );
				$buffer = $create_result;
				if ( !empty( $create_result ) ) {
					$buffer->DATA = $create_result->IBAN;
					$buffer->SWIFT = $create_result->BIC;
				}
			}
		}

		return $buffer;
	}

	/**
	 * Retourne l'identifiant de wallet selon le gateway
	 */
	public function get_wallet_id( $gateway ) {
		$gateway_list_decoded = json_decode( $this->loaded_data->gateway_list );
		if ( $gateway == 'lemonway' && isset( $gateway_list_decoded->lemonway ) ) {
			return $gateway_list_decoded->lemonway;
		}
		return FALSE;
	}

	/**
	 * Retourne la liste de toutes les organisations
	 * @return array
	 */
	public static function list_get($authorized_client_id_string, $offset = 0, $limit = FALSE, $input_link_to_project = FALSE) {
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

	public static function expand_data($item) {
		$item->mandate_file_url = '';
		$mandate_file = WDGRESTAPI_Entity_File::get_single( self::$entity_type, $item->id, 'mandate' );
		if ( !empty( $mandate_file ) ) {
			$item_loaded_data = $mandate_file->get_loaded_data();
			$item->mandate_file_url = $item_loaded_data->url;
		}

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
		'employees_count'		=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 26 ),
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
		'postalcode'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 17 ),
		'city'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 18 ),
		'country'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 19 ),
		'accountant'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
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
		'geolocation'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'gateway_list'			=> array( 'type' => 'varchar', 'other' => '' ),
		'mandate_info'			=> array( 'type' => 'varchar', 'other' => '' )
	);

	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_Organization::$entity_type, WDGRESTAPI_Entity_Organization::$db_properties );
	}
}