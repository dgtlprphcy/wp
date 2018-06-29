<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Advocate_Email
 */
class Variable_Advocate_Email extends AutomateWoo\Variable {


	function load_admin_details() {
		$this->description = __( "Displays the email address of the advocate.", 'automatewoo');
	}


	/**
	 * @param $advocate Advocate
	 * @return string
	 */
	function get_value( $advocate, $parameters ) {
		return $advocate->get_email();
	}
}

return new Variable_Advocate_Email();
