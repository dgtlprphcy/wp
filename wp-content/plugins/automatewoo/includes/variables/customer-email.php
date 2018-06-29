<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Customer_Email
 */
class Variable_Customer_Email extends Variable {


	function load_admin_details() {
		$this->description = __( "Displays the customer's email address. You can use this variable in the To field when sending emails.", 'automatewoo');
	}


	/**
	 * @param $customer Customer
	 * @param $parameters
	 * @return string
	 */
	function get_value( $customer, $parameters ) {
		return $customer->get_email();
	}

}

return new Variable_Customer_Email();
