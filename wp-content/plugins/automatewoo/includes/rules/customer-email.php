<?php

namespace AutomateWoo\Rules;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Customer_Email
 */
class Customer_Email extends Abstract_String {

	public $data_item = 'customer';


	function init() {
		$this->title = __( 'Customer Email', 'automatewoo' );
		$this->group = __( 'Customer', 'automatewoo' );
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $customer, $compare, $value ) {
		return $this->validate_string( $customer->get_email(), $compare, $value );
	}

}

return new Customer_Email();
