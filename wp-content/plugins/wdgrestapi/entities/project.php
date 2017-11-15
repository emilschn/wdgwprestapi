<?php
class WDGRESTAPI_Entity_Project extends WDGRESTAPI_Entity {
	public static $entity_type = 'project';
	
	public function __construct( $id = FALSE, $wpref = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Project::$entity_type, WDGRESTAPI_Entity_Project::$db_properties );
		if ( $wpref != FALSE ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Project::$entity_type );
			$query = 'SELECT * FROM ' .$table_name. ' WHERE wpref='.$wpref;
			$this->loaded_data = $wpdb->get_row( $query );
		}
	}
	
	/**
	 * Retourne la liste des déclarations liées à un projet
	 * @return array
	 */
	public function get_declarations() {
		$buffer = WDGRESTAPI_Entity_Declaration::list_get_by_project_id( $this->loaded_data->id );
		return $buffer;
	}
	
	/**
	 * Récupération des données de royalties concernant un projet
	 * @return string
	 */
	public function get_royalties_data() {
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site( 'get_royalties_by_project', $this->loaded_data->wpref );
		return $buffer;
	}
	
	/**
	 * Récupération de la liste des votes d'un projet
	 * TODO !
	 * @return array
	 */
	public function get_votes_data() {
		$buffer = array(
			array(
				'date'			=> '2017-10-10 20:12:12',
				'id_user'		=> 10,
				'email_user'	=> 'test1@temp.fr',
				'validate'		=> 1,
				'amount'		=> 25
			),
			array(
				'date'			=> '2017-09-10 20:12:12',
				'id_user'		=> 11,
				'email_user'	=> 'test2@temp.fr',
				'validate'		=> 1,
				'amount'		=> 250
			),
			array(
				'date'			=> '2017-09-10 08:12:12',
				'id_user'		=> 12,
				'email_user'	=> 'test3@temp.fr',
				'validate'		=> 0,
				'amount'		=> 15
			),
			array(
				'date'			=> '2017-10-01 00:12:12',
				'id_user'		=> 13,
				'email_user'	=> 'test4@temp.fr',
				'validate'		=> 1,
				'amount'		=> 2500
			),
		);
		return $buffer;
	}
	
	/**
	 * Récupération de la liste des investissements d'un projet
	 * TODO !
	 * @return array
	 */
	public function get_investments_data() {
		$buffer = array(
			array(
				'date'			=> '2017-10-10 20:12:12',
				'id_user'		=> 10,
				'email_user'	=> 'test1@temp.fr',
				'amount'		=> 250
			),
			array(
				'date'			=> '2017-09-10 20:12:12',
				'id_user'		=> 11,
				'email_user'	=> 'test2@temp.fr',
				'amount'		=> 2500
			),
			array(
				'date'			=> '2017-09-10 08:12:12',
				'id_user'		=> 12,
				'email_user'	=> 'test3@temp.fr',
				'amount'		=> 111
			),
			array(
				'date'			=> '2017-10-01 00:12:12',
				'id_user'		=> 13,
				'email_user'	=> 'test4@temp.fr',
				'amount'		=> 222
			),
		);
		return $buffer;
	}
	
	/**
	 * Retourne la liste de tous les projets
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Project::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		$results = $wpdb->get_results( $query );
		
		foreach ( $results as $result ) {
			$result->lw_amount_wallet = 'TODO';
			$result->lw_authenticated = 'TODO';
			$result->lw_sepa_signed = 'TODO';
			$result->roi_declarations_done_count = 'TODO';
			$result->roi_declarations_total = 'TODO';
			$result->turnover_total = 'TODO';
			$result->royalties_total = 'TODO';
			$result->declarations_start_date = 'TODO';
			$result->declarations_end_date = 'TODO';
			$result->organization_name = 'TODO';
			$result->organization_legalform = 'TODO';
			$result->organization_capital = 'TODO';
			$result->organization_idnumber = 'TODO';
			$result->organization_vat = 'TODO';
			$result->organization_address = 'TODO';
			$result->organization_postalcode = 'TODO';
			$result->organization_city = 'TODO';
			$result->organization_country = 'TODO';
			$result->organization_rcs = 'TODO';
			$result->organization_representative_firstname = 'TODO';
			$result->organization_representative_lastname = 'TODO';
			$result->organization_representative_function = 'TODO';
			$result->organization_description = 'TODO';
			$result->organization_fiscal_year_end_month = 'TODO';
			$result->organization_accounting_contact = 'TODO';
			$result->organization_iban = 'TODO';
			$result->organization_bic = 'TODO';
			$result->organization_document_kbis = 'TODO';
			$result->organization_document_rib = 'TODO';
			$result->organization_document_status = 'TODO';
			$result->organization_document_id = 'TODO';
			$result->organization_document_home = 'TODO';
			$result->team_contacts = 'TODO';
		}
		
		return $results;
	}
	
	/**
	 * Retourne les statistiques qui concernent les projets
	 */
	public static function get_stats() {
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site( 'get_projects_stats' );
		return $buffer;
	}
	
	/**
	 * Crée les déclarations manquantes pour un projet spécifique
	 */
	public function create_missing_declarations() {
		//TODO
	}
	
	/**
	 * Envoie les documents sur Lemon Way
	 */
	public function post_documents() {
		//TODO
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	// Pour les types, voir WDGRESTAPI_Entity::get_mysqltype_from_wdgtype
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'wpref'					=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'creation_date'			=> array( 'type' => 'date', 'other' => '' ),
		'name'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'can_go_next'			=> array( 'type' => 'bool', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'category'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'impacts'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'amount_collected'		=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'roi_percent_estimated'	=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'roi_percent'			=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'estimated_budget_file'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'funding_duration'		=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'goal_minimum'			=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'goal_maximum'			=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'yield_for_investors'	=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'maximum_profit'		=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'minimum_profit'		=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'contract_start_date'	=> array( 'type' => 'date', 'other' => '' ),
		'spendings_description'	=> array( 'type' => 'longtext', 'other' => '' ),
		'earnings_description'	=> array( 'type' => 'longtext', 'other' => '' ),
		'simple_info'			=> array( 'type' => 'longtext', 'other' => '' ),
		'detailed_info'			=> array( 'type' => 'longtext', 'other' => '' ),
		'estimated_turnover'	=> array( 'type' => 'varchar', 'other' => '' ),
		'blank_contract_file'	=> array( 'type' => 'varchar', 'other' => '' ),
		'vote_start_datetime'	=> array( 'type' => 'datetime', 'other' => '' ),
		'vote_end_datetime'		=> array( 'type' => 'datetime', 'other' => '' ),
		'vote_count'			=> array( 'type' => 'int', 'other' => '' ),
		'vote_invest_amount'	=> array( 'type' => 'float', 'other' => '' ),
		'funding_start_datetime'	=> array( 'type' => 'datetime', 'other' => '' ),
		'funding_end_datetime'	=> array( 'type' => 'datetime', 'other' => '' ),
		'investments_count'		=> array( 'type' => 'int', 'other' => '' ),
		'costs_to_organization'	=> array( 'type' => 'float', 'other' => '' ),
		'costs_to_investors'	=> array( 'type' => 'float', 'other' => '' ),
		'employees_number'		=> array( 'type' => 'int', 'other' => '' ),
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_Project::$entity_type, WDGRESTAPI_Entity_Project::$db_properties );
	}
	
}