<?php

namespace AutomateWoo;

/**
 * Functions for email click tracking and unsubscribes
 *
 * @class Emails
 */
class Emails {

	/**
	 * Support for custom from name and from email per template by using an array
	 *
	 * custom_template => [
	 * 	template_name
	 * 	from_name
	 * 	from_email
	 * ]
	 *
	 * @var array
	 */
	static $templates = [
		'default' => 'WooCommerce Default',
		'plain' => 'Plain Text',
	];


	/**
	 * Get the from name for outgoing emails.
	 *
	 * @param string|bool $template_id
	 * @return string
	 */
	static function get_from_name( $template_id = false ) {

		$from_name = false;

		if ( $template_id ) {
			// check if template has a custom name
			$template = self::get_template( $template_id );

			if ( is_array( $template ) && isset( $template['from_name'] ) ) {
				$from_name = $template['from_name'];
			}
		}

		if ( ! $from_name ) {
			$from_name = get_option( 'woocommerce_email_from_name' );
		}

		$from_name = apply_filters( 'automatewoo/mailer/from_name', $from_name, $template_id );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}


	/**
	 * Get the from address for outgoing emails.
	 * @param string|bool $template_id
	 * @return string
	 */
	static function get_from_address( $template_id = false ) {

		$from_email = false;

		if ( $template_id ) {
			// check if template has a custom from email
			$template = self::get_template( $template_id );

			if ( is_array( $template ) && isset( $template['from_email'] ) ) {
				$from_email = $template['from_email'];
			}
		}

		if ( ! $from_email ) {
			$from_email = get_option( 'woocommerce_email_from_address' );
		}

		$from_address = apply_filters( 'automatewoo/mailer/from_address', $from_email, $template_id );
		return sanitize_email( $from_address );
	}


	/**
	 * @param $template_id
	 * @return bool|string|array
	 */
	static function get_template( $template_id ) {

		if ( ! $template_id )
			return false;

		$templates = self::get_email_templates( false );
		return isset( $templates[ $template_id ] ) ? $templates[ $template_id ] : false;
	}


	/**
	 * @param bool $names_only : whether to include extra template data or just id => name
	 * @return array
	 */
	static function get_email_templates( $names_only = true ) {

		$templates = apply_filters( 'automatewoo_email_templates', self::$templates );

		if ( ! $names_only )
			return $templates;

		$flat_templates = [];

		foreach ( $templates as $template_id => $template_data ) {
			$flat_templates[$template_id] = is_array( $template_data ) ? $template_data['template_name'] : $template_data;
		}

		return $flat_templates;
	}


	/**
	 * Display and process form submission for unsubscribes
	 */
	static function catch_unsubscribe_url() {

		ob_start();

		$workflow_id = absint( aw_request( 'workflow' ) );
		$email = Clean::email( aw_request( 'user' ) );

		if ( ! $workflow_id || ! $email ) {
			return;
		}

		if ( aw_request( 'confirmed' ) ) {

			$success = false;
			$workflow = AW()->get_workflow( $workflow_id );

			if ( $workflow ) {

				if ( $workflow->is_unsubscribed( $email ) ) {
					$success = true; // already unsubscribed
				}
				else {
					if ( $workflow->unsubscribe_email( $email ) ) {
						$success = true;
					}
				}

			}

			if ( $success ) {
				$notice_type = 'success';
				aw_get_template( 'unsubscribe-success.php' );
			}
			else {
				$notice_type = 'error';
				aw_get_template( 'unsubscribe-error.php' );
			}

		}
		else {
			// Show unsubscribe form
			$notice_type = 'notice';

			aw_get_template( 'unsubscribe-form.php', [
				'unsubscribe_confirm_url' => self::generate_unsubscribe_url( $workflow_id, $email, true )
			]);
		}

		$message = ob_get_clean();

		// Ensure notice is not added twice e.g. if redirected to ssl
		if ( ! wc_has_notice( $message, $notice_type ) ) {
			wc_add_notice( $message, $notice_type );
		}
	}


	/**
	 *
	 */
	static function catch_click_track_url() {
		$redirect = esc_url_raw( aw_request( 'redirect' ) );
		$log_id = aw_request( 'log' );

		if ( ! $redirect || ! $log_id )
			return;

		if ( $log = AW()->get_log( absint( $log_id ) ) ) {
			$log->record_click( $redirect );
		}

		wp_redirect( $redirect );
		exit;
	}


	/**
	 * @param Workflow $workflow
	 * @param $redirect
	 * @return string
	 */
	static function generate_click_track_url( $workflow, $redirect ) {

		if ( ! $workflow->log ) {
			return $redirect;
		}

		$url = add_query_arg([
			'aw-action' => 'click',
			'log' => $workflow->log->get_id(),
			'redirect' => urlencode( $redirect )
		], home_url() );

		return apply_filters( 'automatewoo_click_track_url', $url );
	}


	/**
	 * @param Workflow $workflow
	 * @return string
	 */
	static function generate_open_track_url( $workflow ) {

		if ( ! $workflow->log ) {
			return false;
		}

		$url = add_query_arg([
			'aw-action' => 'open',
			'log' => $workflow->log->get_id()
		], home_url() );

		return apply_filters( 'automatewoo_open_track_url', $url, $workflow );
	}


	/**
	 *
	 */
	static function catch_open_track_url() {

		$log_id = absint( aw_request( 'log' ) );

		if ( $log = AW()->get_log( $log_id ) ) {
			$log->record_open();
		}

		$image_path = AW()->admin_path( '/assets/img/blank.gif' );

		// render image
		header( 'Content-Type: image/gif' );
		header( 'Pragma: public' ); // required
		header( 'Expires: 0' ); // no cache
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: private', false );
		header( 'Content-Disposition: attachment; filename="blank.gif"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . filesize( $image_path ) ); // provide file size
		readfile( $image_path );
		exit;
	}


	/**
	 * Use an email here rather than an id for security
	 *
	 * @param $workflow_id
	 * @param $user_email
	 * @param bool $confirmed
	 *
	 * @return bool|string
	 */
	static function generate_unsubscribe_url( $workflow_id, $user_email, $confirmed = false ) {

		if ( ! $workflow_id || ! $user_email ) {
			return false;
		}

		$url = add_query_arg([
			'aw-action' => 'unsubscribe',
			'workflow' => absint( $workflow_id ),
			'user' => urlencode( $user_email ),
			'confirmed' => $confirmed
		], wc_get_page_permalink('myaccount') );

		return apply_filters( 'automatewoo_unsubscribe_url', $url, $workflow_id );
	}


	/**
	 * @param $input
	 * @param bool $remove_invalid
	 * @return array
	 */
	static function parse_multi_email_field( $input, $remove_invalid = true ) {

		$emails = [];

		$input = preg_replace( '/\s/u', '', $input ); // remove whitespace
		$input = explode(',', $input );

		foreach ( $input as $email ) {
			if ( ! $remove_invalid || is_email( $email ) ) {
				$emails[] = Clean::email( $email );
			}
		}

		return $emails;
	}

}
