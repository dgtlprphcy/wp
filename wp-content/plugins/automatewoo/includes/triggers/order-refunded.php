<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Order_Refunded
 */
class Trigger_Order_Refunded extends Trigger_Abstract_Order_Status_Base {

	public $_target_status = 'refunded';


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __('Order Refunded', 'automatewoo');
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$order = $workflow->data_layer()->get_order();

		if ( ! $order || ! $order->has_status( 'refunded' ) )
			return false;

		if ( ! parent::validate_workflow( $workflow ) )
			return false;

		return true;
	}

}
