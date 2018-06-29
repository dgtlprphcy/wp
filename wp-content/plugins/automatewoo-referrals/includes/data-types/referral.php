<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Data_Type;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Data_Type_Referral
 */
class Data_Type_Referral extends Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		return is_a( $item, 'AutomateWoo\Referrals\Referral' );
	}


	/**
	 * @param Referral $item
	 * @return mixed
	 */
	function compress( $item ) {
		return $item->get_id();
	}


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return mixed
	 */
	function decompress( $compressed_item, $compressed_data_layer ) {
		return AW_Referrals()->get_referral( $compressed_item );
	}

}

return new Data_Type_Referral();
