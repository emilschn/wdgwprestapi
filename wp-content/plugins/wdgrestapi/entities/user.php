<?php
class WDGRESTAPI_Entity_User extends WDGRESTAPI_Entity {
	public static $entity_type = 'user';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_User::$entity_type, WDGRESTAPI_Entity_User::$db_properties );
	}
	
	/**
	 * Override de la fonction de sauvegarde pour supprimer le cache des listes d'utilisateur
	 */
	public function save() {
		parent::save();
		WDGRESTAPI_Entity_Cache::delete_by_name_like( '/users' );
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
		$item->birthday_date = WDGRESTAPI_Entity::standardize_date( $item->birthday_date );
		return $item;
	}
	
	/**
	 * Retourne la liste des ROIs de cet utilisateur
	 * @return array
	 */
	public function get_rois() {
		$buffer = WDGRESTAPI_Entity_ROI::list_get_by_recipient_id( $this->loaded_data->id, WDGRESTAPI_Entity_ROI::$recipient_type_user );
		return $buffer;
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
		WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity_User::update_email > '.$new_email. ' != ' .$param_email );
		
		if ( empty( $new_email ) || $param_email == $new_email ) {
			$buffer['error'] = '404';
			$buffer['error-message'] = 'Invalid new email';
			
		} else {
			$posted_params = array(
				'new_email'	=> $new_email
			);
			$return = WDGRESTAPI_Entity::post_data_on_client_site( 'update_user_email', $param_email, $posted_params );
			WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity_User::update_email > $return : ' . $return);
			
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
		
		global $wpdb;
		$table_investments = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Investment::$entity_type );
		$count_query = "SELECT COUNT( DISTINCT user_id ) AS nb FROM " .$table_investments;
		$count_results = $wpdb->get_results( $count_query );
		$buffer->investors_count = $count_results[ 0 ]->nb;
		
		$count_multi_query = "SELECT COUNT( DISTINCT user_id ) AS nb FROM " .$table_investments. " GROUP BY user_id HAVING COUNT( user_id ) > 1";
		$count_multi_results = $wpdb->get_results( $count_multi_query );
		$buffer->investors_multi_count = $count_multi_results[ 0 ]->nb;

		return $buffer;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'wpref'					=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'gender'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'name'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'surname'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'surname_use'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'username'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'birthday_date'			=> array( 'type' => 'date', 'other' => '' ),
		'birthday_city'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'birthday_district'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'birthday_department'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'birthday_country'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'nationality'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'address_number'		=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'address_number_comp'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'address'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'postalcode'			=> array( 'type' => 'int', 'other' => '' ),
		'city'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'country'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'tax_country'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'phone_number'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'description'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'contact_if_deceased'	=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'bank_iban'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_bic'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_holdername'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_address'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_address2'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
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
		'signup_date'			=> array( 'type' => 'date', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_User::$entity_type, WDGRESTAPI_Entity_User::$db_properties );
	}
	
}