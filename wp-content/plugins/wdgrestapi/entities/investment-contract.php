<?php
class WDGRESTAPI_Entity_InvestmentContract extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'investment_contract';
	
	public static $status_active = 'active';
	public static $status_canceled = 'canceled';
	public static $status_finished = 'finished';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	/**
	 * Retourne la liste de tous les contrats d'investissement
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string, $subscription_id = FALSE ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		if ( !empty( $subscription_id ) ) {
			$query .= " AND subscription_id=" .$subscription_id;
		}
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	public static function list_get_by_project( $project_id ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE project_id = " .$project_id;
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	public static function list_get_by_investor( $user_id, $user_type ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE investor_id = " .$user_id. " AND investor_type='" .$user_type. "'";
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
		
		'investor_id'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'investor_type'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		
		'project_id'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'organization_id'		=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		
		'subscription_id'		=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'subscription_date'		=> array( 'type' => 'datetime', 'other' => 'NOT NULL' ),
		'subscription_amount'	=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'start_date'			=> array( 'type' => 'datetime', 'other' => 'NOT NULL' ),
		'end_date'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'frequency'				=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		
		'turnover_type'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'turnover_percent'		=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		
		'amount_received'		=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'minimum_to_receive'	=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'maximum_to_receive'	=> array( 'type' => 'float', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}