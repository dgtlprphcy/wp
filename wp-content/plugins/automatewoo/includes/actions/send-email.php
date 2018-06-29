<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Send_Email
 */
class Action_Send_Email extends Action {

	public $can_be_previewed = true;


	function init() {
		$this->title = __( 'Send Email', 'automatewoo' );
		$this->group = __( 'Email', 'automatewoo' );
	}


	function load_fields() {

		$to = ( new Fields\Text() )
			->set_name( 'to' )
			->set_title( __( 'To', 'automatewoo' ) )
			->set_description( __( 'Enter emails here or use email variables like {{ customer.email }}. Multiple emails can be used, separated by commas.', 'automatewoo' ) )
			->set_placeholder( __( 'E.g. {{ customer.email }}, email@example.org', 'automatewoo' ) )
			->set_variable_validation()
			->set_required();

		$subject = ( new Fields\Text() )
			->set_name ('subject' )
			->set_title( __( 'Email subject', 'automatewoo' ) )
			->set_variable_validation()
			->set_required();

		$heading = ( new Fields\Text() )
			->set_name( 'email_heading' )
			->set_title( __('Email heading', 'automatewoo' ) )
			->set_variable_validation()
			->set_description( __( 'The appearance will depend on your email template. Not all templates support this field.', 'automatewoo' ) );

		$template = ( new Fields\Select( false ) )
			->set_name('template')
			->set_title( __('Template', 'automatewoo' ) )
			->set_options( Emails::get_email_templates() );

		$email_content = ( new Fields\Email_Content() ); // no easy way to define data attributes

		$this->add_field( $to );
		$this->add_field( $subject );
		$this->add_field( $heading );
		$this->add_field( $template );
		$this->add_field( $email_content );
	}


	/**
	 * @param $content
	 * @return mixed
	 */
	private function sanitize_email_content( $content ) {

		add_filter( 'safe_style_css', [ $this, 'filter_safe_css' ] );

		$allowed_html = wp_kses_allowed_html('post');

		// allow inline styles
		$allowed_html['style'] = [
			'type' => true
		];

		$allowed_html['script'] = [];

		$content = wp_kses( $content, $allowed_html );

		remove_filter( 'safe_style_css', [ $this, 'filter_safe_css' ] );

		return $content;
	}


	/**
	 * @param array $css
	 * @return array
	 */
	function filter_safe_css( $css ) {
		$css[] = '-webkit-border-radius';
		$css[] = '-moz-border-radius';
		$css[] = 'border-radius';
		$css[] = 'display';
		return $css;
	}


	/**
	 * Generates the HTML content for the email
	 * @param array $send_to
	 * @return string|\WP_Error|true
	 */
	function preview( $send_to = [] ) {
		
		$email_heading = Clean::string( $this->get_option('email_heading', true ) );
		$email_content = $this->sanitize_email_content( $this->get_option('email_content', true, true ) );
		$subject = Clean::string( $this->get_option( 'subject', true ) );
		$template = Clean::string( $this->get_option( 'template' ) );

		$current_user = get_user_by( 'id', get_current_user_id() );

		// no user object should be present when sending emails
		wp_set_current_user( 0 );

		if ( ! empty( $send_to ) ) {
			foreach ( $send_to as $recipient ) {

				$email = new Workflow_Email( $this->workflow );
				$email->set_recipient( $recipient );
				$email->set_subject( $subject );
				$email->set_heading( $email_heading );
				$email->set_template( $template );
				$email->set_content( $email_content );

				$sent = $email->send();

				if ( is_wp_error( $sent ) ) {
					return $sent;
				}
			}

			return true;
		}
		else {

			$email = new Workflow_Email( $this->workflow );
			$email->set_recipient( $current_user->get('user_email') );
			$email->set_subject( $subject );
			$email->set_heading( $email_heading );
			$email->set_template( $template );
			$email->set_content( $email_content );

			$mailer = $email->get_mailer();

			\AW_Mailer_API::setup( $mailer, $this->workflow );

			$html = $mailer->get_html();

			\AW_Mailer_API::cleanup();

			return $html;
		}
	}


	/**
	 * @return void
	 */
	function run() {

		$email_heading = Clean::string( $this->get_option('email_heading', true ) );
		$email_content = $this->sanitize_email_content( $this->get_option('email_content', true, true ) );
		$subject = Clean::string( $this->get_option('subject', true ) );
		$template = Clean::string( $this->get_option( 'template' ) );

		$to = Clean::string( $this->get_option( 'to', true ) );
		$to = Emails::parse_multi_email_field( $to, false );

		foreach ( $to as $recipient ) {

			$email = new Workflow_Email( $this->workflow );
			$email->set_recipient( $recipient );
			$email->set_subject( $subject );
			$email->set_heading( $email_heading );
			$email->set_template( $template );
			$email->set_content( $email_content );

			$sent = $email->send();

			if ( is_wp_error( $sent ) ) {
				$this->workflow->add_action_log_note( $this, $sent->get_error_message() );
			}
			else {
				$this->workflow->add_action_log_note( $this, sprintf( __( 'Successfully sent to %s', 'automatewoo'), $recipient ) );
			}
		}
	}

}
