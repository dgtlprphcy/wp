<?php
/**
 * Request a Review email template (plain)
 *
 * @package 	Woocommerce Request a Review
 * @version     1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

echo $email_heading . "\n\n";

echo __( "Hi there.", 'woocommerce' ) . "\n\n";

echo __( "The following items have just been shipped and are on their way to you:", 'woocommerce' ) . "\n\n";

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text );

foreach( $shipped_items as $item ){
	$_product     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
	$item_meta    = new WC_Order_Item_Meta( $item['item_meta'], $_product );

	// Product Name
	echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item ) . "";
	
	// Variation
	if ( $item_meta->meta ) {
		echo $item_meta->display( true, true );
	}
	
	echo "\n";
	echo "----------------------------------------------------\n";
	
	foreach($item['shipped_info'] as $info){
		echo $info['quantity_shipped']." shipped on ".$info['date_shipped']."\n";
	}
	
	echo "\n\n";
	
}	

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text );

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );