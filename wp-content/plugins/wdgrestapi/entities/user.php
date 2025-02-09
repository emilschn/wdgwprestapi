<?php
class WDGRESTAPI_Entity_User extends WDGRESTAPI_Entity
{
	public static $entity_type = 'user';

	public function __construct($id = FALSE)
	{
		parent::__construct($id, self::$entity_type, self::$db_properties);

		// Si on a trouvé une ligne correspondante à cette ID et que les informations de validation de mail ne sont pas définies, on les initialise
		if (!empty($this->loaded_data->wpref) && empty($this->loaded_data->email_is_validated)) {
			$this->loaded_data->email_is_validated = wp_generate_uuid4();
			$this->save();
		}
	}

	/**
	 * Récupère un utilisateur à partir de son id WP
	 */
	public static function get_by_wpref($wpref)
	{
		global $wpdb;
		if (empty($wpdb)) {
			return FALSE;
		}
		$table_name = WDGRESTAPI_Entity::get_table_name(self::$entity_type);
		$query = 'SELECT * FROM ' . $table_name . ' WHERE wpref=' . $wpref;
		$result = $wpdb->get_row($query);
		$user = new WDGRESTAPI_Entity_User($result->id);

		return $user;
	}

	/**
	 * Récupère un utilisateur à partir de son adresse e-mail
	 */
	public static function get_by_email($email)
	{
		global $wpdb;
		if (empty($wpdb)) {
			return FALSE;
		}
		$table_name = WDGRESTAPI_Entity::get_table_name(self::$entity_type);
		$query = 'SELECT * FROM ' . $table_name . ' WHERE email=\'' . $email . '\' LIMIT 1';
		$result = $wpdb->get_row($query);
		if (empty($result) || empty($result->id)) {
			return FALSE;
		}
		$user = new WDGRESTAPI_Entity_User($result->id);

		return $user;
	}

	/**
	 * Override de la fonction de sauvegarde pour supprimer le cache des listes d'utilisateur
	 */
	public function save()
	{
		$buffer = parent::save();
		WDGRESTAPI_Entity_Cache::delete_by_name_like('/users');

		return $buffer;
	}

	public function get_loaded_data($with_links = FALSE)
	{
		$buffer = parent::get_loaded_data();
		$buffer = WDGRESTAPI_Entity_User::standardize_data($buffer);

		if (!empty($with_links)) {
			// Récupération des projets liés
			$project_list_by_user_id = WDGRESTAPI_Entity_ProjectUser::get_list_by_user_id($this->loaded_data->id);
			$project_list = array();
			foreach ($project_list_by_user_id as $link_item) {
				$project = new WDGRESTAPI_Entity_Project($link_item->id_project);
				$loaded_data = $project->get_loaded_data();
				array_push($project_list, array(
					"id" => $loaded_data->id,
					"wpref" => $loaded_data->wpref,
					"name" => $loaded_data->name,
					"type" => $link_item->type
				));
			}
			$buffer->projects = $project_list;

			// Récupération des organisations liées
			$organization_list_by_user_id = WDGRESTAPI_Entity_OrganizationUser::get_list_by_user_id($this->loaded_data->id);
			$organization_list = array();
			foreach ($organization_list_by_user_id as $link_item) {
				$organization = new WDGRESTAPI_Entity_Organization($link_item->id_organization);
				$loaded_data = $organization->get_loaded_data();
				array_push($organization_list, array(
					"id" => $loaded_data->id,
					"wpref" => $loaded_data->wpref,
					"name" => $loaded_data->name,
					"type" => $link_item->type
				));
			}
			$buffer->organizations = $organization_list;

			// Récupération des abonnements liés
			$buffer->subscriptions = WDGRESTAPI_Entity_Subscription::get_subscriptions_by_subscriber_id($this->loaded_data->id, 'user');
		}

		return $buffer;
	}

	/**
	 * Refait un tour des données pour les retourner au meilleur format
	 * @param type $item
	 */
	public static function standardize_data($item)
	{
		if (!empty($item)) {
			$item->birthday_date = WDGRESTAPI_Entity::standardize_date($item->birthday_date);
		}

		return $item;
	}

	/**
	 * Retourne la liste des investissements de cet utilisateur
	 * @return array
	 */
	public function get_investments($input_sort = FALSE)
	{
		if (empty($this->loaded_data->id)) {
			return FALSE;
		}

		$investments = WDGRESTAPI_Entity_Investment::get_list_by_user($this->loaded_data->id);
		$investments_pending = WDGRESTAPI_Entity_Investment::get_list_by_user($this->loaded_data->id, FALSE, 'pending');
		$investments = array_merge($investments, $investments_pending);

		if (empty($input_sort)) {
			return $investments;
		}

		if ($input_sort == 'project') {
			$investment_contracts = WDGRESTAPI_Entity_InvestmentContract::list_get_by_investor($this->loaded_data->id, 'user');

			$investment_contracts_by_subscription_wpref = array();
			foreach ($investment_contracts as $investment_contract_item) {
				$investment_contracts_by_subscription_wpref[$investment_contract_item->subscription_id] = $investment_contract_item;
			}

			// *****
			// Certains contrats d'investissement sont orphelins :
			// ils existent sans que l'utilisateur n'ait investi lui-même
			// Exemple : succession, ...
			$orphans_investment_contracts = array();
			foreach ($investment_contracts as $investment_contract_item) {
				$orphans_investment_contracts[$investment_contract_item->subscription_id] = $investment_contract_item;
			}
			// Premier parcours des investissements pour garder les contrats d'investissement sans investissement
			foreach ($investments as $investment_item) {
				if ($investment_item->status != 'publish' && $investment_item->status != 'pending') {
					continue;
				}
				if (!empty($orphans_investment_contracts[$investment_item->wpref])) {
					unset($orphans_investment_contracts[$investment_item->wpref]);
				}
			}
			// Parcours des contrats d'investissement restants,
			// récupération de l'investissement d'origine
			// et ajout dans la liste des investissements "normaux"
			foreach ($orphans_investment_contracts as $orphan_contract_wpref => $orphan_contract) {
				$new_investment_item = new WDGRESTAPI_Entity_Investment(FALSE, FALSE, $orphan_contract_wpref);
				array_push($investments, $new_investment_item->get_loaded_data());
			}
			// *****

			// Vraie initialisation des données d'investissement
			$projects_by_id = array();
			foreach ($investments as $investment_item) {
				if ($investment_item->status != 'publish' && $investment_item->status != 'pending') {
					continue;
				}

				if (empty($projects_by_id[$investment_item->project])) {
					$projects_by_id[$investment_item->project] = array();
					$projects_by_id[$investment_item->project]['project_id'] = $investment_item->project;

					// Données liées au projet
					$project_entity = new WDGRESTAPI_Entity_Project($investment_item->project);
					$project_entity_data = $project_entity->get_loaded_data(FALSE);
					$projects_by_id[$investment_item->project]['project_wpref'] = $project_entity_data->wpref;
					$projects_by_id[$investment_item->project]['project_name'] = $project_entity_data->name;
					$projects_by_id[$investment_item->project]['project_status'] = $project_entity_data->status;
					$projects_by_id[$investment_item->project]['project_amount'] = $project_entity_data->amount_collected;
					$projects_by_id[$investment_item->project]['project_funding_end_date'] = $project_entity_data->funding_end_datetime;
					$projects_by_id[$investment_item->project]['project_contract_start_date'] = $project_entity_data->contract_start_date;
					$projects_by_id[$investment_item->project]['project_funding_duration'] = $project_entity_data->funding_duration;
					$projects_by_id[$investment_item->project]['project_roi_percent'] = $project_entity_data->roi_percent;
					$projects_by_id[$investment_item->project]['project_roi_percent_estimated'] = $project_entity_data->roi_percent_estimated;
					$projects_by_id[$investment_item->project]['project_goal_maximum'] = $project_entity_data->goal_maximum;
					$projects_by_id[$investment_item->project]['project_first_payment_date'] = '';
					$projects_by_id[$investment_item->project]['project_url'] = $project_entity_data->url;
					$projects_by_id[$investment_item->project]['project_estimated_turnover'] = $project_entity_data->estimated_turnover;
					$projects_by_id[$investment_item->project]['project_estimated_turnover_unit'] = '';

					$projects_by_id[$investment_item->project]['declarations'] = WDGRESTAPI_Entity_Declaration::list_get_by_project_id( $investment_item->project, TRUE, FALSE, TRUE );

					$projects_by_id[$investment_item->project]['investments'] = array();
				}

				$new_item = array();

				// Données intrinsèques à l'investissement
				$new_item['id'] = $investment_item->id;
				$new_item['wpref'] = $investment_item->wpref;
				$new_item['amount'] = $investment_item->amount;
				$new_item['invest_datetime'] = $investment_item->invest_datetime;
				$new_item['status'] = $investment_item->status;
				$new_item['mean_payment'] = $investment_item->mean_payment;

				// Données liées au contrat
				$new_item['contract_status'] = '';
				if (!empty($investment_contracts_by_subscription_wpref[$investment_item->wpref])) {
					$new_item['contract_status'] = $investment_contracts_by_subscription_wpref[$investment_item->wpref]->status;
					$new_item['amount'] = $investment_contracts_by_subscription_wpref[$investment_item->wpref]->subscription_amount;
				}

				// Données liées aux royalties
				$new_item['rois'] = WDGRESTAPI_Entity_ROI::list_get_by_investment_wpref_and_user( $investment_item->wpref, $this->loaded_data->id );;

				array_push($projects_by_id[$investment_item->project]['investments'], $new_item);
			}

			// Re-liste des projets en tableau non-associatif
			$buffer = array();
			foreach ($projects_by_id as $project_item) {
				array_push($buffer, $project_item);
			}
		}

		return $buffer;
	}

	public function get_investment_royalties($inv_id)
	{
		$investment = new WDGRESTAPI_Entity_Investment($inv_id);
		$loadedData = $investment->get_loaded_data();

		$output = [];


		$projects_by_id = [];
		$investment_contracts = WDGRESTAPI_Entity_InvestmentContract::list_get_by_investor($loadedData->user_id, 'user');

		$investment_contracts_by_subscription_wpref = array();
		foreach ($investment_contracts as $investment_contract_item) {
			$investment_contracts_by_subscription_wpref[$investment_contract_item->subscription_id] = $investment_contract_item;
		}


		$projects_by_id = array();
		$projects_by_id['project_id'] = $loadedData->project;

		// Données liées au projet
		$project_entity = new WDGRESTAPI_Entity_Project($loadedData->project);
		$project_entity_data = $project_entity->get_loaded_data();
		$projects_by_id['project_wpref'] = $project_entity_data->wpref;
		$projects_by_id['project_name'] = $project_entity_data->name;
		$projects_by_id['project_status'] = $project_entity_data->status;
		$projects_by_id['project_amount'] = $project_entity_data->amount_collected;
		$projects_by_id['project_funding_end_date'] = $project_entity_data->funding_end_datetime;
		$projects_by_id['project_contract_start_date'] = $project_entity_data->contract_start_date;
		$projects_by_id['project_funding_duration'] = $project_entity_data->funding_duration;
		$projects_by_id['project_roi_percent'] = $project_entity_data->roi_percent;
		$projects_by_id['project_roi_percent_estimated'] = $project_entity_data->roi_percent_estimated;
		$projects_by_id['project_goal_maximum'] = $project_entity_data->goal_maximum;
		$projects_by_id['project_first_payment_date'] = '';
		$projects_by_id['project_url'] = $project_entity_data->url;
		$projects_by_id['project_estimated_turnover'] = $project_entity_data->estimated_turnover;
		$projects_by_id['project_estimated_turnover_unit'] = '';

		$projects_by_id['declarations'] = WDGRESTAPI_Entity_Declaration::list_get_by_project_id($loadedData->project, TRUE, FALSE, TRUE);

		$projects_by_id['investments'] = array();

		$new_item = array();

		// Données intrinsèques à l'investissement
		$new_item['id'] = $loadedData->id;
		$new_item['wpref'] = $loadedData->wpref;
		$new_item['amount'] = $loadedData->amount;
		$new_item['invest_datetime'] = $loadedData->invest_datetime;
		$new_item['status'] = $loadedData->status;
		$new_item['mean_payment'] = $loadedData->mean_payment;

		// Données liées au contrat
		$new_item['contract_status'] = '';
		if (!empty($investment_contracts_by_subscription_wpref[$loadedData->wpref])) {
			$new_item['contract_status'] = $investment_contracts_by_subscription_wpref[$loadedData->wpref]->status;
			$new_item['amount'] = $investment_contracts_by_subscription_wpref[$loadedData->wpref]->subscription_amount;
		}
		
		// Données liées aux royalties
		$new_item['rois'] = WDGRESTAPI_Entity_ROI::list_get_by_investment_wpref_and_user($loadedData->wpref, $loadedData->user_id);
		array_push($projects_by_id['investments'], $new_item);

		return $projects_by_id;
	}

	public function get_investments_new($input_sort = FALSE)
	{
		if (empty($this->loaded_data->id)) {
			return FALSE;
		}

		$investments = WDGRESTAPI_Entity_Investment::get_list_by_user($this->loaded_data->id);
		$investments_pending = WDGRESTAPI_Entity_Investment::get_list_by_user($this->loaded_data->id, FALSE, 'pending');
		$investments = array_merge($investments, $investments_pending);
		$roi = WDGRESTAPI_Entity_ROI::get_total_investment_by_user_id($this->loaded_data->id);
		$output['roi_total'] = $roi->total;
		$projects = [];
		foreach($investments as $invest){
			if ($invest->status != 'publish' && $invest->status != 'pending') {
				continue;
			}

			if(!isset($projects[$invest->project])){
				$project = WDGRESTAPI_Entity_Project::get_project_name($invest->project);
				$projects[$invest->project]['name'] = $project->name;
				$projects[$invest->project]['status'] = $project->status;
				$projects[$invest->project]['project_funding_end_date'] = $project->funding_end_datetime;
				$projects[$invest->project]['wpref'] = $project->wpref;
				$projects[$invest->project]['funding_duration'] = $project->funding_duration;
				$projects[$invest->project]['url'] = $project->url;

				$projects[$invest->project]['items'] = [];
			}

			$projects[$invest->project]['items'][$invest->id] = [
				'id' => $invest->id,
				'wpref' => $invest->wpref,
				'amount' => $invest->amount,
				'date' => date_i18n('j F Y', strtotime($invest->invest_datetime)),
				'time' => date_i18n('H\hi', strtotime($invest->invest_datetime)),
				'status' => $invest->status,
				'payment_status' => $invest->payment_status,
				'project_status' => $projects[$invest->project]['status'],
				'mean_payment' => $invest->mean_payment,
			];
		}

		$output['investments'] = $projects;

		return $output;
	}


	/**
	 * Retourne la liste des contrats d'investissement de cet utilisateur
	 * @return array
	 */
	public function get_investment_contracts()
	{
		$buffer = FALSE;
		if (!empty($this->loaded_data->id)) {
			$buffer = WDGRESTAPI_Entity_InvestmentContract::list_get_by_investor($this->loaded_data->id, 'user');
		}

		return $buffer;
	}

	/**
	 * Retourne la liste des ROIs de cet utilisateur
	 * @return array
	 */
	public function get_rois()
	{
		$buffer = FALSE;
		if (!empty($this->loaded_data->id)) {
			$buffer = WDGRESTAPI_Entity_ROI::list_get_by_recipient_id($this->loaded_data->id, WDGRESTAPI_Entity_ROI::$recipient_type_user);
		}

		return $buffer;
	}

	/**
	 * Retourne la liste des transactions de cette organisation
	 */
	public function get_transactions()
	{
		if (!empty($this->loaded_data->gateway_list)) {
			return WDGRESTAPI_Entity_Transaction::list_get_by_user_id($this->loaded_data->id, json_decode($this->loaded_data->gateway_list));
		}

		return FALSE;
	}

	/**
	 * Retourne la liste des actions effectuées par l'utilisateur
	 */
	public function get_activities()
	{
		$buffer = array(
			'projects' => array(
				array(
					'id' => 1,
					'name' => "Super projet",
					'vote_validate' => 0,
					'vote_environment' => 1,
					'vote_social' => 2,
					'vote_economy' => 3,
					'vote_risk' => 4,
					'vote_invest_sum' => 40,
					'invest_amount' => 50,
					'contract_url' => "https://www.wedogood.co",
				),
				array(
					'id' => 2,
					'name' => "Super projet 2",
					'vote_validate' => 1,
					'vote_environment' => 2,
					'vote_social' => 2,
					'vote_economy' => 1,
					'vote_risk' => 1,
					'vote_invest_sum' => 405,
					'invest_amount' => 520,
					'contract_url' => "https://www.wedogood.co",
				),
			)
		);

		return $buffer;
	}

	/**
	 * Recherche un vIBAN existant
	 * Si pas trouvé, en crée un et le retourne
	 */
	public function get_viban()
	{
		$buffer = FALSE;
		$wdgrestapi = WDGRESTAPI::instance();
		$wdgrestapi->add_include_lib('gateways/lemonway');
		$lw = WDGRESTAPI_Lib_Lemonway::instance();
		$gateway_list_decoded = json_decode($this->loaded_data->gateway_list);
		if (isset($gateway_list_decoded->lemonway)) {
			$lw_wallet_id = $gateway_list_decoded->lemonway;
			$buffer = $lw->get_viban($lw_wallet_id);
			if (empty($buffer)) {
				$create_result = $lw->create_viban($lw_wallet_id);
				$buffer = $create_result;
				if (!empty($create_result)) {
					$buffer->DATA = $create_result->IBAN;
					$buffer->SWIFT = $create_result->BIC;
				}
			}
		}

		return $buffer;
	}

	/**
	 * Retourne l'identifiant de wallet selon le gateway
	 */
	public function get_wallet_id($gateway)
	{
		$gateway_list_decoded = json_decode($this->loaded_data->gateway_list);
		if ($gateway == 'lemonway' && isset($gateway_list_decoded->lemonway)) {
			return $gateway_list_decoded->lemonway;
		}
		return FALSE;
	}

	/**
	 * Enregistre l'identifiant de wallet selon le gateway
	 */
	public function set_wallet_id($gateway, $id)
	{
		$gateway_list_decoded = json_decode($this->loaded_data->gateway_list);
		if (empty($gateway_list_decoded)) {
			$gateway_list_decoded = array();
		}
		$gateway_list_decoded[$gateway] = $id;

		parent::set_property('gateway_list', json_encode($gateway_list_decoded));
		$this->save();
		return json_encode($gateway_list_decoded);
	}

	/**
	 * Retourne la liste des investissement liées à cet utilisateur
	 */
	public function get_subscriptions_by_subscriber_id()
	{
		$buffer = WDGRESTAPI_Entity_Subscription::get_subscriptions_by_subscriber_id($this->loaded_data->id, 'user');

		return $buffer;
	}

	/**
	 * Récupération des données de royalties concernant un utilisateur
	 * @return string
	 */
	public static function get_royalties_data($param_email)
	{
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site('get_royalties_by_user', $param_email);

		return $buffer;
	}

	/**
	 * Met à jour l'e-mail de l'utilisateur
	 * @param string $param_email
	 * @param array $posted_array
	 * @return string
	 */
	public static function update_email($param_email, $posted_array)
	{
		$buffer = array();
		$new_email = $posted_array['new_email'];

		if (empty($new_email) || $param_email == $new_email) {
			$buffer['error'] = '404';
			$buffer['error-message'] = 'Invalid new email';
		} else {
			$posted_params = array(
				'new_email' => $new_email
			);
			$return = WDGRESTAPI_Entity::post_data_on_client_site('update_user_email', $param_email, $posted_params);

			if ($return == 'success') {
				$buffer = 'success';
			} else {
				$buffer['error'] = '404';
				$buffer['error-message'] = $return;
			}
		}

		return $buffer;
	}

	/**
	 * Retourne la liste de tous les utilisateurs
	 * @return array
	 */
	public static function list_get($authorized_client_id_string, $offset = 0, $limit = FALSE, $full = FALSE, $input_link_to_project = FALSE)
	{
		global $wpdb;
		if (!isset($wpdb)) {
			return FALSE;
		}

		$table_name = WDGRESTAPI_Entity::get_table_name(WDGRESTAPI_Entity_User::$entity_type);

		if (!empty($input_link_to_project)) {
			// TODO : changer requete pour faire liaison avec table votes et table investissements
			$query = "SELECT * FROM " . $table_name . " WHERE client_user_id IN " . $authorized_client_id_string . " ORDER BY email ASC";
			$count_query = "SELECT COUNT(*) AS nb FROM " . $table_name . " WHERE client_user_id IN " . $authorized_client_id_string;
		} else {
			$query = "SELECT * FROM " . $table_name . " WHERE client_user_id IN " . $authorized_client_id_string . " ORDER BY email ASC";
			$count_query = "SELECT COUNT(*) AS nb FROM " . $table_name . " WHERE client_user_id IN " . $authorized_client_id_string;
		}

		// Gestion offset et limite
		if (empty($limit)) {
			$limit = 100;
		}
		if ($offset > 0 || !empty($limit)) {
			$query .= " LIMIT ";

			if ($offset > 0) {
				$query .= $offset . ", ";
				if (empty($limit)) {
					$query .= "0";
				}
			}
			if (!empty($limit)) {
				$query .= $limit;
			}
		}

		$results = $wpdb->get_results($query);
		foreach ($results as $result) {
			$result->type = 'user';
			$rand_project_manager = rand(0, 20);
			$result->is_project_manager = ($rand_project_manager > 17); // TODO
			$result = WDGRESTAPI_Entity_Project::standardize_data($result);
		}

		if ($full) {
			foreach ($results as $result) {
				$result->vote_count = rand(0, 20); //TODO
				$result->invest_count = rand(0, 30); //TODO
				$result->invest_amount = rand(0, 20000); //TODO
				$result->invest_amount_royalties = rand(0, 200); //TODO
				$result->royalties_amount_received = rand(0, 700); //TODO
				$result->lw_amount_wallet = rand(0, 500); //TODO
				$result->lw_wallet_authentication = 'todo'; //TODO
				$result->lw_iban_authentication = 'todo'; //TODO
			}
		}

		$count_results = $wpdb->get_results($count_query);

		$buffer = array(
			'offset' => $offset,
			'limit' => $limit,
			'count' => count($results),
			'total' => $count_results[0]->nb,
			'results' => $results
		);

		return $buffer;
	}

	/**
	 * Retourne les statistiques qui concernent les utilisateurs
	 */
	public static function get_stats()
	{
		$buffer = WDGRESTAPI_Entity::get_data_on_client_site('get_users_stats');
		if (empty($buffer)) {
			return $buffer;
		}

		$buffer->investors_count = 0;
		$buffer->investors_multi_count = 0;

		global $wpdb;
		if (isset($wpdb)) {
			$table_investments = WDGRESTAPI_Entity::get_table_name(WDGRESTAPI_Entity_Investment::$entity_type);
			$count_query = "SELECT COUNT( DISTINCT user_id ) AS nb FROM " . $table_investments;
			$count_results = $wpdb->get_results($count_query);
			$buffer->investors_count = $count_results[0]->nb;

			$count_multi_query = "SELECT COUNT( DISTINCT user_id ) AS nb FROM " . $table_investments . " GROUP BY user_id HAVING COUNT( user_id ) > 1";
			$count_multi_results = $wpdb->get_results($count_multi_query);
			$buffer->investors_multi_count = $count_multi_results[0]->nb;
		}

		return $buffer;
	}

	/*******************************************************************************
	 * GESTION BDD
	 ******************************************************************************/

	public static $db_properties = array(
		'unique_key' => 'id',
		'id' => array('type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT'),
		'wpref' => array('type' => 'id', 'other' => 'NOT NULL'),
		'client_user_id' => array('type' => 'id', 'other' => 'DEFAULT 1 NOT NULL'),
		'email' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'gender' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'name' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'surname' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'surname_use' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'username' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'birthday_date' => array('type' => 'date', 'other' => ''),
		'birthday_city' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'birthday_district' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'birthday_department' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'birthday_country' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'nationality' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'address_number' => array('type' => 'int', 'other' => 'NOT NULL'),
		'address_number_comp' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'address' => array('type' => 'longtext', 'other' => 'NOT NULL'),
		'postalcode' => array('type' => 'varchar', 'other' => ''),
		'city' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'country' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'tax_country' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'phone_number' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'description' => array('type' => 'longtext', 'other' => 'NOT NULL'),
		'contact_if_deceased' => array('type' => 'longtext', 'other' => 'NOT NULL'),
		'bank_iban' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'bank_bic' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'bank_holdername' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'bank_address' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'bank_address2' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'document_id' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'document_home' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'document_rib' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'authentification_mode' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'activation_key' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'password' => array('type' => 'varchar', 'other' => 'NOT NULL'),
		'signup_date' => array('type' => 'date', 'other' => ''),
		'royalties_notifications' => array('type' => 'varchar', 'other' => ''),
		'gateway_list' => array('type' => 'varchar', 'other' => ''),
		'language' => array('type' => 'varchar', 'other' => ''),
		'email_is_validated' => array('type' => 'varchar', 'other' => ''),
		'risk_validation_time' => array('type' => 'datetime', 'other' => ''),
		'source' => array('type' => 'varchar', 'other' => '')
	);

	// Mise à jour de la bdd
	public static function upgrade_db()
	{
		return WDGRESTAPI_Entity::upgrade_entity_db(self::$entity_type, self::$db_properties);
	}
}