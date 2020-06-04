<?php
class WDGRESTAPI_Entity_Transaction extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'transaction';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}
	
	/**
	 * Retourne la liste des transactions liées à un utilisateur
	 * @return array
	 */
	public static function list_get_by_user_id( $user_id, $gateway_list ) {
		return self::list_get_updated_by_gateway_list( $user_id, FALSE, $gateway_list );
	}

	/**
	 * Retourne la liste des transactions liées à une organisation
	 */
	public static function list_get_by_organization_id( $organization_id, $gateway_list ) {
		return self::list_get_updated_by_gateway_list( $organization_id, TRUE, $gateway_list );
	}

	/**
	 * Crée la requête de récupération des transactions liées à un utilisateur ou une organisation
	 */
	private static function list_get_by_entity_id( $item_id, $is_legal_entity, $limit = FALSE ) {
		$is_legal_entity_str = $is_legal_entity ? '1' : '0';
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
		$query = 'SELECT * FROM ' .$table_name. ' WHERE (sender_id = ' .$item_id. ' AND sender_is_legal_entity = ' .$is_legal_entity_str. ') OR (recipient_id = ' .$item_id. ' AND recipient_is_legal_entity = ' .$is_legal_entity_str. ') ORDER BY id DESC';
		if ( !empty( $limit ) ) {
			$query .= ' LIMIT ' .$limit;
		}
		
		$results = $wpdb->get_results( $query );
		return $results;
	}

	/**
	 * Renvoie de la liste des transactions mise à jour
	 */
	private static function list_get_updated_by_gateway_list( $item_id, $is_legal_entity, $gateway_list ) {
		if ( isset( $gateway_list->lemonway ) ) {
			// Met à jour la BDD avec les dernières infos en provenance de LW
			self::update_with_lw_data( $item_id, $is_legal_entity, $gateway_list->lemonway );
		}

		// Retourne les résultats mis à jour
		return self::list_get_by_entity_id( $item_id, $is_legal_entity );
	}

	/**
	 * Insère les informations provenant de LW dans la BDD
	 */
	private static function update_with_lw_data( $item_id, $is_legal_entity, $lemonway_id ) {
		if ( empty( $item_id ) || empty( $lemonway_id ) ) {
			return;
		}

		// Récupère les transactions précédemment enregistrées et réordonne par ID en index
		// Permet d'éviter les doublons, sert de cache pour accélérer
		$previous_items_by_wedogood_entity_id = array();
		$previous_items_by_gateway_id = array();
		$previous_items = self::list_get_by_entity_id( $item_id, $is_legal_entity );
		if ( !empty( $previous_items ) ) {
			foreach ( $previous_items as $item ) {
				if ( !empty( $item->gateway_name ) && !empty( $item->type ) && !empty( $item->gateway_transaction_id ) ) {
					$previous_items_by_gateway_id[ $item->gateway_name. '::' .$item->type. '::' .$item->gateway_transaction_id ] = $item;
				}
				
				if ( !empty( $item->wedogood_entity ) && !empty( $item->wedogood_entity_id ) ) {
					$previous_items_by_wedogood_entity_id[ $item->wedogood_entity. '::' .$item->wedogood_entity_id ] = $item;
				}
			}
		}

		// Récupération lib LW
		$wdgrestapi = WDGRESTAPI::instance();
		$wdgrestapi->add_include_lib( 'gateways/lemonway' );
		$lw = WDGRESTAPI_Lib_Lemonway::instance();

		// Exécute la requête chez LW et réordonne par ID en index si pas existant
		$lw_items_by_gateway_id = array();
		$lw_last_transactions = $lw->get_wallet_transactions( $lemonway_id );
		if ( !empty( $lw_last_transactions ) ) {
			foreach ( $lw_last_transactions as $transaction_item ) {
				if ( !isset( $previous_items_by_gateway_id[ 'lemonway::' .$transaction_item->TYPE. '::' .$transaction_item->ID ] ) ) {
					$lw_items_by_gateway_id[ $transaction_item->TYPE. '::' . $transaction_item->ID ] = $transaction_item;
				}
			}
		}

		//***********************
		// Récupère les investissements liés à cet utilisateur
		$investments_by_user = WDGRESTAPI_Entity_Investment::get_list_by_user( $item_id, $is_legal_entity );
		// Parcourt les éléments
		if ( !empty( $investments_by_user ) ) {
			foreach ( $investments_by_user as $investment_item ) {
				// Ne prend pas les failed
				if ( $investment_item->status == 'failed' ) {
					continue;
				}

				// Ne prend pas ceux déjà enregistrés
				if ( !isset( $previous_items_by_wedogood_entity_id[ 'investment::' .$investment_item->id ] ) ) {
					// Récupération organisation liée au projet investi
					$organizations_linked = WDGRESTAPI_Entity_ProjectOrganization::get_list_by_project_id( $investment_item->project );
					$orga_linked_id = 0;
					foreach ( $organizations_linked as $project_orga_link ) {
						if ( $project_orga_link->type == WDGRESTAPI_Entity_ProjectOrganization::$link_type_manager ) {
							$orga_linked_id = $project_orga_link->id_organization;
						}
					}

					// Récupère un éventuel P2P lié
					$linked_p2p = '';
					if ( !empty( $investment_item->payment_provider_p2p_id ) ) {
						$linked_p2p = $investment_item->payment_provider_p2p_id;

					} else {
						// Si c'est par wallet, l'id est stocké juste après "wallet" dans la clé
						if ( strpos( $investment_item->payment_key, 'wallet' ) !== FALSE ) {
							$split_payment_key = explode( 'wallet_', $investment_item->payment_key );
							if ( count( $split_payment_key ) > 1 ) {
								$linked_p2p = $split_payment_key[ 1 ];
							}
						}
						// Si c'est par virement, on n'a pas stocké d'identifiant
						// Mais il y a tout de même un P2P correspondant quelque part...
						if ( $investment_item->mean_payment == 'wire' ) {
							// On parcourt les items LW restants
							// On ne parcourt que 
							// - les P2P
							// - du même montant
							// - partis vers l'organisation du projet
							foreach ( $lw_items_by_gateway_id as $transaction_item ) {
								if (	$transaction_item->TYPE == '2'
										&& strpos( $transaction_item->REC, 'ORGA' .$orga_linked_id. 'W' ) !== FALSE
										&& intval( $transaction_item->DEB ) == $investment_item->amount
											) {
									$linked_p2p = $transaction_item->ID;
									break;
								}
							}
						}
	
						// On met à jour la donnée d'investissement avec le P2P trouvé pour accélérer le processus la prochaine fois
						if ( !empty( $linked_p2p ) ) {
							$investment_item_update = new WDGRESTAPI_Entity_Investment( $investment_item->id );
							$investment_item_update->set_property( 'payment_provider_p2p_id', $linked_p2p );
							$investment_item_update->save();
						}
					}

					// Supprimer dans la liste des items LW
					if ( !empty( $linked_p2p ) ) {
						unset( $lw_items_by_gateway_id[ '2::' .$linked_p2p ] );
					}

					$gateway_name = $investment_item->payment_provider;
					if ( $investment_item->mean_payment == 'check' ) {
						$gateway_name = 'check';
					}

					// Ajoute l'élément
					self::insert_item(
						$investment_item->invest_datetime, $investment_item->amount * 100,
						$item_id, $is_legal_entity, '',
						$orga_linked_id, true, 'campaign',
						'investment', 'success',
						$gateway_name, $investment_item->mean_payment, $linked_p2p,
						'investment', $investment_item->id,
						$investment_item->project
					);

				} else {
					// Supprimer tout de même le P2P lié à la transaction existant pour ne pas l'ajouter en plus
					$previous_transaction_item = $previous_items_by_wedogood_entity_id[ 'investment::' .$investment_item->id ];
					unset( $lw_items_by_gateway_id[ '2::' .$previous_transaction_item->gateway_transaction_id ] );
				}
			}
		}

		//***********************
		// Récupère les rois liées à cet utilisateur, puis parcours et supprime si déjà enregistré
		$rois_by_recipient_id = WDGRESTAPI_Entity_ROI::list_get_by_recipient_id( $item_id, $is_legal_entity ? 'orga' : 'user' );
		if ( !empty( $rois_by_recipient_id ) ) {
			foreach ( $rois_by_recipient_id as $roi_item ) {
				// Ne prend que les transferred
				if ( $roi_item->status != 'transferred' ) {
					continue;
				}

				if ( !isset( $previous_items_by_wedogood_entity_id[ 'roi::' .$roi_item->id ] ) ) {
					// Récupère un éventuel P2P lié
					$gateway = $roi_item->gateway;
					$mean_payment = 'wallet';
					if ( $gateway != 'lemonway' ) {
						$mean_payment = $gateway;
					}

					$linked_p2p = '';
					if ( !empty( $roi_item->id_transfer ) ) {
						$linked_p2p = $roi_item->id_transfer;
					}

					// Récupère la date du roi
					$datetime = new DateTime( $roi_item->date_transfer );

					if ( !empty( $linked_p2p ) ) {
						// Si possible, Prend la date du P2P qui est plus précise
						$datetime = DateTime::createFromFormat( 'd/m/Y H:i:s', $lw_items_by_gateway_id[ '2::' .$linked_p2p ]->DATE );
						
						// Supprime dans la liste des items LW
						unset( $lw_items_by_gateway_id[ '2::' .$linked_p2p ] );
					}

					$transaction_datetime = $datetime->format( 'Y-m-d H:i:s' );

					// Ajoute l'élément
					self::insert_item(
						$transaction_datetime, $roi_item->amount * 100,
						$roi_item->id_orga, true, 'royalties',
						$item_id, $is_legal_entity, '',
						'roi', 'success',
						$gateway, $mean_payment, $linked_p2p,
						'roi', $roi_item->id,
						$roi_item->id_project
					);

				} else {
					// Supprimer tout de même le P2P lié à la transaction existant pour ne pas l'ajouter en plus
					$previous_transaction_item = $previous_items_by_wedogood_entity_id[ 'roi::' .$roi_item->id ];
					unset( $lw_items_by_gateway_id[ '2::' .$previous_transaction_item->gateway_transaction_id ] );
				}
			}
		}
		
		//***********************
		// Parcours des données de LW restants pour les insérer si ce n'est pas un doublon
		foreach ( $lw_items_by_gateway_id as $transaction_item ) {
			if ( isset( $previous_items_by_gateway_id[ 'lemonway::' .$transaction_item->TYPE. '::' .$transaction_item->ID ] ) ) {
				continue;
			}

			$status = '';
			switch ( $transaction_item->STATUS ) {
				case '3': // transaction effectuée avec succès
					$status = 'success';
					break;
				case '4': // erreur
					$status = 'error';
					break;
				case '0': // en attente de finalisation
					$status = 'pending';
					break;
				case '16': // en attente de validation (carte avec paiement différé)
					$status = 'pending-validation';
					break;
			}

			// On n'ajoute que les transactions réalisées avec succès
			if ( $status != 'success' ) {
				continue;
			}

			$datetime = DateTime::createFromFormat( 'd/m/Y H:i:s', $transaction_item->DATE );
			$transaction_datetime = $datetime->format( 'Y-m-d H:i:s' );
			
			$mean_payment = '';
			$type = '';
			$amount_in_cents = 0;
			$sender_wallet_id = 0;
			$recipient_wallet_id = 0;
			switch ( $transaction_item->TYPE ) {
				case '0': // money in
					$type = 'moneyin';
					if ( isset( $result_item->EXTRA ) && !empty( $result_item->EXTRA->NUM ) ) {
						$mean_payment = 'card';
					} else {
						if ( !empty( $result_item->MLABEL ) ) {
							$mean_payment = 'mandate';
						} else {
							$mean_payment = 'wire';
						}
					}
					$amount_in_cents = $transaction_item->CRED * 100;
					$recipient_wallet_id = $transaction_item->REC;
					break;
				case '1': // money out
					$type = 'moneyout';
					$mean_payment = 'wire';
					$amount_in_cents = $transaction_item->DEB * 100;
					$sender_wallet_id = $transaction_item->SEN;
					break;
				case '2': // p2p
					$type = 'p2p';
					$mean_payment = 'wallet';
					$amount_in_cents = $transaction_item->DEB * 100;
					$sender_wallet_id = $transaction_item->SEN;
					$recipient_wallet_id = $transaction_item->REC;
					break;
			}

			// Découpe du nom de wallet de départ
			$sender_id = 0;
			$sender_wallet_type = '';
			$sender_is_legal_entity = ( strpos( $sender_wallet_id, 'ORGA' ) !== FALSE ) ? 1 : 0;
			if ( $sender_wallet_id == 'SC' ) {
				$sender_wallet_id = 'society';

			} else {
				$wp_user_id_start = strpos( $sender_wallet_id, 'W' );

				if ( $wp_user_id_start !== FALSE ) {
					$wp_user_id_start++;
					$sender_wpref = substr( $sender_wallet_id, $wp_user_id_start );
					if ( empty( $sender_is_legal_entity ) ) {
						$entity = WDGRESTAPI_Entity_User::get_by_wpref( $sender_wpref );
	
					} else {
						if ( strpos( $sender_wallet_id, 'CAMPAIGN' ) !== FALSE ) {
							$sender_wallet_type = 'campaign';
						}
						if ( strpos( $sender_wallet_id, 'ROYALTIES' ) !== FALSE ) {
							$sender_wallet_type = 'royalties';
						}
						if ( strpos( $sender_wallet_id, 'TAX' ) !== FALSE ) {
							$sender_wallet_type = 'tax';
						}
	
						$sender_wpref = preg_replace( '/[^0-9]/', '', $sender_wpref );
						$entity = WDGRESTAPI_Entity_Organization::get_by_wpref( $sender_wpref );
					}
					$sender_id = $entity->get_loaded_data()->id;
				}
			}

			// Découpe du nom de wallet d'arrivée
			$recipient_id = 0;
			$recipient_wallet_type = '';
			$recipient_is_legal_entity = ( strpos( $recipient_wallet_id, 'ORGA' ) !== FALSE ) ? 1 : 0;
			if ( $recipient_wallet_id == 'SC' ) {
				$recipient_wallet_type = 'society';

			} else {
				$wp_user_id_start = strpos( $recipient_wallet_id, 'W' );
				
				if ( $wp_user_id_start !== FALSE ) {
					$wp_user_id_start++;
					$recipient_wpref = substr( $recipient_wallet_id, $wp_user_id_start );
					if ( empty( $recipient_is_legal_entity ) ) {
						$entity = WDGRESTAPI_Entity_User::get_by_wpref( $recipient_wpref );
	
					} else {
						if ( strpos( $recipient_wallet_id, 'CAMPAIGN' ) !== FALSE ) {
							$recipient_wallet_type = 'campaign';
						}
						if ( strpos( $recipient_wallet_id, 'ROYALTIES' ) !== FALSE ) {
							$recipient_wallet_type = 'royalties';
						}
						if ( strpos( $recipient_wallet_id, 'TAX' ) !== FALSE ) {
							$recipient_wallet_type = 'tax';
						}
	
						$recipient_wpref = preg_replace( '/[^0-9]/', '', $recipient_wpref );
						$entity = WDGRESTAPI_Entity_Organization::get_by_wpref( $recipient_wpref );
					}
					$recipient_id = $entity->get_loaded_data()->id;
				}
			}

			self::insert_item(
				$transaction_datetime, $amount_in_cents,
				$sender_id, $sender_is_legal_entity, $sender_wallet_type,
				$recipient_id, $recipient_is_legal_entity, $recipient_wallet_type,
				$type, $status,
				'lemonway', $mean_payment, $transaction_item->ID,
				'', 0,
				0
			);
		}
	}

	
	private static function insert_item(
				$datetime, $amount_in_cents,
				$sender_id, $is_sender_legal_entity, $sender_wallet_type,
				$recipient_id, $is_recipient_legal_entity, $recipient_wallet_type,
				$type, $status,
				$gateway_name, $gateway_mean_payment, $gateway_transaction_id,
				$wedogood_entity, $wedogood_entity_id,
				$project_id
			) {
		$transaction_new = new WDGRESTAPI_Entity_Transaction();
		$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
		$transaction_new->set_property( 'client_user_id', $current_client->ID );
		$transaction_new->set_property( 'datetime', $datetime );
		$transaction_new->set_property( 'amount_in_cents', $amount_in_cents );
		$transaction_new->set_property( 'sender_id', $sender_id );
		$transaction_new->set_property( 'sender_is_legal_entity', $is_sender_legal_entity ? '1' : '0' );
		$transaction_new->set_property( 'sender_wallet_type', $sender_wallet_type );
		$transaction_new->set_property( 'recipient_id', $recipient_id );
		$transaction_new->set_property( 'recipient_is_legal_entity', $is_recipient_legal_entity ? '1' : '0' );
		$transaction_new->set_property( 'recipient_wallet_type', $recipient_wallet_type );
		$transaction_new->set_property( 'type', $type );
		$transaction_new->set_property( 'status', $status );
		$transaction_new->set_property( 'gateway_name', $gateway_name );
		$transaction_new->set_property( 'gateway_mean_payment', $gateway_mean_payment );
		$transaction_new->set_property( 'gateway_transaction_id', $gateway_transaction_id );
		$transaction_new->set_property( 'wedogood_entity', $wedogood_entity );
		$transaction_new->set_property( 'wedogood_entity_id', $wedogood_entity_id );
		$transaction_new->set_property( 'project_id', $project_id );
		$transaction_new->save();
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'				=> 'id',
		'id'						=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'			=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'datetime'					=> array( 'type' => 'datetime' ),
		'amount_in_cents'			=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'sender_id'					=> array( 'type' => 'id' ),
		'sender_is_legal_entity'	=> array( 'type' => 'bool', 'other' => 'NOT NULL' ),
		'sender_wallet_type'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'recipient_id'				=> array( 'type' => 'id' ),
		'recipient_is_legal_entity'	=> array( 'type' => 'bool', 'other' => 'NOT NULL' ),
		'recipient_wallet_type'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'type'						=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'status'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'gateway_name'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'gateway_mean_payment'		=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'gateway_transaction_id'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'wedogood_entity'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'wedogood_entity_id'		=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'project_id'				=> array( 'type' => 'id', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}