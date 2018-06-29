<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Update_Product_Meta
 * @since 1.1.4
 */
class Action_Update_Product_Meta extends Action {

	public $required_data_items = [ 'product' ];


	function init() {
		$this->title = __( 'Add / Update Product Meta', 'automatewoo' );
	}


	function load_fields() {

		$meta_key = ( new Fields\Text() )
			->set_name( 'meta_key' )
			->set_title( __('Meta key', 'automatewoo') )
			->set_required()
			->set_variable_validation();

		$meta_value = ( new Fields\Text() )
			->set_name( 'meta_value' )
			->set_title( __( 'Meta value', 'automatewoo') )
			->set_variable_validation();

		$this->add_field($meta_key);
		$this->add_field($meta_value);
	}


	/**
	 * Requires a product data item
	 *
	 * @return mixed|void
	 */
	function run() {

		// Do we have an order object?
		if ( ! $product = $this->workflow->get_data_item('product') )
			return;

		$meta_key = Clean::string( $this->get_option( 'meta_key', true ) );
		$meta_value = Clean::string( $this->get_option( 'meta_value', true ) );

		// Make sure there is a meta key but a value is not required
		if ( $meta_key ) {
			Compat\Product::update_meta( $product, $meta_key, $meta_value );
		}

	}

}
