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
		$this->loaded_data = json_decode('{}');
		
		// Si un id est passé, on construit à partir de la base de données
		if ( $id != FALSE ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( $entity_type );
			$query = 'SELECT * FROM ' .$table_name. ' WHERE id='.$id;
			$this->loaded_data = $wpdb->get_row( $query );
		
		// Sinon, on initialise avec les différents champs
		} else {
			
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
			$metadata_list = (object)[];
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
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity::save > update > $this->loaded_data->id = ' . $this->loaded_data->id );
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity::save > update > $array_properties = ' . print_r( $array_properties, TRUE ) );
			$result = $wpdb->update( 
				$table_name,
				$array_properties,
				array(
					'id' => $this->loaded_data->id
				)
			);
			if ( $wpdb->last_query !== '' ) {
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity::save > update > $wpdb->last_query = ' . $wpdb->last_query );
			}
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity::save > update > $result = ' . print_r( $result, TRUE ) );
			if ( $wpdb->last_error !== '' ) {
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity::save > update > $wpdb->last_result = ' . print_r( $wpdb->last_result, TRUE ) );
			}
		
		// Sinon, on crée un nouvel objet et met à jour l'identifiant
		} else {
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity::save > insert > $array_properties = ' . print_r( $array_properties, TRUE ) );
			$result = $wpdb->insert( 
				$table_name, 
				$array_properties
			);
			if ($result !== FALSE) {
				$this->loaded_data->id = $wpdb->insert_id;
			}
			if ( $wpdb->last_query !== '' ) {
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity::save > update > $wpdb->last_query = ' . $wpdb->last_query );
			}
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity::save > insert > $result = ' . print_r( $result, TRUE ) );
			if ( $wpdb->last_error !== '' ) {
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity::save > insert > $wpdb->last_result = ' . print_r( $wpdb->last_result, TRUE ) );
			}
		}
		return $result;
	}
	
	/**
	 * Supprime l'élément de la base de données
	 */
	public function delete() {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( $this->current_entity_type );
		$where_properties = array(
			'id'	=> $this->loaded_data->id
		);
		$wpdb->delete( 
			$table_name, 
			$where_properties
		);
		return true;
	}
	
	/**
	 * Se charge de récupérer des données en fonction de l'identifiant client
	 * @param string $action
	 * @param string $param
	 * @return array
	 */
	protected static function get_data_on_client_site( $action, $param = '1' ) {
		$buffer = '';
		
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$ref_client_url = $current_client->user_url;
		$route = '/connexion';
		$params = '?action=' .$action. '&param=' .urlencode( $param );
		
		$cached_version_entity = new WDGRESTAPI_Entity_Cache( FALSE, $params );
		$cached_value = $cached_version_entity->get_value();
		
		if ( !empty( $cached_value ) ) {
			$buffer = $cached_value;
			
		} else {
			$url = $ref_client_url . $route . $params;
//			WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity::get_data_on_client_site > $url : ' . $url);

			$remote_result = wp_remote_get(
				$url,
				array( 'timeout' => 10 )
			);
			
			if ( is_array( $remote_result ) && isset( $remote_result[ 'body' ] ) ) {
				$buffer = $remote_result["body"];
				$cached_version_entity->save( $params, $buffer );
				
			} else {
//				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity::get_data_on_client_site > error : ' . print_r( $remote_result, TRUE ) );
			}
		}
		return json_decode( $buffer );
	}
	
	/**
	 * Se charge de poster des données sur le site du client
	 * @param string $action
	 * @param string $param
	 * @param array $posted_params
	 * @return array
	 */
	protected static function post_data_on_client_site( $action, $param, $posted_params ) {
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$ref_client_url = $current_client->user_url;
		$route = '/connexion';
		$params = '?action=' .$action. '&param=' .urlencode( $param );
		$url = $ref_client_url . $route . $params;
		
		WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity::post_data_on_client_site > $url : ' . $url);
		$buffer = wp_remote_post(
			$url,
			array(
				'body'	=> $posted_params
			)
		);
//		WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity::post_data_on_client_site > $buffer : ' . print_r( $buffer, true ) );
		
		if ( !is_wp_error( $buffer ) ) {
			return $buffer["body"];
		} else {
			return FALSE;
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
	public static function upgrade_entity_db( $entity_type, $db_properties ) {
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
				$buffer = 'varchar(200)';
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
			
			case 'float':
				$buffer = 'float';
				break;
		}
		
		return $buffer;
	}
	
	/**
	 * Vérifie le format d'une date et corrige si nécessaire
	 * @param string $value
	 * @return string
	 */
	protected static function standardize_date( $value ) {
		$buffer = $value;
		if ( empty( $value ) || $value == null ) {
			$buffer = '0000-00-00';
		}
		return $buffer;
	}
	
	/**
	 * Vérifie le format d'une chaine datetime et corrige si nécessaire
	 * @param string $value
	 * @return string
	 */
	protected static function standardize_datetime( $value ) {
		$buffer = $value;
		if ( empty( $value ) || $value == null ) {
			$buffer = '0000-00-00 00:00:00';
		}
		return $buffer;
	}
	
}