<?php
class WDGRESTAPI_Entity_Investment extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'investment';
	
	public static $status_init = 'init';
	public static $status_expired = 'expired';
	public static $status_started = 'started';
	public static $status_waiting_check = 'waiting-check';
	public static $status_waiting_wire = 'waiting-wire';
	public static $status_error = 'error';
	public static $status_canceled = 'canceled';
	public static $status_validated = 'validated';
	
	public function __construct( $id = FALSE, $token = FALSE, $wpref = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
		
		if ( empty( $id ) ) {
			
			$query = '';
			if ( !empty( $wpref ) ) {
				global $wpdb;
				$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
				$query = "SELECT * FROM " .$table_name. " WHERE wpref=" .$wpref;

			} else if ( !empty( $token ) ) {
			   global $wpdb;
			   $table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
			   $query = "SELECT * FROM " .$table_name. " WHERE token='" .$token. "'";
			}

			if ( !empty( $query ) ) {
				$row_data = $wpdb->get_row( $query );
				if ( !empty( $row_data ) ) {
					$this->loaded_data = $row_data;
				}
			}

		}
		
		if ( !empty( $this->loaded_data ) && $this->loaded_data->status == WDGRESTAPI_Entity_Investment::$status_init && $this->has_token_expired() ) {
			$this->loaded_data->status = WDGRESTAPI_Entity_Investment::$status_expired;
			$this->save();
		}
	}
	
	public function save() {
		parent::save();
	}
	
	/**
	 * Renvoie true si les données qui ont été transmises sont correctes
	 * @return boolean
	 */
	public function has_checked_properties() {
		$buffer = parent::has_checked_properties();
		
		// Champs de données de base
		if ( !WDGRESTAPI_Lib_Validator::is_email( $this->loaded_data->email ) ) {
			array_push( $this->properties_errors, __( "Le champ E-mail (email) n'est pas au bon format.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_gender( $this->loaded_data->gender ) ) {
			array_push( $this->properties_errors, __( "Le champ Genre (gender) n'est pas correct. Devrait valoir male ou female.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->firstname ) ) {
			array_push( $this->properties_errors, __( "Le champ Pr&eacute;nom (firstname) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->lastname ) ) {
			array_push( $this->properties_errors, __( "Le champ Nom (lastname) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		
		// Champs de naissance
		if ( !WDGRESTAPI_Lib_Validator::is_country_iso_code( $this->loaded_data->nationality ) ) {
			array_push( $this->properties_errors, __( "Le champ Nationalit&eacute; (nationality) n'est pas correct (format iso3166-1 alpha-2 attendu).", 'wdgrestapi' ) );
			$buffer = false;
		}
		$this->loaded_data->birthday_day = (int)$this->loaded_data->birthday_day;
		if ( !WDGRESTAPI_Lib_Validator::is_date_day( $this->loaded_data->birthday_day ) ) {
			array_push( $this->properties_errors, __( "Le champ Jour de naissance (birthday_day) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		$this->loaded_data->birthday_month = (int)$this->loaded_data->birthday_month;
		if ( !WDGRESTAPI_Lib_Validator::is_date_month( $this->loaded_data->birthday_month ) ) {
			array_push( $this->properties_errors, __( "Le champ Mois de naissance (birthday_month) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		$this->loaded_data->birthday_year = (int)$this->loaded_data->birthday_year;
		if ( !WDGRESTAPI_Lib_Validator::is_date_year( $this->loaded_data->birthday_year ) ) {
			array_push( $this->properties_errors, __( "Le champ Année de naissance (birthday_year) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_date( $this->loaded_data->birthday_day, $this->loaded_data->birthday_month, $this->loaded_data->birthday_year ) ) {
			array_push( $this->properties_errors, __( "La date de naissance n'est pas correcte.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_major( $this->loaded_data->birthday_day, $this->loaded_data->birthday_month, $this->loaded_data->birthday_year ) ) {
			array_push( $this->properties_errors, __( "La date de naissance ne correspond pas &agrave; quelqu'un de majeur.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->birthday_city ) ) {
			array_push( $this->properties_errors, __( "Le champ Ville de naissance (birthday_city) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		
		// Champs de coordonnées
		if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->address ) ) {
			array_push( $this->properties_errors, __( "Le champ Adresse (address) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_postalcode( $this->loaded_data->postalcode, $this->loaded_data->country ) ) {
			array_push( $this->properties_errors, __( "Le champ Code postal (postalcode) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->city ) ) {
			array_push( $this->properties_errors, __( "Le champ Ville (city) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_country_iso_code( $this->loaded_data->country ) ) {
			array_push( $this->properties_errors, __( "Le champ Pays (country) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		
		// Est-ce qu'il s'agit d'une organisation ?
		$this->loaded_data->is_legal_entity = (int)$this->loaded_data->is_legal_entity;
		if ( !WDGRESTAPI_Lib_Validator::is_boolean( $this->loaded_data->is_legal_entity ) ) {
			array_push( $this->properties_errors, __( "Le champ Validation entit&eacute; l&eacute;gale (is_legal_entity) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		
		// Les tests sur les champs d'organisation ne sont effectués que si il s'agit bien d'une organisation
		if ( $this->loaded_data->is_legal_entity == '1' ) {
			if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->legal_entity_form ) ) {
				array_push( $this->properties_errors, __( "Le champ Forme légale (legal_entity_form) n'est pas correct.", 'wdgrestapi' ) );
				$buffer = false;
			}
			if ( empty( $this->loaded_data->legal_entity_id ) ) {
				array_push( $this->properties_errors, __( "Le champ Numéro SIREN (legal_entity_id) n'est pas correct.", 'wdgrestapi' ) );
				$buffer = false;
			}
			if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->legal_entity_rcs ) ) {
				array_push( $this->properties_errors, __( "Le champ RCS (legal_entity_rcs) n'est pas correct.", 'wdgrestapi' ) );
				$buffer = false;
			}
			if ( !WDGRESTAPI_Lib_Validator::is_number( $this->loaded_data->legal_entity_capital ) ) {
				array_push( $this->properties_errors, __( "Le champ Capital (legal_entity_capital) n'est pas correct.", 'wdgrestapi' ) );
				$buffer = false;
			}
			if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->legal_entity_address ) ) {
				array_push( $this->properties_errors, __( "Le champ Adresse (legal_entity_address) n'est pas correct.", 'wdgrestapi' ) );
				$buffer = false;
			}
			if ( !WDGRESTAPI_Lib_Validator::is_postalcode( $this->loaded_data->legal_entity_postalcode, $this->loaded_data->legal_entity_nationality ) ) {
				array_push( $this->properties_errors, __( "Le champ Code postal (legal_entity_postalcode) n'est pas correct.", 'wdgrestapi' ) );
				$buffer = false;
			}
			if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->legal_entity_city ) ) {
				array_push( $this->properties_errors, __( "Le champ Ville (legal_entity_city) n'est pas correct.", 'wdgrestapi' ) );
				$buffer = false;
			}
			if ( !WDGRESTAPI_Lib_Validator::is_country_iso_code( $this->loaded_data->legal_entity_nationality ) ) {
				array_push( $this->properties_errors, __( "Le champ Nationalit&eacute; (legal_entity_nationality) n'est pas correct.", 'wdgrestapi' ) );
				$buffer = false;
			}
			
		}
		
		// Données relatives à l'investissement
		$this->loaded_data->project = (int)$this->loaded_data->project;
		if ( !WDGRESTAPI_Lib_Validator::is_number_positive_integer( $this->loaded_data->project ) ) {
			array_push( $this->properties_errors, __( "Le champ Projet (project) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		$this->loaded_data->amount = (int)$this->loaded_data->amount;
		if ( !WDGRESTAPI_Lib_Validator::is_number_positive_integer( $this->loaded_data->amount ) ) {
			array_push( $this->properties_errors, __( "Le champ Montant (amount) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_minimum_amount( $this->loaded_data->amount ) ) {
			array_push( $this->properties_errors, __( "Le champ Montant (amount) n'est pas suffisant.", 'wdgrestapi' ) );
			$buffer = false;
		}
		
		// Données relatives aux URLs de callback
		if ( !WDGRESTAPI_Lib_Validator::is_url( $this->loaded_data->redirect_url_ok ) ) {
			array_push( $this->properties_errors, __( "Le champ URL de retour OK (redirect_url_ok) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_url( $this->loaded_data->redirect_url_nok ) ) {
			array_push( $this->properties_errors, __( "Le champ URL de retour NOT OK (redirect_url_nok) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_url( $this->loaded_data->notification_url ) ) {
			array_push( $this->properties_errors, __( "Le champ URL de notification (notification_url) n'est pas correct.", 'wdgrestapi' ) );
			$buffer = false;
		}
		
		
		return $buffer;
	}
	
	/**
	 * Renvoie true sur le token d'investissement a expiré
	 */
	public function has_token_expired() {
	    date_default_timezone_set('Europe/Paris');
		$date_now = new DateTime();
		$date_expiration = new DateTime( $this->token_info->token_expiration );
		$buffer = ( $date_now > $date_expiration );
		return $buffer;
	}
	
	/**
	 * Retourne la liste de tous les investissements
	 * @return array
	 */
	public static function list_get( $start_date = FALSE, $end_date = FALSE, $project_id = FALSE ) {
		if ( !empty( $start_date ) && !empty( $end_date ) ) {
			$test_data = array(
				array(
					'id'			=> '1',
					'datetime'		=> '2017-09-19 17:08',
					'id_user'		=> '1',
					'id_project'	=> '12',
					'amount'		=> '10',
					'status'		=> WDGRESTAPI_Entity_Investment::$status_canceled
				),
				array(
					'id'			=> '2',
					'datetime'		=> '2017-09-19 17:10',
					'id_user'		=> '1',
					'id_project'	=> '12',
					'amount'		=> '10',
					'status'		=> WDGRESTAPI_Entity_Investment::$status_validated
				),
				array(
					'id'			=> '3',
					'datetime'		=> '2017-09-15 17:08',
					'id_user'		=> '2',
					'id_project'	=> '22',
					'amount'		=> '100',
					'status'		=> WDGRESTAPI_Entity_Investment::$status_validated
				),
				array(
					'id'			=> '4',
					'datetime'		=> '2017-09-14 14:08',
					'id_user'		=> '2',
					'id_project'	=> '22',
					'amount'		=> '100',
					'status'		=> WDGRESTAPI_Entity_Investment::$status_error
				),
				array(
					'id'			=> '5',
					'datetime'		=> '2017-09-03 13:01',
					'id_user'		=> '3',
					'id_project'	=> '14',
					'amount'		=> '190',
					'status'		=> WDGRESTAPI_Entity_Investment::$status_validated
				),
				array(
					'id'			=> '6',
					'datetime'		=> '2017-09-01 14:08',
					'id_user'		=> '4',
					'id_project'	=> '12',
					'amount'		=> '140',
					'status'		=> WDGRESTAPI_Entity_Investment::$status_validated
				),
				array(
					'id'			=> '7',
					'datetime'		=> '2017-08-11 19:27',
					'id_user'		=> '5',
					'id_project'	=> '15',
					'amount'		=> '15',
					'status'		=> WDGRESTAPI_Entity_Investment::$status_error
				),
				array(
					'id'			=> '8',
					'datetime'		=> '2017-08-01 07:08',
					'id_user'		=> '6',
					'id_project'	=> '22',
					'amount'		=> '1000',
					'status'		=> WDGRESTAPI_Entity_Investment::$status_canceled
				),
			);
			
			$results = array();
			$start_date->setTime( 0, 0, 1 );
			$end_date->setTime( 23, 59, 59 );
			foreach ( $test_data as $data ) {
				$invest_date = new DateTime( $data['datetime'] );
				if ( $start_date < $invest_date && $invest_date < $end_date ) {
					array_push( $results, $data );
				}
			}
			
		} else {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Investment::$entity_type );
			$user_table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_User::$entity_type );
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$query = "SELECT investment.*, user.email as email, user.name as firstname, user.surname as lastname, user.phone_number as phone_number,";
			$query .= " CONCAT(user.address_number, ' ', user.address) as address, user.postalcode as postalcode, user.city as city";
			$query .= " FROM " .$table_name. " investment";
			$query .= " LEFT JOIN " .$user_table_name. " user ON user.id = investment.user_id";
			$query .= " WHERE investment.client_user_id=" .$current_client->ID;
			if ( !empty( $project_id ) ) {
				$query .= " AND investment.project=" .$project_id;
			}
			
			$results = $wpdb->get_results( $query );
		}
		return $results;
	}

	public static function get_list_by_user( $id_user, $is_legal_entity = FALSE, $status = 'publish', $payment_key = FALSE, $payment_provider = FALSE ) {
		if ( empty( $id_user ) ) {
			return FALSE;
		}

		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;

		$query = "SELECT * FROM " .$table_name. " WHERE client_user_id=" .$current_client->ID;
		$query .= " AND user_id=" .$id_user;
		
		if ( $is_legal_entity ) {
			$query .= " AND is_legal_entity=1";
		} else {
			$query .= " AND is_legal_entity=0";
		}

		if ( !empty( $status ) ) {
			$query .= " AND status='" .$status. "'";
		}
		
		if ( !empty( $payment_key ) ) {
			$query .= " AND payment_key='" .$payment_key. "'";
		}
		
		if ( !empty( $payment_provider ) ) {
			$query .= " AND payment_provider='" .$payment_provider. "'";
		}
		
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	/**
	 * Retourne les statistiques qui concernent les investissements
	 */
	public static function get_stats() {
		$buffer = array();
		
		global $wpdb;
		$table_investments = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		
		$count_query_success = "SELECT COUNT(*) AS nb FROM " .$table_investments. " WHERE status LIKE 'publish'";
		$count_results_success = $wpdb->get_results( $count_query_success );
		$buffer[ 'total' ] = $count_results_success[ 0 ]->nb;
		
		$count_query_failed = "SELECT COUNT(*) AS nb FROM " .$table_investments. " WHERE status LIKE 'failed'";
		$count_results_failed = $wpdb->get_results( $count_query_failed );
		$buffer[ 'payment_errors' ] = $count_results_failed[ 0 ]->nb;
		
		$count_query_total_30_days = "SELECT COUNT(*) AS nb FROM " .$table_investments. " WHERE status LIKE 'publish' AND DATEDIFF( NOW(), invest_datetime ) < 31";
		$count_results_total_30_days = $wpdb->get_results( $count_query_total_30_days );
		$buffer[ 'total_last_30_days' ] = $count_results_total_30_days[ 0 ]->nb;
		
		$count_query_failed_30_days = "SELECT COUNT(*) AS nb FROM " .$table_investments. " WHERE status LIKE 'failed' AND DATEDIFF( NOW(), invest_datetime ) < 31";
		$count_results_failed_30_days = $wpdb->get_results( $count_query_failed_30_days );
		$buffer[ 'payment_errors_monthly' ] = $count_results_failed_30_days[ 0 ]->nb;
		
		$date_now = new DateTime();
		$buffer[ 'total_by_month' ] = array();
		for ( $i = 1; $i <= 12; $i++ ) {
			$buffer[ 'total_by_month' ][ $i ] = array();
			$count_query_by_month_success = "SELECT COUNT(*) AS nb FROM " .$table_investments. " WHERE status LIKE 'publish' AND MONTH( invest_datetime ) = " .$i. " AND YEAR( invest_datetime ) = " .$date_now->format( 'Y' );
			$count_results_by_month_success = $wpdb->get_results( $count_query_by_month_success );
			$buffer[ 'total_by_month' ][ $i ][ 'success' ] = $count_results_by_month_success[ 0 ]->nb;
			
			$count_query_by_month_failed = "SELECT COUNT(*) AS nb FROM " .$table_investments. " WHERE status LIKE 'failed' AND MONTH( invest_datetime ) = " .$i. " AND YEAR( invest_datetime ) = " .$date_now->format( 'Y' );
			$count_results_by_month_failed = $wpdb->get_results( $count_query_by_month_failed );
			$buffer[ 'total_by_month' ][ $i ][ 'failed' ] = $count_results_by_month_failed[ 0 ]->nb;
		}
		
		$buffer[ 'total_by_day_this_month' ] = array();
		for ( $i = 1; $i <= 31; $i++ ) {
			$buffer[ 'total_by_day_this_month' ][ $i ] = array();
			$count_query_by_day_this_month_success = "SELECT COUNT(*) AS nb FROM " .$table_investments. " WHERE status LIKE 'publish' AND DAY( invest_datetime ) = " .$i. " AND MONTH( invest_datetime ) = " .$date_now->format( 'm' ). " AND YEAR( invest_datetime ) = " .$date_now->format( 'Y' );
			$count_results_by_day_this_month_success = $wpdb->get_results( $count_query_by_day_this_month_success );
			$buffer[ 'total_by_day_this_month' ][ $i ][ 'success' ] = $count_results_by_day_this_month_success[ 0 ]->nb;
			
			$count_query_by_day_this_month_failed = "SELECT COUNT(*) AS nb FROM " .$table_investments. " WHERE status LIKE 'failed' AND DAY( invest_datetime ) = " .$i. " AND MONTH( invest_datetime ) = " .$date_now->format( 'm' ). " AND YEAR( invest_datetime ) = " .$date_now->format( 'Y' );
			$count_results_by_day_this_month_failed = $wpdb->get_results( $count_query_by_day_this_month_failed );
			$buffer[ 'total_by_day_this_month' ][ $i ][ 'failed' ] = $count_results_by_day_this_month_failed[ 0 ]->nb;
		}
		
		$count_query_total_with_roi = "SELECT COUNT(*) AS nb FROM " .$table_investments. " WHERE cents_with_royalties > 0";
		$count_results_total_with_roi = $wpdb->get_results( $count_query_total_with_roi );
		$buffer[ 'total_with_royalties' ] = $count_results_total_with_roi[ 0 ]->nb;
		
		$count_query_sum_with_roi = "SELECT SUM(cents_with_royalties) AS nb FROM " .$table_investments. " WHERE cents_with_royalties > 0";
		$count_results_sum_with_roi = $wpdb->get_results( $count_query_sum_with_roi );
		$buffer[ 'amount_with_royalties' ] = $count_results_sum_with_roi[ 0 ]->nb;
		
		return $buffer;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT', 'gs_col_index' => 1 ),
		'wpref'					=> array( 'type' => 'id', 'other' => '' ),
		'token'					=> array( 'type' => 'uid', 'other' => 'NOT NULL' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		
		'user_id'				=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 3 ),
		'user_wpref'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'gender'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'firstname'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'lastname'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		
		'nationality'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 11 ),
		'birthday_day'			=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'birthday_month'		=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'birthday_year'			=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'birthday_city'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'age'					=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 8 ),
		
		'address'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'postalcode'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 9 ),
		'city'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'country'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 10 ),
		'phone_number'			=> array( 'type' => 'varchar', 'other' => '' ),
		
		'is_legal_entity'			=> array( 'type' => 'bool', 'other' => 'NOT NULL', 'gs_col_index' => 4 ),
		'legal_entity_form'			=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_id'			=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_rcs'			=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_capital'		=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_address'		=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_postalcode'	=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_city'			=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_nationality'	=> array( 'type' => 'varchar', 'other' => '' ),
		
		'project'				=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 2 ),
		'amount'				=> array( 'type' => 'int', 'other' => 'NOT NULL', 'gs_col_index' => 6 ),
		'cents_with_royalties'	=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'redirect_url_ok'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'redirect_url_nok'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'notification_url'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'contract_url'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		
		'invest_datetime'		=> array( 'type' => 'datetime', 'other' => 'NOT NULL', 'gs_col_index' => 5 ),
		'is_preinvestment'		=> array( 'type' => 'bool', 'other' => 'NOT NULL' ),
		
		'mean_payment'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'payment_provider'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'payment_key'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'payment_status'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'payment_provider_p2p_id'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		
		'signature_key'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'signature_status'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 7 ),
		'token_expiration'		=> array( 'type' => 'datetime', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_Investment::$entity_type, WDGRESTAPI_Entity_Investment::$db_properties );
	}
	
}