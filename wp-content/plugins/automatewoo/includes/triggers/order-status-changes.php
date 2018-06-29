<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Order_Status_Changes
 */
class Trigger_Order_Status_Changes extends Trigger_Abstract_Order_Status_Base {


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Order Status Changes', 'automatewoo' );
		$this->description = __( 'Triggers each time a order changes status. Restrict triggering to certain status changes with the options for the trigger.', 'automatewoo' );
	}


	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'catch_hooks' ], 100, 3 );
		add_action( 'automatewoo_order_pending', [ $this, 'catch_hooks' ] ); // allowance for pending PayPal orders
	}


	function load_fields() {

		$description = __( 'Select which order statuses will trigger this workflow. Leave blank for any status.', 'automatewoo'  );

		$from = ( new Fields\Order_Status() )
			->set_title( __( 'Status changes from', 'automatewoo'  ) )
			->set_name('order_status_from')
			->set_description( $description )
			->set_multiple();

		$to = ( new Fields\Order_Status() )
			->set_title( __( 'Status changes to', 'automatewoo'  ) )
			->set_name('order_status_to')
			->set_description( $description )
			->set_multiple();

		$this->add_field($from);
		$this->add_field($to);

		parent::load_fields();
	}



	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$order = $workflow->data_layer()->get_order();

		if ( ! $order ) {
			return false;
		}

		// get options
		$order_status_from = Clean::recursive( $workflow->get_trigger_option( 'order_status_from' ) );
		$order_status_to = Clean::recursive( $workflow->get_trigger_option( 'order_status_to' ) );
		$old_status = Temporary_Data::get( 'order_old_status', Compat\Order::get_id( $order ) );

		if ( ! $this->validate_status_field( $order_status_from, $old_status ) )
			return false;

		if ( ! $this->validate_status_field( $order_status_to, $order->get_status() ) )
			return false;

		if ( ! parent::validate_workflow( $workflow ) )
			return false;

		return true;
	}


	/**
	 * Ensures 'to' status has not changed while sitting in queue
	 *
	 * @param $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {

		if ( ! parent::validate_before_queued_event( $workflow ) )
			return false;

		$user = $workflow->data_layer()->get_user();
		$order = $workflow->data_layer()->get_order();

		if ( ! $user || ! $order )
			return false;

		// Option to validate order status
		if ( $workflow->get_trigger_option('validate_order_status_before_queued_run') ) {
			$order_status_to = Clean::recursive( $workflow->get_trigger_option('order_status_to') );

			if ( ! $this->validate_status_field( $order_status_to, $order->get_status() ) )
				return false;
		}

		return true;
	}

}
