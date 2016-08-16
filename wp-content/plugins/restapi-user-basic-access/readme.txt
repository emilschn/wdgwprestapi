=== REST API User Basic Authorization ===
Contributors: emilschn
Donate link: http://www.wedogood.co/
Tags: api, json, REST, rest-api, basic, authorization
Requires at least: 4.5
Tested up to: 4.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

REST API access and user accounts management via basic authorization. Once activated, WP REST API will only be accessible to registered users.

== Description ==

This plugin needs the rest-api plugin (https://wordpress.org/plugins/rest-api/) to be useful.

REST API access and user accounts management via basic authorization.
Once activated, WP REST API will only be accessible to registered users.

For each user, you can set different parameters:
* wether they can or cannot access WP REST API
* an IP address or a list of IP addresses that can access the REST API
* wether they can read, post, edit or delete posts

Simple example of access to WP REST API with basic authorization:
`$headers = array( "Authorization" => "Basic " . base64_encode( $wp_login.':'.$wp_password ) );
$result = wp_remote_get( $url, array( 'headers' => $headers ) );`

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/restapi-user-basic-access` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to a specific user admin panel and set the authorization to access REST API

== Screenshots ==

== Changelog ==

= 0.5 =
* First upload

== Upgrade Notice ==

= 0.5 =
First upload
