<?php
class WDGRESTAPI_Lib_Lemonway {
	private static $_instance = null;
	private static $_cache;

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
			'version'	=> '1.9', //Version actuelle au moment du développement
			'walletIp'	=> $_SERVER['REMOTE_ADDR'],
			'walletUa'	=> $_SERVER['HTTP_USER_AGENT'],
		);
		$this->last_error = FALSE;

		try {
			$this->soap_client = @new SoapClient( YP_LW_URL );

		} catch ( SoapFault $E ) {
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
	private function call( $method_name, $params, $params_override = array() ) {
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
		
		if ( !isset( $params[ 'buffer' ] ) ) {
			$params = json_decode( json_encode( $params ), FALSE );
		}

		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_Lemonway::call > ' .$method_name. ' | ' .print_r( $params, true ) );
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
	
}