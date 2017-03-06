<?php
class WDGRESTAPI_Lib_Geolocation {
	
	public static function get_geolocation_data( $raw_address ) {
		$invalid_chars = array( " " => "+", "," => "", "?" => "", "&" => "", "=" => "" , "#" => "" );
		$raw_address = trim( strtolower( str_replace( array_keys( $invalid_chars ), array_values( $invalid_chars ), $raw_address ) ) );

		if ( empty( $raw_address ) ) {
			return false;
		}

		$transient_name = 'wdg_geocode_' . md5( $raw_address );
		$geocoded_address = get_transient( $transient_name );
		$wdg_geocode_over_query_limit = get_transient( 'wdg_geocode_over_query_limit' );

		// Query limit reached - don't geocode for a while
		if ( $wdg_geocode_over_query_limit && false === $geocoded_address ) {
			return false;
		}

		try {
			if ( false === $geocoded_address || empty( $geocoded_address->results[0] ) ) {
				$result = wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=" . $raw_address . "&sensor=false",
					array(
						'timeout'     => 5,
					    'redirection' => 1,
					    'httpversion' => '1.1',
					    'user-agent'  => 'WordPress/WE DO GOOD; ' . get_bloginfo( 'url' ),
					    'sslverify'   => false
				    )
				);
				$result = wp_remote_retrieve_body( $result );
				$geocoded_address = json_decode( $result );

				if ( $geocoded_address->status ) {
					switch ( $geocoded_address->status ) {
						case 'ZERO_RESULTS' :
							throw new Exception( __( "No results found", 'wdgrestapi' ) );
						break;
						case 'OVER_QUERY_LIMIT' :
							set_transient( 'wdg_geocode_over_query_limit', 1, HOUR_IN_SECONDS );
							throw new Exception( __( "Query limit reached", 'wdgrestapi' ) );
						break;
						case 'OK' :
							if ( ! empty( $geocoded_address->results[0] ) ) {
								set_transient( $transient_name, $geocoded_address, 24 * HOUR_IN_SECONDS * 365 );
							} else {
								throw new Exception( __( "Geocoding error", 'wdgrestapi' ) );
							}
						break;
						default :
							throw new Exception( __( "Geocoding error", 'wdgrestapi' ) );
						break;
					}
				} else {
					throw new Exception( __( "Geocoding error", 'wdgrestapi' ) );
				}
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'error', $e->getMessage() );
		}

		$address = array();
		$address['lat'] = sanitize_text_field( $geocoded_address->results[0]->geometry->location->lat );
		$address['long'] = sanitize_text_field( $geocoded_address->results[0]->geometry->location->lng );

		return $address;
	}
	
}