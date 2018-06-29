<?php

namespace AutomateWoo\Event_Helpers;

use AutomateWoo\Compat;

/**
 * @class Order_Placed
 */
class Order_Created {


	static function init() {
		// add order place hook, limited to fire once per order
		add_action( 'woocommerce_api_create_order', [ __CLASS__, 'order_created' ], 1000 );
		add_action( 'woocommerce_checkout_order_processed', [ __CLASS__, 'order_created' ], 1000 );
		add_filter( 'wcs_renewal_order_created', [ __CLASS__, 'filter_renewal_orders' ], 100 );
	}


	/**
	 * @param \WC_Order $order
	 * @return \WC_Order
	 */
	static function filter_renewal_orders( $order ) {
		self::order_created( Compat\Order::get_id( $order ) );
		return $order;
	}


	/**
	 * @param $order_id int
	 */
	static function order_created( $order_id ) {

		if ( ! $order_id || ! $order = wc_get_order( $order_id ) ) {
			return;
		}

		if ( Compat\Order::get_meta( $order, '_aw_checkout_order_processed' ) ) {
			return; // Ensure only order placed triggers once ever fire once per order
		}

		Compat\Order::update_meta( $order, '_aw_checkout_order_processed', true );

		do_action( 'automatewoo/order/created', $order_id );
		wp_schedule_single_event( time() + 15, 'automatewoo/async/order_created', [ $order_id ] );
	}

}