<?php

namespace AutomateWoo\Rules;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Customer_Country
 */
class Customer_Country extends Abstract_Select {

	public $data_item = 'customer';


	function init() {
		$this->title = __( 'Customer Country', 'automatewoo' );
		$this->group = __( 'Customer', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_select_choices() {
		return WC()->countries->get_allowed_countries();
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $customer, $compare, $value ) {
		return $this->validate_select( $customer->get_billing_country(), $compare, $value );
	}

}

return new Customer_Country();
