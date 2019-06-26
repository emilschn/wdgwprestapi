<?php
class WDGRESTAPI_Entity_AdjustmentDeclaration extends WDGRESTAPI_Entity {
	public static $entity_type = 'adjustment_declaration';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	/**
	 * Retourne la liste des déclarations liées à un ajustement
	 * @param int $id_adjustment
	 * @return array
	 */
	public static function get_list_by_adjustment_id( $id_adjustment ) {
		$buffer = array();
		
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT id_declaration, type FROM " . $table_name;
		$query .= " WHERE id_adjustment=" . $id_adjustment;
		$loaded_data = $wpdb->get_results( $query );
		
		if ( !empty( $loaded_data ) ) {
			foreach ( $loaded_data as $declaration_data ) {
				$declaration_temp = new WDGRESTAPI_Entity_Declaration( $declaration_data->id_declaration );
				array_push( $buffer, $declaration_temp->get_loaded_data() );
			}
		}
		
		return $buffer;
	}
	
	/**
	 * Supprime la liaison entre ajustement, déclaration et un type spécifique
	 * @param int $id_adjustment
	 * @param int $id_declaration
	 * @param string $type
	 */
	public static function remove( $id_adjustment, $id_declaration, $type ) {
		if ( !empty( $id_adjustment ) && !empty( $id_declaration ) && !empty( $type ) ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
			$wpdb->delete(
				$table_name,
				array(
					'id_project'	=> $id_adjustment,
					'id_declaration'=> $id_declaration,
					'type'			=> $type
				)
			);
		}
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	// Pour les types, voir WDGRESTAPI_Entity::get_mysqltype_from_wdgtype
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'id_adjustment'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'id_declaration'		=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}