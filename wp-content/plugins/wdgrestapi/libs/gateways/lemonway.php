<?php
class WDGRESTAPI_Lib_Lemonway {
	private static $_instance = null;
	private static $_cache;

	/**
	0	Identity Card (both sides in one file)
	1	Proof of address
	2	Proof of Bank Information (IBAN or other)
	3	Passport (European Community)
	4	Passport (outside the European Community) 
	5	Residence permit (both sides in one file)
	7	Official company registration document (Kbis extract or equivalent)
	8-10 N'existent pas !
	11	Driving licence (both sides in one file)
	12	Status
	13	Selfie
	21	SDD mandate
	Espaces libres : 6, 14-20
	 */
	private static $document_type_id = 0;
	private static $document_type_proof_address = 1;
	private static $document_type_iban = 2;
	private static $document_type_passport_europe_community = 3;
	private static $document_type_passport_out_europe = 4;
	private static $document_type_residence_permit = 5;
	private static $document_type_company_official_document = 7;
	private static $document_type_driving_licence = 11;
	private static $document_type_company_status = 12;
	private static $document_type_selfie = 13;

	private static $iban_type_virtual = 2;
	private static $iban_status_disabled = 8;
	private static $iban_status_rejected = 9;

	private $soap_client;
	private $params;
	private $last_error;

	/**
	 * Initialise les données à envoyer à Lemonway
	 */
	public function __construct() {
		$this->params = array(
			'wlLogin'	=> YP_LW_LOGIN,
			'wlPass'	=> YP_LW_PASSWORD,
			'language'	=> 'fr',
			'version'	=> '2.6', //Version actuelle au moment du développement
			'walletIp'	=> $_SERVER['REMOTE_ADDR'],
			'walletUa'	=> $_SERVER['HTTP_USER_AGENT'],
		);
		$this->last_error = FALSE;

		try {
			$this->soap_client = @new SoapClient( YP_LW_URL );

		} catch ( SoapFault $E ) {
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_Lemonway::__construct > ' . $E->faultstring );
			$this->set_error( 'SOAPCLIENTINIT', $E->faultstring );
			$this->soap_client = FALSE;
		}
	}
	
	/**
	 * @return WDGRESTAPI_Lib_Lemonway
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Requête au serveur
	 * @param type $method_name
	 * @param type $params
	 * @param type $params_override
	 * @return boolean
	 */
	public function call( $method_name, $params, $params_override = array() ) {
		if ( defined( 'YP_LW_SKIP' ) && YP_LW_SKIP ) {
			return FALSE;
		}
		if ( empty( $this->soap_client ) ) {
			return FALSE;
		}

		// Récupération de tous les paramètres à envoyer
		$lw_params = $this->params;
		foreach ( $lw_params as $key => $value ) {
			$params[ $key ] = $value;
		}
		foreach ( $params_override as $key => $value ) {
			$params[ $key ] = $value;
		}

		// Trace de la requete en supprimant des données trop lourds ou sensibles
		$trace_params = $params;
		if ( isset( $trace_params[ 'iban' ] ) ) {
			$trace_params[ 'iban' ] = 'UNTRACKED';
		}
		if ( isset( $trace_params[ 'bic' ] ) ) {
			$trace_params[ 'bic' ] = 'UNTRACKED';
		}
		if ( isset( $trace_params[ 'buffer' ] ) ) {
			$trace_params[ 'buffer' ] = 'UNTRACKED';
		}
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_Lemonway::call > ' .$method_name. ' | ' .print_r( $trace_params, true ) );

		if ( !isset( $params[ 'buffer' ] ) ) {
			$params = json_decode( json_encode( $params ), FALSE );
		}

		try {
			$call_result = $this->soap_client->$method_name( $params );

		} catch ( SoapFault $E ) {
			$this->set_error( 'SOAPCLIENTINIT', $E->faultstring );
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_Lemonway::call > Error > ' .$E->faultstring );
			return FALSE;
		}

		// Cas particulier : l'appel MoneyInWithCardId retourne MoneyInResult
		if ( $method_name == 'MoneyInWithCardId' ) {
			$method_name = 'MoneyIn';
		}
		$result_obj = $call_result->{$method_name . 'Result'};

		//Annalyse du résultat
		if ( $this->has_errors( $result_obj ) ) {
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_Lemonway::call > ResultError > ' . $this->last_error['Code'] . ' : ' . $this->last_error['Msg'] );
			return FALSE;

		} else {
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_Lemonway::call > Result > ' .print_r( $result_obj, true ) );
			return $result_obj;
		}
	}

/**
* GESTION ERREURS
*/
	/**
	 * Parse le retour pour déterminer si il y a des erreurs et les enregistrer si c'est le cas
	 * @param type $result_obj
	 * @return boolean
	 */
	private function has_errors( $result_obj ) {
		$buffer = false;
		if ( isset( $result_obj->E ) ) {
			$this->last_error['Code'] = $result_obj->E->Code;
			$this->last_error['Msg'] = $result_obj->E->Msg;
			$buffer = true;
		}
		return $buffer;
	}
	
	private function set_error( $code, $msg ) {
		$this->last_error['Code'] = $code;
		$this->last_error['Msg'] = $msg;
	}

	private function get_last_error_code() {
		$buffer = '';
		if ( isset( $this->last_error[ 'Code' ] ) ) {
			$buffer = $this->last_error[ 'Code' ];
		}
		return $buffer;
	}

	private function get_last_error_message() {
		$buffer = '';
		if ( isset( $this->last_error[ 'Msg' ] ) ) {
			$buffer = $this->last_error['Msg'];
		}
		return $buffer;
	}



/**
* GESTION WALLETS
*/
	/**
	 * @param int $wallet_id
	 */
	public function get_wallet_details( $wallet_id ) {
		if ( empty( $wallet_id ) ) return FALSE;
		
		$result = FALSE;

		$param_list = array( 'wallet' => $wallet_id );
		$result = $this->call( 'GetWalletDetails', $param_list );
		
		/**
		 * Retourne les éléments suivants :
		 * ID (identifiant) ; BAL (solde) ; NAME ; EMAIL ; DOCS (liste de documents dont le statut a changé) ; IBANS (liste des IBANs) ; S (statut)
		 */
		if ( !empty( $result->WALLET ) ) {
			return $result->WALLET;
		}
		return FALSE;
	}

	/**
	 * @param int $wallet_id
	 * @param int $date_start Secondes UTC
	 * @param int $date_end Secondes UTC
	 * @return type
	 */
	public function get_wallet_transactions( $wallet_id, $date_start = FALSE, $date_end = FALSE ) {
		if ( empty( $wallet_id ) ) {
			return array();
		}
		
		$param_list = array(
			'wallet'	=> $wallet_id
		);
		if ( !empty( $date_start ) ) {
			$param_list[ 'startDate' ] = $date_start;
			
			if ( !empty( $date_end ) ) {
				$param_list[ 'endDate' ] = $date_end;
			}
		}
		
		$result = $this->call( 'GetWalletTransHistory', $param_list );
		
		return $result->TRANS->HPAY;
	}

	/**
	 * @param int $wallet_id
	 */
	public function create_viban( $wallet_id ) {
		if ( empty( $wallet_id ) ) {
			return FALSE;
		}

		$param_list = array(
			'wallet'	=> $wallet_id,
			'country'	=> 'FR'
		);

		$result = $this->call( 'CreateIBAN', $param_list );
		if ( !empty( $result ) ) {
			if ( isset( $result->E ) ) {
				$result = FALSE;
			} else {
				$result = $result;
			}
		} else {
			$result = FALSE;
		}
		return $result;
	}

	/**
	 * Envoi d'un justificatif de porte-monnaie
	 * @param String $wallet_id
	 * @param String $filename
	 * @param String $lw_doc_id
	 * @param String $bytearray
	 * @return boolean or string
	 */
	public function wallet_upload_file( $wallet_id, $filename, $lw_document_id, $bytearray ) {
		if ( !isset( $wallet_id ) ) {
			return FALSE;
		}

		$param_list = array(
			'wallet'	=> $wallet_id,
			'fileName'	=> $filename,
			'type'		=> $lw_document_id,
			'buffer'	=> $bytearray
		);

		$result = $this->call('UploadFile', $param_list);
		if ( !empty( $result ) ) {
			if ( isset( $result->E ) ) {
				$result = FALSE;
			} else {
				$result = $result->UPLOAD->ID;
			}
		} else {
			$result = FALSE;
		}
		return $result;
	}

	/**
	 * Récupère le type de doc LW à partir d'une chaine interne
	 * Liste des chaines internes : 'id', 'passport', 'tax', 'welfare', 'family', 'birth', 'driving', 'kbis', 'status', 'capital-allocation', 'person2-doc1', 'person2-doc2', 'person3-doc1', 'person3-doc2'
	 */
	public static function get_lw_document_id_from_document_type ( $document_type, $index ) {
		switch ( $document_type ) {
			case 'id':
				if ( $index == 1 ) {
					return self::$document_type_id;
				} else {
					return self::$document_type_proof_address;
				}
				break;
			case 'passport':
				if ( $index == 1 ) {
					return self::$document_type_passport_europe_community;
				} else {
					return self::$document_type_passport_out_europe;
				}
				break;
			case 'tax':
			case 'welfare':
			case 'family':
			case 'birth':
				if ( $index == 1 ) {
					return 6; // Espace libre
				} else {
					return self::$document_type_selfie;
				}
				break;
			case 'driving':
				if ( $index == 1 ) {
					return self::$document_type_driving_licence;
				} else {
					return self::$document_type_selfie;
				}
				break;
			case 'kbis':
				return self::$document_type_company_official_document;
				break;
			case 'status':
				return self::$document_type_company_status;
				break;
			case 'capital-allocation':
				return 14; // Espace libre
				break;
			case 'person2-doc1':
				return 15; // Espace libre
				break;
			case 'person2-doc2':
				return 16; // Espace libre
				break;
			case 'person3-doc1':
				return 17; // Espace libre
				break;
			case 'person3-doc2':
				return 18; // Espace libre
				break;
			case 'person4-doc1':
				return 19; // Espace libre
				break;
			case 'person4-doc2':
				return 20; // Espace libre
				break;
		}
		return 20;
	}

/**
* HELPERS
*/
	/**
	 * Retourne le vIBAN lié à un wallet
	 */
	public function get_viban( $wallet_id ) {
		$buffer = FALSE;

		$wallet_details = $this->get_wallet_details( $wallet_id );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_Lemonway::get_viban > wallet_details : ' .print_r( $wallet_details, true ) );
		if ( isset( $wallet_details->IBANS->IBAN ) ) {
			if ( is_array( $wallet_details->IBANS->IBAN ) ) {
				$iban_item = $wallet_details->IBANS->IBAN[ 0 ];

				// Si le premier élément est bien virtuel et actif, on le prend
				if ( $iban_item->TYPE == self::$iban_type_virtual && $iban_item->S != self::$iban_status_disabled && $iban_item->S != self::$iban_status_rejected ) {
					$buffer = $iban_item;
				}

				// Si le premier IBAN ne correspond pas, on va chercher dans la suite
				if ( empty( $buffer ) && count( $wallet_details->IBANS->IBAN ) > 1 ) {
					foreach ( $wallet_details->IBANS->IBAN as $iban_item ) {
						if ( $iban_item->TYPE == self::$iban_type_virtual && $iban_item->S != self::$iban_status_disabled && $iban_item->S != self::$iban_status_rejected ) {
							$buffer = $iban_item;
							break;
						}
					}
				}

			// Cas particulier : un seul IBAN (n'est pas retourné dans un tableau)
			} else {
				$iban_item = $wallet_details->IBANS->IBAN;
				if ( $iban_item->TYPE == self::$iban_type_virtual ) {
					$buffer = $iban_item;
				}
			}
		}
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_Lemonway::get_viban > buffer : ' .print_r( $buffer, true ) );

		return $buffer;
	}
	
}