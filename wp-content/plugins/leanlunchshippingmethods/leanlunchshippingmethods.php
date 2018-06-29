<?php
/*
Plugin Name: Lean Lunch Shipping plugin
Plugin URI: http://woothemes.com/woocommerce
Description: Add new shipping methods
Version: 0.1
Author: Onstate
Author URI: http://www.onstate.co.uk
*/

// Register Custom Post Type
function lean_lunch_company_post_type() {
	
		$labels = array(
			'name'                  => _x( 'Lean Lunch Companies', 'Post Type General Name', 'text_domain' ),
			'singular_name'         => _x( 'Lean Lunch Company', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'             => __( 'Companies', 'text_domain' ),
			'name_admin_bar'        => __( 'Companies', 'text_domain' ),
			'archives'              => __( 'Company Archives', 'text_domain' ),
			'attributes'            => __( 'Company Attributes', 'text_domain' ),
			'parent_item_colon'     => __( 'Parent Company:', 'text_domain' ),
			'all_items'             => __( 'All Companies', 'text_domain' ),
			'add_new_item'          => __( 'Add New Company', 'text_domain' ),
			'add_new'               => __( 'Add New Company', 'text_domain' ),
			'new_item'              => __( 'New Company', 'text_domain' ),
			'edit_item'             => __( 'Edit Company', 'text_domain' ),
			'update_item'           => __( 'Update Company', 'text_domain' ),
			'view_item'             => __( 'View Company', 'text_domain' ),
			'view_items'            => __( 'View Companies', 'text_domain' ),
			'search_items'          => __( 'Search Company', 'text_domain' ),
			'not_found'             => __( 'No Companies', 'text_domain' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
			'featured_image'        => __( 'Featured Image', 'text_domain' ),
			'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
			'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
			'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
			'insert_into_item'      => __( 'Insert into Company', 'text_domain' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
			'items_list'            => __( 'Company list', 'text_domain' ),
			'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
			'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
		);
		$args = array(
			'label'                 => __( 'Lean Lunch Company', 'text_domain' ),
			'description'           => __( 'Lean Lunch Company', 'text_domain' ),
			'labels'                => $labels,
			'supports'              => array( 'title', ),
			'taxonomies'            => array( 'lean_lunch_company' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 20,
			'menu_icon'             => 'dashicons-networking',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => true,		
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'rewrite'               => false,
			'capability_type'       => 'page',
			'show_in_rest'          => false,
		);
		register_post_type( 'lean_lunch_company', $args );
	
	}
	add_action( 'init', 'lean_lunch_company_post_type', 0 );

/**
 * Check if WooCommerce is active
 */
 if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_filter( 'woocommerce_shipping_methods', function( $methods ) {
		$shippingMethodsArray = array();

		// WP_Query arguments
		$args = array(
			'post_status'            => array( 'publish' ),
			'post_type'              => array( 'lean_lunch_company' ),
			'nopaging'               => true,
			'order'                  => 'ASC',
			'orderby'                => 'title',
			'cache_results'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		);

		// The Query
		$leanLunchCompanies = new WP_Query( $args );

		// The Loop
		if ( $leanLunchCompanies->have_posts() ) {
			while ( $leanLunchCompanies->have_posts() ) {
				$leanLunchCompanies->the_post();
				$postId = get_the_ID();
				$shippingMethodsArray[] = array(
					'id' => get_field('shipping_method_id', $postId),
					'method_title' => get_field('shipping_method_name', $postId),
					'method_description' => get_field('shipping_method_description', $postId),
					'title' => get_field('shipping_method_title', $postId),
					'cost' => (get_field('shipping_method_free', $postId)? 0 : get_field('shipping_method_price', $postId )),
				);
			}
		} else {
			// no posts found
		}

		// Restore original Post Data
		wp_reset_postdata();		

		foreach($shippingMethodsArray AS $freeShippingMethod){
			if ( !class_exists( 'free_shipping_methods' ) ) {
				class free_shipping_methods extends WC_Shipping_Method {
					/**
						* Constructor for your shipping class
						*
						* @access public
						* @return void
						*/
					public function __construct($freeShippingMethod) {
						$this->id                 = $freeShippingMethod['id'];
						$this->method_title       = __( $freeShippingMethod['title'] );
						$this->method_description = __( $freeShippingMethod['method_description'] );
						$this->title              = __( $freeShippingMethod['method_title'] );
						$this->enabled            = "yes";
						$this->init();
					}
	
					/**
						* Init your settings
						*
						* @access public
						* @return void
						*/
					function init() {
						$this->init_form_fields();
						$this->init_settings();

						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
					}
	
					/**
						* calculate_shipping function.
						*
						* @access public
						* @param mixed $package
						* @return void
						*/
					public function calculate_shipping( $package=array() ) {
						$rate = array(
							'id' => $this->id,
							'label' => $this->title,
							'cost' => $freeShippingMethod['cost'],
							'calc_tax' => 'per_item'
						);

						$this->add_rate( $rate );
					}
				}
			}
			$methods[] = new free_shipping_methods($freeShippingMethod);
		}
		return $methods;
	});
}

/*
Change 'no shipping methods' copy
*/

add_filter( 'woocommerce_no_shipping_available_html', 'my_custom_no_shipping_message' );
add_filter( 'woocommerce_cart_no_shipping_available_html', 'my_custom_no_shipping_message' );
function my_custom_no_shipping_message( $message ) {
	return __( '<span class="ll-shipping-message"><b>If your company\'s not registered with us your order for each day needs to total at least '.get_option("minimumordervalue").'.</b><br><a href="/register-your-company/" class="fancy-link">Want to know more about the benefits of registration?</a></span>' );
}
