<?php
class WDGRESTAPI_Entity {
	
	protected $current_entity_type;
	protected $current_db_properties;
	protected $loaded_data;
	protected $properties_errors;
	
	public function __construct( $id, $entity_type, $db_properties ) {
		$this->current_entity_type = $entity_type;
		$this->current_db_properties = $db_properties;
		$this->properties_errors = array();
		
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
	 * Renvoie true si les données qui ont été transmises sont correctes
	 * @return boolean
	 */
	public function has_checked_properties() {
		return true;
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
	 * Nécessite une sauvegarde en BDD ultérieure
	 * @param string $property_name
	 * @param string $property_value
	 */
	public function set_property( $property_name, $property_value ) {
		$this->loaded_data->$property_name = $property_value;
	}

	/**
	 * Pour les entités qui ont un champ metadata, retourne une valeur particulière
	 * @return string
	 */
	public function get_metadata( $property_name ) {
		$metadata_list = json_decode( $this->loaded_data->metadata );
		$buffer = FALSE;
		if ( isset( $metadata_list->$property_name ) ) {
			$buffer = $metadata_list->$property_name;
		}
		return $buffer;
	}
	
	/**
	 * Retourne le tableau des erreurs constatées sur les propriétés
	 * @return array
	 */
	public function get_properties_errors() {
		return $this->properties_errors;
	}

	/**
	 * Pour les entités qui ont un champ metadata, met à jour la valeur d'une des metadata
	 * Nécessite une sauvegarde en BDD ultérieure
	 * @param string nom de la propriété de metadonnée
	 * @param string valeur de la metaonnée
	 */
	public function set_metadata( $property_name, $property_value ) {
		if ( isset( $this->loaded_data->metadata ) ) {
			$metadata_list = json_decode( json_encode( array() ) );
			if ( !empty( $this->loaded_data->metadata ) ) {
				$metadata_list = json_decode( $this->loaded_data->metadata );
			}
			$metadata_list->$property_name = $property_value;
			$this->loaded_data->metadata = json_encode( $metadata_list );
		}
	}
	
	public function make_uid() {
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$test = $current_client->ID . '-' . time() . '-';
		$buffer = md5( $test );
		//Test si effectivement unique
		return $buffer;
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
		return $result;
	}
	
	/**
	 * Se charge de récupérer des données en fonction de l'identifiant client
	 * @param string; $data_type
	 * @return string
	 */
	protected static function get_data_on_client_site( $action, $param ) {
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$ref_client_url = $current_client->user_url;
		$route = '/connexion';
		$params = '?action=' .$action. '&param=' .urlencode( $param );
		$url = $ref_client_url . $route . $params;
		WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity::get_data_on_client_site > $url : ' . $url);
		
		$buffer = wp_remote_get(
			$url,
			array( 'timeout' => 10 )
		);
		return json_decode( $buffer["body"] );
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
		// Ajout de tous les champs de propriétés
		foreach ( $db_properties as $db_property_key => $db_property ) {
			if ( !empty( $db_property[ "type" ] ) ) {
				// Saut de ligne obligatoire pour être interprété par le diff de WP
				$sql .= "
				" . $db_property_key;
				$sql .= " " . WDGRESTAPI_Entity::get_mysqltype_from_wdgtype( $db_property[ "type" ] );
				if ( !empty( $db_property[ "other" ] ) ) {
					$sql .= " " . $db_property[ "other" ];
				}
				$sql .= ",";
			}
		}
		if ( !empty( $db_properties[ "unique_key" ] ) ) {
			// Saut de ligne obligatoire pour être interprété par le diff de WP
			$sql .= "
			UNIQUE KEY " . $db_properties[ "unique_key" ] . " (" .$db_properties[ "unique_key" ]. ")";
		}
		
		// Saut de ligne obligatoire pour être interprété par le diff de WP
		$sql .= " )
		";
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
			
			case 'uid':
				$buffer = 'varchar(50)';
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
			
			case 'datetime':
				$buffer = 'datetime';
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