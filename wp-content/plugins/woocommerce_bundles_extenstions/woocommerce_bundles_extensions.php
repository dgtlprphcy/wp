<?php
/**
 * Plugin Name: Lean Lunch bundle extensions
 * Plugin URI: http://www.woothemes.com/products/product-bundles/
 * Description: Use this plugin to add lean lunch-specific functionality to bundle items
 * Version: 0.1
 * Author: Onstate
 * Author URI: http://www.onstate.co.uk/
 */
 
add_filter( 'woocommerce_bundled_item_classes', 'wc_bundled_item_add_classes', 10, 2 );
function wc_bundled_item_add_classes( $classes, $bundled_item ) {
    $prod = new WC_Product ($bundled_item->product_id);
    $mealType = $prod->get_attribute( 'pa_meal-type' );
    $pasize = str_replace(',','', $prod->get_attribute( 'pa_size' ));
    $classes[] = strtolower($mealType);
    $classes[] = strtolower($pasize);



	return $classes;
}

add_action( 'woocommerce_bundled_item_details', 'add_opening_div', 0, 2 );

function add_opening_div($bundled_item, $bundle) {
    $prod = $bundled_item->product;
    $mealType = $prod->get_attribute( 'pa_meal-type' );
	//echo $mealType;
}