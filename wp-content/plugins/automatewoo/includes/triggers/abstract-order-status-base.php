<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Abstract_Order_Status_Base
 */
abstract class Trigger_Abstract_Order_Status_Base extends Trigger_Abstract_Order_Base {

	/** @var string|false */
	public $_target_status = false;
	

	function load_fields() {
		$this->add_field_validate_queued_order_status();
	}


	/**
	 * Don't use status specific hooks as they fire too early
	 */
	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'catch_hooks' ], 100, 3 );
	}


	/**
	 * @param $order_id
	 * @param bool $old_status
	 * @param bool $new_status
	 */
	function catch_hooks( $order_id, $old_status = false, $new_status = false ) {

		if ( $this->_target_status && $new_status ) {
			if ( $new_status !== $this->_target_status )
				return;
		}

		$order = wc_get_order( $order_id );

		if ( $old_status ) {
			Temporary_Data::set( 'order_old_status', $order_id, $old_status );
		}

		$this->trigger_for_order( $order );
	}


	/**
	 * @param $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {

		if ( ! parent::validate_before_queued_event( $workflow ) )
			return false;

		$customer = $workflow->data_layer()->get_customer();
		$order = $workflow->data_layer()->get_order();

		if ( ! $customer || ! $order )
			return false;

		if ( $this->_target_status ) {
			if ( $workflow->get_trigger_option('validate_order_status_before_queued_run') ) {
				if ( $order->get_status() != $this->_target_status )
					return false;
			}
		}

		return true;
	}

}
