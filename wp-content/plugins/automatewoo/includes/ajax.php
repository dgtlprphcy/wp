<?php

namespace AutomateWoo;

/**
 * @class Ajax
 * @since 2.7
 */
class Ajax {

	/**
	 * Init
	 */
	static function init() {
		add_action( 'init', [ __CLASS__, 'define_ajax' ], 0 );
		add_action( 'template_redirect', [ __CLASS__, 'do_ajax' ], 0 );
	}


	/**
	 * @param  string $request Optional
	 * @return string
	 */
	static function get_endpoint( $request = '' ) {
		return esc_url_raw( add_query_arg( 'aw-ajax', $request ) );
	}


	/**
	 * Set WC AJAX constant and headers.
	 */
	static function define_ajax() {

		if ( empty( $_GET['aw-ajax'] ) )
			return;

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		// Turn off display_errors during AJAX events to prevent malformed JSON
		if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
			@ini_set( 'display_errors', 0 );
		}

		$GLOBALS['wpdb']->hide_errors();
	}


	/**
	 * Send headers
	 */
	private static function send_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}


	/**
	 * Check for AW Ajax request and fire action.
	 */
	static function do_ajax() {
		if ( empty( $_GET['aw-ajax'] ) )
			return;

		if ( ! $action = sanitize_text_field( $_GET['aw-ajax'] ) )
			return;

		self::send_headers();
		do_action( 'automatewoo/ajax/' . sanitize_text_field( $action ) );
		die;
	}

}
