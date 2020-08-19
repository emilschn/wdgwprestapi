<?php
class WDGRESTAPI_Entity_User extends WDGRESTAPI_Entity {
	public static $entity_type = 'user';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}

	/**
	 * Récupère un utilisateur à partir de son id WP
	 */
	public static function get_by_wpref( $wpref ) {
		global $wpdb;
		if ( empty( $wpdb ) ) {
			return FALSE;
		}
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = 'SELECT * FROM ' .$table_name. ' WHERE wpref='.$wpref;
		$result = $wpdb->get_row( $query );
		$user = new WDGRESTAPI_Entity_User( $result->id );
		return $user;
	}
	
	/**
	 * Override de la fonction de sauvegarde pour supprimer le cache des listes d'utilisateur
	 */
	public function save() {
		$buffer = parent::save();
		if ( !empty( $this->loaded_data->id ) ) {
			WDGRESTAPI_Lib_GoogleAPI::set_user_values( $this->loaded_data->id, $this->loaded_data );
		}
		WDGRESTAPI_Entity_Cache::delete_by_name_like( '/users' );
		return $buffer;
	}
	
	public function get_loaded_data( $with_links = FALSE ) {
		$buffer = parent::get_loaded_data();
		$buffer = WDGRESTAPI_Entity_User::standardize_data( $buffer );
		
		if ( !empty( $with_links ) ) {
			// Récupération des projets liés
			$project_list_by_user_id = WDGRESTAPI_Entity_ProjectUser::get_list_by_user_id( $this->loaded_data->id );
			$project_list = array();
			foreach ( $project_list_by_user_id as $link_item ) {
				$project = new WDGRESTAPI_Entity_Project( $link_item->id_project );
				$loaded_data = $project->get_loaded_data();
				array_push( 
					$project_list,
					array( 
						"id"	=> $loaded_data->id,
						"wpref"	=> $loaded_data->wpref,
						"name"	=> $loaded_data->name,
						"type"	=> $link_item->type
					)
				);
			}
			$buffer->projects = $project_list;
			
			// Récupération des organisations liées
			$organization_list_by_user_id = WDGRESTAPI_Entity_OrganizationUser::get_list_by_user_id( $this->loaded_data->id );
			$organization_list = array();
			foreach ( $organization_list_by_user_id as $link_item ) {
				$organization = new WDGRESTAPI_Entity_Organization( $link_item->id_organization );
				$loaded_data = $organization->get_loaded_data();
				array_push( 
					$organization_list,
					array( 
						"id"	=> $loaded_data->id,
						"wpref"	=> $loaded_data->wpref,
						"name"	=> $loaded_data->name,
						"type"	=> $link_item->type
					)
				);
			}
			$buffer->organizations = $organization_list;
		}
		
		return $buffer;
	}
	
	/**
	 * Refait un tour des données pour les retourner au meilleur format
	 * @param type $item
	 */
	public static function standardize_data( $item ) {
		if ( !empty( $item ) ) {
			$item->birthday_date = WDGRESTAPI_Entity::standardize_date( $item->birthday_date );
		}
		return $item;
	}
	
	/**
	 * Retourne la liste des investissements de cet utilisateur
	 * @return array
	 */
	public function get_investments( $input_sort = FALSE ) {
		if ( empty( $this->loaded_data->id ) ) {
			return FALSE;
		}
		
		$investments = WDGRESTAPI_Entity_Investment::get_list_by_user( $this->loaded_data->id );

		if ( empty( $input_sort ) ) {
			return $investments;
		}

		if ( $input_sort == 'project' ) {
			$investment_contracts = WDGRESTAPI_Entity_InvestmentContract::list_get_by_investor( $this->loaded_data->id, 'user' );
	
			$investment_contracts_by_subscription_id = array();
			foreach ( $investment_contracts as $investment_contract_item ) {
				$investment_contracts_by_subscription_id[ $investment_contract_item->subscription_id ] = $investment_contract_item;
			}
	
			$projects_by_id = array();
			foreach ( $investments as $investment_item ) {
				if ( $investment_item->status != 'publish' && $investment_item->status != 'pending' ) {
					continue;
				}

				if ( empty( $projects_by_id[ $investment_item->project ] ) ) {
					$projects_by_id[ $investment_item->project ] = array();
					$projects_by_id[ $investment_item->project ][ 'project_id' ] = $investment_item->project;
	
					// Données liées au projet
					$project_entity = new WDGRESTAPI_Entity_Project( $investment_item->project );
					$project_entity_data = $project_entity->get_loaded_data( FALSE );
					$projects_by_id[ $investment_item->project ][ 'project_wpref' ] = $investment_item->wpref;
					$projects_by_id[ $investment_item->project ][ 'project_name' ] = $project_entity_data->name;
					$projects_by_id[ $investment_item->project ][ 'project_status' ] = $project_entity_data->status;
					$projects_by_id[ $investment_item->project ][ 'project_amount' ] = $project_entity_data->amount_collected;
					$projects_by_id[ $investment_item->project ][ 'project_funding_end_date' ] = $project_entity_data->funding_end_datetime;
					$projects_by_id[ $investment_item->project ][ 'project_contract_start_date' ] = $project_entity_data->contract_start_date;
					$projects_by_id[ $investment_item->project ][ 'project_funding_duration' ] = $project_entity_data->funding_duration;
					$projects_by_id[ $investment_item->project ][ 'project_roi_percent' ] = $project_entity_data->roi_percent;
					$projects_by_id[ $investment_item->project ][ 'project_roi_percent_estimated' ] = $project_entity_data->roi_percent_estimated;
					$projects_by_id[ $investment_item->project ][ 'project_goal_maximum' ] = $project_entity_data->goal_maximum;
					$projects_by_id[ $investment_item->project ][ 'project_first_payment_date' ] = '';
					$projects_by_id[ $investment_item->project ][ 'project_url' ] = $project_entity_data->url;
					$projects_by_id[ $investment_item->project ][ 'project_estimated_turnover' ] = $project_entity_data->estimated_turnover;
					$projects_by_id[ $investment_item->project ][ 'project_estimated_turnover_unit' ] = '';

					$projects_by_id[ $investment_item->project ][ 'declarations' ] = WDGRESTAPI_Entity_Declaration::list_get_by_project_id( $investment_item->project, TRUE );

					$projects_by_id[ $investment_item->project ][ 'investments' ] = array();
				}

				$new_item = array();
	
				// Données intrinsèques à l'investissement
				$new_item[ 'id' ] = $investment_item->id;
				$new_item[ 'wpref' ] = $investment_item->wpref;
				$new_item[ 'amount' ] = $investment_item->amount;
				$new_item[ 'invest_datetime' ] = $investment_item->invest_datetime;
				$new_item[ 'status' ] = $investment_item->status;
				$new_item[ 'mean_payment' ] = $investment_item->mean_payment;
				
				// Données liées au contrat
				$new_item[ 'contract_status' ] = '';
				if ( !empty( $investment_contracts_by_subscription_id[ $investment_item->id ] ) ) {
					$new_item[ 'contract_status' ] = $investment_contracts_by_subscription_id[ $investment_item->id ]->status;
				}
	
				// Données liées aux royalties
				$new_item[ 'rois' ] = WDGRESTAPI_Entity_ROI::list_get_by_investment_wpref( $investment_item->wpref );
	
				array_push( $projects_by_id[ $investment_item->project ][ 'investments' ], $new_item );
			}

			// Re-liste des projets en tableau non-associatif
			$buffer = array();
			foreach ( $projects_by_id as $project_item ) {
				array_push( $buffer, $project_item );
			}
		}
		
		return $buffer;
	}
	
	/**
	 * Retourne la liste des contrats d'investissement de cet utilisateur
	 * @return array
	 */
	public function get_investment_contracts() {
		$buffer = FALSE;
		if ( !empty( $this->loaded_data->id ) ) {
			$buffer = WDGRESTAPI_Entity_InvestmentContract::list_get_by_investor( $this->loaded_data->id, 'user' );
		}
		return $buffer;
	}
	
	/**
	 * Retourne la liste des ROIs de cet utilisateur
	 * @return array
	 */
	public function get_rois() {
		$buffer = FALSE;
		if ( !empty( $this->loaded_data->id ) ) {
			$buffer = WDGRESTAPI_Entity_ROI::list_get_by_recipient_id( $this->loaded_data->id, WDGRESTAPI_Entity_ROI::$recipient_type_user );
		}
		return $buffer;
	}

	/**
	 * Retourne la liste des transactions de cette organisation
	 */
	public function get_transactions() {
		if ( !empty( $this->loaded_data->gateway_list ) ) {
			return WDGRESTAPI_Entity_Transaction::list_get_by_user_id( $this->loaded_data->id, json_decode( $this->loaded_data->gateway_list ) );
		}
		return FALSE;
	}
	
	/**
	 * Retourne la liste des actions effectuées par l'utilisateur
	 */
	public function get_activities() {
		$buffer = array(
			'projects' => array(
				array(
					'id'			=> 1,
					'name'			=> "Super projet",
					'vote_validate'		=> 0,
					'vote_environment'	=> 1,
					'vote_social'		=> 2,
					'vote_economy'		=> 3,
					'vote_risk'			=> 4,
					'vote_invest_sum'	=> 40,
					'invest_amount'		=> 50,
					'contract_url'		=> "https://www.wedogood.co",
				),
				array(
					'id'			=> 2,
					'name'			=> "Super projet 2",
					'vote_validate'		=> 1,
					'vote_environment'	=> 2,
					'vote_social'		=> 2,
					'vote_economy'		=> 1,
					'vote_risk'			=> 1,
					'vote_invest_sum'	=> 405,
					'invest_amount'		=> 520,
					'contract_url'		=> "https://www.wedogood.co",
				),
			)
		);
		return $buffer;
	}
	
	/**
	 * Récupération des données de royalties concernant un utilisateur
	 * @return string
	 */
	public static function get_royalties_data( $param_email ) {
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site( 'get_royalties_by_user', $param_email );
		return $buffer;
	}
	
	/**
	 * Met à jour l'e-mail de l'utilisateur
	 * @param string $param_email
	 * @param array $posted_array
	 * @return string
	 */
	public static function update_email( $param_email, $posted_array ) {
		$buffer = array();
		$new_email = $posted_array[ 'new_email' ];
		
		if ( empty( $new_email ) || $param_email == $new_email ) {
			$buffer['error'] = '404';
			$buffer['error-message'] = 'Invalid new email';
			
		} else {
			$posted_params = array(
				'new_email'	=> $new_email
			);
			$return = WDGRESTAPI_Entity::post_data_on_client_site( 'update_user_email', $param_email, $posted_params );
			
			if ( $return == 'success' ) {
				$buffer = 'success';
				
			} else {
				$buffer['error'] = '404';
				$buffer['error-message'] = $return;
			}
			
		}
		
		return $buffer;
	}
	
	/**
	 * Retourne la liste de tous les utilisateurs
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string, $offset = 0, $limit = FALSE, $full = FALSE, $input_link_to_project = FALSE ) {
		global $wpdb;
		if ( !isset( $wpdb ) ) {
			return FALSE;
		}

		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_User::$entity_type );
		
		if ( !empty( $input_link_to_project ) ) {
			// TODO : changer requete pour faire liaison avec table votes et table investissements
			$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string. " ORDER BY email ASC";
			$count_query = "SELECT COUNT(*) AS nb FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		} else {
			$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string. " ORDER BY email ASC";
			$count_query = "SELECT COUNT(*) AS nb FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		}
		
		// Gestion offset et limite
		if ( empty( $limit ) ) {
			$limit = 100;
		}
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
		foreach ( $results as $result ) {
			$result->type = 'user';
			$rand_project_manager = rand( 0, 20 );
			$result->is_project_manager = ( $rand_project_manager > 17 ); // TODO
			$result = WDGRESTAPI_Entity_Project::standardize_data( $result );
		}
		
		if ( $full ) {
			foreach ( $results as $result ) {
				$result->vote_count = rand( 0, 20 ); //TODO
				$result->invest_count = rand( 0, 30 ); //TODO
				$result->invest_amount = rand( 0, 20000 ); //TODO
				$result->invest_amount_royalties = rand( 0, 200 ); //TODO
				$result->royalties_amount_received = rand( 0, 700 ); //TODO
				$result->lw_amount_wallet = rand( 0, 500 ); //TODO
				$result->lw_wallet_authentication = 'todo'; //TODO
				$result->lw_iban_authentication = 'todo'; //TODO
			}
		}
		
		$count_results = $wpdb->get_results( $count_query );
		
		$buffer = array(
			'offset'	=> $offset,
			'limit'		=> $limit,
			'count'		=> count( $results ),
			'total'		=> $count_results[ 0 ]->nb,
			'results'	=> $results
		);
		
		return $buffer;
	}
	
	/**
	 * Retourne les statistiques qui concernent les utilisateurs
	 */
	public static function get_stats() {
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site( 'get_users_stats' );
		if ( empty( $buffer ) ) {
			return $buffer;
		}

		$buffer->investors_count = 0;
		$buffer->investors_multi_count = 0;
		
		global $wpdb;
		if ( isset( $wpdb ) ) {
			$table_investments = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Investment::$entity_type );
			$count_query = "SELECT COUNT( DISTINCT user_id ) AS nb FROM " .$table_investments;
			$count_results = $wpdb->get_results( $count_query );
			$buffer->investors_count = $count_results[ 0 ]->nb;
			
			$count_multi_query = "SELECT COUNT( DISTINCT user_id ) AS nb FROM " .$table_investments. " GROUP BY user_id HAVING COUNT( user_id ) > 1";
			$count_multi_results = $wpdb->get_results( $count_multi_query );
			$buffer->investors_multi_count = $count_multi_results[ 0 ]->nb;
		}

		return $buffer;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT', 'gs_col_index' => 1 ),
		'wpref'					=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 2 ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 3 ),
		'gender'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 4 ),
		'name'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 5 ),
		'surname'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 6 ),
		'surname_use'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 7 ),
		'username'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 8 ),
		'birthday_date'			=> array( 'type' => 'date', 'other' => '', 'gs_col_index' => 9 ),
		'birthday_city'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 10 ),
		'birthday_district'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 11 ),
		'birthday_department'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 12 ),
		'birthday_country'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 13 ),
		'nationality'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 14 ),
		'address_number'		=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 15 ),
		'address_number_comp'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 16 ),
		'address'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 17 ),
		'postalcode'			=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 18 ),
		'city'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 19 ),
		'country'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 20 ),
		'tax_country'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 21 ),
		'phone_number'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 22 ),
		'description'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 23 ),
		'contact_if_deceased'	=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 24 ),
		'bank_iban'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 25 ),
		'bank_bic'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 26 ),
		'bank_holdername'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 27 ),
		'bank_address'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 28 ),
		'bank_address2'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 29 ),
		'document_id'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'document_home'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'document_rib'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'authentification_mode'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'picture_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'website_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'twitter_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'facebook_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'linkedin_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'viadeo_url'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'activation_key'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'password'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'signup_date'			=> array( 'type' => 'date', 'other' => '' ),
		'royalties_notifications'=> array( 'type' => 'varchar', 'other' => '' ),
		'gateway_list'			=> array( 'type' => 'varchar', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_User::$entity_type, WDGRESTAPI_Entity_User::$db_properties );
	}
	
}