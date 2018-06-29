<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Order_Pending
 */
class Trigger_Order_Pending extends Trigger_Abstract_Order_Status_Base {

	public $_target_status = 'pending';


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __('Order Pending Payment', 'automatewoo');
	}


	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'catch_hooks' ], 100, 3 );
		add_action( 'automatewoo_order_pending', [ $this, 'order_pending' ] ); // allowance for pending PayPal orders
	}


	/**
	 * @param $order_id
	 */
	function order_pending( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! $order->has_status( 'pending' ) ) {
			return; // ensure order is still pending
		}

		$this->trigger_for_order( $order );
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$order = $workflow->data_layer()->get_order();

		if ( ! $order || ! $order->has_status( 'pending' ) )
			return false;

		if ( ! parent::validate_workflow( $workflow ) )
			return false;

		return true;
	}

}
