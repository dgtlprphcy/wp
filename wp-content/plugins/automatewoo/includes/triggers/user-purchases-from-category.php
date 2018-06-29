<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Only allows a single category choice as the text variable system only supports single data items

 * @class Trigger_User_Purchases_From_Category
 */
class Trigger_User_Purchases_From_Category extends Trigger_Abstract_Order_Base {

	public $is_run_for_each_line_item = true;


	function init() {
		$this->supplied_data_items[] = 'category';
	}


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Order Includes Product From Category', 'automatewoo' );
		$this->description = __( 'This trigger fires for each line item in an order, so if an order contains two products from the selected category the workflow will run twice.', 'automatewoo' );
	}


	function load_fields() {

		$category = new Fields\Category();
		$category->set_description( __( 'Only trigger when the a product is purchased from a certain category.', 'automatewoo'  ) );
		$category->set_required();

		$order_status = new Fields\Order_Status( false );
		$order_status->set_required();
		$order_status->set_default( 'wc-completed' );

		$this->add_field( $category );
		$this->add_field( $order_status );
	}


	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'trigger_for_each_order_item' ], 100 );
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$trigger = $workflow->get_trigger();
		$user = $workflow->data_layer()->get_user();
		$order = $workflow->data_layer()->get_order();
		$product = $workflow->data_layer()->get_product();

		if ( ! $user || ! $order || ! $product )
			return false;

		if ( ! $this->validate_order_status_field( $trigger, $order ) )
			return false;

		if ( ! $expected_category_id = absint( $workflow->get_trigger_option('category') ) )
			return false;

		$product_id = Compat\Product::is_variation( $product ) ? Compat\Product::get_parent_id( $product ) : Compat\Product::get_id( $product );
		$categories = wp_get_object_terms( $product_id, 'product_cat', [ 'fields' => 'ids' ] );

		if ( ! $categories ) {
			return false;
		}

		foreach ( $categories as $category_id ) {
			if ( $category_id == $expected_category_id ) {
				$workflow->set_data_item( 'category', get_category( $category_id ) );
				return true;
			}
		}

		return false;
	}

}
