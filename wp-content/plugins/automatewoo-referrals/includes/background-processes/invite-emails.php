<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Background_Processes;
use AutomateWoo\Clean;
use AutomateWoo\Language;

/**
 * Background processor to send invite emails, if a large number must be sent
 */
class Background_Process_Invite_Emails extends Background_Processes\Base {

	/** @var string  */
	public $action = 'referrals_invite_emails';


	/**
	 * @return string
	 */
	function get_title() {
		return __( 'Refer A Friend invite emails', 'automatewoo' );
	}


	/**
	 * @param array $data
	 * @return bool
	 */
	protected function task( $data ) {

		$email = isset( $data['email'] ) ? Clean::email( $data['email'] ) : false;
		$advocate = isset( $data['advocate'] ) ? new Advocate( Clean::id( $data['advocate'] ) ) : false;
		$language = isset( $data['language'] ) ? Clean::string( $data['language'] ) : false;

		if ( ! $email || ! $advocate->exists ) {
			return false;
		}

		if ( $language ) {
			Language::set_current( $language );
		}

		include_once AW_Referrals()->path( '/includes/referral-invite-email.php' );

		$mailer = new Invite_Email( $email, $advocate );
		$mailer->send();

		Language::set_original();

		return false;
	}

}

return new Background_Process_Invite_Emails();
