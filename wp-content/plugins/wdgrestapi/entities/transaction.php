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

		// Réordonne les données précédemment enregistrées pour éviter les doublons
		$previous_items_by_gateway_id = array();
		$previous_items = self::list_get_by_entity_id( $item_id, $is_legal_entity );
		foreach ( $previous_items as $item ) {
			$previous_items_by_gateway_id[ $item->gateway_name. '::' . $item->gateway_transaction_id ] = $item;
		}

		// Récupération lib LW
		$wdgrestapi = WDGRESTAPI::instance();
		$wdgrestapi->add_include_lib( 'gateways/lemonway' );
		$lw = WDGRESTAPI_Lib_Lemonway::instance();

		// Exécute la requête chez LW
		$lw_last_transactions = $lw->get_wallet_transactions( $lemonway_id );
		// Parcours des données de LW pour les insérer si ce n'est pas un doublon
		foreach ( $lw_last_transactions as $transaction_item ) {
			if ( isset( $previous_items_by_gateway_id[ 'lemonway::' . $transaction_item->ID ] ) ) {
				continue;
			}

			$transaction_new = new WDGRESTAPI_Entity_Transaction();
			$current_client_id = 0;
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			if ( !empty( $current_client ) ) {
				$current_client_id = $current_client->ID;
			}
			$transaction_new->set_property( 'client_user_id', $current_client_id );

			$transaction_new->set_property( 'gateway_name', 'lemonway' );
			$transaction_new->set_property( 'gateway_transaction_id', $transaction_item->ID );
			
			$datetime = DateTime::createFromFormat( 'd/m/Y H:i:s', $transaction_item->DATE );
			$transaction_new->set_property( 'datetime', $datetime->format( 'Y-m-d H:i:s' ) );
			
			$type = '';
			$amount_in_cents = 0;
			$sender_wallet_id = 0;
			$recipient_wallet_id = 0;
			switch ( $transaction_item->TYPE ) {
				case '0': // money in
					$type = 'moneyin';
					$amount_in_cents = $transaction_item->CRED * 100;
					$recipient_wallet_id = $transaction_item->REC;
					break;
				case '1': // money out
					$type = 'moneyout';
					$amount_in_cents = $transaction_item->DEB * 100;
					$sender_wallet_id = $transaction_item->SEN;
					break;
				case '2': // p2p
					$type = 'p2p';
					$amount_in_cents = $transaction_item->DEB * 100;
					$sender_wallet_id = $transaction_item->SEN;
					$recipient_wallet_id = $transaction_item->REC;
					break;
			}
			$transaction_new->set_property( 'type', $type );
			$transaction_new->set_property( 'amount_in_cents', $amount_in_cents );

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

			$transaction_new->set_property( 'sender_is_legal_entity', $sender_is_legal_entity );
			$transaction_new->set_property( 'recipient_is_legal_entity', $recipient_is_legal_entity );
			$transaction_new->set_property( 'sender_id', $sender_id );
			$transaction_new->set_property( 'recipient_id', $recipient_id );
			$transaction_new->set_property( 'sender_wallet_type', $sender_wallet_type );
			$transaction_new->set_property( 'recipient_wallet_type', $recipient_wallet_type );

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
			$transaction_new->set_property( 'status', $status );

			$transaction_new->save();
		}
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
		'gateway_transaction_id'	=> array( 'type' => 'varchar', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}