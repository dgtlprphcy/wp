<?php

namespace AutomateWoo\Referrals;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Rule_Advocate_Approved_Referral_Count
 */
class Rule_Advocate_Approved_Referral_Count extends \AW_Rule_Abstract_Number {

	public $data_item = 'advocate';

	public $support_floats = false;


	function init() {
		$this->title = __( 'Advocate Approved Referral Count', 'automatewoo' );
		$this->group = __( 'Referral', 'automatewoo' );
	}


	/**
	 * @param $advocate Advocate
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $advocate, $compare, $value ) {
		return $this->validate_number( $advocate->get_referral_count('approved'), $compare, $value );
	}
}

return new Rule_Advocate_Approved_Referral_Count();
