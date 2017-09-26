<?php
class WDGRESTAPI_Entity_Declaration extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'declaration';
	
	public static $status_declaration = 'declaration';
	public static $status_declaration_late = 'declaration_late';
	public static $status_payment = 'payment';
	public static $status_payment_late = 'payment_late';
	public static $status_waiting_transfer = 'waiting_transfer';
	public static $status_transfer = 'transfer';
	public static $status_finished = 'finished';
	
	public function __construct( $id = FALSE, $payment_token = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Declaration::$entity_type, WDGRESTAPI_Entity_Declaration::$db_properties );
		
		if ( empty( $id ) && !empty( $payment_token ) ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( $this->current_entity_type );
			$query = "SELECT * FROM " .$table_name. " WHERE payment_token='" .$payment_token. "'";
			$this->loaded_data = $wpdb->get_row( $query );
		}
	}
	
	/**
	 * Retourne la liste des ROIs de cette déclaration
	 * @return array
	 */
	public function get_rois() {
		$buffer = WDGRESTAPI_Entity_ROI::list_get_by_declaration_id( $this->loaded_data->id );
		return $buffer;
	}
	
	/**
	 * Retourne la liste de toutes les déclarations
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string, $start_date = FALSE, $end_date = FALSE, $type = FALSE  ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Declaration::$entity_type );
		$query = "SELECT id, id_project, date_due, date_paid, date_transfer, amount, remaining_amount, transfered_previous_remaining_amount, percent_commission, status, mean_payment, file_list, turnover, message, adjustment, employees_number, other_fundings FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		$result_list = $wpdb->get_results( $query );
		
		if ( empty( $start_date ) ) {
			$results = array();
			$start_date->setTime( 0, 0, 1 );
			$end_date->setTime( 23, 59, 59 );
			foreach ( $result_list as $data ) {
				$data_date = FALSE;
				switch ( $type ) {
					case 'due':
						$data_date = $data->date_due;
						break;
				}
				if ( !empty( $data_date ) ) {
					$declaration_date = new DateTime( $data->date_due );
					$declaration_date->setTime( 10, 30, 0 );
					if ( $start_date < $declaration_date && $declaration_date < $end_date ) {
						array_push( $results, $data );
					}
				}
			}
			
		} else {
			$results = $result_list;
		}
		return self::complete_data( $results );
	}
	
	/**
	 * Retourne la liste de toutes les déclarations
	 * @return array
	 */
	public static function list_get_by_project_id( $project_id ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_Declaration::$entity_type );
		$query = "SELECT id, id_project, date_due, date_paid, date_transfer, amount, remaining_amount, transfered_previous_remaining_amount, percent_commission, status, mean_payment, file_list, turnover, message, adjustment, employees_number, other_fundings FROM " .$table_name. " WHERE id_project = " .$project_id;
		$results = $wpdb->get_results( $query );
		return self::complete_data( $results );
	}
	
	/**
	 * Parcourt les données pour ajouter celles qu'il manque
	 * @param array $results
	 * @return array
	 */
	private static function complete_data( $results ) {
		$buffer = array();
		
		foreach ( $results as $result ) {
			$buffer_item = self::complete_single_data( $result );
			array_push( $buffer, $buffer_item );
		}
		
		return $buffer;
	}
	
	/**
	 * Ajoute les données manquantes
	 * @param object $result
	 * @return array
	 */
	public static function complete_single_data( $result ) {
		// Données de base
		$buffer = array(
			'id'						=> $result->id,
			'client_user_id'			=> $result->client_user_id,
			'id_project'				=> $result->id_project,
			'date_due'					=> $result->date_due,
			'date_paid'					=> $result->date_paid,
			'date_transfer'				=> $result->date_transfer,
			'amount'					=> $result->amount,
			'remaining_amount'			=> $result->remaining_amount,
			'transfered_previous_remaining_amount'			=> $result->transfered_previous_remaining_amount,
			'percent_commission'		=> $result->percent_commission,
			'status'					=> $result->status,
			'mean_payment'				=> $result->mean_payment,
			'payment_token'				=> $result->payment_token,
			'file_list'					=> $result->file_list,
			'turnover'					=> $result->turnover,
			'message'					=> $result->message,
			'adjustment'				=> $result->adjustment,
			'employees_number'			=> $result->employees_number,
			'other_fundings'			=> $result->other_fundings
		);
		
		// Données ajoutées
		// Nom du projet
		$project_item = new WDGRESTAPI_Entity_Project( $result->id_project );
		$project_data = $project_item->get_loaded_data();
		$buffer[ 'name_project' ] = $project_data->name;
		// CA total
		$turnover_list = json_decode( $result->turnover );
		if ( empty( $turnover_list ) ) {
			$turnover_list = array();
		}
		$turnover_total = 0;
		foreach ( $turnover_list as $turnover_month ) {
			$turnover_total += $turnover_month;
		}
		$buffer[ 'turnover_total' ] = $turnover_total;
		// Statut d'affichage
		$current_date = new DateTime();
		$due_date = new DateTime( $result->date_due );
		$buffer[ 'status_display' ] = $result->status;
		if ( $buffer[ 'status_display' ] == WDGRESTAPI_Entity_Declaration::$status_declaration && $current_date > $due_date ) {
			$buffer[ 'status_display' ] = WDGRESTAPI_Entity_Declaration::$status_declaration_late;
		}
		if ( $buffer[ 'status_display' ] == WDGRESTAPI_Entity_Declaration::$status_payment && $current_date > $due_date ) {
			$buffer[ 'status_display' ] = WDGRESTAPI_Entity_Declaration::$status_payment_late;
		}
		// Frais investisseurs (TODO : aller chercher la bonne donnée)
		$buffer[ 'cost_to_investors' ] = 0;
		// Nombre de déclarations de CA (TODO : aller chercher la bonne donnée)
		$buffer[ 'turnover_nb' ] = count( $turnover_list );
		// Pourcentage de royalties versé (TODO : aller chercher la bonne donnée)
		$buffer[ 'royalties_percent' ] = 'TODO';
		// Ajustement
		$adjustment = json_decode( $result->adjustment );
		$buffer[ 'adjustment_needed' ] = ( isset( $adjustment->needed ) && $adjustment->needed == 1 ) ? 1 : 0;
		$buffer[ 'adjustment_value' ] = ( isset( $adjustment->value ) ) ? $adjustment->value : 0;
		$buffer[ 'adjustment_turnover_difference' ] = ( isset( $adjustment->turnover_difference ) ) ? $adjustment->turnover_difference : 0;
		$buffer[ 'adjustment_msg_to_author' ] = ( isset( $adjustment->msg_to_author ) ) ? $adjustment->msg_to_author : 0;
		// Contact
		$buffer[ 'organization_email' ] = 'TODO';
		$buffer[ 'organization_phone' ] = 'TODO';
		// Fichiers
		$buffer[ 'certificate' ] = 'TODO';
		$buffer[ 'bill' ] = 'TODO';
		// Infos organisation
		$project_orga_list = WDGRESTAPI_Entity_ProjectOrganization::get_list_by_project_id( $result->id_project );
		$orga_linked_id = 0;
		foreach ( $project_orga_list as $project_orga_link ) {
			if ( $project_orga_link->type == WDGRESTAPI_Entity_ProjectOrganization::$link_type_manager ) {
				$orga_linked_id = $project_orga_link->id_organization;
			}
		}
		$buffer[ 'organization_id' ] = $orga_linked_id;
		$organization_data = FALSE;
		if ( $orga_linked_id > 0 ) {
			$organization_item = new WDGRESTAPI_Entity_Organization( $orga_linked_id );
			$organization_data = $organization_item->get_loaded_data();
		}
		$buffer[ 'organization_email' ] = !empty( $organization_data ) ? $organization_data->email : '';
		return $buffer;
	}
	
	
/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => 'DEFAULT 1 NOT NULL' ),
		'id_project'			=> array( 'type' => 'id', 'other' => 'NOT NULL' ),
		'date_due'				=> array( 'type' => 'date', 'other' => 'DEFAULT \'0000-00-00\'' ),
		'date_paid'				=> array( 'type' => 'date', 'other' => 'DEFAULT \'0000-00-00\'' ),
		'date_transfer'			=> array( 'type' => 'date', 'other' => 'DEFAULT \'0000-00-00\'' ),
		'amount'				=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'remaining_amount'		=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'transfered_previous_remaining_amount'	=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'percent_commission'	=> array( 'type' => 'float', 'other' => 'NOT NULL' ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'mean_payment'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'payment_token'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL' ),
		'file_list'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'turnover'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'message'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'adjustment'			=> array( 'type' => 'longtext', 'other' => 'NOT NULL' ),
		'employees_number'		=> array( 'type' => 'int', 'other' => 'NOT NULL' ),
		'other_fundings'		=> array( 'type' => 'longtext', 'other' => 'NOT NULL' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_db( WDGRESTAPI_Entity_Declaration::$entity_type, WDGRESTAPI_Entity_Declaration::$db_properties );
	}
	
}