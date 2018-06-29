<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Integration_Mailchimp
 * @since 2.3
 */
class Integration_Mailchimp extends Integration {

	/** @var string */
	public $integration_id = 'mailchimp';

	/** @var string */
	private $api_key;

	/** @var string  */
	private $api_root = 'https://<dc>.api.mailchimp.com/3.0';


	/**
	 * @param $api_key
	 */
	function __construct( $api_key ) {
		$this->api_key = $api_key;
		list(, $data_center ) = explode( '-', $this->api_key );
		$this->api_root = str_replace( '<dc>', $data_center, $this->api_root );
	}


	/**
	 * @param $method
	 * @param $endpoint
	 * @param array $args
	 * @param bool $log_errors
	 * @return Remote_Request
	 */
	function request( $method, $endpoint, $args = [], $log_errors = true ) {

		$request_args = [
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $this->api_key )
			],
			'timeout' => 15,
			'method' => $method,
			'sslverify' => false
		];

		$url = $this->api_root . $endpoint;

		switch ( $method ) {
			case 'GET':
				$url = add_query_arg( $args, $url );
				break;

			default:
				$request_args['body'] = json_encode( $args );
				break;
		}

		$request = new Remote_Request( $url, $request_args );

		if ( $log_errors ) {
			if ( $request->is_failed() ) {
				$this->log( $request->get_error_message() );
			}
			elseif ( ! $request->is_http_success_code() ) {
				$this->log(
					$request->get_response_code() . ' ' . $request->get_response_message()
					. '. Method: ' . $method
					. '. Endpoint: ' . $endpoint
					. '. Response body: ' . print_r( $request->get_body(), true ) );
			}
		}

		return $request;
	}



	/**
	 * @return array
	 */
	function get_lists() {

		$cache = Cache::get_transient( 'mailchimp_lists' );

		if ( $cache ) {
			return $cache;
		}

		$request = $this->request( 'GET', '/lists', [
			'count' => 100,
		]);

		$clean_lists = [];

		if ( $request->is_successful() ) {
			$body = $request->get_body();

			if ( is_array( $body['lists'] ) ) {
				foreach( $body['lists'] as $list ) {
					$clean_lists[ $list['id'] ] = $list['name'];
				}
			}
		}

		Cache::set_transient( 'mailchimp_lists', $clean_lists, 0.15 );

		return $clean_lists;
	}


	/**
	 * @param $list_id
	 * @return array
	 */
	function get_list_fields( $list_id ) {

		if ( ! $list_id ) {
			return [];
		}

		$cache_key = "mailchimp_list_fields_$list_id";
		$cache = Cache::get_transient( $cache_key );

		if ( $cache ) {
			return $cache;
		}

		$request = $this->request( 'GET', "/lists/$list_id/merge-fields", [
			'count' => 100,
		]);

		if ( ! $request->is_successful() ) {
			return [];
		}

		$body = $request->get_body();
		$fields = isset( $body['merge_fields'] ) ? $body['merge_fields'] : [];

		Cache::set_transient( $cache_key, $fields, 0.15 );

		return $fields;
	}


	/**
	 * @param $email
	 * @param $list_id
	 * @return bool
	 */
	function is_contact( $email, $list_id ) {

		// check memory cache
		if ( Temporary_Data::exists( 'mailchimp_is_contact', $email ) ) {
			return Temporary_Data::get( 'mailchimp_is_contact', $email );
		}

		$subscriber_hash = md5( $email );
		$subscribed = false;

		// will return 404 if subscriber doesn't exists, so don't log errors for this request
		$request = $this->request( 'GET', "/lists/$list_id/members/$subscriber_hash", [], false );

		if ( $request->is_failed() ) {
			return false; // bail and don't cache
		}

		if ( $request->is_http_success_code() ) {
			$subscribed = true;
		}

		Temporary_Data::set( 'mailchimp_is_contact', $email, $subscribed );

		return $subscribed;
	}


	function clear_cache_data() {
		Cache::delete_transient( 'mailchimp_lists' );
	}

}
