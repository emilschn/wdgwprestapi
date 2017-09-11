<?php
class WDGRESTAPI_Entity_Declaration extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'declaration';
	
	public function __construct( $id = FALSE, $payment_token = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Declaration::$entity_type, WDGRESTAPI_Entity_Declaration::$db_properties );
		
		if ( empty( $id ) && !empty( $payment_token ) ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( $this->current_entity_type );
			$query = "SELECT * FROM " .$table_name. " WHERE payment_token='" .$payment_token. "'";
			$this->loaded_data = $wpdb->get_row( $query );
		}
	}
	
	/**
	 * Retourne la liste des ROIs de cette déclaration
	 * @return array
	 */
	public function get_rois() {
		$buffer = WDGRESTAPI_Entity_ROI::list_get_by_declaration_id( $this->loaded_data->id );
		return $buffer;
	}
	
	/**
	 * Retourne la liste de toutes les déclarations
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Declaration::$entity_type );
		$query = "SELECT id, id_project, date_due, date_paid, date_transfer, amount, remaining_amount, transfered_previous_remaining_amount, percent_commission, status, mean_payment, file_list, turnover, message, adjustment FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	/**
	 * Retourne la liste de toutes les déclarations
	 * @return array
	 */
	public static function list_get_by_project_id( $project_id ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Declaration::$entity_type );
		$query = "SELECT id, id_project, date_due, date_paid, date_transfer, amount, remaining_amount, transfered_previous_remaining_amount, percent_commission, status, mean_payment, file_list, turnover, message, adjustment FROM " .$table_name. " WHERE id_project = " .$project_id;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'id_project'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'date_due'				=> array( 'type' => 'date', 'other' => 'DEFAULT \'0000-00-00\'' ),
		'date_paid'				=> array( 'type' => 'date', 'other' => 'DEFAULT \'0000-00-00\'' ),
		'date_transfer'			=> array( 'type' => 'date', 'other' => 'DEFAULT \'0000-00-00\'' ),
		'amount'				=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'remaining_amount'		=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'transfered_previous_remaining_amount'	=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'percent_commission'	=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'mean_payment'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'payment_token'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'file_list'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'turnover'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'message'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'adjustment'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'employees_number'		=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'other_fundings'		=> array( 'type' => 'longtext', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_Declaration::$entity_type, WDGRESTAPI_Entity_Declaration::$db_properties );
	}
	
}