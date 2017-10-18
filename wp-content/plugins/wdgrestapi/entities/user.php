<?php
class WDGRESTAPI_Entity_User extends WDGRESTAPI_Entity {
	public static $entity_type = 'user';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_User::$entity_type, WDGRESTAPI_Entity_User::$db_properties );
	}
	
	/**
	 * Retourne la liste des ROIs de cet utilisateur
	 * @return array
	 */
	public function get_rois() {
		$buffer = WDGRESTAPI_Entity_ROI::list_get_by_user_id( $this->loaded_data->id );
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
	public static function list_get( $authorized_client_id_string, $add_organizations = FALSE, $full = FALSE, $input_link_to_project = FALSE ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_User::$entity_type );
		
		if ( !empty( $input_link_to_project ) ) {
			// TODO : changer requete pour faire liaison avec table votes et table investissements
			$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		} else {
			$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		}
		$results = $wpdb->get_results( $query );
		
		foreach ( $results as $result ) {
			$result->type = 'user';
			$result->is_project_manager = FALSE; // TODO
		}
		
		if ( $add_organizations ) {
			$list_organizations = WDGRESTAPI_Entity_Organization::list_get( $authorized_client_id_string, $input_link_to_project );
			foreach ( $list_organizations as $organization ) {
				$single_item = array(
					'id'			=> $organization->id,
					'email'			=> $organization->email,
					'type'			=> 'organization',
					'address'		=> $organization->address,
					'postalcode'	=> $organization->postalcode,
					'city'			=> $organization->city,
					'country'		=> $organization->country,
					'bank_owner'	=> $organization->bank_owner,
					'bank_address'	=> $organization->bank_address,
					'bank_iban'		=> $organization->bank_iban,
					'bank_bic'		=> $organization->bank_bic,
					'document_id'				=> 'TODO',
					'document_home'				=> 'TODO',
					'organization_name'			=> $organization->name,
					'organization_legalform'	=> $organization->legalform,
					'organization_capital'		=> $organization->capital,
					'organization_idnumber'		=> $organization->idnumber,
					'organization_vat'			=> $organization->vat,
					'organization_rcs'			=> $organization->rcs,
					'organization_representative_firstname'		=> 'TODO',
					'organization_representative_lastname'		=> 'TODO',
					'organization_representative_function'		=> $organization->representative_function,
					'organization_description'					=> 'TODO',
					'organization_fiscal_year_end_month'		=> $organization->fiscal_year_end_month,
					'organization_accounting_contact'			=> $organization->accounting_contact,
					'organization_document_kbis'				=> 'TODO',
					'organization_document_rib'					=> 'TODO',
					'organization_document_status'				=> 'TODO'
				);
				$single_item_object = json_decode( json_encode( $single_item ), FALSE );
				array_push( $results, $single_item_object );
			}
		}
		
		if ( $full ) {
			foreach ( $results as $result ) {
				$result->vote_count = 0;
				$result->invest_count = 0;
				$result->invest_amount = 0;
				$result->invest_amount_royalties = 0;
				$result->royalties_amount_received = 0;
				$result->lw_amount_wallet = 0;
			}
		}
		
		return $results;
	}
	
	/**
	 * Retourne les statistiques qui concernent les utilisateurs
	 */
	public static function get_stats() {
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site( 'get_users_stats' );
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
		'username'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'birthday_date'			=> array( 'type' => 'date', 'other' => '' ),
		'birthday_city'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'nationality'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'address'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'postalcode'			=> array( 'type' => 'int', 'other' => '' ),
		'city'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'country'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'phone_number'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_iban'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_bic'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_holdername'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_address'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bank_address2'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'document_id'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'document_home'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
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
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_User::$entity_type, WDGRESTAPI_Entity_User::$db_properties );
	}
	
}