<?php

namespace AutomateWoo\Admin\Controllers;

use AutomateWoo\Admin;
use AutomateWoo\Clean;

/**
 * Base admin controller class
 * @since 3.2.4
 */
abstract class Base {

	/** @var string */
	public $name;

	/** @var array */
	private $messages = [];

	/** @var array  */
	private $errors = [];

	/** @var string */
	protected $default_route = 'list';

	/** @var string */
	protected $heading;


	/**
	 * Handle controller requests
	 * @return void
	 */
	abstract function handle();


	/**
	 * @return string
	 */
	function get_heading() {
		if ( isset( $this->heading ) ) {
			return $this->heading;
		}
		return get_admin_page_title();
	}


	function output_messages() {

		if ( sizeof( $this->errors ) > 0 ) {
			foreach ( $this->errors as $error ) {
				echo $this->format_notice( $error, 'error' );
			}
		}
		elseif ( sizeof( $this->messages ) > 0 ) {
			foreach ( $this->messages as $message ) {
				echo $this->format_notice( $message, 'success' );
			}
		}
	}


	/**
	 * @param $notice_data
	 * @param $type
	 * @return string
	 */
	function format_notice( $notice_data, $type ) {

		$class = "notice notice-$type automatewoo-notice";

		if ( is_array( $notice_data ) ) {
			$main_text = $notice_data['main'];
			$extra_text = isset($notice_data['extra']) ? $notice_data['extra'] : '';
			$class .= ' ' . $notice_data['class'];
		}
		else {
			$main_text = $notice_data;
			$extra_text = '';
		}

		return '<div class="' . $class .'"><p><strong>'.esc_html($main_text).'</strong> '.$extra_text.'</p></div>';
	}


	/**
	 * @return string
	 */
	function get_messages() {
		ob_start();
		$this->output_messages();
		return ob_get_clean();
	}


	/**
	 * @return string
	 */
	function get_current_action() {

		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return Clean::string( $_REQUEST['action'] );

		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return Clean::string( $_REQUEST['action2'] );

		return $this->default_route;
	}


	/**
	 * @return string
	 */
	function get_nonce_action() {
		return 'automatewoo-' . $this->name;
	}


	/**
	 * Verify nonce
	 */
	function verify_nonce_action() {
		$nonce = Clean::string( aw_request( '_wpnonce' ) );
		if ( ! wp_verify_nonce( $nonce, $this->get_nonce_action() ) ) {
			wp_die( 'Security check failed.' );
		}
	}


	/**
	 * @param string $main_text
	 * @param string $extra_text
	 * @param string $extra_classes
	 */
	function add_message( $main_text, $extra_text = '', $extra_classes = '' ) {
		$this->messages[] = [
			'main' => $main_text,
			'extra' => $extra_text,
			'class' => $extra_classes
		];
	}


	/**
	 * @param string $main_text
	 * @param string $extra_text
	 * @param string $extra_classes
	 */
	function add_error( $main_text, $extra_text = '', $extra_classes = '' ) {
		$this->errors[] = [
			'main' => $main_text,
			'extra' => $extra_text,
			'class' => $extra_classes
		];
	}


	/**
	 * @return string
	 */
	function get_responses_option_name() {
		return '_automatewoo_admin_temp_messages_' . get_current_user_id();
	}


	function store_responses() {
		update_option( $this->get_responses_option_name(), [
			'errors' => $this->errors,
			'messages' => $this->messages
		], false );
	}


	function load_stored_responses() {
		if ( $store = get_option( $this->get_responses_option_name() ) ) {
			$this->messages = $store['messages'];
			$this->errors = $store['errors'];
		}
		$this->clear_stored_responses();
	}


	function clear_stored_responses() {
		delete_option( $this->get_responses_option_name() );
	}


	/**
	 * @param string $action
	 * @param array $query_args
	 */
	function redirect_after_action( $action = '', $query_args = [] ) {

		$this->store_responses();

		$args = [
			'did-action' => $this->get_current_action()
		];

		if ( $action ) {
			$args['action'] = $action;
		}

		$query_args = array_merge( $args, $query_args );

		wp_redirect( add_query_arg( $query_args, Admin::page_url( $this->name ) ), 302 );
		exit;
	}


	/**
	 * @param $view
	 * @param array $actions
	 */
	function output_view( $view, $actions = [] ) {
		$actions['controller'] = $this;
		$actions['page'] = $this->name;
		$actions['heading'] = $this->get_heading();
		$actions['messages'] = $this->get_messages();
		Admin::get_view( $view, $actions );
	}

}
