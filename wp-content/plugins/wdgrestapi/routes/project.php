<?php
class WDGRESTAPI_Route_Project extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/projects',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/projects/categories',
			WP_REST_Server::READABLE,
			array( $this, 'list_get_categories')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/projects/stats',
			WP_REST_Server::READABLE,
			array( $this, 'list_get_stats')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
		
		WDGRESTAPI_Route::register_external(
			'/project/(?P<wpref>\d+)/status',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_status'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)/declarations',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_declarations'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)/declarations',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create_declarations'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_external(
			'/project/(?P<id>\d+)/royalties',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_royalties'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)/votes',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_votes'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)/investments',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_investments'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)/documents',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_post_documents'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)/contract-models',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_contract_models'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)/contracts',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_contracts'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/project/(?P<id>\d+)/investment-contracts',
			WP_REST_Server::READABLE,
			array( $this, 'single_get_investment_contracts'),
			array( 'id' => array( 'default' => 0 ) )
		);
		
		// Spécifique Equitearly
		WDGRESTAPI_Route::register_external(
			'/project-equitearly',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create_equitearly'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
	}
	
	public static function register() {
		$route_project = new WDGRESTAPI_Route_Project();
		return $route_project;
	}
	
	/**
	 * Retourne la liste des projets
	 * @return array
	 */
	public function list_get() {
		// Gestion cache
		$cache_name = '/projects';
		$cached_version_entity = new WDGRESTAPI_Entity_Cache( FALSE, $cache_name );
		$cached_value = $cached_version_entity->get_value( 60 );
		
		if ( !empty( $cached_value ) ) {
			WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Route_Project::use cache');
			$buffer = json_decode( $cached_value );
		} else {
			WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Route_Project::use request');
			$buffer = WDGRESTAPI_Entity_Project::list_get( $this->get_current_client_autorized_ids_string() );
			$cached_version_entity->save( $cache_name, json_encode( $buffer ) );
		}
		
		return $buffer;
	}
	
	/**
	 * Retourne la liste des catégories de projets
	 * @return array
	 */
	public function list_get_categories() {
		return WDGRESTAPI_Entity_Project::get_categories();
	}
	
	/**
	 * Retourne les statistiques associées aux projets
	 * @return array
	 */
	public function list_get_stats() {
		return WDGRESTAPI_Entity_Project::get_stats();
	}
	
	/**
	 * Retourne un projet par son ID
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Project
	 */
	public function single_get( WP_REST_Request $request ) {
		$input_with_investments = filter_input( INPUT_GET, 'with_investments' );
		$input_with_organization = filter_input( INPUT_GET, 'with_organization' );
		$input_with_poll_answers = filter_input( INPUT_GET, 'with_poll_answers' );
		
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data( FALSE, ( $input_with_investments == '1' ), ( $input_with_organization == '1' ), ( $input_with_poll_answers == '1' ), $this->get_current_client_autorized_ids_string() );
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->log( "WDGRESTAPI_Route_Project::single_get::" . $project_id, json_encode( $loaded_data ) );
				return $loaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_get::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_get", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Retourne le statut d'un projet par son ID WordPress
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_get_status( WP_REST_Request $request ) {
		$project_wpref = $request->get_param( 'wpref' );
		if ( !empty( $project_wpref ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( FALSE, $project_wpref );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$loaded_data = $project_item->get_status();
				$this->log( "WDGRESTAPI_Route_Project::single_get_status::" . $project_wpref, json_encode( $loaded_data ) );
				return $loaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_get_status::" . $project_wpref, "404 : Invalid project WPREF" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_get_status", "404 : Invalid project WPREF (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Retourne les déclarations liées à un projet (par l'ID du projet)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_declarations( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$royalties_data = $project_item->get_declarations();
				$this->log( "WDGRESTAPI_Route_Project::single_get_declarations::" . $project_id, json_encode( $royalties_data ) );
				return $royalties_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_get_declarations::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_get_declarations", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Retourne les royalties d'un projet par son ID
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_royalties( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( FALSE, $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$royalties_data = $project_item->get_royalties_data();
				$this->log( "WDGRESTAPI_Route_Project::single_get_royalties::" . $project_id, json_encode( $royalties_data ) );
				return $royalties_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_get_royalties::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_get_royalties", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Retourne les votes d'un projet par son ID
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_votes( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$votes_data = $project_item->get_votes_data();
				$this->log( "WDGRESTAPI_Route_Project::single_get_votes::" . $project_id, json_encode( $votes_data ) );
				return $votes_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_get_votes::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_get_votes", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Retourne les investissements d'un projet par son ID
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_investments( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$investments_data = $project_item->get_investments_data();
				$this->log( "WDGRESTAPI_Route_Project::single_get_investments::" . $project_id, json_encode( $investments_data ) );
				return $investments_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_get_investments::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_get_investments", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Retourne les modèles de contrats d'un projet par son ID
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_contract_models( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$contract_models_data = $project_item->get_contract_models_data();
				$this->log( "WDGRESTAPI_Route_Project::single_get_contract_models::" . $project_id, json_encode( $contract_models_data ) );
				return $contract_models_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_get_contract_models::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_get_contract_models", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Retourne les contrats d'un projet par son ID
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_contracts( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$contracts_data = $project_item->get_contracts_data();
				$this->log( "WDGRESTAPI_Route_Project::single_get_contracts::" . $project_id, json_encode( $contracts_data ) );
				return $contracts_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_get_contracts::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_get_contracts", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Retourne les contrats d'investisement d'un projet par son ID
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_investment_contracts( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$investment_contracts_data = $project_item->get_investment_contracts_data();
				$this->log( "WDGRESTAPI_Route_Project::single_get_investment_contracts::" . $project_id, json_encode( $investment_contracts_data ) );
				return $investment_contracts_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_get_investment_contracts::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_get_investment_contracts", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Crée un projet
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$project_item = new WDGRESTAPI_Entity_Project();
		$this->set_posted_properties( $project_item, WDGRESTAPI_Entity_Project::$db_properties );
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$project_item->set_property( 'client_user_id', $current_client->ID );
		$project_item->save();
		$reloaded_data = $project_item->get_loaded_data();
		$this->log( "WDGRESTAPI_Route_Project::single_create", json_encode( $reloaded_data ) );
		return $reloaded_data;
	}
	
	
	/**
	 * Crée les déclarations manquantes d'un projet
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create_declarations( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$project_item->create_missing_declarations();
				return $project_item->get_declarations();
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_create_declarations::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_create_declarations", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Edite un projet spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $project_item, WDGRESTAPI_Entity_Project::$db_properties );
				$project_item->save();
				$reloaded_data = $project_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_Project::single_edit::" . $project_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_edit::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_edit", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
	/**
	 * Définit les différentes propriétés d'une entité à partir d'informations postées
	 * Override de la fonction parente pour gérer les données d'organisation transmises
	 * @param WDGRESTAPI_Entity $entity
	 * @param array $properties_list
	 */
	public function set_posted_properties( WDGRESTAPI_Entity $entity, array $properties_list ) {
		// On appelle d'abord la fonction parente pour gérer les données du projet
		parent::set_posted_properties( $entity, $properties_list );
		
		// On gère ensuite les données liées à l'organisation
		$project_organizations = WDGRESTAPI_Entity_ProjectOrganization::get_list_by_project_id( $entity->get_loaded_data()->id );
		$project_organization_entity = new WDGRESTAPI_Entity_Organization( $project_organizations[0]->id_organization );
		foreach ( WDGRESTAPI_Entity_Organization::$db_properties as $property_key => $db_property ) {
			$property_new_value = filter_input( INPUT_POST, 'organization_' . $property_key );
			if ( $property_new_value !== null && $property_new_value !== FALSE ) {
				$project_organization_entity->set_property( $property_key, $property_new_value );
			}
		}
		
		$project_organization_entity->save();
	}
	
	/**
	 * Demande à envoyer les documents à Lemon Way
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_post_documents( WP_REST_Request $request ) {
		$project_id = $request->get_param( 'id' );
		if ( !empty( $project_id ) ) {
			$project_item = new WDGRESTAPI_Entity_Project( $project_id );
			$loaded_data = $project_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$project_item->post_documents();
				$reloaded_data = $project_item->get_loaded_data();
				$this->log( "WDGRESTAPI_Route_Project::single_post_documents::" . $project_id, json_encode( $reloaded_data ) );
				return $reloaded_data;
				
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_post_documents::" . $project_id, "404 : Invalid project ID" );
				return new WP_Error( '404', "Invalid project ID" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_Project::single_post_documents", "404 : Invalid project ID (empty)" );
			return new WP_Error( '404', "Invalid project ID (empty)" );
		}
	}
	
// SPECIFIQUE EQUITEARLY
	/**
	 * Crée un projet depuis Equitearly
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create_equitearly( WP_REST_Request $request ) {
		// Vérification de toutes les données
		/*
		user_login	Login de l'utilisateur	OUI	STRING	Queequeg0925
		user_password	Mot de passe de l'utilisateur	OUI	STRING	azerty123
		user_firstname	Prénom de l'utilisateur	OUI	STRING	Dana
		user_lastname	Nom de famille de l'utilisateur	OUI	STRING	Scully
		user_email	E-mail de l'utilisateur	OUI	STRING	Queequeg0925@hotmail.com
		organization_name	Nom de l'entreprise qui lance le projet	OUI	STRING	Ma super entreprise
		organization_email	E-mail de contact pour l'entreprise	OUI	STRING	contact@superentreprise.fr
		campaign_name	Nom du projet sur la plateforme	OUI	STRING	Mon super projet
		equitearly_investment	Montant de l'investissement d'Equitearly	OUI	INT	20000
		equitearly_charges
		 * 
		 */
		
		$post_errors = array();
		$user_login = filter_input( INPUT_POST, 'user_login' );
		if ( !WDGRESTAPI_Lib_Validator::is_name( $user_login ) ) {
			array_push( $post_errors, __( "Le champ Login (user_login) n'est pas correct.", 'wdgrestapi' ) );
		}
		$user_password = filter_input( INPUT_POST, 'user_password' );
		if ( empty( $user_password ) ) {
			array_push( $post_errors, __( "Le champ Mot de passe (user_password) est vide.", 'wdgrestapi' ) );
		}
		$user_firstname = filter_input( INPUT_POST, 'user_firstname' );
		if ( !WDGRESTAPI_Lib_Validator::is_name( $user_firstname ) ) {
			array_push( $post_errors, __( "Le champ Pr&eacute;nom (user_firstname) n'est pas correct.", 'wdgrestapi' ) );
		}
		$user_lastname = filter_input( INPUT_POST, 'user_lastname' );
		if ( !WDGRESTAPI_Lib_Validator::is_name( $user_lastname ) ) {
			array_push( $post_errors, __( "Le champ Nom de famille (user_lastname) n'est pas correct.", 'wdgrestapi' ) );
		}
		$user_email = filter_input( INPUT_POST, 'user_email' );
		if ( !WDGRESTAPI_Lib_Validator::is_email( $user_email ) ) {
			array_push( $post_errors, __( "Le champ E-mail (user_email) n'est pas au bon format.", 'wdgrestapi' ) );
		}
		$organization_name = filter_input( INPUT_POST, 'organization_name' );
		if ( !WDGRESTAPI_Lib_Validator::is_name( $organization_name ) ) {
			array_push( $post_errors, __( "Le champ Nom de l'entreprise (organization_name) n'est pas correct.", 'wdgrestapi' ) );
		}
		$organization_email = filter_input( INPUT_POST, 'organization_email' );
		if ( !WDGRESTAPI_Lib_Validator::is_email( $organization_email ) ) {
			array_push( $post_errors, __( "Le champ E-mail de l'entreprise (organization_email) n'est pas au bon format.", 'wdgrestapi' ) );
		}
		$campaign_name = filter_input( INPUT_POST, 'campaign_name' );
		if ( !WDGRESTAPI_Lib_Validator::is_name( $campaign_name ) ) {
			array_push( $post_errors, __( "Le champ Nom du projet (campaign_name) n'est pas correct.", 'wdgrestapi' ) );
		}
		$equitearly_investment = filter_input( INPUT_POST, 'equitearly_investment' );
		if ( !WDGRESTAPI_Lib_Validator::is_number_positive_integer( $equitearly_investment ) ) {
			array_push( $post_errors, __( "Le champ Montant de l'investissement Equitearly (equitearly_investment) n'est pas un entier positif.", 'wdgrestapi' ) );
		}
		$equitearly_charges = filter_input( INPUT_POST, 'equitearly_charges' );
		if ( empty( $equitearly_charges ) ) {
			// Rien, champ facultatif
		}
		
		if ( empty( $post_errors ) ) {
			$result = WDGRESTAPI_Entity_Project::new_equitearly( $user_login, $user_password, $user_firstname, $user_lastname, $user_email, $organization_name, $organization_email, $campaign_name, $equitearly_investment, $equitearly_charges );
			if ( empty( $result ) ) {
				$this->log( "WDGRESTAPI_Route_Project::single_create_equitearly", "success" );
			} else {
				$this->log( "WDGRESTAPI_Route_Project::single_create_equitearly", print_r( $result, true ) );
				return new WP_Error( 'cant-create', 'equitearly-project-create-error' );
			}
			
		} else {
			$error_buffer = '';
			foreach ( $post_errors as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_Project::single_create_equitearly", "failed" );
			$this->log( "WDGRESTAPI_Route_Project::single_create_equitearly", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
}