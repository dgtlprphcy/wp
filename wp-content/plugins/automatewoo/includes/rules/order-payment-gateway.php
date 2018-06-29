<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Rule_Order_Payment_Gateway
 */
class Rule_Order_Payment_Gateway extends Rules\Abstract_Select {

	public $data_item = 'order';


	function init() {
		$this->title = __( 'Order Payment Gateway', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_select_choices() {

		if ( ! isset( $this->select_choices ) ) {
			foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
				if ( $gateway->enabled === 'yes') {
					$this->select_choices[$gateway->id] = $gateway->get_title();
				}
			}
		}

		return $this->select_choices;
	}


	/**
	 * @param $order \WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		return $this->validate_select( Compat\Order::get_payment_method( $order ), $compare, $value );
	}

}

return new Rule_Order_Payment_Gateway();
