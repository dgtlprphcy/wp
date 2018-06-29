<?php
/*
 * @package   Woocommerce Partial Orders
 * @author    Code Ninjas 
 * @link      http://codeninjas.co
 * @copyright 2014 Code Ninjas
 *
 * Update to 1.1
 * - Change current order items shipped info to new format (storing shipped info for multiple quantities)
 */
 
global $wpdb;
 
$order_items = $wpdb->get_results("
				SELECT meta_id, order_item_id, meta_value
				FROM wp_woocommerce_order_itemmeta 
				WHERE meta_key = 'shipped'
				ORDER BY order_item_id");
				
foreach( $order_items as $item ){

	$shipped_date = $item->meta_value;
	
	//get the quantity of this order item
	$quantity = $wpdb->get_var("
				SELECT meta_value
				FROM wp_woocommerce_order_itemmeta 
				WHERE meta_key = '_qty' AND order_item_id = " . $item->order_item_id);
				
	//update shipped meta
	$shipped_meta = array(
		'qty' => $quantity,
		'date' => $shipped_date
	);
	
	$set = array( 'meta_value' => serialize( $shipped_meta ) );
	$where = array( 'meta_id' => $item->meta_id );
	$wpdb->update( 'wp_woocommerce_order_itemmeta', $set, $where );		
}
 