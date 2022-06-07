<?php
class WDGRESTAPI_Entity_UserConformity extends WDGRESTAPI_Entity {
	public static $entity_type = 'user_conformity';

	public function __construct($id = FALSE) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}

	public function save() {
		date_default_timezone_set( 'Europe/Paris' );
		$current_date = new DateTime();
		$this->set_property( 'last_update', $current_date->format( 'Y-m-d H:i:s' ) );
		
		return parent::save();
	}

	/**
	 * @return WDGRESTAPI_Entity_UserConformity|boolean
	 */
	public static function get_by_user_id($user_id) {
		if ( empty( $user_id ) ) {
			return FALSE;
		}
		
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT id FROM " .$table_name. " WHERE user_id = " .$user_id. " LIMIT 1";
		$results = $wpdb->get_results( $query );

		$buffer = FALSE;
		if ( !empty( $results ) && !empty( $results[0] ) && !empty( $results[0]->id ) ) {
			$buffer = new WDGRESTAPI_Entity_UserConformity( $results[0]->id );
		}
		return $buffer;
	}

	/*******************************************************************************
	 * GESTION BDD
	 ******************************************************************************/

	public static $db_properties = array(
		'unique_key'					=> 'id',
		'id'							=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'user_id'						=> array( 'type' => 'id' ),
		'last_update'					=> array( 'type' => 'date' ),
		'version'						=> array( 'type' => 'int' ),
		'financial_details'				=> array( 'type' => 'longtext' ),
		'financial_result_in_cents'		=> array( 'type' => 'int' ),
		'knowledge_details'				=> array( 'type' => 'longtext' ),
		'knowledge_result'				=> array( 'type' => 'varchar' ),
		'profession_details'			=> array( 'type' => 'longtext' ),
		'objectives_details'			=> array( 'type' => 'longtext' )
	);

	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
}