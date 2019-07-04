<?php
class WDGRESTAPI_Entity_AdjustmentFile extends WDGRESTAPI_Entity {
	public static $entity_type = 'adjustment_file';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	/**
	 * Retourne la liste des fichiers liés à un ajustement
	 * @param int $id_adjustment
	 * @return array
	 */
	public static function get_list_by_adjustment_id( $id_adjustment ) {
		$buffer = array();
		
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT id_file, type FROM " . $table_name;
		$query .= " WHERE id_adjustment=" . $id_adjustment;
		$loaded_data = $wpdb->get_results( $query );
		
		if ( !empty( $loaded_data ) ) {
			foreach ( $loaded_data as $file_data ) {
				$file_temp = new WDGRESTAPI_Entity_File( $file_data->id_file );
				array_push( $buffer, $file_temp->get_loaded_data() );
			}
		}
		
		return $buffer;
	}
	
	/**
	 * Supprime la liaison entre ajustement, fichier et un type spécifique
	 * @param int $id_adjustment
	 * @param int $id_file
	 * @param string $type
	 */
	public static function remove( $id_adjustment, $id_file ) {
		if ( !empty( $id_adjustment ) && !empty( $id_file ) ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
			$wpdb->delete(
				$table_name,
				array(
					'id_adjustment'	=> $id_adjustment,
					'id_file'		=> $id_file
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
		'id_file'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'type'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}