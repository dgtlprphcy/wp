<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class AW_Rule_Order_Item_Count
 */
class AW_Rule_Order_Item_Count extends AutomateWoo\Rules\Abstract_Number {

	/** @var array  */
	public $data_item = 'order';

	public $support_floats = false;

	/**
	 * Init
	 */
	function init() {
		$this->title = __( 'Order Item Count', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
	}


	/**
	 * @param $order WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		return $this->validate_number( $order->get_item_count(), $compare, $value );
	}


}

return new AW_Rule_Order_Item_Count();
