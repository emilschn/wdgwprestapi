<?php
class WDGRESTAPI_Route_PollAnswer extends WDGRESTAPI_Route {
	
	public function __construct() {
		WDGRESTAPI_Route::register_wdg(
			'/poll-answers',
			WP_REST_Server::READABLE,
			array( $this, 'list_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/poll-answer/(?P<id>\d+)',
			WP_REST_Server::READABLE,
			array( $this, 'single_get')
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/poll-answer',
			WP_REST_Server::CREATABLE,
			array( $this, 'single_create'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )
		);
		
		WDGRESTAPI_Route::register_wdg(
			'/poll-answer/(?P<id>\d+)',
			WP_REST_Server::EDITABLE,
			array( $this, 'single_edit'),
			$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
		);
	}
	
	public static function register() {
		$route_poll_answer = new WDGRESTAPI_Route_PollAnswer();
		return $route_poll_answer;
	}
	
	/**
	 * Retourne la liste des réponses aux sondages
	 * @return array
	 */
	public function list_get() {
		try {
			$input_user = filter_input( INPUT_GET, 'user_id' );
			$input_project = filter_input( INPUT_GET, 'project_id' );
			$input_poll_slug = filter_input( INPUT_GET, 'poll_slug' );
			$limit = filter_input( INPUT_GET, 'limit' );
			$offset = filter_input( INPUT_GET, 'offset' );
			$apply_in_google = filter_input( INPUT_GET, 'apply_in_google' );
			
			WDGRESTAPI_Lib_Logs::log('WDGRESTAPI_Route_PollAnswer::list_get');
			$buffer = WDGRESTAPI_Entity_PollAnswer::list_get( $this->get_current_client_autorized_ids_string(), $input_user, $input_project, $input_poll_slug, $limit, $offset, $apply_in_google );
			
			return $buffer;
			
		} catch ( Exception $e ) {
			$this->log( "WDGRESTAPI_Route_PollAnswer::list_get", $e->getMessage() );
			return new WP_Error( 'cant-get', $e->getMessage() );
		}
	}
	
	/**
	 * Retourne un groupe de réponses à un sondage grâce à son id
	 * @param WP_REST_Request $request
	 * @return \WDGRESTAPI_Entity_PollAnswer
	 */
	public function single_get( WP_REST_Request $request ) {
		$poll_answer_id = $request->get_param( 'id' );
		if ( !empty( $poll_answer_id ) ) {
			try {
				$poll_answer_item = new WDGRESTAPI_Entity_PollAnswer( $poll_answer_id );
				$loaded_data_temp = $poll_answer_item->get_loaded_data();
				
				if ( !empty( $loaded_data_temp ) && $this->is_data_for_current_client( $loaded_data_temp ) ) {
					return $loaded_data_temp;
					
				} else {
					$this->log( "WDGRESTAPI_Route_PollAnswer::single_get::" . $poll_answer_id, "404 : Invalid poll answer id" );
					return new WP_Error( '404', "Invalid poll answer id" );
					
				}
				
			} catch ( Exception $e ) {
				$this->log( "WDGRESTAPI_Route_PollAnswer::single_get::" . $poll_answer_id, $e->getMessage() );
				return new WP_Error( 'cant-get', $e->getMessage() );
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_PollAnswer::single_get", "404 : Invalid poll answer id (empty)" );
			return new WP_Error( '404', "Invalid poll answer id (empty)" );
		}
	}
	
	/**
	 * Crée un groupe de réponses à un sondage
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_create( WP_REST_Request $request ) {
		$poll_answer_item = new WDGRESTAPI_Entity_PollAnswer();
		$this->set_posted_properties( $poll_answer_item, WDGRESTAPI_Entity_PollAnswer::$db_properties );
		if ( $poll_answer_item->has_checked_properties() ) {
			$current_client = WDG_RESTAPIUserBasicAccess_Class_Authentication::$current_client;
			$poll_answer_item->set_property( 'client_user_id', $current_client->ID );
			$save_result = $poll_answer_item->save();
			$reloaded_data = $poll_answer_item->get_loaded_data();
			$this->log( "WDGRESTAPI_Route_PollAnswer::single_create", json_encode( $reloaded_data ) );
			if ( $save_result === false ) {
				global $wpdb;
				$this->log( "WDGRESTAPI_Route_PollAnswer::single_create", print_r( $wpdb, true ) );
				return new WP_Error( 'cant-create', 'db-insert-error' );
			} else {
				$this->log( "WDGRESTAPI_Route_PollAnswer::single_create", "success" );
				return $reloaded_data;
			}
			
		} else {
			$error_list = $poll_answer_item->get_properties_errors();
			$error_buffer = '';
			foreach ( $error_list as $error ) {
				$error_buffer .= $error . " ";
			}
			$this->log( "WDGRESTAPI_Route_PollAnswer::single_create", "failed" );
			$this->log( "WDGRESTAPI_Route_PollAnswer::single_create", $error_buffer );
			return new WP_Error( 'cant-create', $error_buffer );
		}
	}
	
	/**
	 * Edite un groupe de réponses à un sondage
	 * @param WP_REST_Request $request
	 * @return \WP_Error
	 */
	public function single_edit( WP_REST_Request $request ) {
		$poll_answer_id = $request->get_param( 'id' );
		if ( !empty( $poll_answer_id ) ) {
			$poll_answer_item = new WDGRESTAPI_Entity_PollAnswer( $poll_answer_id );
			$loaded_data = $poll_answer_item->get_loaded_data();
			
			if ( !empty( $loaded_data ) && $this->is_data_for_current_client( $loaded_data ) ) {
				$this->set_posted_properties( $poll_answer_item, WDGRESTAPI_Entity_PollAnswer::$db_properties );
				$result = $poll_answer_item->save();
				if ( $result ) {
					$reloaded_data = $poll_answer_item->get_loaded_data();
					$this->log( "WDGRESTAPI_Route_PollAnswer::single_edit::" . $poll_answer_id, json_encode( $reloaded_data ) );
					return $reloaded_data;
					
				} else {
					$this->log( "WDGRESTAPI_Route_PollAnswer::single_edit::" . $poll_answer_id, 'Error invalid data' );
					return new WP_Error( 'cant-edit', "Invalid data" );
				}
				
			} else {
				$this->log( "WDGRESTAPI_Route_PollAnswer::single_edit::" . $poll_answer_id, "404 : Invalid poll answer id" );
				return new WP_Error( '404', "Invalid poll answer id" );
				
			}
			
		} else {
			$this->log( "WDGRESTAPI_Route_PollAnswer::single_edit", "404 : Invalid poll answer id (empty)" );
			return new WP_Error( '404', "Invalid poll answer id (empty)" );
		}
	}
	
}