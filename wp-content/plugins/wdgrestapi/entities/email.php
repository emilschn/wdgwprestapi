<?php
class WDGRESTAPI_Entity_Email extends WDGRESTAPI_Entity {
	public static $entity_type = 'email';

	public function __construct($id = FALSE) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}

	public static function list_get($id_template, $recipient_email) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE template = " .$id_template. " AND recipient='" .$recipient_email. "' ORDER BY id desc";
		$results = $wpdb->get_results( $query );

		return $results;
	}

	public static function list_get_by_project($project_id) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT * FROM " .$table_name. " WHERE id_project = " .$project_id;
		$results = $wpdb->get_results( $query );

		return $results;
	}

	/**
	 *
	 */
	public function save() {
		if ( empty( $this->loaded_data->id ) ) {
			date_default_timezone_set( 'Europe/Paris' );
			$current_date = new DateTime();
			$this->set_property( 'date', $current_date->format( 'Y-m-d H:i:s' ) );
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$this->set_property( 'caller', $current_client->ID );
		}
		parent::save();
	}

	/**
	 * Mail sending procedure
	 */
	public function send() {
		switch ( $this->loaded_data->tool ) {
			case 'sendinblue':
				$buffer = $this->send_sendinblue_mail();
				break;
			case 'sms':
				$buffer = $this->send_sendinblue_sms();
				break;
		}

		return $buffer;
	}

	private function send_sendinblue_mail() {
		include_once plugin_dir_path( __FILE__ ) . '../libs/sendinblue/mailin.php';
		$mailin = new Mailin( 'https://api.sendinblue.com/v2.0', WDG_SENDINBLUE_API_KEY, 8000 );

		// Détermination de la langue d'affichage en fonction du recipient
		// Par défaut, on envoie le template français
		$template_id = $this->loaded_data->template;
		// Pour l'instant, on ne cherche la langue que quand il y a un seul destinataire
		// TODO : envoyer des templates différents selon les groupes d'utilisateurs par langue
		if ( strpos( $this->loaded_data->recipient, ',' ) === FALSE ) {
			$user_recipient = WDGRESTAPI_Entity_User::get_by_email( $this->loaded_data->recipient );
			if ( !empty( $user_recipient ) ) {
				$user_recipient_data = $user_recipient->get_loaded_data();
				$user_recipient_language = $user_recipient_data->language;
				if ( !empty( $user_recipient_language ) && $user_recipient_language != 'fr' ) {
					$template_entity = WDGRESTAPI_Entity_SendinblueTemplate::get_by_fr_id( $template_id );
					$template_entity_data = $template_entity->get_loaded_data();
					if ( !empty( $template_entity_data->{ 'id_sib_' .$user_recipient_language } ) ) {
						$template_id = $template_entity_data->{ 'id_sib_' .$user_recipient_language };
					} else {
						if ( !empty( $template_entity_data->id_sib_en ) ) {
							$template_id = $template_entity_data->id_sib_en;
						}
					}
					$this->loaded_data->template = $template_id;
				}
			}
		}

		$recipients = str_replace( ',', '|', $this->loaded_data->recipient );
		$options = json_decode( $this->loaded_data->options );
		$replyto = ( empty( $options->replyto ) ) ? 'bonjour@wedogood.co' : $options->replyto;
		$data = array(
			'id'		=> $template_id,
			'to'		=> 'admin@wedogood.co',
			'bcc'		=> $recipients,
			'replyto'	=> $replyto,
			'attr'		=> $options
		);

		$is_personal = ( isset( $options->personal ) && !empty( $options->personal ) );
		$is_admin_skipped = ( isset( $options->skip_admin ) && !empty( $options->skip_admin ) );

		// Est-ce qu'on envoie directement à l'utilisateur ?
		if ( $is_personal ) {
			$data[ 'to' ] = $data[ 'bcc' ];
			$data[ 'bcc' ] = 'admin@wedogood.co';

		// Pour certains templates, on n'envoie pas de copie à admin, on envoie directement à l'utilisateur
		} else {
			if ( $is_admin_skipped ) {
				$data[ 'to' ] = $data[ 'bcc' ];
				$data[ 'bcc' ] = '';
			}
		}

		// Possibilité d'ajouter une pièce jointe
		if ( isset( $options->url_attachment ) && !empty( $options->url_attachment ) ) {
			$data[ 'attachment_url' ] = $options->url_attachment;
		}

		$buffer = 'error';
		try {
			$sendinblue_result = $mailin->send_transactional_template( $data );
			if ( $sendinblue_result[ 'code' ] == 'success' ) {
				$buffer = 'success';
			}
			$this->loaded_data->result = json_encode( $sendinblue_result );
		} catch ( Exception $e ) {
			$this->loaded_data->result = 'Error : ' . $e->getMessage();
		}

		$this->save();

		return $buffer;
	}

	/**
	 * Processus complet : création d'une nouvelle liste, ajout des e-mails à la liste et création d'une campagne avec la liste
	 * @return boolean
	 */
	private function send_sendinblue_sms() {
		include_once plugin_dir_path( __FILE__ ) . '../libs/sendinblue/mailin.php';
		$mailin = new Mailin( 'https://api.sendinblue.com/v2.0', WDG_SENDINBLUE_API_KEY, 8000 );

		try {
			// Création d'une nouvelle liste
			$current_date = new DateTime();
			$data_new_list = array(
				'list_name'		=> "(Supprimer API) Liste temporaire - date : " . $current_date->format( 'Y-m-d H:i:s' ),
				'list_parent'	=> 1
			);
			$create_list_result = $mailin->create_list( $data_new_list );

			if ( !isset( $create_list_result[ 'data' ] ) || !isset( $create_list_result[ 'data' ][ 'id' ] ) ) {
				$sendinblue_result = $create_list_result;
			} else {
				$new_list_id = $create_list_result[ 'data' ][ 'id' ];

				// Ajout des utilisateurs à la liste
				$recipient_list = explode( ',', $this->loaded_data->recipient );
				$data_add_users = array(
					'id'		=> $new_list_id,
					'users'		=> $recipient_list
				);
				$mailin->add_users_list( $data_add_users );

				// Création de la campagne avec envoi direct
				$data = array(
					'name'			=> "(Supprimer API) Campagne temporaire - date : " . $current_date->format( 'Y-m-d H:i:s' ),
					'sender'		=> "WE DO GOOD",
					'content'		=> $this->loaded_data->template,
					'listid'		=> array( $new_list_id ),
					'send_now'		=> 1
				);
				$sendinblue_result = $mailin->create_sms_campaign( $data );
			}

			$this->loaded_data->result = json_encode( $sendinblue_result );
		} catch ( Exception $e ) {
			$this->loaded_data->result = 'Error : ' . $e->getMessage();
		}

		$this->save();

		return true;
	}

	public static function clean_sms_list() {
		include_once plugin_dir_path( __FILE__ ) . '../libs/sendinblue/mailin.php';
		$mailin = new Mailin( 'https://api.sendinblue.com/v2.0', WDG_SENDINBLUE_API_KEY, 8000 );

		try {
			$data_list = array(
				'list_parent'	=> 1
			);
			$array_mailin_list = $mailin->get_lists( $data_list );
			$date_today = new DateTime();

			foreach ( $array_mailin_list[ 'data' ] as $mailin_list_item ) {
				if ( isset( $mailin_list_item[ 'name' ] ) ) {
					// Si c'est une liste à supprimer
					$list_name = $mailin_list_item[ 'name' ];
					if ( strpos( $list_name, 'Supprimer API' ) !== FALSE ) {
						// Si la liste a été créée il y a plus de 2 jours
						$date_entered = new DateTime( $mailin_list_item[ 'entered' ] );
						$date_interval = $date_today->diff( $date_entered );
						if ( $date_interval->days > 2 ) {
							$data = array(
								'id' => $mailin_list_item[ 'id' ]
							);
							$mailin->delete_list( $data );
						}
					}
				}
			}
		} catch ( Exception $e ) {
		}
	}

	/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/

	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'date'					=> array( 'type' => 'datetime', 'other' => '' ),
		'caller'				=> array( 'type' => 'id', 'other' => '' ),
		'tool'					=> array( 'type' => 'varchar', 'other' => '' ),
		'template'				=> array( 'type' => 'varchar', 'other' => '' ),
		'recipient'				=> array( 'type' => 'longtext', 'other' => '' ),
		'id_project'			=> array( 'type' => 'id', 'other' => '' ),
		'result'				=> array( 'type' => 'longtext', 'other' => '' ),
		'options'				=> array( 'type' => 'longtext', 'other' => '' )
	);

	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_Email::$entity_type, WDGRESTAPI_Entity_Email::$db_properties );
	}
}