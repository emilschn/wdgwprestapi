<?php
class WDGRESTAPI_Entity_QueuedAction extends WDGRESTAPI_Entity {
	public static $entity_type = 'queued_action';
	
	public static $status_init = 'init';
	public static $status_complete = 'complete';
	
	public static $priority_high = 'high';
	public static $priority_date = 'date';
	public static $priority_default = 'default';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	/**
	 * 
	 */
	public function save() {
		date_default_timezone_set( 'Europe/Paris' );
		$current_date = new DateTime();
		if ( empty( $this->loaded_data->id ) ) {
			$this->set_property( 'status', WDGRESTAPI_Entity_QueuedAction::$status_init );
			$this->set_property( 'date_created', $current_date->format( 'Y-m-d H:i:s' ) );
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$this->set_property( 'caller', $current_client->ID );
		}
		$this->set_property( 'date_modified', $current_date->format( 'Y-m-d H:i:s' ) );
		parent::save();
	}
	
	private static function make_list_get_query( $authorized_client_id_string, $limit = FALSE, $entity_id = FALSE, $action = FALSE, $priority = FALSE ) {
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		
		$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string. " AND status LIKE '" .WDGRESTAPI_Entity_QueuedAction::$status_init. "'";
		
		$where_condition = "";
		if ( !empty( $entity_id ) && is_numeric( $entity_id ) ) {
			$where_condition .= " AND entity_id = " .$entity_id;
		}
		if ( !empty( $action ) ) {
			$where_condition .= " AND action LIKE '" .$action. "'";
		}
		if ( !empty( $priority ) ) {
			$where_condition .= " AND priority LIKE '" .$priority. "'";
			if ( $priority == 'date' ) {
				date_default_timezone_set( 'Europe/Paris' );
				$current_date = new DateTime();
				$where_condition .= " AND date_priority < '" .$current_date->format( 'Y-m-d H:i:s' ). "'";
			}
		}
		$query .= $where_condition;
		
		$query_order_condition = " ORDER BY ID ASC";
		$query .= $query_order_condition;
		
		if ( !empty( $limit ) ) {
			$query .= " LIMIT " .$limit;
		}
		
		return $query;
	}

	public static function list_get( $authorized_client_id_string, $limit = FALSE, $entity_id = FALSE, $action = FALSE ) {
		global $wpdb;
		
		// D'abord, on veut les plus actions avec la priorité la plus élevée
		$query_high = self::make_list_get_query( $authorized_client_id_string, $limit, $entity_id, $action, WDGRESTAPI_Entity_QueuedAction::$priority_high );
		$results = $wpdb->get_results( $query_high );
		$buffer = $results;
		
		// Si il reste de la place, on prend celle qui ont une date dépassée
		if ( count( $buffer ) < $limit  ) {
			$query_date = self::make_list_get_query( $authorized_client_id_string, $limit - count( $buffer ), $entity_id, $action, WDGRESTAPI_Entity_QueuedAction::$priority_date );
			$results = $wpdb->get_results( $query_date );
			if ( !empty( $results ) ) {
				$buffer = array_merge( $buffer, $results );
			}
		}
		
		// Si il reste de la place, on prend celle qui ont une priorité par défaut
		if ( count( $buffer ) < $limit  ) {
			$query_default = self::make_list_get_query( $authorized_client_id_string, $limit - count( $buffer ), $entity_id, $action, WDGRESTAPI_Entity_QueuedAction::$priority_default );
			$results = $wpdb->get_results( $query_default );
			if ( !empty( $results ) ) {
				$buffer = array_merge( $buffer, $results );
			}
		}
		
		return $buffer;
	}


	/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => '' ),
		'date_created'			=> array( 'type' => 'datetime', 'other' => '' ),
		'date_modified'			=> array( 'type' => 'datetime', 'other' => '' ),
		'status'				=> array( 'type' => 'varchar', 'other' => '' ),
		'priority'				=> array( 'type' => 'varchar', 'other' => '' ),
		'date_priority'			=> array( 'type' => 'datetime', 'other' => '' ),
		'action'				=> array( 'type' => 'varchar', 'other' => '' ),
		'entity_id'				=> array( 'type' => 'id', 'other' => '' ),
		'params'				=> array( 'type' => 'longtext', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}