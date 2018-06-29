<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Order_Trigger_Action
 * @since 2.3
 */
class Action_Order_Trigger_Action extends Action {

	public $required_data_items = [ 'order' ];


	function init() {
		$this->title = __( 'Trigger Order Action', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
		$this->description = __( 'Not to be confused with AutomateWoo actions this action can trigger a WooCommerce order action. They can normally be found in the in the top right of of the order edit view.', 'automatewoo');
	}


	function load_fields() {

		$action = new Fields\Select();
		$action->set_name( 'order_action' );
		$action->set_title( __( 'Order action', 'automatewoo' ) );
		$action->set_required();
		$action->set_options( apply_filters( 'woocommerce_order_actions', [
			'regenerate_download_permissions' => __( 'Generate download permissions', 'woocommerce' )
		]));

		$this->add_field($action);
	}


	/**
	 * @return void
	 */
	function run() {

		$action = Clean::string( $this->get_option( 'order_action' ) );
		$order = $this->workflow->get_data_item('order');

		if ( ! $action || ! $order )
			return;


		if ( $action == 'regenerate_download_permissions' ) {

			if ( version_compare( WC()->version, '3.0', '<' ) ) {
				delete_post_meta( $order->id, '_download_permissions_granted' );
				wc_downloadable_product_permissions( $order->id );
			}
			else {
				$data_store = \WC_Data_Store::load( 'customer-download' );
				$data_store->delete_by_order_id( $order->id );
				wc_downloadable_product_permissions( $order->id, true );
			}

		}
		else {
			if ( ! did_action( 'woocommerce_order_action_' . sanitize_title( $action ) ) ) {
				do_action( 'woocommerce_order_action_' . sanitize_title( $action ), $order );
			}
		}
	}
}