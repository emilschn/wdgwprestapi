<?php
class WDGRESTAPI_Entity_Project extends WDGRESTAPI_Entity {
	public static $entity_type = 'project';
	
	public static $status_preparing = 'preparing';
	public static $status_validated = 'validated';
	public static $status_preview = 'preview';
	public static $status_vote = 'vote';
	public static $status_collecte = 'collecte';
	public static $status_funded = 'funded';
	public static $status_closed = 'closed';
	public static $status_archive = 'archive';
	
	public function __construct( $id = FALSE, $wpref = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Project::$entity_type, WDGRESTAPI_Entity_Project::$db_properties );
		if ( $wpref != FALSE ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Project::$entity_type );
			$query = 'SELECT * FROM ' .$table_name. ' WHERE wpref='.$wpref;
			$this->loaded_data = $wpdb->get_row( $query );
		}
	}
	
	public function set_property( $property_name, $property_value ) {
		parent::set_property( $property_name, $property_value );
		if ( $property_name == 'url' ) {
			$posted_params = array(
				'new_url'	=> $property_value
			);
			WDGRESTAPI_Entity::post_data_on_client_site( 'set_project_url', $this->loaded_data->wpref, $posted_params );
		}
	}
	
	/**
	 * Retourne les données du statut du projet
	 */
	public function get_status() {
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site( 'get_status_by_project', $this->loaded_data->wpref );
		return $buffer;
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
			$project_roideclarations = WDGRESTAPI_Entity_Declaration::list_get_by_project_id( $result->id );
			$project_declarations_done_count = 0;
			$project_declarations_turnover_total = 0;
			$project_declarations_royalties_total = 0;
			$project_first_declaration = $project_roideclarations[0];
			$project_last_declaration = end( $project_roideclarations );
			foreach ( $project_roideclarations as $roideclaration ) {
				if ( $roideclaration[ 'status' ] == WDGRESTAPI_Entity_Declaration::$status_finished ) {
					$project_declarations_done_count++;
					$project_declarations_turnover_total += $roideclaration[ 'turnover_total' ];
					$project_declarations_royalties_total += $roideclaration[ 'amount' ];
				}
			}
			
			$project_organizations = WDGRESTAPI_Entity_ProjectOrganization::get_list_by_project_id( $result->id );
			$project_organization = new WDGRESTAPI_Entity_Organization( $project_organizations[0]->id_organization );
			$project_organization_data = $project_organization->get_loaded_data();
			
			$result->lw_amount_wallet = 0;
			$result->lw_authenticated = 0;
			$result->lw_sepa_signed = 0;
			$result->roi_declarations_done_count = $project_declarations_done_count;
			$result->roi_declarations_total = count( $project_roideclarations );
			$result->turnover_total = $project_declarations_turnover_total;
			$result->royalties_total = $project_declarations_royalties_total;
			$result->declarations_start_date = $project_first_declaration->date_due;
			$result->declarations_end_date = $project_last_declaration->date_due;
			$result->organization_name = $project_organization_data->name;
			$result->organization_legalform = $project_organization_data->legalform;
			$result->organization_capital = $project_organization_data->capital;
			$result->organization_idnumber = $project_organization_data->idnumber;
			$result->organization_vat = $project_organization_data->vat;
			$result->organization_address = $project_organization_data->address;
			$result->organization_postalcode = $project_organization_data->postalcode;
			$result->organization_city = $project_organization_data->city;
			$result->organization_country = $project_organization_data->country;
			$result->organization_rcs = $project_organization_data->rcs;
			$result->organization_representative_firstname = 'TODO';
			$result->organization_representative_lastname = 'TODO';
			$result->organization_representative_function = $project_organization_data->representative_function;
			$result->organization_description = 'TODO';
			$result->organization_fiscal_year_end_month = $project_organization_data->fiscal_year_end_month;
			$result->organization_accounting_contact = 'TODO';
			$result->organization_iban = $project_organization_data->iban;
			$result->organization_bic = $project_organization_data->bic;
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
	 * Retourne les catégories qu'on peut lier aux projets
	 */
	public static function get_categories() {
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site( 'get_projects_categories' );
		return $buffer;
	}
	
	/**
	 * Retourne les statistiques qui concernent les projets
	 */
	public static function get_stats() {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Project::$entity_type );
		$query = "SELECT * FROM " .$table_name;
		$results = $wpdb->get_results( $query );
		
		$date_now = new DateTime();
		
		$buffer = array();
		$buffer[ 'total' ] = count( $results );
		$buffer[ 'funded_amount' ] = 0;
		$buffer[ 'royalties_amount' ] = 0;
		$buffer[ 'statuses' ] = array();
		$status_list = array( 'posted', 'preparing', 'vote', 'funding', 'declaring', 'declaring_late', 'funded', 'closed', 'archive' );
		foreach ( $status_list as $status ) {
			$buffer[ 'statuses' ][ $status ] = array(
				'count'		=> 0,
				'percent'	=> 0
			);
		}
		
		foreach ( $results as $result ) {
			if ( $result->status == WDGRESTAPI_Entity_Project::$status_preparing ) {
				$buffer[ 'statuses' ][ 'posted' ][ 'count' ]++;
			}
			if ( $result->status == WDGRESTAPI_Entity_Project::$status_validated ) {
				$buffer[ 'statuses' ][ 'preparing' ][ 'count' ]++;
			}
			if ( $result->status == WDGRESTAPI_Entity_Project::$status_vote ) {
				$buffer[ 'statuses' ][ 'vote' ][ 'count' ]++;
			}
			if ( $result->status == WDGRESTAPI_Entity_Project::$status_collecte ) {
				$buffer[ 'statuses' ][ 'funding' ][ 'count' ]++;
			}
			if ( $result->status == WDGRESTAPI_Entity_Project::$status_funded ) {
				$buffer[ 'statuses' ][ 'declaring' ][ 'count' ]++;
			}
			if ( $result->status == WDGRESTAPI_Entity_Project::$status_closed ) {
				$buffer[ 'statuses' ][ 'closed' ][ 'count' ]++;
			}
			if ( $result->status == WDGRESTAPI_Entity_Project::$status_funded || $result->status == WDGRESTAPI_Entity_Project::$status_closed ) {
				$buffer[ 'funded_amount' ] += $result->amount_collected;
				$buffer[ 'statuses' ][ 'funded' ][ 'count' ]++;
				$declarations = WDGRESTAPI_Entity_Declaration::list_get_by_project_id( $result->id );
				foreach ( $declarations as $declaration ) {
					$buffer[ 'royalties_amount' ] += $declaration->amount;
					$date_due = new DateTime( $declaration->date_due );
					$date_interval = $date_now->diff( $date_due );
					if ( $declaration->status != WDGRESTAPI_Entity_Declaration::$status_finished && ( $date_due < $date_now || $date_interval->format( '%a' ) < 10 ) ) {
						$buffer[ 'statuses' ][ 'declaring_late' ][ 'count' ]++;
						break;
					}
				}
			}
			if ( $result->status == WDGRESTAPI_Entity_Project::$status_archive ) {
				$buffer[ 'statuses' ][ 'archive' ][ 'count' ]++;
			}
		}
		
		foreach ( $status_list as $status ) {
			$buffer[ 'statuses' ][ $status ][ 'percent' ] = $buffer[ 'statuses' ][ $status ][ 'count' ] / $buffer[ 'total' ] * 100;
			$buffer[ 'statuses' ][ $status ][ 'percent' ] = round( $buffer[ 'statuses' ][ $status ][ 'percent' ] * 100 ) / 100;
		}
		
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

	/**
	 * Crée un projet pour Equitearly, via la plateforme
	 * @param type $user_login
	 * @param type $user_password
	 * @param type $user_firstname
	 * @param type $user_lastname
	 * @param type $user_email
	 * @param type $organization_name
	 * @param type $organisation_email
	 * @param type $campaign_name
	 * @param type $equitearly_investment
	 * @param type $equitearly_charges
	 */
	public static function new_equitearly( $user_login, $user_password, $user_firstname, $user_lastname, $user_email, $organization_name, $organization_email, $campaign_name, $equitearly_investment, $equitearly_charges ) {
		$posted_params = array(
			'user_login'			=> $user_login,
			'user_password'			=> $user_password,
			'user_firstname'		=> $user_firstname,
			'user_lastname'			=> $user_lastname,
			'user_email'			=> $user_email,
			'organization_name'		=> $organization_name,
			'organization_email'	=> $organization_email,
			'campaign_name'			=> $campaign_name,
			'equitearly_investment'	=> $equitearly_investment,
			'equitearly_charges'	=> $equitearly_charges
		);
		return WDGRESTAPI_Entity::post_data_on_client_site( 'post_project_equitearly', '', $posted_params );
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
		'url'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'can_go_next'			=> array( 'type' => 'bool', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'category'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'impacts'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'partners'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
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