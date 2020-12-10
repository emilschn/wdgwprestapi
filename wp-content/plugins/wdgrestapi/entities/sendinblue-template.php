<?php
class WDGRESTAPI_Entity_SendinblueTemplate extends WDGRESTAPI_Entity {
	public static $entity_type = 'sendinblue_template';

	public function __construct( $id = FALSE ) {
		parent::__construct( $id, self::$entity_type, self::$db_properties );
	}



/*******************************************************************************
 * GESTION BDD
 ******************************************************************************/
	public static $db_properties = array(
		'unique_key'			=> 'id',
		'id'					=> array( 'type' => 'id', 'other' => 'NOT NULL AUTO_INCREMENT' ),
		'slug'					=> array( 'type' => 'varchar', 'other' => '' ),
		'description'			=> array( 'type' => 'varchar', 'other' => '' ),
		'id_sib_fr'				=> array( 'type' => 'id', 'other' => '' ),
		'id_sib_en'				=> array( 'type' => 'id', 'other' => 'DEFAULT 0' ),
		'variables_names'		=> array( 'type' => 'longtext', 'other' => '' ),
		'wdg_email_cc'			=> array( 'type' => 'longtext', 'other' => '' )
	);
	
	// Mise à jour de la bdd
	public static function upgrade_db() {
		return WDGRESTAPI_Entity::upgrade_entity_db( self::$entity_type, self::$db_properties );
	}
	
}