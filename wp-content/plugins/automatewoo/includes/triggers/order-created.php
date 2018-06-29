<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Order_Created
 */
class Trigger_Order_Created extends Trigger_Abstract_Order_Base {


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Order Created', 'automatewoo' );
		$this->description = __( 'This trigger fires after an order is created in the database. At checkout this happens before payment is confirmed.', 'automatewoo' );
	}


	function register_hooks() {
		if ( AUTOMATEWOO_DISABLE_ASYNC_ORDER_CREATED ) {
			add_action( 'automatewoo/order/created', [ $this, 'trigger_for_order' ] );
		}
		else {
			add_action( 'automatewoo/async/order_created', [ $this, 'trigger_for_order' ] );
		}
	}

}
