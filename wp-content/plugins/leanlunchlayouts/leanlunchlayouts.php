<?php
/*
Plugin Name: Lean Lunch custom layouts
Plugin URI: http://www.onstate.co.uk
Description: Various functions to customise site layout
Author: Onstate
Version: 0.1
Author URI: http://www.onstate.co.uk
*/

/**
 * Remove sidebar
 */

add_action( 'init', 'os_remove_sidebar' );
function os_remove_sidebar() {
remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
}

