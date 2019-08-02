<?php
require_once dirname( __FILE__ ) . '/../../libs/validator.php';

class WDGRESTAPILibValidatorTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider booleanProvider
	 */
	public function testisBoolean( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_boolean( $value ) );
	}
	
	public function booleanProvider() {
		return [
			'1 as string'			=> [ '1', TRUE ],
			'1 as int'				=> [ 1, TRUE ],
			'0 as string'			=> [ '0', TRUE ],
			'0 as int'				=> [ 0, TRUE ],
			'1.0 as float'			=> [ 1.0, TRUE ],
			'1.0 as string'			=> [ '1.0', FALSE ],
			'one as string'			=> [ 'one', FALSE ],
			'zero as string'		=> [ 'zero', FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ],
			'true as string'		=> [ 'true', FALSE ],
			'false as string'		=> [ 'false', FALSE ]
		];
	}

	/**
	 * @dataProvider numberProvider
	 */
	public function testisNumber( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_number( $value ) );
	}
	
	public function numberProvider() {
		return [
			'1 as string'			=> [ '1', TRUE ],
			'1 as int'				=> [ 1, TRUE ],
			'0 as string'			=> [ '0', TRUE ],
			'0 as int'				=> [ 0, TRUE ],
			'-1 as string'			=> [ '-1', TRUE ],
			'-1 as int'				=> [ -1, TRUE ],
			'1.0 as float'			=> [ 1.0, TRUE ],
			'1.0 as string'			=> [ '1.0', TRUE ],
			'one as string'			=> [ 'one', FALSE ],
			'zero as string'		=> [ 'zero', FALSE ],
			'1.01 as float'			=> [ 1.01, TRUE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider numberPositiveProvider
	 */
	public function testisNumberPositive( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_number_positive( $value ) );
	}
	
	public function numberPositiveProvider() {
		return [
			'1 as string'			=> [ '1', TRUE ],
			'1 as int'				=> [ 1, TRUE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'-1 as string'			=> [ '-1', FALSE ],
			'-1 as int'				=> [ -1, FALSE ],
			'1.0 as float'			=> [ 1.0, TRUE ],
			'1.0 as string'			=> [ '1.0', TRUE ],
			'one as string'			=> [ 'one', FALSE ],
			'zero as string'		=> [ 'zero', FALSE ],
			'1.01 as float'			=> [ 1.01, TRUE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider numberIntegerProvider
	 */
	public function testisNumberInteger( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_number_integer( $value ) );
	}
	
	public function numberIntegerProvider() {
		return [
			'1 as string'			=> [ '1', TRUE ],
			'1 as int'				=> [ 1, TRUE ],
			'0 as string'			=> [ '0', TRUE ],
			'0 as int'				=> [ 0, TRUE ],
			'-1 as string'			=> [ '-1', TRUE ],
			'-1 as int'				=> [ -1, TRUE ],
			'1.0 as float'			=> [ 1.0, TRUE ],
			'1.0 as string'			=> [ '1.0', TRUE ],
			'one as string'			=> [ 'one', FALSE ],
			'zero as string'		=> [ 'zero', FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider numberPositiveIntegerProvider
	 */
	public function testisNumberPositiveInteger( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_number_positive_integer( $value ) );
	}
	
	public function numberPositiveIntegerProvider() {
		return [
			'1 as string'			=> [ '1', TRUE ],
			'1 as int'				=> [ 1, TRUE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'-1 as string'			=> [ '-1', FALSE ],
			'-1 as int'				=> [ -1, FALSE ],
			'1.0 as float'			=> [ 1.0, TRUE ],
			'1.0 as string'			=> [ '1.0', TRUE ],
			'one as string'			=> [ 'one', FALSE ],
			'zero as string'		=> [ 'zero', FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider emailProvider
	 */
	public function testisEmail( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_email( $value ) );
	}
	
	public function emailProvider() {
		return [
			'mail as string'					=> [ 'mail', FALSE ],
			'mail@mail as string'				=> [ 'mail@mail', FALSE ],
			'mail@mail.com as string'			=> [ 'mail@mail.com', TRUE ],
			'mail@mail.fr as string'			=> [ 'mail@mail.fr', TRUE ],
			'mail.mail@mail.fr as string'		=> [ 'mail.mail@mail.fr', TRUE ],
			'm@mail.fr as string'				=> [ 'm@mail.fr', TRUE ],
			'mail.mail@mail.a as string'		=> [ 'mail.mail@mail.a', FALSE ],
			'http://mail@mail.fr as string'		=> [ 'http://mail@mail.fr', FALSE ],
			'1 as string'			=> [ '1', FALSE ],
			'1 as int'				=> [ 1, FALSE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'1.0 as float'			=> [ 1.0, FALSE ],
			'1.0 as string'			=> [ '1.0', FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider nameProvider
	 */
	public function testisName( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_name( $value ) );
	}
	
	public function nameProvider() {
		return [
			'name as string'					=> [ 'name', TRUE ],
			'firstname lastname as string'		=> [ 'firstname lastname', TRUE ],
			'Firstname Lastname as string'		=> [ 'Firstname Lastname', TRUE ],
			'firstname-lastname as string'		=> [ 'firstname-lastname', TRUE ],
			'firstname.lastname as string'		=> [ 'firstname.lastname', TRUE ],
			'mail@mail as string'				=> [ 'mail@mail', FALSE ],
			'mail@mail.com as string'			=> [ 'mail@mail.com', FALSE ],
			'1 as string'			=> [ '1', FALSE ],
			'1 as int'				=> [ 1, FALSE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'1.0 as float'			=> [ 1.0, FALSE ],
			'1.0 as string'			=> [ '1.0', FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider genderProvider
	 */
	public function testisGender( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_gender( $value ) );
	}
	
	public function genderProvider() {
		return [
			'male as string'					=> [ 'male', TRUE ],
			'female as string'					=> [ 'female', TRUE ],
			'name as string'					=> [ 'name', FALSE ],
			'mail@mail.com as string'			=> [ 'mail@mail.com', FALSE ],
			'1 as string'			=> [ '1', FALSE ],
			'1 as int'				=> [ 1, FALSE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'1.0 as float'			=> [ 1.0, FALSE ],
			'1.0 as string'			=> [ '1.0', FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider countryIsoCodeProvider
	 */
	public function testisCountryIsoCode( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_country_iso_code( $value ) );
	}
	
	public function countryIsoCodeProvider() {
		return [
			'FR as string'					=> [ 'FR', TRUE ],
			'fr as string'					=> [ 'fr', TRUE ],
			'Fr as string'					=> [ 'Fr', TRUE ],
			'IT as string'					=> [ 'IT', TRUE ],
			'FRA as string'					=> [ 'FRA', FALSE ],
			'France as string'				=> [ 'France', FALSE ],
			'mail@mail.com as string'		=> [ 'mail@mail.com', FALSE ],
			'1 as string'			=> [ '1', FALSE ],
			'1 as int'				=> [ 1, FALSE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'1.0 as float'			=> [ 1.0, FALSE ],
			'1.0 as string'			=> [ '1.0', FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider dateDayProvider
	 */
	public function testisDateDay( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_date_day( $value ) );
	}
	
	public function dateDayProvider() {
		return [
			'mail@mail.com as string'		=> [ 'mail@mail.com', FALSE ],
			'name as string'		=> [ 'name', FALSE ],
			'1 as string'			=> [ '1', TRUE ],
			'1 000 as string'		=> [ '1 000', FALSE ],
			'1 as int'				=> [ 1, TRUE ],
			'12 as int'				=> [ 15, TRUE ],
			'123 as int'			=> [ 32, FALSE ],
			'123 as int'			=> [ 123, FALSE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'-1 as string'			=> [ '-1', FALSE ],
			'-1 as int'				=> [ -1, FALSE ],
			'1.0 as float'			=> [ 1.0, TRUE ],
			'1.0 as string'			=> [ '1.0', TRUE ],
			'-1.0 as float'			=> [ -1.0, FALSE ],
			'-1.0 as string'		=> [ '-1.0', FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider dateMonthProvider
	 */
	public function testisDateMonth( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_date_month( $value ) );
	}
	
	public function dateMonthProvider() {
		return [
			'mail@mail.com as string'		=> [ 'mail@mail.com', FALSE ],
			'name as string'		=> [ 'name', FALSE ],
			'1 as string'			=> [ '1', TRUE ],
			'1 000 as string'		=> [ '1 000', FALSE ],
			'1 as int'				=> [ 1, TRUE ],
			'12 as int'				=> [ 11, TRUE ],
			'12 as int'				=> [ 13, FALSE ],
			'123 as int'			=> [ 123, FALSE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'-1 as string'			=> [ '-1', FALSE ],
			'-1 as int'				=> [ -1, FALSE ],
			'1.0 as float'			=> [ 1.0, TRUE ],
			'1.0 as string'			=> [ '1.0', TRUE ],
			'-1.0 as float'			=> [ -1.0, FALSE ],
			'-1.0 as string'		=> [ '-1.0', FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider dateYearProvider
	 */
	public function testisDateYear( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_date_year( $value ) );
	}
	
	public function dateYearProvider() {
		return [
			'mail@mail.com as string'		=> [ 'mail@mail.com', FALSE ],
			'name as string'		=> [ 'name', FALSE ],
			'1 as string'			=> [ '1', TRUE ],
			'1 000 as string'		=> [ '1 000', TRUE ],
			'1 984 as string'		=> [ '1 984', TRUE ],
			'1 as int'				=> [ 1, TRUE ],
			'12 as int'				=> [ 11, TRUE ],
			'0 as string'			=> [ '0', TRUE ],
			'0 as int'				=> [ 0, TRUE ],
			'-1 as string'			=> [ '-1', TRUE ],
			'-1 as int'				=> [ -1, TRUE ],
			'1.0 as float'			=> [ 1.0, TRUE ],
			'1.0 as string'			=> [ '1.0', TRUE ],
			'-1.0 as float'			=> [ -1.0, TRUE ],
			'-1.0 as string'		=> [ '-1.0', TRUE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider dateProvider
	 */
	public function testisDate( $date_day, $date_month, $date_year, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_date( $date_day, $date_month, $date_year ) );
	}
	
	public function dateProvider() {
		return [
			'01 01 2001 as string'		=> [ '01', '01', '2001', TRUE ],
			'32 01 2001 as string'		=> [ '32', '01', '2001', FALSE ],
			'01 13 2001 as string'		=> [ '01', '13', '2001', FALSE ],
			'1 1 2001 as int'			=> [ 1, 1, 2001, TRUE ],
			'1.0 1 2001 as float'		=> [ 1.0, 1, 2001, TRUE ],
			'1.0 1 2001 as string'		=> [ '1.0', 1, 2001, TRUE ],
			'true 01 2001 as boolean'	=> [ true, 01, 2001, FALSE ],
			'01 true 2001 as boolean'	=> [ 01, true, 2001, FALSE ],
			'01 01 true as boolean'		=> [ 01, 01, true, FALSE ],
			'01 01 name as string'		=> [ 01, 01, 'name', FALSE ]
		];
	}

	/**
	 * @dataProvider majorProvider
	 */
	public function testisMajor( $date_day, $date_month, $date_year, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_major( $date_day, $date_month, $date_year ) );
	}
	
	public function majorProvider() {
		return [
			'01 01 1998 as string'		=> [ '01', '01', '1998', TRUE ],
			'01 01 2019 as string'		=> [ '01', '01', '2030', FALSE ],
			'01 01 1998 as int'			=> [ 1, 1, 1998, TRUE ],
			'true 01 2001 as boolean'	=> [ true, 01, 2001, FALSE ],
			'01 true 2001 as boolean'	=> [ 01, true, 2001, FALSE ],
			'01 01 true as boolean'		=> [ 01, 01, true, FALSE ],
			'01 01 name as string'		=> [ 01, 01, 'name', FALSE ]
		];
	}

	/**
	 * @dataProvider postalcodeProvider
	 */
	public function testisPostalCode( $postal_code, $country_code, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_postalcode( $postal_code, $country_code ) );
	}
	
	public function postalcodeProvider() {
		return [
			'57000 FR as string'		=> [ '57000', 'FR', TRUE ],
			'57 000 FR as string'		=> [ '57 000', 'FR', TRUE ],
			'57000 FR as int'			=> [ 57000, 'FR', TRUE ],
			'true FR as boolean'		=> [ true, 'FR', FALSE ],
			'true FR as string'			=> [ 'true', 'FR', FALSE ],
			'57000 true as string'		=> [ 57000, 'other', TRUE ]
		];
	}

	/**
	 * @dataProvider minimumAmountProvider
	 */
	public function testisMinimumAmount( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_minimum_amount( $value ) );
	}
	
	public function minimumAmountProvider() {
		return [
			'10 as int'			=> [ 10, TRUE ],
			'1 as int'			=> [ 1, FALSE ],
			'-1 as int'			=> [ -1, FALSE ],
			'100 as int'		=> [ 100, TRUE ],
			'10.01 as int'		=> [ 10.01, TRUE ],
			'10 as string'		=> [ '10', TRUE ],
			'10.01 as string'	=> [ '10.01', TRUE ],
			'name as string'	=> [ 'name', FALSE ],
			'true as boolean'	=> [ true, FALSE ],
		];
	}

	/**
	 * @dataProvider urlProvider
	 */
	public function testisUrl( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_url( $value ) );
	}
	
	public function urlProvider() {
		return [
			'url as string'					=> [ 'url', FALSE ],
			'http://url as string'			=> [ 'http://url', FALSE ],
			'http://url.fr as string'		=> [ 'http://url.fr', TRUE ],
			'https://url.fr as string'		=> [ 'https://url.fr', TRUE ],
			'http://www.url.fr as string'	=> [ 'http://www.url.fr', TRUE ],
			'https://www.url.fr as string'	=> [ 'https://www.url.fr', TRUE ],
			'1 as int'				=> [ 1, FALSE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider ibanProvider
	 */
	public function testisIban( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_iban( $value ) );
	}
	
	public function ibanProvider() {
		return [
			'FR76 3000 4025 1100 0111 8625 268 as string'		=> [ 'FR76 3000 4025 1100 0111 8625 268', TRUE ],
			'FR7630004025110001118625268 as string'				=> [ 'FR7630004025110001118625268', TRUE ],
			'fr76 3000 4025 1100 0111 8625 268 as string'		=> [ 'fr76 3000 4025 1100 0111 8625 268', TRUE ],
			'fr7630004025110001118625268 as string'				=> [ 'fr7630004025110001118625268', TRUE ],
			'iban as string'		=> [ 'iban', FALSE ],
			'1 as int'				=> [ 1, FALSE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	/**
	 * @dataProvider bicProvider
	 */
	public function testisBic( $value, $expected ) {
		$this->assertEquals( $expected, WDGRESTAPI_Lib_Validator::is_bic( $value ) );
	}
	
	public function bicProvider() {
		return [
			'BNPAFRPPIFE as string'	=> [ 'BNPAFRPPIFE', TRUE ],
			'bnpafrppife as string'	=> [ 'bnpafrppife', TRUE ],
			'bic as string'		=> [ 'bic', FALSE ],
			'1 as int'				=> [ 1, FALSE ],
			'0 as string'			=> [ '0', FALSE ],
			'0 as int'				=> [ 0, FALSE ],
			'1.01 as float'			=> [ 1.01, FALSE ],
			'null as null'			=> [ null, FALSE ],
			'null as string'		=> [ 'null', FALSE ],
			'true as boolean'		=> [ true, FALSE ],
			'false as boolean'		=> [ false, FALSE ]
		];
	}

	
	
}