<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Send_SMS_Twilio
 * @since 1.1.9
 */
class Action_Send_SMS_Twilio extends Action {


	function init() {
		$this->title = __( 'Send SMS (Twilio)', 'automatewoo' );
		$this->group = __( 'SMS', 'automatewoo' );
	}


	function load_fields() {
		$sms_recipient = ( new Fields\Text() )
			->set_name( 'sms_recipient' )
			->set_title( __( 'SMS recipient', 'automatewoo'  ) )
			->set_description( __( 'When using the {{ order.billing_phone }} variable the country code will be added automatically, if not already entered by the customer, by referencing the billing country.', 'automatewoo' ) )
			->set_variable_validation()
			->set_required();

		$sms_body = ( new Fields\Text_Area() )
			->set_name( 'sms_body' )
			->set_title( __( 'SMS body', 'automatewoo'  ) )
			->set_rows(2)
			->set_variable_validation()
			->set_required();

		$this->add_field( $sms_recipient );
		$this->add_field( $sms_body );
	}


	/**
	 * @return mixed|void
	 */
	function run() {

		$sms_recipient = Clean::string( $this->get_option( 'sms_recipient', true ) );
		$sms_body = Clean::textarea( $this->get_option( 'sms_body', true ) );

		if ( empty( $sms_recipient ) ) {
			$this->workflow->add_action_log_note( $this, __( 'Invalid phone number', 'automatewoo') );
			return;
		}

		if ( empty( $sms_body ) ) {
			$this->workflow->add_action_log_note( $this, __( 'Empty message body', 'automatewoo') );
			return;
		}

		if ( $this->workflow->is_ga_tracking_enabled() ) {
			$replacer = new Replace_Helper( $sms_body, [ $this->workflow, 'append_ga_tracking_to_url' ], 'text_urls' );
			$sms_body = $replacer->process();
		}

		$sms_recipient = Phone_Numbers::parse( $sms_recipient, $this->workflow->get_location()->get_country() );

		Integrations::load_twilio();

		$from = AW()->options()->twilio_from;
		$sid = AW()->options()->twilio_auth_id;
		$token = AW()->options()->twilio_auth_token;

		$client = new \Services_Twilio($sid, $token);

		try {
			$message = $client->account->messages->sendMessage( $from, $sms_recipient, $sms_body );
			$this->workflow->add_action_log_note( $this, sprintf( __( 'Successfully sent to %s', 'automatewoo' ), $sms_recipient ) );
		}
		catch( \Exception $e ) {
			$this->workflow->add_action_log_note( $this, $e->getMessage() );
		}
	}

}
