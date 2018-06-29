<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;

/**
 * @class Invite_Email
 */
class Invite_Email {

	/** @var string */
	public $email;

	/** @var Advocate */
	public $advocate;


	/**
	 * Constructor
	 * @param $email
	 * @param Advocate $advocate
	 */
	function __construct( $email, $advocate ) {
		$this->email = $email;
		$this->advocate = $advocate;
	}


	/**
	 * @return string
	 */
	function get_subject() {
		return $this->replace_variables( AW_Referrals()->options()->share_email_subject );
	}


	/**
	 * @return string
	 */
	function get_heading() {
		return $this->replace_variables( AW_Referrals()->options()->share_email_heading );
	}


	/**
	 * @return string
	 */
	function get_content() {
		$content = $this->replace_variables( AW_Referrals()->options()->share_email_body );

		if ( AW_Referrals()->options()->type === 'link' ) {
			$content = $this->make_trackable_urls( $content );
		}

		return $content;
	}


	/**
	 *
	 */
	function get_template() {
		return AW_Referrals()->options()->share_email_template;
	}


	/**
	 *
	 */
	function get_html() {
		$mailer = $this->get_mailer();
		return $mailer->get_html();
	}


	/**
	 * @return AutomateWoo\Mailer
	 */
	function get_mailer() {

		$mailer = new AutomateWoo\Mailer( $this->get_subject(), $this->email, $this->get_content(), $this->get_template() );
		$mailer->set_heading( $this->get_heading() );

		return apply_filters( 'automatewoo/referrals/invite_email/mailer', $mailer, $this );
	}


	/**
	 * @param $content string
	 * @return string
	 */
	function replace_variables( $content ) {
		return Option_Variables::process( $content, $this->advocate );
	}


	/**
	 * @param $content string
	 * @return string
	 */
	function make_trackable_urls( $content ) {
		$replacer = new AutomateWoo\Replace_Helper( $content, [ $this, '_callback_trackable_urls' ], 'href_urls' );
		return $replacer->process();
	}


	/**
	 * @param $url
	 *
	 * @return string
	 */
	function _callback_trackable_urls( $url ) {

		if ( ! $url )
			return '';

		$url = add_query_arg( [
			AW_Referrals()->options()->share_link_parameter => $this->advocate->get_advocate_key()
		], $url );

		return 'href="' . $url . '"';
	}


	/**
	 * @return \WP_Error|true
	 */
	function send() {

		$mailer = $this->get_mailer();
		$sent = $mailer->send();

		if ( ! is_wp_error( $sent ) ) {
			$this->create_record();
		}

		return $sent;
	}


	/**
	 * Record each email shared
	 */
	function create_record() {
		$record = new Invite();
		$record->set_email( $this->email );
		$record->set_advocate_id( $this->advocate->get_id() );
		$record->set_date( new \DateTime() );
		$record->save();
	}

}
