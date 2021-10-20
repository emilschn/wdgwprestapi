<?php
class WDGRESTAPI_Entity_Subscription extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'subscription';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}

	/**
	 * Retourne la liste des investissement liées à un utilisateur
	 * @param int $id_subscriber
	 * @return array
	 */
	public static function get_subscriptions_by_subscriber_id($id_subscriber, $type_subscriber) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = 'SELECT id, status, id_project, amount_type, amount, payment_method, modality, start_date FROM ' . $table_name. ' WHERE id_subscriber = ' . $id_subscriber. ' AND type_subscriber = \''. $type_subscriber . '\'';
		$buffer = $wpdb->get_results( $query );
		return $buffer;
	}

	/**
	 * Retourne la liste de tous abonnements actifs
	 * @return array
	 */
	public static function get_subscriptions( $status ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = 'SELECT * FROM ' .$table_name. ' WHERE status=\''.$status . '\'';
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'id_subscriber'	    	=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
        'id_activator'	    	=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
        'type_subscriber'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
        'id_project'	    	=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
        'amount_type'		    => array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
        'amount'	            => array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'payment_method'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'modality'			    => array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
        'start_date'			=> array( 'type' => 'datetime', 'other' => 'NOT NULL' ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
        'end_date'			    => array( 'type' => 'datetime')
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}