<?php
class WDGRESTAPI_Entity_Project_Draft extends WDGRESTAPI_Entity {
	public static $entity_type = 'project_draft';
	
	public static $status_preparing = 'preparing';
	public static $status_validated = 'validated';
	public static $status_preview = 'preview';
	public static $status_vote = 'vote';
	public static $status_collecte = 'collecte';
	public static $status_funded = 'funded';
	public static $status_closed = 'closed';
	public static $status_archive = 'archive';
	
	public function __construct( $id = FALSE ) {
		parent::__construct( $id, WDGRESTAPI_Entity_Project_Draft::$entity_type, WDGRESTAPI_Entity_Project_Draft::$db_properties );
	}
		


/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	
	// Pour les types, voir WDGRESTAPI_Entity::get_mysqltype_from_wdgtype
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT', 'gs_col_index' => 1 ),
		'guid'					=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 2 ),
		'id_user'				=> array( 'type' => 'id', 'other' => 'NOT NULL', 'gs_col_index' => 3 ),
		'email'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 4 ),
		'status'				=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 5 ),
		'step'					=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 6 ),
		'authorization'			=> array( 'type' => 'varchar', 'other' => 'NOT NULL', 'gs_col_index' => 7 ),
		'metadata'				=> array( 'type' => 'longtext', 'other' => 'NOT NULL', 'gs_col_index' => 8 )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( WDGRESTAPI_Entity_Project_Draft::$entity_type, WDGRESTAPI_Entity_Project_Draft::$db_properties );
	}
	
}