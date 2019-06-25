<?php
class WDGRESTAPI_Entity_Adjustment extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'adjustment';
	
	public static $type_turnover_difference = 'turnover_difference';
	public static $type_turnover_difference_remainders = 'turnover_difference_remainders';
	public static $type_fixed_amount = 'fixed_amount';
	public static $type_previous_adjustment_correction = 'previous_adjustment_correction';
	public static $type_royalties_remainders = 'royalties_remainders';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	/**
	 * Retourne la liste des ajustements liés à une déclaration
	 * @param int $declaration_id
	 * @return array
	 */
	public static function list_get_by_declaration_id( $declaration_id ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE id_declaration = " .$declaration_id. " ORDER BY date_created ASC";
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	/**
	 * Retourne la liste des ajustements liés à une déclaration
	 * @param int $project_id
	 * @return array
	 */
	public static function list_get_by_project_id( $project_id ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE id_project = " .$project_id. " ORDER BY date_created ASC";
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT', 'gs_col_index' => 1 ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'id_project'			=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 2 ),
		'id_declaration'		=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 2 ),
		'date_created'			=> array( 'type' => 'datetime', 'other' => 'NOT NULL', 'gs_col_index' => 3 ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 4 ),
		'turnover_difference'	=> array( 'type' => 'float', 'other' => 'NOT NULL', 'gs_col_index' => 5 ),
		'amount'				=> array( 'type' => 'float', 'other' => 'NOT NULL', 'gs_col_index' => 6 ),
		'message_organization'	=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 7 ),
		'message_investors'		=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 8 )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}