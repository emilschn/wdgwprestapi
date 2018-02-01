<?php
class WDGRESTAPI_Entity_BankInfo extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'bankinfo';
	
	public function __construct( $id = FALSE, $email = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_BankInfo::$entity_type, WDGRESTAPI_Entity_BankInfo::$db_properties );
		
		if ( empty( $id ) && !empty( $email ) ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( $this->current_entity_type );
			$query = "SELECT * FROM " .$table_name. " WHERE email='" .$email. "'";
			$this->loaded_data = $wpdb->get_row( $query );
		}
	}
	
	/**
	 * Renvoie true si les données qui ont été transmises sont correctes
	 * @return boolean
	 */
	public function has_checked_properties() {
		$buffer = parent::has_checked_properties();
		
		if ( !WDGRESTAPI_Lib_Validator::is_email( $this->loaded_data->email ) ) {
			array_push( $this->properties_errors, __( "Le champ E-mail (email) n'est pas au bon format.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->holdername ) ) {
			array_push( $this->properties_errors, __( "Le champ Nom (holdername) n'est pas au bon format.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_iban( $this->loaded_data->iban ) ) {
			array_push( $this->properties_errors, __( "Le champ IBAN (iban) n'est pas au bon format.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_bic( $this->loaded_data->bic ) ) {
			array_push( $this->properties_errors, __( "Le champ BIC (bic) n'est pas au bon format.", 'wdgrestapi' ) );
			$buffer = false;
		}
		if ( !WDGRESTAPI_Lib_Validator::is_name( $this->loaded_data->address1 ) ) {
			array_push( $this->properties_errors, __( "Le champ Adresse 1 (address1) n'est pas au bon format.", 'wdgrestapi' ) );
			$buffer = false;
		}
		
		return $buffer;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'date_update'			=> array( 'type' => 'datetime', 'other' => 'NOT NULL' ),
		
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'holdername'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'iban'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'bic'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'address1'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'address2'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_BankInfo::$entity_type, WDGRESTAPI_Entity_BankInfo::$db_properties );
	}
	
}