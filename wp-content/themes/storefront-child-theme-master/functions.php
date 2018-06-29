<?php

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
//add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );

/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
}

/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */

// reload css
//wp_enqueue_style( 'storefront-child-style', get_stylesheet_directory_uri() . '/style.css', array(), filemtime( get_stylesheet_directory() . '/style.css' ) );


// add js to header
add_action( 'wp_enqueue_scripts', 'add_js_to_header', 100 );

function add_js_to_header() {
	wp_enqueue_script( 'parallax', get_stylesheet_directory_uri() . '/assets/js/parallax.min.js', array ( 'jquery' ), 1.1, true);
	wp_enqueue_script( 'script', get_stylesheet_directory_uri() . '/assets/js/script.js', array ( 'jquery' ), filemtime( get_stylesheet_directory() . '/assets/js/script.js' ), 1.1, true);


	// create js variable for stylesheet uri
	$wnm_custom = array( 'stylesheet_directory_uri' => get_stylesheet_directory_uri() );
	wp_localize_script( 'script', 'directory_uri', $wnm_custom );

}


// Change Shipping Text to Delivery globably
add_filter('gettext', 'translate_reply');
add_filter('ngettext', 'translate_reply');

function translate_reply($translated) {
  $translated = str_ireplace('Shipping', 'Delivery', $translated);
  return $translated;
}

// show/hide my account/register/cart on menu
function show_hide_what_on_menu() {

    if ( is_user_logged_in() ) {
        $output="<style> .nav-register, .secondary-navigation { display: none!important; } </style>";
    } else {
        $output="<style> .nav-myaccount, .site-header-cart{ display: none!important; } </style>";
    }

    echo $output;
}

add_action('wp_head','show_hide_what_on_menu');


//remove customizer inline styles from parent theme
function my_theme_remove_storefront_standard_functionality() {

set_theme_mod('storefront_styles', '');
set_theme_mod('storefront_woocommerce_styles', '');

}

add_action( 'init', 'my_theme_remove_storefront_standard_functionality' );


// add preloader to header
function preloader_header(){
   echo '<div class="preloader"><i class="llicons-foodslice llicons-spin"></i></div>';
}


add_action( 'storefront_before_header' ,'preloader_header');


// add featured image as page banner
function add_featured_image_as_page_banner() {


	if(get_queried_object_id() == 0){
		$pagebanner = get_post_thumbnail_id( get_option( 'woocommerce_shop_page_id' ) );

	}else{
		$pagebanner = get_post_thumbnail_id( get_queried_object_id() );

	}

	$pagebanner_url = wp_get_attachment_image_src($pagebanner, full);

	if(!empty($pagebanner_url)){
		echo $pagebanner_url[0];
	}

}



// add modal to footer
function modal_footer(){

	$modalhtml = '<div class="modal-bg">';
	$modalhtml .= '<div class="modal-box"></div>';
	$modalhtml .= '</div>';
   	echo $modalhtml;
}


add_action( 'wp_footer' ,'modal_footer');


// add description to menu index page
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_single_excerpt', 5);

// change add to cart message on menu index
add_filter( 'bundle_add_to_cart_text', 'wc_custom_cart_button_text');
function wc_custom_cart_button_text() {

        return __( 'View full menu', 'woocommerce' );

}


// change add to basket on menu details page
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text' );

function woo_custom_cart_button_text() {

		$text = __( 'Place order for '.get_the_title(), 'woocommerce' );

		if ( isset( $_GET[ 'update-bundle' ] ) ) {

			$updating_cart_key = wc_clean( $_GET[ 'update-bundle' ] );

			if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {
				$text = __( 'Update order for '.get_the_title(), 'woocommerce-product-bundles' );
			}
		}

        return $text;

}

// change update order text
add_filter('gettext', 'change_update_order_text');
add_filter('ngettext', 'change_update_order_text');

function change_update_order_text($translated) {
  $translated = str_ireplace('When finished, click the <strong>Update Cart</strong> button.', 'When finished, click the <strong>Update order</strong> button.', $translated);
  return $translated;
}


// change basket updated text
add_filter('gettext', 'change_basket_updated_text');
add_filter('ngettext', 'change_basket_updated_text');

function change_basket_updated_text($translated) {
  $translated = str_ireplace('Basket updated', 'Lunch orders updated', $translated);
  return $translated;
}

// customise product title in menu index
add_action('woocommerce_shop_loop_item_title2','woocommerce_template_loop_product_title2');
function woocommerce_template_loop_product_title2() {

	$wcproducttitle = str_replace('th','', get_the_title());
	$wcproducttitle = str_replace('1st','1',$wcproducttitle);
	$wcproducttitle = str_replace('2nd','2',$wcproducttitle);
	$wcproducttitle = str_replace('3rd','3',$wcproducttitle);

	echo '<div class="wc-menu-index-title"><h2 class="woocommerce-loop-product__title">' . $wcproducttitle . '</h2></div>';
}


// order by date oldest in admin
add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_woocommerce_get_catalog_ordering_args' );
add_filter( 'woocommerce_default_catalog_orderby_options', 'custom_woocommerce_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'custom_woocommerce_catalog_orderby' );

 // Apply custom args to main query
function custom_woocommerce_get_catalog_ordering_args( $args ) {
	$orderby_value = isset( $_GET['orderby'] ) ? woocommerce_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

	if ( 'oldest_to_recent' == $orderby_value ) {
		$args['orderby'] = 'date';
		$args['order'] = 'ASC';
	}

	return $args;
}

// Create new sorting method in admin
function custom_woocommerce_catalog_orderby( $sortby ) {

	$sortby['oldest_to_recent'] = __( 'Oldest to most recent', 'woocommerce' );

	return $sortby;
}


// add modal for full description product bundle page
remove_action( 'woocommerce_bundled_item_details', 'wc_pb_template_bundled_item_description', 20, 2 );
add_action( 'woocommerce_bundled_item_details', 'wc_pb_template_bundled_item_description2', 20, 2 );

function wc_pb_template_bundled_item_description2( $bundled_item, $bundle ) {

	wc_get_template( 'single-product/bundled-item-description.php', array(
		'title'        => $bundled_item->get_title(),
		'description' => $bundled_item->get_description(),
		'full_description' => get_post($bundled_item->product_id)->post_content,
		'product_id' => $bundled_item->product_id,
		'item_id' => $bundled_item->item_id

	), false, WC_PB()->plugin_path() . '/templates/' );
}

// reset qty fields to 0 after adding to bag
//add_filter( 'woocommerce_quantity_input_args', 'custom_woocommerce_quantity_input_args' );

function custom_woocommerce_quantity_input_args( $args ) {
if ( is_singular( 'product' ) ) {
  $args['input_value'] = 0;
}
return $args;
}

/** * Redirect users after add to cart.
 */
add_filter( 'woocommerce_add_to_cart_redirect', 'll_redirect_to_days_list' );
function ll_redirect_to_days_list( $url )
{
    $url = get_permalink( 6 );
    return $url;
}

// change add to cart message
add_filter ( 'wc_add_to_cart_message', 'wc_add_to_cart_message_filter', 10, 2 );
function wc_add_to_cart_message_filter($message, $product_id = null) {
    $titles[] = get_the_title( $product_id );

    $titles = array_filter( $titles );
    $added_text = sprintf( '<p class="larger stronger">We’ve added an order to your bag for %s.</p>', wc_format_list_of_items( $titles ) );

    $added_text .= '<p class="thinmarg-bottom">If you\'d like to order for another day you can do that below.</p>';
    $added_text .= '<a href="'.wc_get_page_permalink( 'cart' ).'" class="button">View orders & check out</a>';

    return $added_text;
}

add_filter( 'woocommerce_shipping_package_name' , 'woocommerce_replace_text_shipping_to_delivery', 10, 3);

/**
 *
 * Function to replace shipping text to delivery text
 *
 * @param $package_name
 * @param $i
 * @param $package
 *
 * @return string
 */
function woocommerce_replace_text_shipping_to_delivery($package_name, $i, $package){
    return sprintf( _nx( 'Delivery', 'Delivery %d', ( $i + 1 ), 'shipping packages', 'woocommerce' ), ( $i + 1 ) );
}

// remove related products
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

// deregister some plugin css
add_action( 'wp_print_styles', 'deregister_my_styles', 100 );

function deregister_my_styles() {
	wp_deregister_style( 'spp-styles' );  // product pagination
	wp_deregister_style( 'storefront-woocommerce-bundles-style' );
	wp_deregister_style( 'wcqi-css' ); // +- qty increment

}


// change burger menu text
add_filter( 'storefront_menu_toggle_text', 'custom_storefront_menu_toggle_text');
function custom_storefront_menu_toggle_text() {

        return __( 'More', 'storefront' );

}

/** change priority of cart in header and  change html**/

// remove cart from orginal position
add_action( 'init', 'remove_sf_actions' );
function remove_sf_actions() {

	remove_action( 'storefront_header', 'storefront_header_cart', 60 );

}

// add wrapper around cart
add_action( 'storefront_header', 'storefront_secondary_navigation_wrapper',  25 );
function storefront_secondary_navigation_wrapper(){

	echo '<div class="storefront-secondary-navigation">';
}

add_action( 'storefront_header', 'storefront_secondary_navigation_wrapper_close',  35 );
function storefront_secondary_navigation_wrapper_close(){

	echo '</div>';
}

// position cart top right and change html
add_action( 'storefront_header', 'storefront_header_cart', 32 );
function storefront_header_cart() {
	if ( storefront_is_woocommerce_activated() ) {
		if ( is_cart() ) {
			$class = 'current-menu-item';
		} else {
			$class = '';
		}
	?>
	<ul id="site-header-cart" class="site-header-cart menu">
		<li class="<?php echo esc_attr( $class ); ?>">
			<?php storefront_cart_link(); ?>
		</li>
		<li style="display: none!important">
			<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
		</li>
	</ul>
	<?php
	}
}

// change cart html
function storefront_cart_link() {
	?>
		<a class="cart-contents" href="<?php echo esc_url( WC()->cart->get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your lunch orders', 'storefront' ); ?>">

			<span class="amount" style="display: none!important"><?php echo wp_kses_data( WC()->cart->get_cart_subtotal() ); ?></span>
			<span class="count"><span class="count-icon"><?php echo WC()->cart->get_cart_contents_count();?></span> <span class="count-text"><?php echo  _n( 'lunch<br>order', 'lunch<br>orders', WC()->cart->get_cart_contents_count(), 'storefront' )?></span></span>
		</a>
	<?php
}

//* Add gallery thumbs to woocommerce shop page
add_action('woocommerce_before_shop_loop_item_title','wps_add_extra_product_thumbs', 5);
function wps_add_extra_product_thumbs() {

	if ( is_shop() ) {

		global $product;

		//$attachment_ids = $product->get_gallery_attachment_ids();
		$attachment_ids = $product->get_gallery_image_ids();


		$i = 0;
		foreach( array_slice( $attachment_ids, 0,3 ) as $attachment_id ) {
			$i++;
		  	$thumbnail_url = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' )[0];
		  	echo '<div class="wc-product-thumb wc-product-thumb-'.$i.'">';
		  	echo '<img src="' . $thumbnail_url . '">';
		  	echo '</div>';

		}


	}

 }

 // Remove product images from the menu index
 remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );

// remove price from menu index
 remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

 // add wrapper around title and desc on menu index
add_action('woocommerce_before_shop_loop_item_title','add_item_title_wrapper', 15);
function add_item_title_wrapper() {

	echo '<div class="wc-plp-wrapper">';

}
add_action('woocommerce_after_shop_loop_item_title','add_item_title_wrapper_close', 15);
function add_item_title_wrapper_close() {

	echo '</div>';

}

// change position of button cta on menu index
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 10 );

// add cta to register company on login/register page
add_action( 'woocommerce_register_form_start','register_company_cta',10 );
function register_company_cta() {

	echo '<div class="ll-register-company-cta"><a href="/register-your-company/">Want to register your<br>company? Click here</a></div>';

}


//Create global custom fields in admin
add_action('admin_menu', 'add_gcf_interface');

function add_gcf_interface() {
	add_options_page('Global Custom Fields', 'Global Custom Fields', '8', 'functions', 'editglobalcustomfields');
}

function editglobalcustomfields() {
	?>
	<div class='wrap'>
	<h2>Global Custom Fields</h2>
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options') ?>

	<p><strong>Minimum order value:</strong><br />
	<input type="text" name="minimumordervalue" size="45" value="<?php echo get_option('minimumordervalue'); ?>" /></p>

	<p><input type="submit" name="Submit" value="Update Options" /></p>

	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="minimumordervalue" />

	</form>
	</div>
	<?php
}

// create shortcodes for global custom fields
// e.g [global_custom_field key="minimumordervalue"]
function gcf_func($atts) {

    extract(shortcode_atts(array(
    		'key' => ''
        ), $atts));

    return get_option($key);
}
add_shortcode('global_custom_field', 'gcf_func');

//custom shipping methods based on customer group capabilities
add_filter( 'woocommerce_package_rates', 'custom_shipping_methods', 100, 2 );    
function custom_shipping_methods( $rates, $package ){
   
   //determine whether the user is an employee of a registered company
   $groups_user = new Groups_User( get_current_user_id() );
   $can_be_registered = $groups_user->can( 'can_be_registered' );
   
   $free = array();
   
    // if the user is a member of a registered company then show methods for the company (and any additional rates) and hide flat rate / free shipping
     if ( $can_be_registered == 1 ) {
       
         foreach ( $rates as $rate_id => $rate ) {
            if($rate->method_id != 'flat_rate' && $rate->method_id != 'free_shipping' ){
               $free[ $rate_id ] = $rate;
             
           }
       }
   }
   
   else {
   
       // the customer does not belong to a company. If free shipping is available then hide all other shipping methods
       foreach ( $rates as $rate_id => $rate ) {
           if ( 'free_shipping' === $rate->method_id ) {
               $free[ $rate_id ] = $rate;
               break;
           }
       }

   }
    return ! empty( $free ) ? $free : $rates;
}
