<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Format;

/**
 * @class Advocate_Key_Manager
 */
class Advocate_Key_Manager {


	static function clean_advocate_keys() {

		$expiry = AW_Referrals()->options()->get_advocate_key_expiry();

		// never expire keys
		if ( $expiry === 0 )
			return;

		$expire_date = new \DateTime();
		$expire_date->modify( '-' . $expiry . ' weeks' );

		$query = new Advocate_Key_Query();
		$query->where( 'created', $expire_date->format( Format::MYSQL ), '<' );
		$query->set_limit( 50 );

		foreach ( $query->get_results() as $result ) {
			$result->delete();
		}
	}

}
