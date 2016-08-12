<?php
class WDGRESTAPI_Entity {
	
	protected $current_entity_type;
	protected $current_db_properties;
	protected $loaded_data;
	
	public function __construct( $id, $entity_type, $db_properties ) {
		$this->current_entity_type = $entity_type;
		$this->current_db_properties = $db_properties;
		
		// Si un id est passé, on construit à partir de la base de données
		if ( $id != FALSE ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( $entity_type );
			$query = 'SELECT * FROM ' .$table_name. ' WHERE id='.$id;
			$this->loaded_data = $wpdb->get_row( $query );
		
		// Sinon, on initialise avec les différents champs
		} else {
			
			$this->loaded_data = json_decode('{}');
			foreach ($db_properties as $db_key => $db_property) {
				if ( $db_key != 'unique_key' && $db_key != 'id' ) {
					$this->loaded_data->$db_key = FALSE;
				}
			}
			
		}
	}
	
	/**
	 * Retourne la liste des propriétés chargées dans la BDD
	 * @return object
	 */
	public function get_loaded_data() {
		return $this->loaded_data;
	}
	
	/**
	 * Définit la valeur d'une propriété
	 * @param string $property_name
	 * @param string $value
	 */
	public function set_property( $property_name, $value ) {
		$this->loaded_data->$property_name = $value;
	}
	
	/**
	 * Enregistre ou crée si nécessaire
	 */
	public function save() {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( $this->current_entity_type );
		
		// Préparation de toutes les propriétés
		$array_properties = array();
		foreach ( $this->current_db_properties as $db_key => $db_property ) {
			if ( $db_key != 'unique_key' && $db_key != 'id' ) {
				$array_properties[ $db_key ] = $this->loaded_data->$db_key;
			}
		}
		
		// Si il y a déjà un ID, on met simplement à jour les données sur la ligne concernée
		if ( !empty( $this->loaded_data->id ) ) {
			$result = $wpdb->update( 
				$table_name,
				$array_properties,
				array(
					'id' => $this->loaded_data->id
				)
			);
		
		// Sinon, on crée un nouvel objet et met à jour l'identifiant
		} else {
			$result = $wpdb->insert( 
				$table_name, 
				$array_properties
			);
			if ($result !== FALSE) {
				$this->loaded_data->id = $wpdb->insert_id;
			}
		}
	}
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	/**
	 * Met à jour les données d'une table de la BDD à partir d'une liste de propriétés
	 * @param string $entity_type
	 * @param array $db_properties
	 */
	public static function upgrade_db( $entity_type, $db_properties ) {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = WDGRESTAPI_Entity::get_table_name( $entity_type );
		
		$sql = "CREATE TABLE " .$table_name. " (";
		foreach ( $db_properties as $db_property_key => $db_property ) {
			if ( !empty( $db_property[ "type" ] ) ) {
				$sql .= " " . $db_property_key;
				$sql .= " " . WDGRESTAPI_Entity::get_mysqltype_from_wdgtype( $db_property[ "type" ] );
				if ( !empty( $db_property[ "other" ] ) ) {
					$sql .= " " . $db_property[ "other" ];
				}
				$sql .= ",";
			}
		}
		if ( !empty( $db_properties[ "unique_key" ] ) ) {
			$sql .= " UNIQUE KEY " . $db_properties[ "unique_key" ] . " (" .$db_properties[ "unique_key" ]. ")";
		}
		
		$sql .= " ) ";
		$sql .= $charset_collate. ";";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$result = dbDelta( $sql );
		return $result;
	}
	
	/**
	 * Retourne le nom de la table de BDD correspondante au type d'entité
	 * @param string $entity_type
	 */
	public static function get_table_name( $entity_type ) {
		global $wpdb;
		return $wpdb->prefix . 'entity_' . $entity_type;
	}
	
	/**
	 * Retourne un type MySQL à partir d'un type maison WDG
	 * @param string $mysql_type
	 * @return string
	 */
	public static function get_mysqltype_from_wdgtype( $mysql_type ) {
		$buffer = '';
		switch ( $mysql_type ) {
			case 'id':
				$buffer = 'mediumint(9)';
				break;
			
			case 'varchar':
				$buffer = 'varchar(100)';
				break;
			
			case 'longtext':
				$buffer = 'longtext';
				break;
			
			case 'date':
				$buffer = 'date';
				break;
			
			case 'bool':
				$buffer = 'int(1)';
				break;
			
			case 'int':
				$buffer = 'int(11)';
				break;
		}
		
		return $buffer;
	}
	
}