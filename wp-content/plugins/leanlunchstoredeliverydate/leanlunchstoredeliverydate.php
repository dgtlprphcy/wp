<?php
/*
Plugin Name: Lean Lunch Store Delivery Date
Plugin URI: http://woothemes.com/woocommerce
Description: Store delivery date against products for reporting purposes
Version: 0.1
Author: Onstate
Author URI: http://www.onstate.co.uk
*/

add_filter( 'woocommerce_add_cart_item_data', 'add_cart_item_custom_data_date', 10, 2 );
function add_cart_item_custom_data_date( $cart_item_meta, $product_id ) {
  global $woocommerce;
  // $cart_item_meta['test_field'] = $_POST['test_field'];
  $cart_item_meta['Delivery date'] = '';
  return $cart_item_meta; 
}

//Get it from the session and add it to the cart variable
function get_cart_items_from_session( $item, $values, $key ) {
    if ( array_key_exists( 'Delivery date', $values ) )
        $parent = wc_pb_get_bundled_cart_item_container( $item );
        if (is_object($parent['data'])) {
        $item[ 'Delivery date' ] = $parent['data']->post->post_title;
    }
    return $item;
}
add_filter( 'woocommerce_get_cart_item_from_session', 'get_cart_items_from_session', 1, 3 );

// Save on checkout
function kia_add_order_item_meta( $item_id, $values ) {
    if ( ! empty( $values['Delivery date'] ) ) {
        woocommerce_add_order_item_meta( $item_id, 'Delivery date', $values['Delivery date'] );           
    }
}
add_action( 'woocommerce_add_order_item_meta', 'kia_add_order_item_meta', 10, 2 );

//
/*
 * Get item data to display in cart
 * @param array $other_data
 * @param array $cart_item
 * @return array
 */
function kia_get_item_data( $other_data, $cart_item ) {
 
    if ( isset( $cart_item['Delivery date'] ) ){
 
        $other_data[] = array(
            'name' => __( 'Delivery date', 'kia-plugin-textdomain' ),
            'value' => sanitize_text_field( $cart_item['Delivery date'] )
        );
 
    }
 
    return $other_data;
 
}
add_filter( 'woocommerce_get_item_data', 'kia_get_item_data', 10, 2 );



?>