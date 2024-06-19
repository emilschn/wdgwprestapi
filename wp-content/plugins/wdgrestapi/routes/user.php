<?php
class WDGRESTAPI_Route_User extends WDGRESTAPI_Route
{

	public function __construct()
	{
		WDGRESTAPI_Route::register_wdg(
			'/users',
			WP_REST_Server::READABLE,
			array($this, 'list_get')
		);

		WDGRESTAPI_Route::register_wdg(
			'/users/stats',
			WP_REST_Server::READABLE,
			array($this, 'list_get_stats')
		);

		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array($this, 'single_get'),
			array('id' => array('default' => 0))
		);

		WDGRESTAPI_Route::register_wdg(
			'/user',
			WP_REST_Server::CREATABLE,
			array($this, 'single_create'),
			$this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE)
		);

		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array($this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE)
		);

		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/investments',
			WP_REST_Server::READABLE,
			array($this, 'single_get_investments'),
			array('id' => array('default' => 0))
		);

		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/investment-contracts',
			WP_REST_Server::READABLE,
			array($this, 'single_get_investment_contracts'),
			array('id' => array('default' => 0))
		);

		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/rois',
			WP_REST_Server::READABLE,
			array($this, 'single_get_rois'),
			array('token' => array('default' => 0))
		);

		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/transactions',
			WP_REST_Server::READABLE,
			array($this, 'single_get_transactions'),
			array('token' => array('default' => 0))
		);
		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/transactions/export',
			WP_REST_Server::READABLE,
			array($this, 'single_get_transactions_export'),
			array('id' => array('default' => 0))
		);
		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/activities',
			WP_REST_Server::READABLE,
			array($this, 'single_get_activities'),
			array('token' => array('default' => 0))
		);

		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/subscriptions',
			WP_REST_Server::READABLE,
			array($this, 'single_get_subscriptions'),
			array('token' => array('default' => 0))
		);

		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/virtual-iban',
			WP_REST_Server::READABLE,
			array($this, 'single_get_virtual_iban'),
			array('token' => array('default' => 0))
		);

		WDGRESTAPI_Route::register_wdg(
			'/user/(?P<id>\d+)/conformity',
			WP_REST_Server::READABLE,
			array($this, 'single_get_conformity'),
			array('token' => array('default' => 0))
		);

		WDGRESTAPI_Route::register_external(
			'/user/(?P<email>[a-zA-Z0-9\-\@\.]+)',
			WP_REST_Server::EDITABLE,
			array($this, 'single_edit_email'),
			$this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE)
		);

		WDGRESTAPI_Route::register_external(
			'/user/(?P<email>[a-zA-Z0-9\-\@\.]+)/royalties',
			WP_REST_Server::READABLE,
			array($this, 'single_get_royalties'),
			array('id' => array('default' => 0))
		);
	}


	public static function register()
	{
		$route_user = new WDGRESTAPI_Route_User();
		return $route_user;
	}

	/**
	 * Retourne la liste des utilisateurs
	 * @return array
	 */
	public function list_get()
	{
		try {
			$input_offset = filter_input(INPUT_GET, 'offset');
			$input_limit = filter_input(INPUT_GET, 'limit');
			$input_full = filter_input(INPUT_GET, 'full');
			$input_link_to_project = filter_input(INPUT_GET, 'link_to_project');

			$offset = (!empty($input_offset)) ? $input_offset : 0;
			$full = ($input_full == '1') ? TRUE : FALSE;

			// Gestion cache
			$cache_name = '/users?offset=' . $input_offset;
			if (!empty($input_limit)) {
				$cache_name .= '&limit=' . $input_limit;
			}
			if (!empty($input_full)) {
				$cache_name .= '&full=' . $input_full;
			}
			if (!empty($input_link_to_project)) {
				$cache_name .= '&link_to_project=' . $input_link_to_project;
			}
			$cached_version_entity = new WDGRESTAPI_Entity_Cache(FALSE, $cache_name);
			$cached_value = $cached_version_entity->get_value(60);

			if (!empty($cached_value)) {
				WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Route_User::use cache');
				$buffer = json_decode($cached_value);
			} else {
				WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Route_User::use request');
				$buffer = WDGRESTAPI_Entity_User::list_get($this->get_current_client_autorized_ids_string(), $offset, $input_limit, $full, $input_link_to_project);
				$cached_version_entity->save($cache_name, json_encode($buffer));
			}

			return $buffer;

		} catch (Exception $e) {
			$this->log("WDGRESTAPI_Route_User::list_get", $e->getMessage());
			return new WP_Error('cant-get', $e->getMessage());
		}
	}

	/**
	 * Retourne les statistiques concernant les utilisateurs
	 * @return array
	 */
	public function list_get_stats()
	{
		try {
			return WDGRESTAPI_Entity_User::get_stats();

		} catch (Exception $e) {
			$this->log("WDGRESTAPI_Route_User::list_get_stats", $e->getMessage());
			return new WP_Error('cant-get', $e->getMessage());
		}
	}

	/**
	 * Retourne un utilisateur par son ID
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_Project
	 */
	public function single_get(WP_REST_Request $request)
	{
		$input_with_links = filter_input(INPUT_GET, 'with_links');

		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			try {
				$user_item = new WDGRESTAPI_Entity_User($user_id);
				$loaded_data = $user_item->get_loaded_data(($input_with_links == '1'));

				if (!empty($loaded_data) && $this->is_data_for_current_client($loaded_data)) {
					return $loaded_data;

				} else {
					$this->log("WDGRESTAPI_Route_User::single_get::" . $user_id, "404 : Invalid user ID");
					return new WP_Error('404', "Invalid user ID");

				}

			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get::" . $user_id, $e->getMessage());
				return new WP_Error('cant-get', $e->getMessage());
			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_get", "404 : Invalid user ID (empty)");
			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}

	/**
	 * Retourne les royalties d'un utilisateur par son ID
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_royalties(WP_REST_Request $request)
	{
		$user_email = FALSE;
		if (!empty($request)) {
			$user_email = $request->get_param('email');
		}
		if (!empty($user_email)) {
			try {
				$royalties_data = WDGRESTAPI_Entity_User::get_royalties_data($user_email);
				return $royalties_data;

			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get_royalties::" . $user_email, $e->getMessage());
				return new WP_Error('cant-get', $e->getMessage());
			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_get_royalties", "404 : Invalid user email (empty)");
			return new WP_Error('404', "Invalid user email (empty)");
		}
	}

	/**
	 * Crée un utilisateur
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create(WP_REST_Request $request)
	{
		$user_item = new WDGRESTAPI_Entity_User();
		$this->set_posted_properties($user_item, WDGRESTAPI_Entity_User::$db_properties);

		$current_client_id = 0;
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		if (!empty($current_client)) {
			$current_client_id = $current_client->ID;
		}
		$user_item->set_property('client_user_id', $current_client_id);
		$user_item->save();

		$reloaded_data = $user_item->get_loaded_data();
		$this->log("WDGRESTAPI_Route_User::single_create", json_encode($reloaded_data));
		return $reloaded_data;
	}

	/**
	 * Edite un utilisateur spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit(WP_REST_Request $request)
	{
		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			$user_item = new WDGRESTAPI_Entity_User($user_id);
			$loaded_data = $user_item->get_loaded_data();
			$cloned_data = clone $loaded_data;

			if (!empty($loaded_data) && $this->is_data_for_current_client($loaded_data)) {
				$this->set_posted_properties($user_item, WDGRESTAPI_Entity_User::$db_properties);
				$user_item->save();
				$reloaded_data = $user_item->get_loaded_data();
				$this->log("WDGRESTAPI_Route_User::single_edit::" . $user_id, json_encode($reloaded_data));

				// on regarde si dans l'API on a un identifiant de wallet, si non, on le créé
				$gateway_list_decoded = json_decode($loaded_data->gateway_list);
				$wdgrestapi = WDGRESTAPI::instance();
				$wdgrestapi->add_include_lib('gateways/lemonwayWalletEditionHelper');
				if (empty($gateway_list_decoded) || !isset($gateway_list_decoded->lemonway)) {
					$lw = WDGRESTAPI_Lib_LemonwayWalletEditionHelper::instance();
					$wallet_registered = $lw->wallet_register($user_item);
				} elseif ($cloned_data->email !== $reloaded_data->email || $cloned_data->gender !== $reloaded_data->gender || $cloned_data->name !== $reloaded_data->name || $cloned_data->surname !== $reloaded_data->surname || $cloned_data->country !== $reloaded_data->country || $cloned_data->phone_number !== $reloaded_data->phone_number || $cloned_data->birthday_date !== $reloaded_data->birthday_date || $cloned_data->nationality !== $reloaded_data->nationality || $cloned_data->company_website !== $reloaded_data->company_website) {
					// si le wallet était existant et qu'on change l'email de cet utilisateur, ou son genre, ou sa date de naissance, ou sa nationalité...
					// on met à jour le wallet
					$lw = WDGRESTAPI_Lib_LemonwayWalletEditionHelper::instance();
					$wallet_updated = $lw->wallet_update($user_item->get_wallet_id('lemonway'), $reloaded_data->email, $reloaded_data->gender, $reloaded_data->name, $reloaded_data->surname, $reloaded_data->country, $reloaded_data->phone_number, $reloaded_data->birthday_date, $reloaded_data->nationality, $reloaded_data->company_website);
				}
				// on recharge de nouveau pour avoir le bon gateway
				$reloaded_data = $user_item->get_loaded_data();

				return $reloaded_data;

			} else {
				$this->log("WDGRESTAPI_Route_User::single_edit::" . $user_id, "404 : Invalid user ID");
				return new WP_Error('404', "Invalid user ID");

			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_edit", "404 : Invalid user ID (empty)");
			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}

	/**
	 * Edite un utilisateur spécifique
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit_email(WP_REST_Request $request)
	{
		$user_email = FALSE;
		if (!empty($request)) {
			$user_email = $request->get_param('email');
		}
		if (!empty($user_email)) {
			$email_data = WDGRESTAPI_Entity_User::update_email($user_email, $_POST);
			$this->log("WDGRESTAPI_Route_User::single_edit_email::" . $user_email, json_encode($email_data));
			return $email_data;

		} else {
			$this->log("WDGRESTAPI_Route_User::single_edit_email", "404 : Invalid user email (empty)");
			return new WP_Error('404', "Invalid user email (empty)");
		}
	}

	/**
	 * Retourne les investissements liés à un utilisateur (par l'ID de l'utilisateur)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_investments(WP_REST_Request $request)
	{
		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			try {
				$user_item = new WDGRESTAPI_Entity_User($user_id);
				$loaded_data = $user_item->get_loaded_data();

				if (!empty($loaded_data) && $this->is_data_for_current_client($loaded_data)) {
					$input_sort = filter_input(INPUT_GET, 'sort');
					$investments_data = $user_item->get_investments($input_sort);
					return $investments_data;

				} else {
					$this->log("WDGRESTAPI_Route_User::single_get_investments::" . $user_id, "404 : Invalid user ID");
					return new WP_Error('404', "Invalid user ID");

				}

			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get_investments::" . $user_id, $e->getMessage());
				return new WP_Error('cant-get', $e->getMessage());
			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_get_investments", "404 : Invalid user ID (empty)");
			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}

	/**
	 * Retourne les contrats d'investissement liés à un utilisateur (par l'ID de l'utilisateur)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_investment_contracts(WP_REST_Request $request)
	{
		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			try {
				$user_item = new WDGRESTAPI_Entity_User($user_id);
				$loaded_data = $user_item->get_loaded_data();

				if (!empty($loaded_data) && $this->is_data_for_current_client($loaded_data)) {
					$rois_data = $user_item->get_investment_contracts();
					return $rois_data;

				} else {
					$this->log("WDGRESTAPI_Route_User::single_get_investment_contracts::" . $user_id, "404 : Invalid user ID");
					return new WP_Error('404', "Invalid user ID");

				}

			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get_investment_contracts::" . $user_id, $e->getMessage());
				return new WP_Error('cant-get', $e->getMessage());
			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_get_investment_contracts", "404 : Invalid user ID (empty)");
			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}

	/**
	 * Retourne les ROIs liées à un utilisateur (par l'ID de l'utilisateur)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_rois(WP_REST_Request $request)
	{
		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			try {
				$user_item = new WDGRESTAPI_Entity_User($user_id);
				$loaded_data = $user_item->get_loaded_data();

				if (!empty($loaded_data) && $this->is_data_for_current_client($loaded_data)) {
					$rois_data = $user_item->get_rois();
					return $rois_data;

				} else {
					$this->log("WDGRESTAPI_Route_User::single_get_rois::" . $user_id, "404 : Invalid user ID");
					return new WP_Error('404', "Invalid user ID");

				}

			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get_rois::" . $user_id, $e->getMessage());
				return new WP_Error('cant-get', $e->getMessage());
			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_get_rois", "404 : Invalid user ID (empty)");
			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}

	/**
	 * Retourne les transactions liées à un utilisateur (par l'ID de l'utilisateur)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_transactions(WP_REST_Request $request)
	{
		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			try {
				$user_item = new WDGRESTAPI_Entity_User($user_id);
				$loaded_data = $user_item->get_loaded_data();

				if (!empty($loaded_data) && $this->is_data_for_current_client($loaded_data)) {
					$transactions_data = $user_item->get_transactions();
					return $transactions_data;

				} else {
					$this->log("WDGRESTAPI_Route_User::single_get_transactions::" . $user_id, "404 : Invalid user ID");
					return new WP_Error('404', "Invalid user ID");

				}

			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get_transactions::" . $user_id, $e->getMessage());
				return new WP_Error('cant-get', $e->getMessage());
			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_get_transactions", "404 : Invalid user ID (empty)");
			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}

	public function single_get_transactions_export(WP_REST_Request $request)
	{
		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			try {
				$user_item = new WDGRESTAPI_Entity_User($user_id);
				$loaded_data = $user_item->get_loaded_data();

				if (!empty($loaded_data) && $this->is_data_for_current_client($loaded_data)) {
					$userfilepath = __DIR__ . '/../files/transactions/' . $user_id . '_transactions.json';
					if (file_exists($userfilepath)) {
						$data = file_get_contents($userfilepath);
						if (str_contains($data, 'project_name')) {
							return $data;
						} else {
							return new WP_Error('404', "not yet");

						}
					} else {
						return new WP_Error('404', "not yet");
					}
				} else {
					$this->log("WDGRESTAPI_Route_User::single_get_transactions_export::" . $user_id, "404 : Invalid user ID");
					return new WP_Error('404', "Invalid user ID");
				}

			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get_transactions_export::" . $user_id, $e->getMessage());
				return new WP_Error('cant-get', $e->getMessage());
			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_get_transactions_export", "404 : Invalid user ID (empty)");
			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}


	/**
	 * Retourne les actions effectuées par un utilisateur (par l'ID de l'utilisateur)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_activities(WP_REST_Request $request)
	{
		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			try {
				$user_item = new WDGRESTAPI_Entity_User($user_id);
				$loaded_data = $user_item->get_loaded_data();

				if (!empty($loaded_data) && $this->is_data_for_current_client($loaded_data)) {
					$activities_data = $user_item->get_activities();
					return $activities_data;

				} else {
					$this->log("WDGRESTAPI_Route_User::single_get_activities::" . $user_id, "404 : Invalid user ID");
					return new WP_Error('404', "Invalid user ID");

				}

			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get_activities::" . $user_id, $e->getMessage());
				return new WP_Error('cant-get', $e->getMessage());
			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_get_activities", "404 : Invalid user ID (empty)");
			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}

	/**
	 * Retourne un vIBAN associé à un investisseur (en le créant si nécessaire)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_virtual_iban(WP_REST_Request $request)
	{
		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			try {
				$user_item = new WDGRESTAPI_Entity_User($user_id);
				$loaded_data = $user_item->get_loaded_data();

				if (!empty($loaded_data) && $this->is_data_for_current_client($loaded_data)) {
					$viban_data = $user_item->get_viban();
					return $viban_data;

				} else {
					$this->log("WDGRESTAPI_Route_User::single_get_virtual_iban::" . $user_id, "404 : Invalid user ID");
					return new WP_Error('404', "Invalid user ID");

				}

			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get_virtual_iban::" . $user_id, $e->getMessage());
				return new WP_Error('cant-get', $e->getMessage());
			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_get_virtual_iban", "404 : Invalid user ID (empty)");
			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}

	public function single_get_conformity(WP_REST_Request $request)
	{
		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			try {
				$user_conformity_item = WDGRESTAPI_Entity_UserConformity::get_by_user_id($user_id);
				if (empty($user_conformity_item)) {
					return array();
				}
				$loaded_data = $user_conformity_item->get_loaded_data();
				WDGRESTAPI_Lib_Logs::log("WDGRESTAPI_Route_User::single_get_conformity " . $user_id . " >> " . json_encode($loaded_data));

				if (!empty($loaded_data)) {
					return $loaded_data;

				} else {
					$this->log("WDGRESTAPI_Route_User::single_get_conformity::" . $user_id, "404 : Invalid user ID");
					return new WP_Error('404', "Invalid user ID");

				}

			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get_conformity::" . $user_id, $e->getMessage());
				return new WP_Error('cant-get', $e->getMessage());
			}

		} else {
			$this->log("WDGRESTAPI_Route_User::single_get_conformity", "404 : Invalid user ID (empty)");
			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}

	/**
	 * Retourne les abonnements liées à un utilisateur (par l'ID de l'utilisateur)
	 * @param WP_REST_Request $request
	 * @return object
	 */
	public function single_get_subscriptions(WP_REST_Request $request)
	{
		$user_id = FALSE;
		if (!empty($request)) {
			$user_id = $request->get_param('id');
		}
		if (!empty($user_id)) {
			try {
				$user_item = new WDGRESTAPI_Entity_User($user_id);
				$loaded_data = $user_item->get_loaded_data();

				if (!empty($loaded_data) && $this->is_data_for_current_client($loaded_data)) {
					$loaded_data = $user_item->get_subscriptions_by_subscriber_id();

					return $loaded_data;
				} else {
					$this->log("WDGRESTAPI_Route_User::single_get_subscriptions::" . $user_id, "404 : Invalid user ID");

					return new WP_Error('404', "Invalid user ID");
				}
			} catch (Exception $e) {
				$this->log("WDGRESTAPI_Route_User::single_get_subscriptions::" . $user_id, $e->getMessage());

				return new WP_Error('cant-get', $e->getMessage());
			}
		} else {
			$this->log("WDGRESTAPI_Route_User::single_get_subscriptions", "404 : Invalid user ID (empty)");

			return new WP_Error('404', "Invalid user ID (empty)");
		}
	}
}