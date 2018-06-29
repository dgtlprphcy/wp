<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_User_Purchases_From_Taxonomy_Term
 */
class Trigger_User_Purchases_From_Taxonomy_Term extends Trigger_Abstract_Order_Base {


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __('Order Includes Product from Taxonomy Term', 'automatewoo');
	}


	function load_fields() {

		$taxonomy = new Fields\Taxonomy();
		$taxonomy->set_required();

		$term = new Fields\Taxonomy_Term();
		$term->set_required();

		$order_status = new Fields\Order_Status( false );
		$order_status->set_required();
		$order_status->set_default('wc-completed');

		$this->add_field( $taxonomy );
		$this->add_field( $term );
		$this->add_field( $order_status );
	}


	/**
	 * When could this trigger run?
	 */
	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'trigger_for_order' ], 100, 1 );
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$trigger = $workflow->get_trigger();
		$user = $workflow->data_layer()->get_user();
		$order = $workflow->data_layer()->get_order();

		if ( ! $user || ! $order )
			return false;

		if ( ! $this->validate_order_status_field( $trigger, $order ) )
			return false;

		$stored_term_data = Clean::string( $workflow->get_trigger_option('term') );

		if ( ! strstr( $stored_term_data, '|' ) )
			return false;

		list( $term_id, $taxonomy ) = explode( '|', $stored_term_data );

		if ( ! $term_id || ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		foreach ( $order->get_items() as $item ) {

			$product_terms = wp_get_object_terms( Compat\Order_Item::get_product_id( $item ), $taxonomy );

			if ( ! $product_terms )
				continue;

			foreach( $product_terms as $product_term ) {
				if ( $product_term->term_id == $term_id ) {
					return true;
				}
			}
		}

		return false;
	}

}
