<?php

namespace AutomateWoo\Background_Processes;

use AutomateWoo\Customer_Factory;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Background processor for the customer win back trigger
 */
class Customer_Win_Back extends Base {

	/** @var string  */
	public $action = 'customer_win_back';


	/**
	 * @return string
	 */
	function get_title() {
		return __( 'Customer win back', 'automatewoo' );
	}


	/**
	 * @param array $data
	 * @return mixed
	 */
	protected function task( $data ) {

		$customer = isset( $data['customer_id'] ) ? Customer_Factory::get( absint( $data['customer_id'] ) ) : false;
		$workflow = isset( $data['workflow_id'] ) ? AW()->get_workflow( absint( $data['workflow_id'] ) ) : false;

		if ( ! $customer || ! $workflow ) {
			return false;
		}

		// make the customer's last order object available for this trigger
		$orders = wc_get_orders([
			'customer' => $customer->get_user_id(),
			'status' => [ 'wc-completed', 'wc-processing' ],
			'limit' => 1
		]);

		if ( empty( $orders ) ) {
			return false;
		}

		$workflow->maybe_run([
			'customer' => $customer,
			'order' => current( $orders )
		]);

		return false;
	}

}

return new Customer_Win_Back();
