<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Background_Processes;
use AutomateWoo\Clean;
use AutomateWoo\Language;

/**
 * @class Invite_Form_Handler
 * @since 1.2.14
 */
class Invite_Form_Handler {

	/** @var array */
	public $emails = [];

	/** @var array */
	public $errors = [];

	/** @var int */
	public $valid_emails_count = 0;

	/** @var Advocate  */
	public $advocate;


	/**
	 * Handle the email share
	 */
	function handle() {

		$this->emails = $this->get_emails();
		$this->advocate = $this->get_advocate();

		if ( ! $this->advocate ) {
			return;
		}

		if ( empty( $this->emails ) ) {
			$this->errors[] = __( 'Please enter some email addresses.', 'automatewoo-referrals' );
			return;
		}

		$valid_emails = [];

		foreach ( $this->emails as $email ) {

			$sharable = $this->is_email_sharable( $email );

			if ( is_wp_error( $sharable ) ) {
				$this->errors[] = $sharable->get_error_message();
			}
			else {
				$valid_emails[] = $email;
			}
		}

		$this->valid_emails_count = count( $valid_emails );
		$this->dispatch( $valid_emails );
	}


	/**
	 * Dispatch share emails
	 * @param $emails
	 */
	function dispatch( $emails ) {

		if ( $this->valid_emails_count > 4 ) {

			/** @var Background_Process_Invite_Emails $process */
			$process = Background_Processes::get( 'referrals_invite_emails' );

			foreach( $emails as $email ) {

				$process->push_to_queue([
					'email' => $email,
					'advocate' => $this->advocate->get_id(),
					'language' => Language::get_current()
				]);
			}

			$process->save()->dispatch();
		}
		else {
			// send now
			include_once AW_Referrals()->path( '/includes/referral-invite-email.php' );

			foreach( $emails as $email ) {
				$mailer = new Invite_Email( $email, $this->advocate );
				$sent = $mailer->send();

				if ( is_wp_error( $sent ) ) {
					$this->errors[] = $sent->get_error_message();
				}
			}
		}

	}


	/**
	 * Add errors/success notices
	 */
	function set_response_notices() {
		// if no errors and no emails sent
		if ( empty( $this->errors ) && $this->valid_emails_count === 0 ) {
			$this->errors[] = __( 'Sorry, your emails failed to send.', 'automatewoo-referrals' );
		}

		foreach ( $this->errors as $error ) {
			wc_add_notice( $error, 'error' );
		}

		if ( $this->valid_emails_count > 0 ) {
			wc_add_notice( sprintf(
				'<strong>' . __( 'Success! %d referral email%s sent.' . '</strong>', 'automatewoo-referrals' ),
				$this->valid_emails_count, $this->valid_emails_count === 1 ? '' : 's'
			));
		}
	}


	/**
	 * @param $email
	 * @return true|\WP_Error
	 */
	function is_email_sharable( $email ) {

		if ( ! is_email( $email ) ) {
			return new \WP_Error( 1, sprintf( __( '%s is not a valid email address.', 'automatewoo-referrals' ), "<strong>$email</strong>" )  );
		}

		if ( $email == Clean::email( $this->advocate->get_email() ) ) {
			return new \WP_Error( 2, sprintf( __( 'Referring your own email (%s) is not allowed.','automatewoo-referrals'), $email ) );
		}

		if ( $this->is_existing_customer( $email ) ) {

			if ( apply_filters( 'automatewoo/referrals/block_existing_customer_share', ! AW_Referrals()->options()->allow_existing_customer_referrals, $email ) ) {
				return new \WP_Error( 3, sprintf( __( '<strong>%s</strong> is already a customer.', 'automatewoo-referrals'), $email ) );
			}
			else {
				// ensure the email has not already been successfully referred
				if ( count( AW_Referrals()->get_referred_orders_by_customer( $email ) ) !== 0 ) {
					return new \WP_Error( 4, sprintf( __( '<strong>%s</strong> has already been referred.', 'automatewoo-referrals'), $email ) );
				}
			}
		}

		return true;
	}


	/**
	 * Check if the email belongs to an existing user or a guest who has placed an order
	 * @param $email
	 * @return bool
	 */
	function is_existing_customer( $email ) {

		$user = get_user_by( 'email', $email );

		if ( $user )
			return true;

		$orders = wc_get_orders([
			'customer' => $email,
			'limit' => 1,
			'return' => 'ids'
		]);

		return ! empty( $orders );
	}


	/**
	 * @return array
	 */
	function get_emails() {

		$emails = Clean::recursive( aw_request( 'emails' ) );

		if ( empty( $emails ) )
			return [];

		// handle comma separated textarea
		if ( ! is_array( $emails ) ) {
			$emails = explode( ',', $emails );
		}

		$emails = array_map( [ 'AutomateWoo\Clean', 'email' ], $emails );

		return array_filter( $emails );
	}


	/**
	 * advocate is the current user
	 * @return Advocate|false
	 */
	function get_advocate() {
		$advocate = new Advocate( get_current_user_id() );
		if ( $advocate->exists ) return $advocate;
		return false;
	}

}
