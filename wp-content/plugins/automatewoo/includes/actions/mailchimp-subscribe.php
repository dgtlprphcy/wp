<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_MailChimp_Subscribe
 * @since 2.0.3
 */
class Action_MailChimp_Subscribe extends Action_MailChimp_Abstract {

	function init() {
		$this->title = __( 'Add Contact To List', 'automatewoo' );
		parent::init();
	}


	function load_fields() {

		$email = ( new Fields\Text() )
			->set_name( 'email' )
			->set_title( __( 'Contact email', 'automatewoo' ) )
			->set_description( __( 'You can use variables such as customer.email here. If blank customer.email will be used.', 'automatewoo' ) )
			//->set_required()
			->set_variable_validation();

		$double_optin = ( new Fields\Checkbox() )
			->set_name('double_optin')
			->set_title( __( 'Double optin', 'automatewoo' ) )
			->set_description( __( 'Users will receive an email asking them to confirm their subscription.', 'automatewoo' ) );

		$this->add_list_field();
		$this->add_field( $email );
		$this->add_field( $double_optin );
	}


	/**
	 * @return void
	 */
	function run() {

		$list_id = Clean::string( $this->get_option('list') );
		$email = Clean::email( $this->get_option( 'email', true ) );
		$user = $this->workflow->get_data_item('user'); // user object is not required but will be used if present

		if ( ! $list_id )
			return;

		if ( ! $email && $user ) {
			// fallback to user.email for backwards compatibility
			$email = strtolower( $user->user_email );
		}

		$args = [];
		$subscriber_hash = md5( $email );

		$args['email_address'] = $email;
		$args['status'] = $this->get_option('double_optin') ? 'pending' : 'subscribed';

		if ( $user ) {
			$args['merge_fields'] = [
				'FNAME' => $user->first_name,
				'LNAME' => $user->last_name
			];
		}

		Integrations::mailchimp()->request( 'PUT', "/lists/$list_id/members/$subscriber_hash", $args );
	}

}
