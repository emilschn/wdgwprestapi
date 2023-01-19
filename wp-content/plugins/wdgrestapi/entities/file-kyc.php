<?php
class WDGRESTAPI_Entity_FileKYC extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'file_kyc';
	
	public static $file_entity_types = array( 'user', 'organization' );
	
	public static $document_types = array( 'id', 'passport', 'tax', 'welfare', 'family', 'birth', 'driving', 'criminal_record', 'kbis', 'status', 'capital-allocation', 'person2-doc1', 'person2-doc2', 'person3-doc1', 'person3-doc2', 'person4-doc1', 'person4-doc2', 'bank' );

	public static $avoid_send_to_lw = array( 'criminal_record' );

	public static $authorized_format_list = array('pdf', 'jpg', 'jpeg', 'bmp', 'gif', 'tif', 'tiff', 'png');

	private $file_data;

	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	/**
	 * Retourne un élément FileKYC en fonction de son gateway_id
	 * @param int $gateway_id
	 * @return WDGRESTAPI_Entity_File
	 */
	public static function get_single_by_gateway_id( $gateway_id ) {
		if ( empty( $gateway_id ) ) {
			return FALSE;
		}

		$buffer = FALSE;
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT id FROM " .$table_name. " WHERE gateway_user_id=" . $gateway_id . " OR  gateway_organization_id=" . $gateway_id . "  ORDER BY id desc";
		$loaded_data = $wpdb->get_row( $query );
		if ( !empty( $loaded_data->id ) ) {
			$buffer = new WDGRESTAPI_Entity_FileKYC( $loaded_data->id );
		}
		
		return $buffer;
	}
	
	/**
	 * Retourne un élément FileKYC en fonction de ses paramètres
	 * @param string $entity_type
	 * @param int $entity_id
	 * @param string $doc_type
	 * @param string $doc_index
	 * @return WDGRESTAPI_Entity_File
	 */
	public static function get_single( $entity_type, $entity_id, $doc_type, $doc_index = 1 ) {
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
		$query = "SELECT id FROM " .$table_name. " WHERE " .$entity_id_query. " AND doc_type='" .$doc_type . "' AND doc_index='" .$doc_index. "' ORDER BY id desc";
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::get_single > $query = ' . $query, FALSE );
					
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
		if ( !empty( $buffer ) ) {
			$buffer->url = '';
			if ( !empty( $this->loaded_data->file_name ) ) {
				$buffer->url = home_url( '/wp-content/plugins/wdgrestapi/' .$this->get_path(). '/' .$this->loaded_data->file_name );
			}
		}
		return $buffer;
	}
	
	/**
	 * Récupère la liste des documents relatifs à certains paramètres
	 */
	public static function get_list( $entity_type, $user_id, $organization_id ) {
		$buffer = array();
		
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		
		$query = "SELECT f.id FROM " .$table_name. " f";
		if ( !empty( $entity_type ) ) {
			$query .= " WHERE ";
			$query .= " f.status IN ('uploaded', 'merged') ";
			
			if ( !empty( $entity_type ) ) {
				if ( $entity_type === 'organization' && !empty( $organization_id ) ) {
					$query .= " AND f.organization_id=" .$organization_id;
				} else if ( !empty( $user_id ) ){
					$query .= " AND f.user_id=" .$user_id;
				}
			}
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
	public function save( $needSendFile = TRUE ) {
		// à chaque modification sur le fichier kyc, on envoie les infos à LW
		if ( in_array( $this->loaded_data->doc_type, self::$document_types ) ) {
			// Si on passe en statut 'removed', il faut supprimer l'existant
			if ( $this->loaded_data->status == 'removed' ) {
				$this->try_remove_current_file();
				$this->loaded_data->file_signature = '';
				$this->loaded_data->gateway_user_id = 0;
				$this->loaded_data->gateway_organization_id = 0;
				$needSendFile = FALSE; // on n'a pas besoin de l'envoyer à LW puisqu'on n'envoie pas le status
			}

			// Enregistrement du fichier à partir des données binaires
			if ( !empty( $this->file_data ) ) {
				$path = $this->make_path();
				$random_filename = $this->get_random_filename( $path, $this->loaded_data->file_extension );
				$written = file_put_contents( $path . $random_filename, $this->file_data );
				if (!$written ){
					return 'SERVER';
				}

				$this->compress_file( $path . $random_filename );

				$this->loaded_data->file_signature = md5( $this->file_data );
				$this->loaded_data->status = 'uploaded';
				$this->loaded_data->gateway = 'lemonway';
				$this->loaded_data->file_name = $random_filename;


				$file_size = strlen( $this->file_data );
				if ( $file_size < 10 ) {
					WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity_FileKYC::save error UPLOAD');
					return 'UPLOAD';
				}
				if ( ( $file_size / 1024) / 1024 > 8 ) {
					WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity_FileKYC::save error SIZE');
					return 'SIZE';
				}
				// on ne met à jour l'update_date que si on modifie le fichier (pour le trouver dans le bon dossier)
				$current_datetime = new DateTime();
				$this->loaded_data->update_date = $current_datetime->format( 'Y-m-d H:i:s' );
				WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity_FileKYC::save change update_date '. $this->loaded_data->update_date . ' de '.$this->loaded_data->file_name);
			}


			if ( $needSendFile ){
				$this->loaded_data->gateway_user_id = 0;
				$this->loaded_data->gateway_organization_id = 0;
				WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity_FileKYC::save on ajoute une action pour envoyer à LW');
				// Ajout d'une tâche décalée d'envoi à LW
				$new_queued_action = new WDGRESTAPI_Entity_QueuedAction();
				$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
				$new_queued_action->set_property( 'client_user_id', $current_client->ID );
				$new_queued_action->set_property( 'priority', 'high' );
				$new_queued_action->set_property( 'action', 'document_kyc_send_to_lemonway' );
				$new_queued_action->set_property( 'entity_id', $this->loaded_data->id );
				$new_queued_action->save();
			}
			// Enregistrement des informations de base de données
			parent::save();
			
		} else {
			WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity_FileKYC::save FALSE');
			return FALSE;
		}
	}

	private function compress_file( $file_path ) {
		$quality = 80;
		// TODO : si besoin d'optimiser les performances, essayer de ne télécharger que le début du fichier
		$info = getimagesize( $file_path );

		switch ( $info['mime'] ) {
			case 'image/jpeg':
				$image = imagecreatefromjpeg( $file_path );
				break;

			case 'image/gif':
				$image = imagecreatefromgif( $file_path );
				break;

			case 'image/png':
				$image = imagecreatefrompng( $file_path );
				break;
		}

		if ( $image ) {
			imagejpeg( $image, $file_path, $quality );
			$this->file_data = file_get_contents( $file_path );
		}
	}

	/**
	 * Envoie le fichier vers LW après l'avoir optimisé
	 */
	public function send_to_lw() {
		if ( $this->loaded_data->status != 'uploaded' ) {
			return FALSE;
		}

		// Si on ne doit pas transmettre ce type de doc à LW, on stoppe la procédure
		if ( in_array( $this->loaded_data->doc_type, self::$avoid_send_to_lw ) ) {
			return FALSE;
		}

		// TODO : optimiser le fichier
		// TODO : enregistrer en base si le wallet est authentifié ou pas ?
		// TODO : gérer les retours (erreurs)
		$buffer = 'sent';
		// Envoi à LW dans le bon slot
		$wdgrestapi = WDGRESTAPI::instance();
		$wdgrestapi->add_include_lib( 'gateways/lemonway' );
		$lw = WDGRESTAPI_Lib_Lemonway::instance();
		$lw_document_id = WDGRESTAPI_Lib_Lemonway::get_lw_document_id_from_document_type( $this->loaded_data->doc_type, $this->loaded_data->doc_index );
		$lw_file_data = file_get_contents( $this->get_relative_path() . $this->loaded_data->file_name );

		// si c'est un utilisateur
		if ( !empty( $this->loaded_data->user_id ) ) {
			$user = new WDGRESTAPI_Entity_User( $this->loaded_data->user_id );
			$user_wallet_id = $user->get_wallet_id( 'lemonway' );
			
			if ( empty( $user_wallet_id ) ) {
				$buffer = 'empty_wallet';
			} else if ( $this->loaded_data->doc_type == 'bank' ){
				// si c'est un rib, on envoie toujours à LW
				$this->loaded_data->gateway_user_id = $lw->wallet_upload_file( $user_wallet_id, $this->loaded_data->file_name, $lw_document_id, $lw_file_data );
			} else if( $lw->get_wallet_details( $user_wallet_id )->STATUS == 6 ){
				// sinon, on n'envoie que si pas authentifié
				$buffer = 'already_authentified';
			} else {
				// s'il s'agit de documents d'identité 
				$id_documents_array = array('id', 'passport', 'tax', 'welfare', 'family', 'birth', 'driving');			
				if( in_array( $this->loaded_data->doc_type , $id_documents_array) ){
					// il peut y avoir une fusion des recto et verso à faire
					$buffer = $this->merge_files_if_needed($wdgrestapi, $lw, $user_wallet_id, $lw_document_id, $lw_file_data);				
				} else {
					$buffer = 'unknown_document';
				}
			}
		}

		// si c'est une organisation
		if ( !empty( $this->loaded_data->organization_id ) ) {
			$organization = new WDGRESTAPI_Entity_Organization( $this->loaded_data->organization_id );
			$organization_wallet_id = $organization->get_wallet_id( 'lemonway' );
			if ( !empty( $organization_wallet_id ) && ( $this->loaded_data->doc_type == 'bank' || $lw->get_wallet_details( $organization_wallet_id )->STATUS != 6 )  ) {
				$this->loaded_data->gateway_organization_id = $lw->wallet_upload_file( $organization_wallet_id, $this->loaded_data->file_name, $lw_document_id, $lw_file_data );
			} else {
				$buffer = 'already_authentified';
			}
		}

		parent::save();

		// TODO : gérer les retours (erreurs)
		return $buffer;
	}

	/**
	 * Vérifie s'il faut, ou non, fusionner un recto et un verso en un seul document, et envoit le document
	 *
	 * @param WDGRESTAPI $wdgrestapi
	 * @param WDGRESTAPI_Lib_Lemonway $lw
	 * @param mixed $user_wallet_id
	 * @param mixed $lw_document_id
	 * @param mixed $lw_file_data
	 * @return string
	 */
	private function merge_files_if_needed($wdgrestapi, $lw, $user_wallet_id, $lw_document_id, $lw_file_data) {
		$buffer = 'sent';
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > $this->loaded_data->id = ' . $this->loaded_data->id, $this->current_entity_type );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > $this->loaded_data->doc_type = ' . $this->loaded_data->doc_type, $this->current_entity_type );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > $this->loaded_data->doc_index = ' . $this->loaded_data->doc_index, $this->current_entity_type );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > $this->loaded_data->file_name = ' . $this->loaded_data->file_name, $this->current_entity_type );
		
		// on essaie de savoir si pour ce fichier on a un recto et un verso
		if( $this->loaded_data->doc_index == 2){
			// si c'est un verso on essaie de récupérer le recto 
			$verso_kyc = $this;
			$verso = $this->get_relative_path() . $this->loaded_data->file_name;
			$recto_kyc = self::get_single('user', $this->loaded_data->user_id, $this->loaded_data->doc_type, 1);					
			if( isset($recto_kyc) && $recto_kyc !== FALSE ) {
				$recto = $this->get_relative_path($recto_kyc->loaded_data->update_date) . $recto_kyc->loaded_data->file_name;
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > on recupère le $recto = ' . $recto_kyc->loaded_data->id . ' path = ' . $recto, $this->current_entity_type );
			}
		} else {
			// si c'est un recto, on essaie de récupérer le verso
			$recto_kyc = $this;
			$recto = $this->get_relative_path() . $this->loaded_data->file_name;
			$verso_kyc = self::get_single('user', $this->loaded_data->user_id, $this->loaded_data->doc_type, 2);	
			if( isset($verso_kyc) && $verso_kyc !== FALSE ) {
				$verso = $this->get_relative_path($verso_kyc->loaded_data->update_date) . $verso_kyc->loaded_data->file_name;
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > on a récupéré le $verso = ' . $verso_kyc->loaded_data->id . ' path = ' . $verso, $this->current_entity_type );
			}			
		}

		$needMergeFile = FALSE;
		$needSendFile = TRUE;

		// si on a bel et bien un recto et un verso
		if( !empty($recto) && !empty($verso) ) {
			$needMergeFile = TRUE;
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > il y a un recto et un verso ' , $this->current_entity_type );

			// on regarde si on a déjà un fichier fusionné en base (doc_index 0)					
			$merge_kyc = self::get_single('user', $this->loaded_data->user_id, $this->loaded_data->doc_type, 0);	
			
			if( isset($merge_kyc) && $merge_kyc !== FALSE ) {
				// si on en a déjà un, c'est celui là qu'on envoie
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > on a deja un fichier mergé enregistré ' , $this->current_entity_type );
				$lw_file_data = file_get_contents( $this->get_relative_path($merge_kyc->loaded_data->update_date) . $merge_kyc->loaded_data->file_name );
				// on vérifie si les file_signature des recto et verso constituants le fichier mergé correspond au file_signature des fichiers actuels
				$metadata_decoded = json_decode( $merge_kyc->loaded_data->metadata );
				if ( $metadata_decoded->recto_file_signature == $recto_kyc->loaded_data->file_signature && $metadata_decoded->verso_file_signature == $verso_kyc->loaded_data->file_signature ){
					// pas besoin de refaire le fichier, il correspond bien aux recto et verso actuels
					WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > pas besoin de refaire le fichier, il correspond bien aux recto et verso actuels ' , $this->current_entity_type );
					$needMergeFile = FALSE;
					$mergeFileName = $merge_kyc->loaded_data->file_name;
					$mergeDateTime = $merge_kyc->loaded_data->update_date;	
				}

				if( isset($merge_kyc->loaded_data->gateway_user_id) && $merge_kyc->loaded_data->gateway_user_id != 0){
					// il a déjà été envoyé avec succès à LW
					WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > il a déjà été envoyé avec succès à LW ' , $this->current_entity_type );
					$needSendFile = FALSE;
				}
			} 

			// si on a besoin de concaténer 2 fichiers
			if ( $needMergeFile ) {
				$wdgrestapi->add_include_lib( 'merge-files/mergeFiles' );
				// génère un nouveau nom de fichier pour le fichier concaténé
				$current_datetime = new DateTime();
				$mergeDateTime = $current_datetime->format( 'Y-m-d H:i:s' );	
				$path = $this->make_path( $mergeDateTime );
				$mergeFileName = $this->get_random_filename( $path, 'pdf' );
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > on a besoin de concaténer 2 fichiers $mergeFileName = ' . $mergeFileName, $this->current_entity_type );
				// on fusionne les 2 documents en un seul
				$merge_success = WDGRESTAPI_Lib_MergeFiles::mergeRectoVersoFiles($recto, $verso, $this->get_relative_path( $mergeDateTime ) . $mergeFileName);	
								
				if( isset($merge_kyc) && $merge_kyc !== FALSE ) {
					$merge_kyc->set_property( 'status', 'removed' );// on supprime l'ancien fichier qui ne correspond plus
					$merge_kyc->save( FALSE );
				}
				// s'il y a eu un pb de concaténation
				if ( $merge_success != TRUE ) {		
					WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > merge a échoué $merge_success = ' . $merge_success, $this->current_entity_type );
					// TODO : que faire s'il y a eu un pb de merge (hormis de loguer) ?
					return $merge_success;
				}
			}
				
			if ( $needSendFile ) {
				// et c'est ce document qu'on envoie à LW dans le bon slot (quitte à écraser le recto envoyé précédemment)		
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > on envoie le fichier suivant $mergeFileName = ' . $mergeFileName, $this->current_entity_type );
				$lw_file_data = file_get_contents( $this->get_relative_path( $mergeDateTime ) . $mergeFileName );
				$lw_document_id = WDGRESTAPI_Lib_Lemonway::get_lw_document_id_from_document_type( $this->loaded_data->doc_type, 1 );
				$gateway_user_id = $lw->wallet_upload_file( $user_wallet_id, $mergeFileName, $lw_document_id, $lw_file_data );
				// on enregistre ce document en base avec un doc_index 0
				// si le document n'existait pas en base, on le créé
				if( !isset($merge_kyc) || $merge_kyc === FALSE ) {
					$merge_kyc = new WDGRESTAPI_Entity_FileKYC();
					$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
					$merge_kyc->set_property( 'client_user_id', $current_client->ID );
					$merge_kyc->set_property( 'user_id', $this->loaded_data->user_id );
					$merge_kyc->set_property( 'doc_type', $this->loaded_data->doc_type );
					$merge_kyc->set_property( 'doc_index', 0 );// doc_index à 0 pour indiquer que c'est un fichier mergé
					$merge_kyc->set_property( 'file_extension', 'pdf' );
					$merge_kyc->set_property( 'status', 'merged' );// on met un nouveau status pour indiquer que c'est un fichier mergé
					$merge_kyc->set_property( 'gateway', 'lemonway' );
				}
				// on enregistre l'id LW dans le fichier mergé, et aussi dans le fichier d'origine (pour afficher le bon état)
				$merge_kyc->set_property( 'gateway_user_id', $gateway_user_id );
				$this->loaded_data->gateway_user_id = $gateway_user_id;
				// on met à jour les informations spécifiques
				$merge_kyc->set_property( 'file_name', $mergeFileName );
				$merge_kyc->set_property( 'file_signature', md5( $lw_file_data ) );
				// on ne met à jour l'update_date que si on modifie le fichier (pour le trouver dans le bon dossier)
				$merge_kyc->set_property( 'update_date', $mergeDateTime );
				
				WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Entity_FileKYC::merge_files_if_needed change update_date '. $this->loaded_data->update_date . ' de '.$this->loaded_data->file_name);
				// on enregistre le file_signature des recto et verso en metadata du fichier mergé
				$metadata = array();
				$metadata[ 'recto_file_signature' ] = $recto_kyc->loaded_data->file_signature;
				$metadata[ 'verso_file_signature' ] = $verso_kyc->loaded_data->file_signature;
				$merge_kyc->set_property( 'metadata', json_encode( $metadata ) );
				$merge_kyc->save( FALSE ); // on enregistre ce nouveau fichier sans lui ajouter une tâche d'envoi à LW puisqu'on vient de le faire
			}
			
		} else {
			// on n'a (pour l'instant) qu'un fichier pour cette pièce d'identité, c'esst peut-être déjà un recto-verso, on l'envoie
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Entity_FileKYC::merge_files_if_needed > un seul fichier à envoyer : $this->loaded_data->file_name = ' . $this->loaded_data->file_name, $this->current_entity_type );
				
			$this->loaded_data->gateway_user_id = $lw->wallet_upload_file( $user_wallet_id, $this->loaded_data->file_name, $lw_document_id, $lw_file_data );
		}
		return $buffer;
	}

	/**
	 * Retourne le chemin du document (chemin créé à partir de la date d'envoi)
	 */
	private function get_path( $datetime = '' ) {		
		if ( $datetime == '' ) {			
			$datetime = new DateTime( $this->loaded_data->update_date );
		} else {
			$datetime = new DateTime( $datetime );
		}
		$date_str = $datetime->format( 'Y-m-d' );
		return 'files/kyc/' . $date_str;
	}

	/**
	 * Retourne le chemin du document relativement au fichier en cours
	 */
	private function get_relative_path( $updateDateTime = '' ) {
		return __DIR__. '/../' .$this->get_path( $updateDateTime ). '/';
	}
	
	/**
	 * Récupère le chemin du fichier et crée les dossiers nécessaires
	 */
	private function make_path( $updateDateTime = '' ) {
		$buffer = $this->get_relative_path( $updateDateTime );
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
		if ( !empty( $this->loaded_data->user_id ) ) {
			$buffer = $this->loaded_data->user_id. '-';
		} else {
			$buffer = $this->loaded_data->organization_id. '-';
		}
		
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
		'organization_id'		=> array( 'type' => 'id', 'other' => '' ),
		'doc_type'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'doc_index'				=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'file_extension'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'file_name'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'file_signature'		=> array( 'type' => 'longtext', 'other' => '' ),
		'update_date'			=> array( 'type' => 'datetime', 'other' => '' ),
		'status'				=> array( 'type' => 'varchar', 'other' => '' ),
		'gateway'				=> array( 'type' => 'varchar', 'other' => '' ),
		'gateway_user_id'		=> array( 'type' => 'id', 'other' => '' ),
		'gateway_organization_id'=> array( 'type' => 'id', 'other' => '' ),
		'metadata'				=> array( 'type' => 'longtext', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}