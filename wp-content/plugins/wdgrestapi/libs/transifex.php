<?php
class WDGRESTAPI_Lib_Transifex {

	private static $transifex_api_version_2 = '2';
	private static $transifex_api_version_2_5 = '2.5';
	private static $transifex_url_2 = 'https://www.transifex.com/api/2/';
	private static $transifex_url_2_5 = 'https://api.transifex.com/';

	private static $transifex_organization_slug = 'we-do-good';
	private static $transifex_project_slug = 'sendinblue-templates-mails';

/********************************************************************
 * 
 ********************************************************************/
	/**
	 * Requête globale à transifex
	 */
	private static function query( $uri, $data = array(), $api_version = '2.5', $method_post = TRUE ) {
		$curl_handle = curl_init();

		// Définition de l'URL à appeler
		$url = self::$transifex_url_2_5;
		if ( $api_version == self::$transifex_api_version_2 ) {
			$url = self::$transifex_url_2;
		}
		$url .= $uri;
		curl_setopt( $curl_handle, CURLOPT_URL, $url );

		// Paramètres d'authentification
		curl_setopt( $curl_handle, CURLOPT_USERPWD, WDG_TRANSIFEX_TOKEN );
		curl_setopt( $curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, TRUE );

		// Ajout des données facultatives si requêtes POST
		if ( !empty( $data ) ) {
			$headers = array( 'Content-Type: application/json' );
			if ( $method_post ) {
				curl_setopt( $curl_handle, CURLOPT_POST, TRUE );
			} else {
				curl_setopt( $curl_handle, CURLOPT_CUSTOMREQUEST, 'PUT' );
				array_push( $headers, 'X-HTTP-Method-Override: PUT' );
			}
			curl_setopt( $curl_handle, CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $curl_handle, CURLOPT_POSTFIELDS, json_encode( $data ) );
		}

		// Exécution de la requête est gestion d'erreur
		$buffer = curl_exec( $curl_handle );
		if ( curl_errno( $curl_handle ) ) {
			WDGRESTAPI_Lib_Logs::log( 'Transifex error : ' . print_r( curl_error( $curl_handle ), TRUE ) );
		}

		// Finitions
		curl_close( $curl_handle );
		return json_decode( $buffer );
	}

	/**
	 * Requête GET
	 */
	private static function get( $uri, $version = '' ) {
		return self::query( $uri, FALSE, $version );
	}
	
	/**
	 * Requête POST
	 */
	private static function post( $uri, $data, $version = '' ) {
		return self::query( $uri, $data, $version );
	}
	
	/**
	 * Requête PUT
	 */
	private static function put( $uri, $data, $version = '' ) {
		return self::query( $uri, $data, $version, FALSE );
	}

/********************************************************************
 * 
 ********************************************************************/
	/**
	 * Liste des resources d'un projet
	 */
	public static function get_project_resources() {
		$uri = 'organizations/' .self::$transifex_organization_slug;
		$uri .= '/projects/' .self::$transifex_project_slug;
		$uri .= '/resources/';
		self::get( $uri );
	}

	/**
	 * Infos liées à une resource spécifique
	 */
	public static function get_resource_info( $resource_slug ) {
		$uri = 'organizations/' .self::$transifex_organization_slug;
		$uri .= '/projects/' .self::$transifex_project_slug;
		$uri .= '/resources/' .$resource_slug. '/';
		return self::get( $uri );
	}

	/**
	 * Vérifie si une resource existe ou non,
	 * puis crée ou met à jour, selon le cas
	 */
	public static function create_or_update_resource( $resource_slug, $content, $resource_name = '' ) {
		// Vérification si existant
		$resource_info = self::get_resource_info( $resource_slug );

		// Si retour de code d'erreur, la resource n'existe probablement pas
		if ( !empty( $resource_info->error_code ) ) {
			// Si, en effet, la resource n'existe pas, on la crée en envoyant le contenu
			if ( $resource_info->error_code == 'not_found' ) {
				if ( empty( $resource_name ) ) {
					$resource_name = $resource_slug;
				}
				self::create_resource( $resource_slug, $resource_name, $content );

			// Log au cas où
			} else {
				WDGRESTAPI_Lib_Logs::log( 'Transifex get_resource_info error : ' . print_r( $resource_info, TRUE ) );
			}
		
		// Sinon, la resource existe, il faut la mettre à jour
		} else {
			self::update_resource_content( $resource_slug, $content );
		}
	}

	/**
	 * Crée une nouvelle resource
	 */
	public static function create_resource( $resource_slug, $resource_name, $content ) {
		$uri = 'project/' .self::$transifex_project_slug;
		$uri .= '/resources/';

		$data = array();
		$data[ 'slug' ] = $resource_slug;
		$data[ 'name' ] = $resource_name;
		$data[ 'i18n_type' ] = 'HTML';
		$data[ 'priority' ] = '0';
		$data[ 'categories' ] = '';
		$data[ 'content' ] = $content;

		self::post( $uri, $data, self::$transifex_api_version_2 );
	}

	/**
	 * Récupère le contenu lié à une resource (il s'agit du contenu d'origine, donc français pour nous)
	 */
	public static function get_resource_content( $resource_slug ) {
		$uri = 'project/' .self::$transifex_project_slug;
		$uri .= '/resource/' .$resource_slug;
		$uri .= '/content';

		self::get( $uri, self::$transifex_api_version_2 );
	}

	/**
	 * Met à jour le contenu lié à une resource
	 */
	public static function update_resource_content( $resource_slug, $content ) {
		$uri = 'project/' .self::$transifex_project_slug;
		$uri .= '/resource/' .$resource_slug;
		$uri .= '/content';

		$data = array();
		$data[ 'content' ] = $content;

		self::put( $uri, $data, self::$transifex_api_version_2 );
	}

	/**
	 * Retourne la traduction d'une resource à partir du code de langue
	 */
	public static function get_resource_translation( $resource_slug, $lang_code ) {
		$uri = 'project/' .self::$transifex_project_slug;
		$uri .= '/resource/' .$resource_slug;
		$uri .= '/translation/' .$lang_code;

		$result = self::get( $uri, self::$transifex_api_version_2 );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_Transifex::get_resource_translation [' .$resource_slug. '] [' .$lang_code. '] : ' . print_r($result, true) );
		return $result->content;
	}
}