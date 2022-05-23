<?php
class WDGRESTAPI_Entity_Email extends WDGRESTAPI_Entity {
	public static $entity_type = 'email';

	public function __construct($id = FALSE) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}

	/**
	 * Retourne tous les mails liés à un destinataire
	 */
	public static function list_get($id_template, $recipient_email) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT id, date, recipient, template FROM " .$table_name. " WHERE template = " .$id_template. " AND recipient='" .$recipient_email. "' ORDER BY id desc";
		$results = $wpdb->get_results( $query );

		return $results;
	}

	/**
	 * Liste des mails envoyés liés à un projet
	 */
	public static function list_get_by_project($project_id) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = "SELECT id, date, recipient, template FROM " .$table_name. " WHERE id_project = " .$project_id;
		$results = $wpdb->get_results( $query );

		return $results;
	}

	/**
	 * Enregistrement BDD pour garder une trace de l'action
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
	 * Envoi de notification
	 */
	public function send() {
		switch ( $this->loaded_data->tool ) {
			case 'sendinblue':
			case 'sendinblue-v3':
				$buffer = $this->send_sendinblue_mail_v3();
				break;
			case 'sms':
				$buffer = $this->send_sendinblue_sms();
				break;
		}

		return $buffer;
	}

	/**
	 * Envoi de mail via SendInBlue v3
	 */
	private function send_sendinblue_mail_v3() {
		$wdgrestapi = WDGRESTAPI::instance();
		$wdgrestapi->add_include_lib( 'sendinblue/sendinblue-v3-helper' );

		$sib_instance = SIBv3Helper::instance();

		$options = json_decode( $this->loaded_data->options );
		$is_personal = ( isset( $options->personal ) && !empty( $options->personal ) );
		$is_admin_skipped = ( isset( $options->skip_admin ) && !empty( $options->skip_admin ) );

		// Séparation des destinataires pour transmettre à SiB
		$list_recipients = array( 'admin@wedogood.co' );
		
		if ( strpos( $this->loaded_data->recipient, ', ') !== FALSE ) {
			$list_recipients_bcc_temp = explode( ',', $this->loaded_data->recipient );
			$list_recipients_bcc = array();
			foreach ( $list_recipients_bcc_temp as $recipient ) {
				if ( strpos( $recipient, '@ ') !== FALSE ) {
					array_push( $list_recipients_bcc, $recipient );
				}
			}
		} else {
			$list_recipients_bcc = explode( ',', $this->loaded_data->recipient );
		}

		$list_recipients_cc = array();
		$replyto = ( empty( $options->replyto ) ) ? 'bonjour@wedogood.co' : $options->replyto;
		$sender_name = ( empty( $options->sender_name ) ) ? 'WE DO GOOD' : $options->sender_name;
		$sender_email = ( empty( $options->sender_email ) ) ? 'admin@wedogood.co' : $options->sender_email;

		// Est-ce qu'on envoie directement à l'utilisateur ?
		if ( $is_personal ) {
			$list_recipients = $list_recipients_bcc;
			$list_recipients_bcc = array( 'admin@wedogood.co' );

		// Pour certains templates, on n'envoie pas de copie à admin, on envoie directement à l'utilisateur
		} else {
			if ( $is_admin_skipped ) {
				$list_recipients = $list_recipients_bcc;
				$list_recipients_bcc = array();
			}
		}

		// Possibilité d'ajouter une pièce jointe
		$attachment_url = '';
		if ( isset( $options->url_attachment ) && !empty( $options->url_attachment ) ) {
			$attachment_url = $options->url_attachment;
		}

		$buffer = 'error';
		try {
			$sendinblue_result = $sib_instance->sendHtmlEmail($options->content, $options->object, $list_recipients, $list_recipients_bcc, $list_recipients_cc, $sender_name, $sender_email, $replyto, $attachment_url);
			if ( !empty( $sendinblue_result ) ) {
				$result_to_save = array(
					'code' => 'success',
					'data' => array(
						'message-id' => $sendinblue_result
					),
				);
				$this->loaded_data->result = json_encode( $result_to_save );
			} else {
				$this->loaded_data->result = SIBv3Helper::getLastErrorMessage();
			}
		} catch ( Exception $e ) {
			$this->loaded_data->result = 'Error : ' . $e->getMessage();
		}

		$this->save();

		return $buffer;
	}

	/**
	 * Envoi de SMS via SendInBlue
	 * Processus complet : création d'une nouvelle liste, ajout des e-mails à la liste et création d'une campagne avec la liste
	 * @return boolean
	 */
	private function send_sendinblue_sms() {
		$wdgrestapi = WDGRESTAPI::instance();
		$wdgrestapi->add_include_lib( 'sendinblue/sendinblue-v3-helper' );

		$sib_instance = SIBv3Helper::instance();

		try {
			$user_entity = WDGRESTAPI_Entity_User::get_by_email( $this->loaded_data->recipient );
			$user_entity_data = $user_entity->get_loaded_data();
			$user_phone_number = $user_entity_data->phone_number;
			$sendinblue_result = $sib_instance->sendSmsTransactional( $user_phone_number, $this->loaded_data->template );

			if ( !empty( $sendinblue_result ) ) {
				$this->loaded_data->result = json_encode( $sendinblue_result );
			} else {
				$this->loaded_data->result = SIBv3Helper::getLastErrorMessage();
			}
		} catch ( Exception $e ) {
			$this->loaded_data->result = 'Error : ' . $e->getMessage();
		}

		$this->save();

		return true;
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