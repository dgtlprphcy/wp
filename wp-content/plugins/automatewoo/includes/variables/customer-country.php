<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Customer_Country
 */
class Variable_Customer_Country extends Variable {


	function load_admin_details() {
		$this->description = __( "Displays the customer's billing country.", 'automatewoo');
	}


	/**
	 * @param $customer Customer
	 * @param $parameters array
	 * @param $workflow Workflow
	 * @return string
	 */
	function get_value( $customer, $parameters, $workflow ) {
		$country = $customer->get_billing_country();
		$countries = WC()->countries->get_countries();
		return isset( $countries[ $country ] ) ? $countries[ $country ] : '';
	}

}

return new Variable_Customer_Country();
