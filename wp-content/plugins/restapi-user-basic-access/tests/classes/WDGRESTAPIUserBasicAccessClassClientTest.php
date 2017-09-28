<?php
require_once dirname( __FILE__ ) . '/../../../../../wp-includes/class-wp-user.php';
require_once dirname( __FILE__ ) . '/../../classes/client.php';

class WDGRESTAPIUserBasicAccessClassClientTest extends PHPUnit_Framework_TestCase {
	
    /**
     * @dataProvider ipProvider
     */
	public function testIsAuthorizedIP( $client_ip, $authorized_ip, $expected ) {
        $this->assertEquals( $expected, WDG_RESTAPIUserBasicAccess_Class_Client::get_is_authorized_ip( $authorized_ip, $client_ip ) );
	}
	
	public function ipProvider() {
        return [
            'Localhost, Empty list'					=> [ '127.0.0.1', '', FALSE ],
            'Localhost, Null list'					=> [ '127.0.0.1', null, FALSE ],
            'Localhost, Sing Localhost list'		=> [ '127.0.0.1', '127.0.0.1', TRUE ],
            'Localhost, Sing false list'			=> [ '127.0.0.1', '127.0.0.2', FALSE ],
            'Localhost, Mult true list'				=> [ '127.0.0.1', '127.0.0.1,127.0.0.2', TRUE ],
            'Localhost, Mult false list'			=> [ '127.0.0.1', '127.0.0.0,127.0.0.2', FALSE ],
            'Localhost, Sing dummy list'			=> [ '127.0.0.1', 'dummy', FALSE ],
            'Localhost, Mult dummy list'			=> [ '127.0.0.1', 'dummy,dummy2', FALSE ],
            'Localhost, Sing space Localhost list'	=> [ '127.0.0.1', ' 127.0.0.1', TRUE ],
            'Localhost, Sing space Localhost list2'	=> [ '127.0.0.1', '127.0.0.1 ', TRUE ],
            'Localhost, Mult space Localhost list'	=> [ '127.0.0.1', '127.0.0.1, 127.0.0.2 ', FALSE ],
            'Localhost, Mult space false list'		=> [ '127.0.0.1', '127.0.0.0, 127.0.0.2 ', FALSE ],
            'Localhost, Sing cont false list'		=> [ '127.0.0.1', '127.0.0.11', FALSE ],
            'Localhost, Mult cont false list'		=> [ '127.0.0.1', '127.0.0.11,127.0.0.12', FALSE ],
            'Localhost, Mult cont space false list'	=> [ '127.0.0.1', '127.0.0.11, 127.0.0.12', FALSE ]
        ];
	}
	
    /**
     * @dataProvider actionProvider
     */
	public function testIsAuthorizedAction( $client_method, $authorized_method, $expected ) {
        $this->assertEquals( $expected, WDG_RESTAPIUserBasicAccess_Class_Client::get_is_authorized_action( $authorized_method, $client_method ) );
	}
	
	public function actionProvider() {
        return [
            'GET, Empty list'						=> [ 'GET', '', FALSE ],
            'GET, Null list'						=> [ 'GET', null, FALSE ],
            'GET, Dummy list'						=> [ 'Dummy', 'Dummy', FALSE ],
            'GET, False list'						=> [ 'GET', '{"get":"0","post":"1","put":"1","delete":"1"}', FALSE ],
            'GET, True list'						=> [ 'GET', '{"get":"1","post":"1","put":"1","delete":"1"}', TRUE ],
            'get, False list'						=> [ 'get', '{"get":"0","post":"1","put":"1","delete":"1"}', FALSE ],
            'get, True list'						=> [ 'get', '{"get":"1","post":"1","put":"1","delete":"1"}', TRUE ],
            'POST, False list'						=> [ 'POST', '{"get":"0","post":"0","put":"1","delete":"1"}', FALSE ],
            'POST, True list'						=> [ 'POST', '{"get":"1","post":"1","put":"1","delete":"1"}', TRUE ],
            'PUT, False list'						=> [ 'PUT', '{"get":"0","post":"1","put":"0","delete":"1"}', FALSE ],
            'PUT, True list'						=> [ 'PUT', '{"get":"1","post":"1","put":"1","delete":"1"}', TRUE ],
            'PATCH, False list'						=> [ 'PATCH', '{"get":"0","post":"1","put":"0","delete":"1"}', FALSE ],
            'PATCH, True list'						=> [ 'PATCH', '{"get":"1","post":"1","put":"1","delete":"1"}', TRUE ],
            'DELETE, False list'					=> [ 'DELETE', '{"get":"0","post":"1","put":"1","delete":"0"}', FALSE ],
            'DELETE, True list'						=> [ 'DELETE', '{"get":"1","post":"1","put":"1","delete":"1"}', TRUE ],
            'Dummy, Normal list'					=> [ 'Dummy', '{"get":"1","post":"1","put":"1","delete":"1"}', FALSE ],
            'Dummy, Dummy list'						=> [ 'Dummy', 'Dummy', FALSE ],
            'Dummy, Dummy list 2'					=> [ 'Dummy', '{"dummy":}', FALSE ],
            'Dummy, Dummy list 3'					=> [ 'Dummy', '{"dummy":"dummy"}', FALSE ],
        ];
	}
	
}