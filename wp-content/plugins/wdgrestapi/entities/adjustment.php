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
	public static function list_get_by_declaration_id( $declaration_id, $with_links = FALSE ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE id_declaration = " .$declaration_id. " ORDER BY date_created ASC";
		$results = $wpdb->get_results( $query );

		if ( $with_links ) {
			$buffer = array();
			if ( !empty( $results ) ) {
				foreach ( $results as $single_result ) {
					$single_result->files = WDGRESTAPI_Entity_AdjustmentFile::get_list_by_adjustment_id( $single_result->id );
					$single_result->declarations = WDGRESTAPI_Entity_AdjustmentDeclaration::get_list_by_adjustment_id( $single_result->id );
					array_push( $buffer, $single_result );
				}
			}
			return $buffer;

		} else {
			return $results;
		}
	}
	
	/**
	 * Retourne la liste des ajustements liés à une déclaration
	 * @param int $project_id
	 * @return array
	 */
	public static function list_get_by_project_id( $project_id, $with_links = FALSE ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE id_project = " .$project_id. " ORDER BY date_created ASC";
		$results = $wpdb->get_results( $query );

		if ( $with_links ) {
			$buffer = array();
			if ( !empty( $results ) ) {
				foreach ( $results as $single_result ) {
					$single_result->files = WDGRESTAPI_Entity_AdjustmentFile::get_list_by_adjustment_id( $single_result->id );
					$single_result->declarations = WDGRESTAPI_Entity_AdjustmentDeclaration::get_list_by_adjustment_id( $single_result->id );
					array_push( $buffer, $single_result );
				}
			}
			return $buffer;

		} else {
			return $results;
		}
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'id_project'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'id_declaration'		=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'date_created'			=> array( 'type' => 'datetime', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'turnover_checked'		=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'turnover_difference'	=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'amount'				=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'message_organization'	=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'message_investors'		=> array( 'type' => 'longtext', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}