<?php
class WDGRESTAPI_Entity_SendinblueTemplate extends WDGRESTAPI_Entity {
	public static $entity_type = 'sendinblue_template';

	public function __construct( $slug = FALSE ) {
		parent::__construct( FALSE, self::$entity_type, self::$db_properties );

		if ( !empty( $slug ) ) {
			global $wpdb;
			$table_name = WDGRESTAPI_Entity::get_table_name( self::$entity_type );
			$query = "SELECT * FROM " .$table_name. " WHERE slug = '" .$slug. "'";
			$row_data = $wpdb->get_row( $query );
			if ( !empty( $row_data ) ) {
				$this->loaded_data = $row_data;
			}
		}
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