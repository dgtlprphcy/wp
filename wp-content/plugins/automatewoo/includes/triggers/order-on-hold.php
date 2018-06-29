<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Order_On_Hold
 */
class Trigger_Order_On_Hold extends Trigger_Abstract_Order_Status_Base {

	public $_target_status = 'on-hold';


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __('Order On Hold', 'automatewoo');
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$order = $workflow->data_layer()->get_order();

		if ( ! $order || ! $order->has_status( 'on-hold' ) )
			return false;

		if ( ! parent::validate_workflow( $workflow ) )
			return false;

		return true;
	}

}
