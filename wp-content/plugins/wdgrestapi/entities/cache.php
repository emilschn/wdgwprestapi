<?php
class WDGRESTAPI_Entity_Cache extends WDGRESTAPI_Entity {
	public static $entity_type = 'cache';
	
	public function __construct( $id = FALSE, $name = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Cache::$entity_type, WDGRESTAPI_Entity_Cache::$db_properties );
		
		if ( empty( $id ) && !empty( $name ) ) {
			global $wpdb;
			if ( isset( $wpdb ) ) {
				$table_name = WDGRESTAPI_Entity::get_table_name( $this->current_entity_type );
				$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
				$query = "SELECT * FROM " .$table_name. " WHERE name='" .$name. "' AND caller=" .$current_client->ID;
				$this->loaded_data = $wpdb->get_row( $query );
			}
		}
	}
	
	/**
	 * Ajoute ou met à jour une ligne dans la bdd
	 */
	public function save() {
		date_default_timezone_set( 'Europe/Paris' );
		$current_date = new DateTime();
		$this->set_property( 'date', $current_date->format( 'Y-m-d H:i:s' ) );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		if ( !empty( $current_client ) ) {
			$this->set_property( 'caller', $current_client->ID );
		}
		parent::save();
	}

	/**
	 * Définit le nom
	 * @param string $name
	 */
	public function set_name( $name ) {
		$this->set_property( 'name', $name );
	} 
	
	/**
	 * Définit la valeur
	 * @param string $value
	 */
	public function set_value( $value ) {
		$this->set_property( 'value', $value );
	} 
	/**
	 * Retourne TRUE si la valeur a expiré dans le cache
	 * @param int $expiration en minutes
	 * @return boolean
	 */
	public function has_expired( $expiration = 5 ) {
		$buffer = FALSE;
		
		date_default_timezone_set( 'Europe/Paris' );
		$current_date = new DateTime();
		$saved_date = new DateTime( $this->loaded_data->date );
		if ( !empty( $this->loaded_data->date ) ) {
			$date_diff = $current_date->diff( $saved_date );
			$buffer = ( $expiration < $date_diff->i + $date_diff->h * 60 + $date_diff->d * 60 * 24 + $date_diff->m * 60 * 24 * 31 + $date_diff->y * 60 * 24 * 365 );
		}
		
		return $buffer;
	}
	
	/**
	 * Retourne la valeur du cache si celle-ci n'a pas expiré
	 * @param int $expiration en minutes
	 * @return boolean or string
	 */
	public function get_value( $expiration = 5 ) {
		$buffer = FALSE;
		if ( !empty( $this->loaded_data->value ) && !$this->has_expired( $expiration ) ) {
			$buffer = $this->loaded_data->value;
		}
		return $buffer;
	}
	
	public static function delete_by_name_like( $like_pattern ) {
		global $wpdb;
		if ( !isset( $wpdb ) ) {
			return FALSE;
		}
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Cache::$entity_type );
		$wpdb->query( 
			$wpdb->prepare( 
				"DELETE FROM ".$table_name." WHERE name LIKE '%%s%'",
				$like_pattern
			)
		);
	}
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'date'					=> array( 'type' => 'datetime', 'other' => '' ),
		'name'					=> array( 'type' => 'varchar', 'other' => '' ),
		'value'					=> array( 'type' => 'longtext', 'other' => '' ),
		'caller'				=> array( 'type' => 'id', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_Cache::$entity_type, WDGRESTAPI_Entity_Cache::$db_properties );
	}
	
}