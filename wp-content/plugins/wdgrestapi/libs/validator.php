<?php
/**
 * Classe de validation de données
 */
class WDGRESTAPI_Lib_Validator {
	
	/**
	 * Liste iso des pays
	 * Format iso3166-1 alpha-2
	 */
	public static $country_list = array(
		"FR" => "FRANCE",
		"AF" => "AFGHANISTAN",
		"AX" => "ÅLAND ISLANDS",
		"AL" => "ALBANIA",
		"DZ" => "ALGERIA",
		"AS" => "AMERICAN SAMOA",
		"AD" => "ANDORRA",
		"AO" => "ANGOLA",
		"AI" => "ANGUILLA",
		"AQ" => "ANTARCTICA",
		"AG" => "ANTIGUA AND BARBUDA",
		"AR" => "ARGENTINA",
		"AM" => "ARMENIA",
		"AW" => "ARUBA",
		"AU" => "AUSTRALIA",
		"AT" => "AUSTRIA",
		"AZ" => "AZERBAIJAN",
		"BS" => "BAHAMAS",
		"BH" => "BAHRAIN",
		"BD" => "BANGLADESH",
		"BB" => "BARBADOS",
		"BY" => "BELARUS",
		"BE" => "BELGIUM",
		"BZ" => "BELIZE",
		"BJ" => "BENIN",
		"BM" => "BERMUDA",
		"BT" => "BHUTAN",
		"BO" => "BOLIVIA, PLURINATIONAL STATE OF",
		"BQ" => "BONAIRE, SINT EUSTATIUS AND SABA",
		"BA" => "BOSNIA AND HERZEGOVINA",
		"BW" => "BOTSWANA",
		"BV" => "BOUVET ISLAND",
		"BR" => "BRAZIL",
		"IO" => "BRITISH INDIAN OCEAN TERRITORY",
		"BN" => "BRUNEI DARUSSALAM",
		"BG" => "BULGARIA",
		"BF" => "BURKINA FASO",
		"BI" => "BURUNDI",
		"KH" => "CAMBODIA",
		"CM" => "CAMEROON",
		"CA" => "CANADA",
		"CV" => "CAPE VERDE",
		"KY" => "CAYMAN ISLANDS",
		"CF" => "CENTRAL AFRICAN REPUBLIC",
		"TD" => "CHAD",
		"CL" => "CHILE",
		"CN" => "CHINA",
		"CX" => "CHRISTMAS ISLAND",
		"CC" => "COCOS (KEELING) ISLANDS",
		"CO" => "COLOMBIA",
		"KM" => "COMOROS",
		"CG" => "CONGO",
		"CD" => "CONGO, THE DEMOCRATIC REPUBLIC OF THE",
		"CK" => "COOK ISLANDS",
		"CR" => "COSTA RICA",
		"CI" => "CÔTE D'IVOIRE",
		"HR" => "CROATIA",
		"CU" => "CUBA",
		"CW" => "CURAÇAO",
		"CY" => "CYPRUS",
		"CZ" => "CZECH REPUBLIC",
		"DK" => "DENMARK",
		"DJ" => "DJIBOUTI",
		"DM" => "DOMINICA",
		"DO" => "DOMINICAN REPUBLIC",
		"EC" => "ECUADOR",
		"EG" => "EGYPT",
		"SV" => "EL SALVADOR",
		"GQ" => "EQUATORIAL GUINEA",
		"ER" => "ERITREA",
		"EE" => "ESTONIA",
		"ET" => "ETHIOPIA",
		"FK" => "FALKLAND ISLANDS (MALVINAS)",
		"FO" => "FAROE ISLANDS",
		"FJ" => "FIJI",
		"FI" => "FINLAND",
		"GF" => "FRENCH GUIANA",
		"PF" => "FRENCH POLYNESIA",
		"TF" => "FRENCH SOUTHERN TERRITORIES",
		"GA" => "GABON",
		"GM" => "GAMBIA",
		"GE" => "GEORGIA",
		"DE" => "GERMANY",
		"GH" => "GHANA",
		"GI" => "GIBRALTAR",
		"GR" => "GREECE",
		"GL" => "GREENLAND",
		"GD" => "GRENADA",
		"GP" => "GUADELOUPE",
		"GU" => "GUAM",
		"GT" => "GUATEMALA",
		"GG" => "GUERNSEY",
		"GN" => "GUINEA",
		"GW" => "GUINEA-BISSAU",
		"GY" => "GUYANA",
		"HT" => "HAITI",
		"HM" => "HEARD ISLAND AND MCDONALD ISLANDS",
		"VA" => "HOLY SEE (VATICAN CITY STATE)",
		"HN" => "HONDURAS",
		"HK" => "HONG KONG",
		"HU" => "HUNGARY",
		"IS" => "ICELAND",
		"IN" => "INDIA",
		"ID" => "INDONESIA",
		"IR" => "IRAN, ISLAMIC REPUBLIC OF",
		"IQ" => "IRAQ",
		"IE" => "IRELAND",
		"IM" => "ISLE OF MAN",
		"IL" => "ISRAEL",
		"IT" => "ITALY",
		"JM" => "JAMAICA",
		"JP" => "JAPAN",
		"JE" => "JERSEY",
		"JO" => "JORDAN",
		"KZ" => "KAZAKHSTAN",
		"KE" => "KENYA",
		"KI" => "KIRIBATI",
		"KP" => "KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF",
		"KR" => "KOREA, REPUBLIC OF",
		"KW" => "KUWAIT",
		"KG" => "KYRGYZSTAN",
		"LA" => "LAO PEOPLE'S DEMOCRATIC REPUBLIC",
		"LV" => "LATVIA",
		"LB" => "LEBANON",
		"LS" => "LESOTHO",
		"LR" => "LIBERIA",
		"LY" => "LIBYA",
		"LI" => "LIECHTENSTEIN",
		"LT" => "LITHUANIA",
		"LU" => "LUXEMBOURG",
		"MO" => "MACAO",
		"MK" => "MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF",
		"MG" => "MADAGASCAR",
		"MW" => "MALAWI",
		"MY" => "MALAYSIA",
		"MV" => "MALDIVES",
		"ML" => "MALI",
		"MT" => "MALTA",
		"MH" => "MARSHALL ISLANDS",
		"MQ" => "MARTINIQUE",
		"MR" => "MAURITANIA",
		"MU" => "MAURITIUS",
		"YT" => "MAYOTTE",
		"MX" => "MEXICO",
		"FM" => "MICRONESIA, FEDERATED STATES OF",
		"MD" => "MOLDOVA, REPUBLIC OF",
		"MC" => "MONACO",
		"MN" => "MONGOLIA",
		"ME" => "MONTENEGRO",
		"MS" => "MONTSERRAT",
		"MA" => "MOROCCO",
		"MZ" => "MOZAMBIQUE",
		"MM" => "MYANMAR",
		"NA" => "NAMIBIA",
		"NR" => "NAURU",
		"NP" => "NEPAL",
		"NL" => "NETHERLANDS",
		"NC" => "NEW CALEDONIA",
		"NZ" => "NEW ZEALAND",
		"NI" => "NICARAGUA",
		"NE" => "NIGER",
		"NG" => "NIGERIA",
		"NU" => "NIUE",
		"NF" => "NORFOLK ISLAND",
		"MP" => "NORTHERN MARIANA ISLANDS",
		"NO" => "NORWAY",
		"OM" => "OMAN",
		"PK" => "PAKISTAN",
		"PW" => "PALAU",
		"PS" => "PALESTINE, STATE OF",
		"PA" => "PANAMA",
		"PG" => "PAPUA NEW GUINEA",
		"PY" => "PARAGUAY",
		"PE" => "PERU",
		"PH" => "PHILIPPINES",
		"PN" => "PITCAIRN",
		"PL" => "POLAND",
		"PT" => "PORTUGAL",
		"PR" => "PUERTO RICO",
		"QA" => "QATAR",
		"RE" => "RÉUNION",
		"RO" => "ROMANIA",
		"RU" => "RUSSIAN FEDERATION",
		"RW" => "RWANDA",
		"BL" => "SAINT BARTHÉLEMY",
		"SH" => "SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA",
		"KN" => "SAINT KITTS AND NEVIS",
		"LC" => "SAINT LUCIA",
		"MF" => "SAINT MARTIN (FRENCH PART)",
		"PM" => "SAINT PIERRE AND MIQUELON",
		"VC" => "SAINT VINCENT AND THE GRENADINES",
		"WS" => "SAMOA",
		"SM" => "SAN MARINO",
		"ST" => "SAO TOME AND PRINCIPE",
		"SA" => "SAUDI ARABIA",
		"SN" => "SENEGAL",
		"RS" => "SERBIA",
		"SC" => "SEYCHELLES",
		"SL" => "SIERRA LEONE",
		"SG" => "SINGAPORE",
		"SX" => "SINT MAARTEN (DUTCH PART)",
		"SK" => "SLOVAKIA",
		"SI" => "SLOVENIA",
		"SB" => "SOLOMON ISLANDS",
		"SO" => "SOMALIA",
		"ZA" => "SOUTH AFRICA",
		"GS" => "SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS",
		"SS" => "SOUTH SUDAN",
		"ES" => "SPAIN",
		"LK" => "SRI LANKA",
		"SD" => "SUDAN",
		"SR" => "SURINAME",
		"SJ" => "SVALBARD AND JAN MAYEN",
		"SZ" => "SWAZILAND",
		"SE" => "SWEDEN",
		"CH" => "SWITZERLAND",
		"SY" => "SYRIAN ARAB REPUBLIC",
		"TW" => "TAIWAN, PROVINCE OF CHINA",
		"TJ" => "TAJIKISTAN",
		"TZ" => "TANZANIA, UNITED REPUBLIC OF",
		"TH" => "THAILAND",
		"TL" => "TIMOR-LESTE",
		"TG" => "TOGO",
		"TK" => "TOKELAU",
		"TO" => "TONGA",
		"TT" => "TRINIDAD AND TOBAGO",
		"TN" => "TUNISIA",
		"TR" => "TURKEY",
		"TM" => "TURKMENISTAN",
		"TC" => "TURKS AND CAICOS ISLANDS",
		"TV" => "TUVALU",
		"UG" => "UGANDA",
		"UA" => "UKRAINE",
		"AE" => "UNITED ARAB EMIRATES",
		"GB" => "UNITED KINGDOM",
		"US" => "UNITED STATES",
		"UM" => "UNITED STATES MINOR OUTLYING ISLANDS",
		"UY" => "URUGUAY",
		"UZ" => "UZBEKISTAN",
		"VU" => "VANUATU",
		"VE" => "VENEZUELA, BOLIVARIAN REPUBLIC OF",
		"VN" => "VIET NAM",
		"VG" => "VIRGIN ISLANDS, BRITISH",
		"VI" => "VIRGIN ISLANDS, U.S.",
		"WF" => "WALLIS AND FUTUNA",
		"EH" => "WESTERN SAHARA",
		"YE" => "YEMEN",
		"ZM" => "ZAMBIA",
		"ZW" => "ZIMBABWE"
	);
	
	public static $minimum_amount = 10;
	
	/**
	 * Vérifie si le champ est bien 1 ou 0
	 * @param string $input
	 * @return boolean
	 */
	public static function is_boolean( $input ) {
		return ( $input === 1 || $input === 0 );
	}
	
	/**
	 * Vérifie si le champ est bien un nombre
	 * @param string $input
	 * @return boolean
	 */
	public static function is_number( $input ) {
		return ( is_numeric( $input ) || ( $input == 0 ) );
	}
	
	/**
	 * Vérifie si le champ est bien un nombre positif
	 * @param string $input
	 * @return boolean
	 */
	public static function is_number_positive( $input ) {
		return ( $input > 0 );
	}

	/**
	 * Vérifie si la variable est un e-mail
	 * @param string $input
	 * @return boolean
	 */
	public static function is_email( $input ) {
		return is_email( $input );
	}
	
	/**
	 * Vérifie si la variable est un nom
	 * @param string $input
	 * @return boolean
	 */
	public static function is_name( $input ) {
		return !empty( $input );
	}
	
	/**
	 * Vérifie si la variable est de type "genre"
	 * @param string $input
	 * @return boolean
	 */
	public static function is_gender( $input ) {
		return ( $input == "male" || $input == "female" );
	}
	
	/**
	 * Vérifie si la variable est bien un code de nationalité
	 * @param string $input
	 * @return boolean
	 */
	public static function is_country_iso_code( $input ) {
		return !empty( $input ) && isset( WDGRESTAPI_Lib_Validator::$country_list[ $input ] );
	}
	
	/**
	 * Vérifie si la variable est bien un jour de date
	 * @param string $input
	 * @return boolean
	 */
	public static function is_date_day( $input ) {
		return ( is_numeric( $input ) && ( $input <= 31 ) );
	}
	
	/**
	 * Vérifie si la variable est bien un mois de date
	 * @param string $input
	 * @return boolean
	 */
	public static function is_date_month( $input ) {
		return ( is_numeric( $input ) && ( $input <= 12 ) );
	}
	
	/**
	 * Vérifie si la variable est bien une année de date
	 * @param string $input
	 * @return boolean
	 */
	public static function is_date_year( $input ) {
		return is_numeric( $input );
	}
	
	/**
	 * Vérifie si la date est correcte
	 * @param string $input_day
	 * @param string $input_month
	 * @param string $input_year
	 * @return boolean
	 */
	public static function is_date( $input_day, $input_month, $input_year ) {
		return checkdate( $input_month, $input_day, $input_year );
	}
	
	/**
	 * Vérifie si la date correspond à quelqu'un de majeur
	 * @param string $input_day
	 * @param string $input_month
	 * @param string $input_year
	 * @return boolean
	 */
	public static function is_major( $input_day, $input_month, $input_year ) {
		$today_day = date('j');
		$today_month = date('n');
		$today_year = date('Y');
		$years_diff = $today_year - $input_year;
		if ( $today_month <= $input_month ) {
			if ( $input_month == $today_month ) {
				if ( $input_day > $today_day ) {
					$years_diff--;
				}
			} else {
				$years_diff--;
			}
		}
		return ( $years_diff >= 18 );
	}
	
	/**
	 * Vérifie si la variable est bien un code postal
	 * @param string $input
	 * @return boolean
	 */
	public static function is_postalcode( $input ) {
		return ( preg_match( '#^[0-9]{5}$#', $input ) );
	}
	
	/**
	 * Vérifie si le montant est supérieur à 10
	 * @param string $input
	 * @return boolean
	 */
	public static function is_minimum_amount( $input ) {
		return ( $input >= WDGRESTAPI_Lib_Validator::$minimum_amount );
	}
	
	/**
	 * Vérifie si la variable est une URL
	 * @param string $input
	 * @return boolean
	 */
	public static function is_url( $input ) {
		return ( filter_var( $input, FILTER_VALIDATE_URL ) !== FALSE );
	}
}