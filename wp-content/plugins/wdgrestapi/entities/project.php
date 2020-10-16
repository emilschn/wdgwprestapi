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
	
	public function save() {
		parent::save();
		WDGRESTAPI_Lib_GoogleAPI::set_project_values( $this->loaded_data->id, $this->loaded_data );
		WDGRESTAPI_Entity_Cache::delete_by_name_like( '/projects' );
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
	
	public function get_loaded_data( $expand = TRUE, $with_investments = FALSE, $with_organization = FALSE, $with_poll_answers = FALSE, $authorized_client_id_string = FALSE ) {
		$buffer = parent::get_loaded_data();
		if ( $expand && !empty( $buffer ) ) {
			$buffer = WDGRESTAPI_Entity_Project::expand_single_data( $buffer );
		}
		$buffer = WDGRESTAPI_Entity_Project::standardize_data( $buffer );
		
		if ( $with_investments ) {
			$investments_list = WDGRESTAPI_Entity_Investment::list_get( FALSE, FALSE, $buffer->id );
			$buffer->investments = $investments_list;
			
			$investment_drafts_list = WDGRESTAPI_Entity_InvestmentDraft::list_get( $authorized_client_id_string, $buffer->id );
			$buffer->investment_drafts = $investment_drafts_list;
		}
		
		if ( $with_organization ) {
			$organizations_linked = WDGRESTAPI_Entity_ProjectOrganization::get_list_by_project_id( $buffer->id );
			$orga_linked_id = 0;
			foreach ( $organizations_linked as $project_orga_link ) {
				if ( $project_orga_link->type == WDGRESTAPI_Entity_ProjectOrganization::$link_type_manager ) {
					$orga_linked_id = $project_orga_link->id_organization;
				}
			}
			$organization_data = FALSE;
			if ( $orga_linked_id > 0 ) {
				$organization_item = new WDGRESTAPI_Entity_Organization( $orga_linked_id );
				$organization_data = $organization_item->get_loaded_data();
			}
			$buffer->organization = $organization_data;
		}
		
		if ( $with_poll_answers ) {
			if ( $authorized_client_id_string == FALSE ) {
				$authorized_client_id_string = '(' .$buffer->client_user_id. ')';
			}
			$poll_answers_list = WDGRESTAPI_Entity_PollAnswer::list_get( $authorized_client_id_string, FALSE, $buffer->id, FALSE );
			$buffer->poll_answers = $poll_answers_list;
		}
		
		return $buffer;
	}
	
	/**
	 * Refait un tour des données pour les retourner au meilleur format
	 * @param type $item
	 */
	public static function standardize_data( $item ) {
		if($item){
			$item->creation_date = WDGRESTAPI_Entity::standardize_date( $item->creation_date );
			$item->contract_start_date = WDGRESTAPI_Entity::standardize_date( $item->contract_start_date );
			$item->declarations_start_date = WDGRESTAPI_Entity::standardize_date( $item->declarations_start_date );
			$item->declarations_end_date = WDGRESTAPI_Entity::standardize_date( $item->declarations_end_date );
			$item->vote_start_datetime = WDGRESTAPI_Entity::standardize_datetime( $item->vote_start_datetime );
			$item->vote_end_datetime = WDGRESTAPI_Entity::standardize_datetime( $item->vote_end_datetime );
			$item->funding_start_datetime = WDGRESTAPI_Entity::standardize_datetime( $item->funding_start_datetime );
			$item->funding_end_datetime = WDGRESTAPI_Entity::standardize_datetime( $item->funding_end_datetime );
		}
		return $item;
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
	public function get_declarations( $is_data_restricted_to_entity, $with_links = FALSE ) {
		$buffer = WDGRESTAPI_Entity_Declaration::list_get_by_project_id( $this->loaded_data->id, $is_data_restricted_to_entity, $with_links );
		return $buffer;
	}
	
	public function get_adjustments( $with_links = FALSE ) {
		$buffer = WDGRESTAPI_Entity_Adjustment::list_get_by_project_id( $this->loaded_data->id, $with_links );
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
	
	public function get_contract_models_data() {
		return WDGRESTAPI_Entity_ContractModel::get_by_entity_id( 'project', $this->loaded_data->id );
	}
	
	public function get_contracts_data() {
		return WDGRESTAPI_Entity_Contract::list_get( 'project', $this->loaded_data->id );
	}
	
	public function get_investment_contracts_data() {
		return WDGRESTAPI_Entity_InvestmentContract::list_get_by_project( $this->loaded_data->id );
	}
	
	public function get_emails_data() {
		return WDGRESTAPI_Entity_Email::list_get_by_project( $this->loaded_data->id );
	}
	
	public static function expand_single_data( $item ) {
		// Augmentation des données retournées avec des informations statiques
		$project_roideclarations = WDGRESTAPI_Entity_Declaration::list_get_by_project_id( $item->id );
		$project_declarations_done_count = 0;
		$project_declarations_turnover_total = 0;
		$project_declarations_royalties_total = 0;
		$project_first_declaration = FALSE;
		$project_last_declaration = FALSE;
		if ( $project_roideclarations ) {
			$project_first_declaration = $project_roideclarations[0];
			$project_last_declaration = end( $project_roideclarations );
			foreach ( $project_roideclarations as $roideclaration ) {
				if ( $roideclaration[ 'status' ] == WDGRESTAPI_Entity_Declaration::$status_finished ) {
					$project_declarations_done_count++;
					$project_declarations_turnover_total += $roideclaration[ 'turnover_total' ];
					$project_declarations_royalties_total += $roideclaration[ 'amount' ];
				}
			}
		}

		$item->lw_amount_wallet = 0;
		$item->lw_authenticated = 0;
		$item->lw_sepa_signed = 0;
		$item->roi_declarations_done_count = $project_declarations_done_count;
		$item->roi_declarations_total = count( $project_roideclarations );
		$item->turnover_total = $project_declarations_turnover_total;
		$item->royalties_total = $project_declarations_royalties_total;
		$item->declarations_list = $project_roideclarations;
		if ( $project_first_declaration ) {
			$item->declarations_start_date = $project_first_declaration->date_due;
		}
		if ( $project_last_declaration ) {
			$item->declarations_end_date = $project_last_declaration->date_due;
		} else {
			$item->declarations_end_date = FALSE;
		}

		// Augmentation de la liste des données avec données éditables d'organisation
		$project_organizations = WDGRESTAPI_Entity_ProjectOrganization::get_list_by_project_id( $item->id );
		$project_organization = new WDGRESTAPI_Entity_Organization( $project_organizations[0]->id_organization );
		$project_organization_data = $project_organization->get_loaded_data();

		$item->organization_name = $project_organization_data->name;
		$item->organization_legalform = $project_organization_data->legalform;
		$item->organization_capital = $project_organization_data->capital;
		$item->organization_idnumber = $project_organization_data->idnumber;
		$item->organization_vat = $project_organization_data->vat;
		$item->organization_address = $project_organization_data->address;
		$item->organization_postalcode = $project_organization_data->postalcode;
		$item->organization_city = $project_organization_data->city;
		$item->organization_country = $project_organization_data->country;
		$item->organization_rcs = $project_organization_data->rcs;
		$item->organization_representative_firstname = 'TODO';
		$item->organization_representative_lastname = 'TODO';
		$item->organization_representative_function = $project_organization_data->representative_function;
		$item->organization_description = $project_organization_data->description;
		$item->organization_fiscal_year_end_month = $project_organization_data->fiscal_year_end_month;
		$item->organization_accounting_contact = 'TODO';
		$item->organization_quickbooks_id = 'TODO';
		$item->organization_iban = $project_organization_data->iban;
		$item->organization_bic = $project_organization_data->bic;
		$item->organization_document_kbis = 'TODO';
		$item->organization_document_rib = 'TODO';
		$item->organization_document_status = 'TODO';
		$item->organization_document_id = 'TODO';
		$item->organization_document_home = 'TODO';
		
		return $item;
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
			if ( !empty( $result ) ) {
				$result = WDGRESTAPI_Entity_Project::expand_single_data( $result );
				$result = WDGRESTAPI_Entity_Project::standardize_data( $result );
			}
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
	 * Retourne les statistiques de la page d'accueil du site
	 */
	public static function get_home_stats() {
		global $wpdb;
		$buffer = array();

		// Somme des montants collectés
		//SELECT SUM(amount_collected) FROM `wdgrestapi1524_entity_project` WHERE status = 'funded' OR status = 'closed'
		$project_table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = 'SELECT SUM(amount_collected) AS amount FROM ' .$project_table_name . ' WHERE status=\'funded\' OR status=\'closed\'';
		$result_amount_collected = $wpdb->get_results( $query );
		$buffer[ 'amount_collected' ] = $result_amount_collected[ 0 ]->amount;

		// Nombre d'investisseurs
		//SELECT COUNT(DISTINCT user_id) FROM `wdgrestapi1524_entity_investment` investment LEFT JOIN `wdgrestapi1524_entity_project` project ON project.id = investment.project WHERE investment.status = 'publish' AND (project.status = 'funded' OR project.status = 'closed') 
		$investment_table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Investment::$entity_type );
		$query = 'SELECT COUNT(DISTINCT user_id) AS nb_investors FROM ' .$investment_table_name . ' investment';
		$query .= ' LEFT JOIN ' .$project_table_name. ' project';
		$query .= ' ON project.id = investment.project';
		$query .= ' WHERE investment.status = \'publish\'';
		$query .= ' AND (project.status=\'funded\' OR project.status=\'closed\')';
		$result_count_investors = $wpdb->get_results( $query );
		$buffer[ 'count_investors' ] = $result_count_investors[ 0 ]->nb_investors;

		// Nombre de projets qui versent des royalties
		//SELECT COUNT(DISTINCT id_project) FROM `wdgrestapi1524_entity_declaration` decla LEFT JOIN `wdgrestapi1524_entity_project` project ON project.id = decla.id_project WHERE decla.amount > 0 AND (project.status = 'funded' OR project.status = 'closed') 
		$declaration_table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Declaration::$entity_type );
		$query = 'SELECT COUNT(DISTINCT id_project) AS nb_projects FROM ' .$declaration_table_name . ' declaration';
		$query .= ' LEFT JOIN ' .$project_table_name. ' project';
		$query .= ' ON project.id = declaration.id_project';
		$query .= ' WHERE declaration.amount > 0';
		$query .= ' AND (project.status=\'funded\')';
		$result_count_royaltying_projects = $wpdb->get_results( $query );
		$buffer[ 'royaltying_projects' ] = $result_count_royaltying_projects[ 0 ]->nb_projects;

		return $buffer;
	}

	/**
	 * Retourne la liste des projets à chercher
	 */
	public static function get_search_list() {
		global $wpdb;

		// 
		$project_table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$organization_table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Organization::$entity_type );
		$project_organization_table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_ProjectOrganization::$entity_type );
		$query = '
SELECT project.wpref as wpref, project.name as name, project.url as url, organization.name as organization_name FROM ' .$project_table_name . ' project
LEFT JOIN ' .$project_organization_table_name. ' project_organization
ON project.id = project_organization.id_project
LEFT JOIN ' .$organization_table_name. ' organization
ON organization.id = project_organization.id_organization
WHERE status=\''. self::$status_vote .'\' OR status=\''. self::$status_collecte .'\' OR status=\''. self::$status_funded .'\' OR status=\''. self::$status_closed .'\' OR status=\''. self::$status_archive .'\'
';

		$result_project_list = $wpdb->get_results( $query );

		return $result_project_list;
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
				$buffer[ 'funded_amount' ] += $result->amount_collected;
				$buffer[ 'statuses' ][ 'closed' ][ 'count' ]++;
				$declarations = WDGRESTAPI_Entity_Declaration::list_get_by_project_id( $result->id );
				foreach ( $declarations as $declaration ) {
					$buffer[ 'royalties_amount' ] += $declaration->amount;
				}
			}
			if ( $result->status == WDGRESTAPI_Entity_Project::$status_funded ) {
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
	 * @param type $organization_email
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
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT', 'gs_col_index' => 1 ),
		'wpref'					=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 2 ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'creation_date'			=> array( 'type' => 'date', 'other' => '', 'gs_col_index' => 3 ),
		'name'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 4 ),
		'url'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 5 ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 6 ),
		'description'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 7 ),
		'can_go_next'			=> array( 'type' => 'bool', 'other' => 'NOT NULL', 'gs_col_index' => 8 ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'category'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'impacts'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'partners'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'tousnosprojets'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'amount_collected'		=> array( 'type' => 'float', 'other' => 'NOT NULL', 'gs_col_index' => 9 ),
		'roi_percent_estimated'	=> array( 'type' => 'float', 'other' => 'NOT NULL', 'gs_col_index' => 10 ),
		'roi_percent'			=> array( 'type' => 'float', 'other' => 'NOT NULL', 'gs_col_index' => 11 ),
		'estimated_budget_file'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'funding_duration'		=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 12 ),
		'declaration_periodicity'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 13 ),
		'goal_minimum'			=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 14 ),
		'goal_maximum'			=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 15 ),
		'yield_for_investors'	=> array( 'type' => 'float', 'other' => 'NOT NULL', 'gs_col_index' => 16 ),
		'maximum_profit'		=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 17 ),
		'maximum_profit_precision'		=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 18 ),
		'minimum_profit'		=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 19 ),
		'contract_start_date'	=> array( 'type' => 'date', 'other' => '', 'gs_col_index' => 20 ),
		'contract_start_date_is_undefined'	=> array( 'type' => 'bool', 'other' => '' ),
		'declarations_start_date'	=> array( 'type' => 'date', 'other' => '', 'gs_col_index' => 21 ),
		'spendings_description'	=> array( 'type' => 'longtext', 'other' => '', 'gs_col_index' => 22 ),
		'earnings_description'	=> array( 'type' => 'longtext', 'other' => '', 'gs_col_index' => 23 ),
		'simple_info'			=> array( 'type' => 'longtext', 'other' => '', 'gs_col_index' => 24 ),
		'detailed_info'			=> array( 'type' => 'longtext', 'other' => '', 'gs_col_index' => 25 ),
		'estimated_turnover'	=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 26 ),
		'blank_contract_file'	=> array( 'type' => 'longtext', 'other' => '' ),
		'vote_start_datetime'	=> array( 'type' => 'datetime', 'other' => '', 'gs_col_index' => 27 ),
		'vote_end_datetime'		=> array( 'type' => 'datetime', 'other' => '', 'gs_col_index' => 28 ),
		'vote_count'			=> array( 'type' => 'int', 'other' => '', 'gs_col_index' => 29 ),
		'vote_invest_amount'	=> array( 'type' => 'float', 'other' => '', 'gs_col_index' => 30 ),
		'funding_start_datetime'	=> array( 'type' => 'datetime', 'other' => '', 'gs_col_index' => 31 ),
		'funding_end_datetime'	=> array( 'type' => 'datetime', 'other' => '', 'gs_col_index' => 32 ),
		'investments_count'		=> array( 'type' => 'int', 'other' => '', 'gs_col_index' => 33 ),
		'minimum_costs_to_organization'	=> array( 'type' => 'float', 'other' => '', 'gs_col_index' => 34 ),
		'costs_to_organization'	=> array( 'type' => 'float', 'other' => '', 'gs_col_index' => 35 ),
		'costs_to_investors'	=> array( 'type' => 'float', 'other' => '', 'gs_col_index' => 36 ),
		'turnover_per_declaration'	=> array( 'type' => 'int', 'other' => '', 'gs_col_index' => 37 ),
		'employees_number'		=> array( 'type' => 'int', 'other' => '', 'gs_col_index' => 38 ),
		'team_contacts'			=> array( 'type' => 'longtext', 'other' => '', 'gs_col_index' => 39 ),
		'minimum_goal_display'	=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 40 ),
		'common_goods_turnover_percent'	=> array( 'type' => 'float', 'other' => '', 'gs_col_index' => 41 ),
		'product_type'			=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 42 ),
		'acquisition'			=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 43 )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}