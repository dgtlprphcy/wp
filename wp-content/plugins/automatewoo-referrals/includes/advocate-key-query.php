<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Advocate_Key_Query
 * @since 1.6
 */
class Advocate_Key_Query extends AutomateWoo\Query_Custom_Table {

	/** @var string  */
	public $table_id = 'referral-advocate-keys';

	/** @var string  */
	protected $model = 'AutomateWoo\Referrals\Advocate_Key';


	/**
	 * @return Advocate_Key[]
	 */
	function get_results() {
		return parent::get_results();
	}

}
