<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Order_Change_Status
 * @since 1.1.4
 */
class Action_Order_Change_Status extends Action {

	public $required_data_items = [ 'order' ];


	function init() {
		$this->title = __( 'Change Order Status', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
	}


	function load_fields() {
		$order_status = new Fields\Order_Status( false );
		$order_status->set_description( __( 'Order status will be changed to this.', 'automatewoo' ) );
		$order_status->set_required();

		$this->add_field($order_status);
	}


	/**
	 * @return void
	 */
	function run() {

		/** @var \WC_Order $order */
		$order = $this->workflow->get_data_item('order');
		$status = Clean::string( $this->get_option('order_status') );

		if ( ! $status || ! $order )
			return;

		$note = sprintf( __('AutomateWoo Workflow: %s.', 'automatewoo'), $this->workflow->get_title() );

		$order->update_status( $status, $note );
	}

}
