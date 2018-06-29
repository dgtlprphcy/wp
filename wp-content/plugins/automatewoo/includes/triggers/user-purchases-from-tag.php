<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Only allows a single tag choice as the text variable system only supports single data items
 *
 * @class Trigger_User_Purchases_From_Tag
 */
class Trigger_User_Purchases_From_Tag extends Trigger_Abstract_Order_Base {


	function init() {
		$this->supplied_data_items[] = 'tag';
	}


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Order Includes Product From Tag', 'automatewoo' );
	}


	function load_fields() {
		$category = new Fields\Tag();
		$category->set_description( __( 'Only trigger when the a product is purchased from a certain tag.', 'automatewoo'  ) );
		$category->set_required();

		$order_status = new Fields\Order_Status( false );
		$order_status->set_required();
		$order_status->set_default('wc-completed');

		$this->add_field( $category );
		$this->add_field( $order_status );
	}


	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'trigger_for_order' ], 100 );
	}


	/**
	 * @param $workflow Workflow
	 *
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

		if ( ! $target_tag = absint( $workflow->get_trigger_option('tag') ) )
			return false;

		foreach ( $order->get_items() as $item ) {

			$product_tags = wp_get_object_terms( Compat\Order_Item::get_product_id( $item ), 'product_tag' );

			if ( ! $product_tags )
				continue;

			foreach( $product_tags as $tag ) {
				if ( $tag->term_id == $target_tag ) {
					$workflow->set_data_item('tag', $tag );
					return true;
				}
			}
		}

		return false;
	}

}
