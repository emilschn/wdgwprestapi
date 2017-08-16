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
	
	public function __construct( $id = FALSE, $token = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Investment::$entity_type, WDGRESTAPI_Entity_Investment::$db_properties );
		
		if ( empty( $id ) && !empty( $token ) ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( $this->current_entity_type );
			$query = "SELECT * FROM " .$table_name. " WHERE token='" .$token. "'";
			$this->loaded_data = $wpdb->get_row( $query );
		}
		
		if ( isset( $this->loaded_data ) && $this->loaded_data->status == WDGRESTAPI_Entity_Investment::$status_init && $this->has_token_expired() ) {
			$this->loaded_data->status = WDGRESTAPI_Entity_Investment::$status_expired;
			$this->save();
		}
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
		if ( !WDGRESTAPI_Lib_Validator::is_postalcode( $this->loaded_data->postalcode ) ) {
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
			if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->legal_entity_id ) ) {
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
			if ( !WDGRESTAPI_Lib_Validator::is_postalcode( $this->loaded_data->legal_entity_postalcode ) ) {
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
	public static function list_get() {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Investment::$entity_type );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$query = "SELECT id, email, project, amount, status FROM " .$table_name. " WHERE client_user_id=" .$current_client->ID;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'token'					=> array( 'type' => 'uid', 'other' => 'NOT NULL' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'gender'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'firstname'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'lastname'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		
		'nationality'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'birthday_day'			=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'birthday_month'		=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'birthday_year'			=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'birthday_city'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		
		'address'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'postalcode'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'city'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'country'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		
		'is_legal_entity'			=> array( 'type' => 'bool', 'other' => 'NOT NULL' ),
		'legal_entity_form'			=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_id'			=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_rcs'			=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_capital'		=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_address'		=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_postalcode'	=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_city'			=> array( 'type' => 'varchar', 'other' => '' ),
		'legal_entity_nationality'	=> array( 'type' => 'varchar', 'other' => '' ),
		
		'project'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'amount'				=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'redirect_url_ok'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'redirect_url_nok'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'notification_url'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'token_expiration'		=> array( 'type' => 'datetime', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_Investment::$entity_type, WDGRESTAPI_Entity_Investment::$db_properties );
	}
	
}