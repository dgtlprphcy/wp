<?php

namespace AutomateWoo\Referrals;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Rule_Customer_Rejected_Referral_Count
 */
class Rule_Customer_Rejected_Referral_Count extends \AW_Rule_Abstract_Number {

	public $data_item = 'customer';

	public $support_floats = false;


	function init() {
		$this->title = __( 'Customer Rejected Referral Count', 'automatewoo' );
		$this->group = __( 'Referral', 'automatewoo' );
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $customer, $compare, $value ) {

		if ( $customer->is_registered() ) {
			$advocate = new Advocate( $customer->get_user() );
			$count = $advocate->get_referral_count('rejected');
		}
		else {
			$count = 0;
		}

		return $this->validate_number( $count, $compare, $value );
	}
}

return new Rule_Customer_Rejected_Referral_Count();
