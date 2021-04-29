<?php
class WDGRESTAPI_Entity_PollAnswer extends WDGRESTAPI_Entity {
	
	public static $entity_type = 'poll_answer';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_PollAnswer::$entity_type, WDGRESTAPI_Entity_PollAnswer::$db_properties );
	}
	
	public function save() {
		$current_datetime = new DateTime();
		$this->loaded_data->date = $current_datetime->format( 'Y-m-d H:i:s' );
		parent::save();
	}
	
	/**
	 * Retourne la liste de toutes les réponses aux questions
	 * @return array
	 */
	public static function list_get( $authorized_client_id_string, $user_id, $project_id, $poll_slug, $offset = 0, $limit = FALSE, $apply_in_google = FALSE ) {
		global $wpdb;
		$table_name = WDGRESTAPI_Entity::get_table_name( WDGRESTAPI_Entity_PollAnswer::$entity_type );
		
		$query = "SELECT * FROM " .$table_name. " WHERE client_user_id IN " .$authorized_client_id_string;
		if ( !empty( $user_id ) ) {
			$query .= " AND user_id=" .$user_id;
		}
		if ( !empty( $project_id ) ) {
			$query .= " AND project_id=" .$project_id;
		}
		if ( !empty( $poll_slug ) ) {
			$query .= " AND poll_slug='" .$poll_slug. "'";
		}
		
		if ( $offset > 0 || !empty( $limit ) ) {
			$query .= " LIMIT ";
			
			if ( $offset > 0 ) {
				$query .= $offset . ", ";
				if ( empty( $limit ) ) {
					$query .= "0";
				}
			}
			if ( !empty( $limit ) ) {
				$query .= $limit;
			}
		}
		
		$buffer = $wpdb->get_results( $query );
		
		return $buffer;
	}


/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT', 'gs_col_index' => 1 ),
		'client_user_id'		=> array( 'type' => 'id', 'other' => '' ),
		'date'					=> array( 'type' => 'datetime', 'other' => '', 'gs_col_index' => 2 ),
		'poll_slug'				=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 3 ),
		'poll_version'			=> array( 'type' => 'id', 'other' => '', 'gs_col_index' => 4 ),
		'answers'				=> array( 'type' => 'longtext', 'other' => '', 'gs_col_index' => 5 ),
		'context'				=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 6 ),
		'context_amount'		=> array( 'type' => 'int', 'other' => '', 'gs_col_index' => 7 ),
		'project_id'			=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 8 ),
		'user_id'				=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 9 ),
		'user_age'				=> array( 'type' => 'int', 'other' => '', 'gs_col_index' => 10 ),
		'user_postal_code'		=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 11 ),
		'user_gender'			=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 12 ),
		'user_email'			=> array( 'type' => 'varchar', 'other' => '', 'gs_col_index' => 13 )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_PollAnswer::$entity_type, WDGRESTAPI_Entity_PollAnswer::$db_properties );
	}
	
}