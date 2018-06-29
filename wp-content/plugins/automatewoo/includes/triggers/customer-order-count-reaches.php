<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Customer_Order_Count_Reaches
 */
class Trigger_Customer_Order_Count_Reaches extends Trigger {

	public $supplied_data_items = [ 'customer', 'order' ];


	function load_admin_details() {
		$this->title = __( 'Customer Order Count Reaches', 'automatewoo' );
		$this->description = __( "This trigger checks the customer's order count each time an order is completed.", 'automatewoo' );
		$this->group = __( 'Customers', 'automatewoo' );
	}


	function load_fields() {
		$order_count = new Fields\Number();
		$order_count->set_name( 'order_count' );
		$order_count->set_title( __( 'Order count', 'automatewoo' ) );
		$order_count->set_required();

		$this->add_field( $order_count );
	}


	/**
	 * Must run on woocommerce_order_status_changed after customer totals have been updated
	 */
	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'catch_hooks' ], 100, 3 );
	}


	/**
	 * @param $order_id
	 * @param $old_status
	 * @param $new_status
	 */
	function catch_hooks( $order_id, $old_status, $new_status ) {

		if ( $new_status !== 'completed' ) {
			return;
		}

		if ( ! $order = wc_get_order( $order_id ) ) {
			return;
		}

		$this->maybe_run([
			'order' => $order,
			'customer' => Customer_Factory::get_by_order( $order )
		]);
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$customer = $workflow->data_layer()->get_customer();
		$order = $workflow->data_layer()->get_order();

		if ( ! $customer || ! $order ) {
			return false;
		}

		$order_count = absint( $workflow->get_trigger_option('order_count') );

		// fail if no order count set
		if ( ! $order_count ) {
			return false;
		}

		// Only do this once for each user (for each workflow)
		if ( $workflow->get_run_count_for_customer( $customer ) !== 0 ) {
			return false;
		}

		// Validate order count
		if ( $customer->get_order_count() < $order_count ) {
			return false;
		}

		return true;
	}

}
