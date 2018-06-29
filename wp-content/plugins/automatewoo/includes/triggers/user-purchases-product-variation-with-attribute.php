<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_User_Purchases_Product_Variation_With_Attribute
 */
class Trigger_User_Purchases_Product_Variation_With_Attribute extends Trigger {


	public $supplied_data_items = [ 'user', 'order', 'order_item', 'product' ];


	function load_admin_details() {
		$this->title = __('Order Includes Product Variation with Specific Attribute', 'automatewoo');
		$this->group = __( 'Orders', 'automatewoo' );
		$this->description = __(
			"This trigger will look at the selected variations for each order item for the selected attribute terms. " .
			"For example if you have an attribute for 'size' and you select 'SML' and 'MED' in the Terms field then this trigger " .
			"will fire if an order is placed that contains a product in 'SML' or a product in 'MED'.",
			'automatewoo'  );
	}


	function load_fields() {
		$attribute = new Fields\Attribute();
		$attribute->set_required();

		$terms = new Fields\Attribute_Term();
		$terms->set_required();

		$order_status = new Fields\Order_Status( false );
		$order_status->set_required();
		$order_status->set_default('wc-completed');

		$this->add_field( $attribute );
		$this->add_field( $terms );
		$this->add_field( $order_status );
	}


	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'catch_hooks' ], 100, 1 );
	}


	/**
	 * @param $order_id
	 */
	function catch_hooks( $order_id ) {

		if ( ! $this->has_workflows() )
			return;

		$order = wc_get_order( $order_id );
		$user = AW()->order_helper->prepare_user_data_item( $order );

		// need to loop through every item as an order might have more than 1 product with a matching variation

		foreach ( $order->get_items() as $order_item_id => $order_item ) {

			$this->maybe_run([
				'order' => $order,
				'order_item' => AW()->order_helper->prepare_order_item( $order_item_id, $order_item ),
				'user' => $user,
				'product' => Compat\Order::get_product_from_item( $order, $order_item )
			]);
		}
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$trigger = $workflow->get_trigger();
		$user = $workflow->data_layer()->get_user();
		$order = $workflow->data_layer()->get_order();
		$order_item = $workflow->data_layer()->get_order_item();

		if ( ! $user || ! $order || ! $order_item )
			return false;


		// check status
		if ( ! $this->validate_order_status_field( $trigger, $order ) )
			return false;

		// Validate attribute terms
		$valid_attribute_terms = Clean::multi_select_values( $workflow->get_trigger_option('term') );

		// no selected terms
		if ( empty( $valid_attribute_terms ) )
			return false;

		// look for at least 1 valid term
		foreach ( $valid_attribute_terms as $valid_attribute_term ) {

			if ( ! strstr( $valid_attribute_term, '|' ) )
				continue;

			list( $attribute_term_id, $taxonomy ) = explode( '|', $valid_attribute_term );

			$target_term = get_term( $attribute_term_id, $taxonomy );
			$actual_term_slug = Compat\Order_Item::get_attribute( $order_item, $taxonomy );

			if ( $actual_term_slug ) {
				if ( $actual_term_slug === $target_term->slug )
					return true;
			}
		}

		return false;
	}

}
