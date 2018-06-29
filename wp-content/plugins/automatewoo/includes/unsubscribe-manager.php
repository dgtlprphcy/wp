<?php

namespace AutomateWoo;

/**
 * @class Unsubscribe_Manager
 */
class Unsubscribe_Manager {


	/**
	 * Consolidate email based unsubscribes that match a user_id
	 * @param $user_id int
	 */
	static function consolidate_user( $user_id ) {

		if ( ! $user = get_user_by( 'id', $user_id ) ) {
			return;
		}

		// MUST have an email, sometimes the user email is blank such as for social customer logins
		if ( ! $email = Clean::email( $user->user_email ) ) {
			return;
		}

		$query = new Unsubscribe_Query();
		$query->where( 'email', strtolower( $email ) );
		$unsubscribes = $query->get_results();

		foreach( $unsubscribes as $unsubscribe ) {
			$unsubscribe->set_email( '' );
			$unsubscribe->set_user_id( $user_id );
			$unsubscribe->save();
		}
	}

}
