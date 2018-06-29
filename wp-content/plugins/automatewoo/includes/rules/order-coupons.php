<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class AW_Rule_Order_Coupons
 */
class AW_Rule_Order_Coupons extends AutomateWoo\Rules\Abstract_Select {

	/** @var array  */
	public $data_item = 'order';

	public $is_multi = true;


	function init() {
		$this->title = __( 'Order Coupons', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_select_choices() {

		if ( ! isset( $this->select_choices ) ) {
			$this->select_choices = [];

			$coupons = get_posts([
				'post_type' => 'shop_coupon',
				'posts_per_page' => -1,
				'meta_query' => [
					[
						'key' => '_is_aw_coupon',
						'compare' => 'NOT EXISTS'
					]
				]
			]);

			foreach ( $coupons as $coupon ) {
				$this->select_choices[$coupon->post_title] = $coupon->post_title;
			}
		}

		return $this->select_choices;
	}


	/**
	 * @param WC_Order $order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		return $this->validate_select( $order->get_used_coupons(), $compare, $value );
	}


}

return new AW_Rule_Order_Coupons();
