<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Referral_Query
 * @since 1.6
 */
class Referral_Query extends AutomateWoo\Query_Custom_Table {

	/** @var string  */
	public $table_id = 'referrals';

	/** @var string  */
	protected $model = 'AutomateWoo\Referrals\Referral';


	/**
	 * @return Referral[]
	 */
	function get_results() {
		return parent::get_results();
	}

}
