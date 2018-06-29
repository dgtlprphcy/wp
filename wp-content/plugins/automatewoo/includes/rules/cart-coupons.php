<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class AW_Rule_Cart_Coupons
 */
class AW_Rule_Cart_Coupons extends AutomateWoo\Rules\Abstract_Select {

	public $data_item = 'cart';

	public $is_multi = true;

	/**
	 * Init
	 */
	function init() {
		$this->title = __( 'Cart Coupons', 'automatewoo' );
		$this->group = __( 'Cart', 'automatewoo' );
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
	 * @param $cart AutomateWoo\Cart
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $cart, $compare, $value ) {
		return $this->validate_select( array_keys( $cart->get_coupons() ), $compare, $value );
	}


}

return new AW_Rule_Cart_Coupons();
