<?php
class WDGRESTAPI_Entity_FileKYC extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'file-kyc';
	
	public static $file_entity_types = array( 'user', 'organization' );
	
	public static $document_types = array( 'id', 'passport', 'tax', 'welfare', 'family', 'birth', 'driving', 'kbis', 'status', 'capital-allocation', 'person2-doc1', 'person2-doc2', 'person3-doc1', 'person3-doc2' );
	
	private $file_data;

	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	/**
	 * Retourne un élément FileKYC en fonction de ses paramètres
	 * @param string $entity_type
	 * @param int $entity_id
	 * @param string $doc_type
	 * @return WDGRESTAPI_Entity_File
	 */
	public static function get_single( $entity_type, $entity_id, $doc_type ) {
		if ( empty( $entity_type ) || empty( $entity_id ) || empty( $doc_type ) ) {
			return FALSE;
		}

		$buffer = FALSE;
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$entity_id_query = "user_id=" .$entity_id;
		if ( $entity_type === 'organization' ) {
			$entity_id_query = "organization_id=" .$entity_id;
		}
		$query = "SELECT id FROM " .$table_name. " WHERE " .$entity_id_query. " AND doc_type='" .$doc_type. "' ORDER BY id desc";
		$loaded_data = $wpdb->get_row( $query );
		if ( !empty( $loaded_data->id ) ) {
			$buffer = new WDGRESTAPI_Entity_FileKYC( $loaded_data->id );
		}
		
		return $buffer;
	}
	
	/**
	 * Surcharge la fonction parente pour ajouter l'URL
	 */
	public function get_loaded_data() {
		$buffer = parent::get_loaded_data();
		$buffer->url = '';
		if ( !empty( $this->loaded_data->file_name ) ) {
			$buffer->url = home_url( '/wp-content/plugins/wdgrestapi/' .$this->get_path(). '/' .$this->loaded_data->file_name );
		}
		return $buffer;
	}
	
	/**
	 * Récupère la liste des documents relatifs à certains paramètres
	 */
	public static function get_list( $entity_type = '', $entity_id = '', $doc_type = '' ) {
		$buffer = array();
		
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		
		$query = "SELECT f.id FROM " .$table_name. " f";
		if ( !empty( $entity_type ) || !empty( $doc_type ) ) {
			$query .= " WHERE ";
			$query_where = "";
			
			if ( !empty( $entity_type ) ) {
				if ( $entity_type === 'organization' ) {
					$query_where = "f.organization_id=" .$entity_id;
				} else {
					$query_where = "f.user_id=" .$entity_id;
				}
			}
			
			if ( !empty( $doc_type ) ) {
				if ( !empty( $query_where ) ) {
					$query_where .= " AND ";
				}
				$query_where .= "f.doc_type='" .$doc_type. "'";
			}
			
			$query .= $query_where;
		}
		
		$loaded_data = $wpdb->get_results( $query );
		
		if ( !empty( $loaded_data ) ) {
			foreach ( $loaded_data as $file_data ) {
				$file_temp = new WDGRESTAPI_Entity_FileKYC( $file_data->id );
				array_push( $buffer, $file_temp->get_loaded_data() );
			}
		}
		
		return $buffer;
	}
	
	/**
	 * Initialise les données binaires du fichier
	 */
	public function set_file_data( $base64_file_data ) {
		$this->file_data = base64_decode( $base64_file_data );
	}

	/**
	 * Surcharge la fonction de sauvegarde pour renommer le fichier de la bonne façon
	 */
	public function save() {
		if ( in_array( $this->loaded_data->entity_type, self::$file_entity_types ) && in_array( $this->loaded_data->doc_type, self::$document_types ) ) {
			// Si c'est une mise à jour, il faut supprimer l'existant
			$this->try_remove_current_file();

			// Enregistrement du fichier à partir des données binaires
			$current_datetime = new DateTime();
			$this->loaded_data->update_date = $current_datetime->format( 'Y-m-d H:i:s' );
			$path = $this->make_path();
			$random_filename = $this->get_random_filename( $path, $this->loaded_data->file_extension );
			file_put_contents( $path . $random_filename, $this->file_data );
			$this->loaded_data->file_signature = md5( $this->file_data );

			// TODO : optimiser le fichier

			// Enregistrement des informations de base de données
			$this->loaded_data->file_name = $random_filename;
			$this->loaded_data->status = 'uploaded';
			parent::save();
			
		} else {
			return FALSE;
		}
	}

	/**
	 * Retourne le chemin du document (chemin créé à partir de la date d'envoi)
	 */
	private function get_path() {
		$datetime = new DateTime( $this->loaded_data->update_date );
		$date_str = $datetime->format( 'Y-m-d' );
		return 'files/kyc/' . $date_str;
	}

	/**
	 * Retourne le chemin du document relativement au fichier en cours
	 */
	private function get_relative_path() {
		return __DIR__. '/../' .$this->get_path(). '/';
	}
	
	/**
	 * Récupère le chemin du fichier et crée les dossiers nécessaires
	 */
	private function make_path() {
		$buffer = $this->get_relative_path();
		if ( !is_dir( $buffer ) ) {
			mkdir( $buffer, 0777, true );
		}
		return $buffer;
	}

	/**
	 * Supprime le fichier existant
	 */
	private function try_remove_current_file() {
		if ( !empty( $this->loaded_data->file_name ) ) {
			unlink( $this->get_relative_path() . $this->loaded_data->file_name );
		}
	}
	
	/**
	 * Créer un nom de fichier aléatoire
	 */
	private function get_random_filename( $path, $ext ) {
		$buffer = $this->loaded_data->id. '-';
		
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$size = strlen( $chars );
		for( $i = 0; $i < 15; $i++ ) {
			$buffer .= $chars[ rand( 0, $size - 1 ) ];
		}
		
		while ( file_exists( $path . $buffer . '.' . $ext ) ) {
			$buffer .= $chars[ rand( 0, $size - 1 ) ];
		}
		
		$buffer = $buffer . '.' . $ext;
		return $buffer;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'user_id'				=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'organization_id'		=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'doc_type'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'doc_index'				=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'file_extension'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'file_name'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'file_signature'		=> array( 'type' => 'longtext', 'other' => '' ),
		'update_date'			=> array( 'type' => 'datetime', 'other' => '' ),
		'status'				=> array( 'type' => 'varchar', 'other' => '' ),
		'metadata'				=> array( 'type' => 'longtext', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}