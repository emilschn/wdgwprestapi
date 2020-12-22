<?php
class WDGRESTAPI_Entity_SendinblueTemplate extends WDGRESTAPI_Entity {
	public static $entity_type = 'sendinblue_template';

	public function __construct( $slug = FALSE ) {
		parent::__construct( FALSE, self::$entity_type, self::$db_properties );

		if ( !empty( $slug ) ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
			$query = "SELECT * FROM " .$table_name. " WHERE slug = '" .$slug. "'";
			$row_data = $wpdb->get_row( $query );
			if ( !empty( $row_data ) ) {
				$this->loaded_data = $row_data;
			}
		}
	}

	public static function get_by_fr_id( $fr_id ) {
		global $wpdb;
		if ( empty( $wpdb ) ) {
			return FALSE;
		}
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = 'SELECT * FROM ' .$table_name. ' WHERE id_sib_fr=' .$fr_id;
		$result = $wpdb->get_row( $query );
		if ( empty( $result ) || empty( $result->id ) ) {
			return FALSE;
		}
		$user = new WDGRESTAPI_Entity_SendinblueTemplate( $result->slug );
		return $user;
	}

	/**
	 * Met à jour les textes à la fois sur SendInBlue et sur Transifex, selon les avancées des uns et des autres
	 */
	public function update_texts() {
		// On commence par récupérer les chaines traduites (car si on met à jour Transifex d'abord, ça les écrasera)
		$this->update_language_content_from_transifex( 'en' );
		// Puis on renvoie les données du template français vers Transifex
		$this->send_french_content_to_transifex();
	}

	/**
	 * Retourne l'objet SIB
	 */
	private function get_sendinblue() {
		include_once( plugin_dir_path( __FILE__ ) . '../libs/sendinblue/mailin.php');
		$mailin = new Mailin( 'https://api.sendinblue.com/v2.0', WDG_SENDINBLUE_API_KEY, 8000 );
		return $mailin;
	}

	/**
	 * Va chercher le contenu du template sur SiB
	 */
	public function get_french_content() {
		$mailin = $this->get_sendinblue();
		$data = array( 'id' => $this->loaded_data->id_sib_fr );
		$sendinblue_result = $mailin->get_campaign_v2( $data );
		return $sendinblue_result[ 'data' ][ 0 ][ 'html_content' ];
	}

	/**
	 * Envoie le contenu français (d'origine) vers Transifex
	 */
	public function send_french_content_to_transifex() {
		$french_content = $this->get_french_content();
		WDGRESTAPI_Lib_Transifex::create_or_update_resource( $this->loaded_data->slug, $french_content, 'Template ' .$this->loaded_data->slug );
	}

	/**
	 * Envoie le contenu traduit sur Transifex vers SendInBlue
	 */
	public function update_language_content_from_transifex( $language_code ) {
		// Récupération du contenu traduit sur Transifex
		$translation_content = WDGRESTAPI_Lib_Transifex::get_resource_translation( $this->loaded_data->slug, $language_code );
		if ( empty( $translation_content ) ) {
			return FALSE;
		}

		$mailin = $this->get_sendinblue();

		// Si il n'y a pas encore de template traduit, on le crée
		if ( empty( $this->loaded_data->{ 'id_sib_' .$language_code } ) ) {
			// Récupération des données du template français de base pour reprendre le plus gros des données
			$data = array( 'id' => $this->loaded_data->id_sib_fr );
			$sendinblue_fr_template = $mailin->get_campaign_v2( $data );
			$template_fr_data = $sendinblue_fr_template[ 'data' ][ 0 ];

			// Définition des données pour le nouveau template
			$data = array(
				'from_name'			=> $template_fr_data[ 'from_name' ],
				'template_name'		=> $template_fr_data[ 'campaign_name' ] . ' [en]',
				'bat'				=> '',
				'html_content'		=> $translation_content,
				'html_url' 			=> '',
				'subject'			=> $template_fr_data[ 'subject' ] . ' [en]',
				'from_email'		=> $template_fr_data[ 'from_email' ],
				'reply_to'			=> $template_fr_data[ 'reply_to' ],
				'to_field'			=> $template_fr_data[ 'to_field' ],
				'status'			=> 0,
				'attachment_url'	=> ''
			);
			$result = $mailin->create_template( $data );

			// Si le template traduit est créé, on met à jour l'id dans la BDD
			$this->loaded_data->{ 'id_sib_' .$language_code } = $result[ 'data' ][ 'id' ];
			$this->save();

		} else {
			$data = array(
				'id'			=> $this->loaded_data->{ 'id_sib_' .$language_code },
				'html_content'	=> $translation_content
			);
			$mailin->update_template( $data );
		}
	}


/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'slug'					=> array( 'type' => 'varchar', 'other' => '' ),
		'description'			=> array( 'type' => 'varchar', 'other' => '' ),
		'id_sib_fr'				=> array( 'type' => 'id', 'other' => '' ),
		'id_sib_en'				=> array( 'type' => 'id', 'other' => 'DEFAULT 0' ),
		'variables_names'		=> array( 'type' => 'longtext', 'other' => '' ),
		'wdg_email_cc'			=> array( 'type' => 'longtext', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}