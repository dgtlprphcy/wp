<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Cart_Link
 */
class Variable_Cart_Link extends Variable {


	function load_admin_details() {
		$this->description = __( "Displays a unique link to the cart page that will also restore items to the users cart.", 'automatewoo');
	}


	/**
	 * @param $cart Cart
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $cart, $parameters ) {
		return add_query_arg([
			'aw-action' => 'restore-cart',
			'token' => $cart->get_token()
		], wc_get_page_permalink('cart') );
	}
}

return new Variable_Cart_Link();
