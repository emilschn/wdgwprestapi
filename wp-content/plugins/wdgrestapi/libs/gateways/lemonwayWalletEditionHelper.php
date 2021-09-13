<?php

/**
 * Classe helper interne pour créer et modifier des wallets LemonWay
 */
class WDGRESTAPI_Lib_LemonwayWalletEditionHelper {

	
	private static $_instance = null;
	private static $last_error;
	private static $cache_wallet_details;
	private $lw;
	
	/*********************** WALLETS ***********************/
	public static $wallet_type_payer = '1';
	public static $wallet_type_beneficiary = '2';

	public function __construct() {
		$this->lw = WDGRESTAPI_Lib_Lemonway::instance();		
	}

	/**
	 * @return WDGRESTAPI_Lib_LemonwayWalletEditionHelper
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Création d'un porte-monnaie
	 * @param type $new_wallet_id : Identifiant du porte-monnaie sur la plateforme
	 * @param type $client_mail
	 * @param type $client_title : Civilité (1 char)
	 * @param type $client_first_name
	 * @param type $client_last_name
	 * @param type $country : Pays au format ISO-3
	 * @param type $phone_number : Facultatif ; format MSISDN (code pays sans + ni 00)
	 * @param type $birthdate : Format JJ/MM/AAAA
	 * @param type $nationality : Pays au format ISO-3
	 * @param type $payer_or_beneficiary : Statut payer/beneficiary
	 * @return type
	 */
	public function wallet_register($user_item) {
		$loaded_data = $user_item->get_loaded_data();
		if ( !$user_item->get_wallet_id() ){
			$new_wallet_id = $user_item->get_wallet_id();
		} else {
			$new_wallet_id = $this->format_lemonway_id($loaded_data->wpref);
		}
		
		$param_list = array(
			'wallet'			=> $new_wallet_id,
			'clientMail'		=> $loaded_data->email,
			'clientTitle'		=> $this->get_lemonway_title($loaded_data->gender),
			'clientFirstName'	=> $loaded_data->name,
			'clientLastName'	=> $loaded_data->surname,
			'ctry'				=> $this->get_country($loaded_data->country),
			'birthdate'			=> $this->get_lemonway_birthdate($loaded_data->birthday_date),
			'nationality'		=> $this->get_country($loaded_data->nationality),
			'payerOrBeneficiary' => self::$wallet_type_payer
		);
		if ( !empty( $phone_number ) ) {
			$param_list['phoneNumber'] = $this->get_lemonway_phone_number($loaded_data->phone_number); 
		}
		$result = $this->lw->call('RegisterWallet', $param_list);

		if ($result !== FALSE) {
			$result = $result->WALLET->LWID;
			// on met à jour le wallet_id pour l'utilisateur
			$user_item->set_wallet_id('lemonway', $result );
		}

		return $result;
	}

	/**
	 * Mise à jour d'un porte-monnaie
	 * @param type $wallet_id : Identifiant du porte-monnaie sur la plateforme
	 * @param type $client_mail
	 * @param type $client_title : Civilité (1 char)
	 * @param type $client_first_name
	 * @param type $client_last_name
	 * @param type $country : Pays au format ISO-3
	 * @param type $phone_number : Facultatif ; format MSISDN (code pays sans + ni 00)
	 * @param type $birthdate : Format JJ/MM/AAAA
	 * @param type $nationality : Pays au format ISO-3
	 * @param type $company_website
	 * @return type
	 */
	public function wallet_update($wallet_id, $client_mail = '', $client_title = '', $client_first_name = '', $client_last_name = '', $country = '', $phone_number = '', $birthdate = '', $nationality = '', $company_website = '') {
		if ( empty( $wallet_id ) ) {
			return FALSE;
		}

		$param_list = array( 'wallet' => $wallet_id );
		if ( !empty( $client_mail ) ) {
			$param_list['newEmail'] = $client_mail;
		}
		if ( !empty( $client_title ) ) {
			$param_list['newTitle'] = $client_title;
		}
		if ( !empty( $client_first_name ) ) {
			$param_list['newFirstName'] = $client_first_name;
		}
		if ( !empty( $client_last_name ) ) {
			$param_list['newLastName'] = $client_last_name;
		}
		if ( !empty( $country ) ) {
			$param_list['newCtry'] = $country;
		}
		if ( !empty( $phone_number ) ) {
			$param_list['newPhoneNumber'] = $phone_number;
		}
		if ( !empty( $birthdate ) ) {
			$param_list['newBirthDate'] = $birthdate;
		}
		if ( !empty( $nationality ) ) {
			$param_list['newNationality'] = $nationality;
		}
		if ( !empty( $company_website ) ) {
			$param_list['newCompanyWebsite'] = $company_website;
		}

		$result = $this->lw->call('UpdateWalletDetails', $param_list);

		return $result;
	}

	/**
	 * DÃ©finit l'identifiant de l'utilisateur sur lemonway
	 * @return string
	 */
	public function format_lemonway_id($wpref) {
		if ( !empty( $wpref ) ) {
			$db_lw_id = 'USERW'.$wpref;
			if ( defined( 'YP_LW_USERID_PREFIX' ) ) {
				$db_lw_id = YP_LW_USERID_PREFIX . $db_lw_id;
			}
		}

		return $db_lw_id;
	}

	/**
	 * RÃ©cupÃ¨re le genre de l'utilisateur, formattÃ© pour lemonway
	 * @return string
	 */
	public function get_lemonway_title($gender) {
		$buffer = "U";
		if ( $gender == 'male' ) {
			$buffer = "M";
		} elseif ( $gender == "female" ) {
			$buffer = "F";
		}

		return $buffer;
	}

	public function get_lemonway_phone_number($phone_number) {
		if (!empty($phone_number)) {            
			// Si ça commence par un "+" on peut estimer que la personne a déjà fait attention, donc on ne formattera pas au style français
			$skip_french_format = false;
			if (substr($phone_number, 0, 1) == '+') {
				$skip_french_format = true;
			}
			$lemonway_phone_number = str_replace(array(' ', '.', '-', '+'), '', $phone_number);
			if (!empty($lemonway_phone_number) && !$skip_french_format) {
				$lemonway_phone_number = substr($lemonway_phone_number, -9);
				$lemonway_phone_number = '33' . $lemonway_phone_number;
			}
			return $lemonway_phone_number;
		}
	}

	public function get_lemonway_birthdate($birthday) {
		// format : dd/MM/yyyy
		$birthday_datetime = new DateTime( $birthday );

		return $birthday_datetime->format( 'd/m/Y' );
	}

	public function get_country($country ) {
		$buffer = $country;
		// if ( empty( $buffer ) || $buffer == '---' ) {
		// 	$buffer = $this->wp_user->get('user_country');
		// }
		// Si le dernier caractÃ¨re est un espace, on le supprime
		if ( substr( $buffer, -1 ) == ' ' ) {
			$buffer = substr( $buffer, 0, -1 );
		}

		// Le pays est saisi, il faut tenter de le convertir
		global $country_list, $country_list_iso2_to_iso3, $country_translation;
		// D'abord, on le met en majuscule
		$upper_country = strtoupper( $buffer );
		if ( isset( $country_translation[ htmlentities( $upper_country ) ] ) ) {
			$upper_country = $country_translation[ htmlentities( $upper_country ) ];
		}

		// On le cherche en iso2
		$iso2_key = array_search( $upper_country, $country_list );

		// On le transforme en iso3
		if ( !empty( $iso2_key ) && !empty( $country_list_iso2_to_iso3[ $iso2_key ] ) ) {
			$buffer = $country_list_iso2_to_iso3[ $iso2_key ];
		} else {
			if ( !empty( $country_list_iso2_to_iso3[ $buffer ] ) ) {
				$buffer = $country_list_iso2_to_iso3[ $buffer ];
			}
		}

		return $buffer;
	}

}