<?php

namespace AutomateWoo;

/**
 * @class Remote_Request
 * @since 2.3.1
 */
class Remote_Request {

	/** @var array */
	public $http_success_codes = [ 200, 201, 202, 203, 204 ];

	/**
	 * Response from wp_remote_request()
	 * @var array|\WP_Error
	 */
	public $request;


	/**
	 * Passes to wp_remote_request()
	 *
	 * @param $url
	 * @param $args
	 */
	function __construct( $url, $args ) {
		$this->request = wp_remote_request( $url, $args );
	}


	/**
	 * @return bool
	 */
	function is_failed() {
		return is_wp_error( $this->request );
	}


	/**
	 * @return string|false
	 */
	function get_error_message() {
		if ( $this->is_failed() ) {
			return $this->request->get_error_message();
		}
		return false;
	}


	/**
	 * @return int
	 */
	function get_response_code() {

		if ( $this->is_failed() )
			return 0;

		return $this->request['response']['code'];
	}


	/**
	 * @return string
	 */
	function get_response_message() {

		if ( $this->is_failed() )
			return '';

		return $this->request['response']['message'];
	}


	/**
	 * @return array|false
	 */
	function get_body() {
		if ( $this->is_failed() )
			return false;

		return json_decode( $this->request['body'], true );
	}


	/**
	 * @return string
	 */
	function get_body_raw() {

		if ( $this->is_failed() )
			return '';

		return $this->request['body'];
	}


	/**
	 * @return bool
	 */
	function is_http_success_code() {
		return in_array( $this->get_response_code(), $this->http_success_codes );
	}


	/**
	 * @return bool
	 */
	function is_successful() {
		return ! $this->is_failed() && $this->is_http_success_code();
	}

}
