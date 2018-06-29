<?php

namespace AutomateWoo;

/**
 * @class Workflow_Email
 * @since 2.8.6
 */
class Workflow_Email {

	/** @var Workflow  */
	public $workflow;

	/** @var string */
	public $recipient;

	/** @var string */
	public $subject;

	/** @var string */
	public $content;

	/** @var string */
	public $heading;

	/** @var string */
	public $template;


	/**
	 * @param Workflow $workflow
	 */
	function __construct( $workflow ) {
		$this->workflow = $workflow;
	}


	/**
	 * @param $recipient
	 */
	function set_recipient( $recipient ) {
		$this->recipient = $recipient;
	}


	/**
	 * @param $subject
	 */
	function set_subject( $subject ) {
		$this->subject = $subject;
	}


	/**
	 * @param $content
	 */
	function set_content( $content ) {
		$this->content = $content;
	}


	/**
	 * @param $heading
	 */
	function set_heading( $heading ) {
		$this->heading = $heading;
	}


	/**
	 * @param $template
	 */
	function set_template( $template ) {
		$this->template = $template;
	}


	/**
	 * @return Mailer
	 */
	function get_mailer() {

		$mailer = new Mailer( $this->subject, $this->recipient, $this->content, $this->template );
		$mailer->set_heading( $this->heading );
		$mailer->extra_footer_text = $this->get_unsubscribe_link();

		if ( $this->workflow->is_tracking_enabled() ) {
			$mailer->tracking_pixel_url = Emails::generate_open_track_url( $this->workflow );
			$mailer->replace_content_urls_callback = [ $this, 'replace_content_urls_callback' ];
		}

		return apply_filters( 'automatewoo/workflow/mailer', $mailer, $this );
	}


	/**
	 * @return bool|string
	 */
	function get_unsubscribe_link() {

		$url = Emails::generate_unsubscribe_url( $this->workflow->get_id(), $this->recipient );

		if ( ! $url ) {
			return false;
		}

		$text = apply_filters( 'automatewoo_email_unsubscribe_text', __( 'Unsubscribe', 'automatewoo' ), $this, $this->workflow );

		return '<a href="' . $url . '" target="_blank">' . $text . '</a>';
	}


	/**
	 * @param $url
	 * @return string
	 */
	function replace_content_urls_callback( $url ) {

		$url = html_entity_decode( $url );
		$url = $this->workflow->append_ga_tracking_to_url( $url );
		$url = Emails::generate_click_track_url( $this->workflow, $url );

		return 'href="' . esc_url( $url ) . '"';
	}


	/**
	 * @return bool|\WP_Error
	 */
	function send() {

		$mailer = $this->get_mailer();

		if ( ! $this->workflow ) {
			return new \WP_Error( 'workflow_blank', __( 'Workflow was not defined for email.', 'automatewoo' ) );
		}

		// validate email before checking if unsubscribed
		$validate_email = $mailer->validate_recipient_email();

		if ( is_wp_error( $validate_email ) ) {
			return $validate_email;
		}

		if ( $this->workflow->is_unsubscribed( $this->recipient ) ) {
			return new \WP_Error( 'email_unsubscribed', sprintf( __( '%s is unsubscribed from this workflow.', 'automatewoo' ), $this->recipient ) );
		}

		\AW_Mailer_API::setup( $mailer, $this->workflow );

		$sent = $mailer->send();

		\AW_Mailer_API::cleanup();

		return $sent;
	}

}
