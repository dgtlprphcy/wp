<?php
/**
 * Common functionality shared between admin and frontend
 *
 * @package   Woocommerce Partial Orders
 * @author    Code Ninjas 
 * @link      http://codeninjas.co
 * @copyright 2014 Code Ninjas
 */
 
 class WPO_Common {
 
	/**
	 * Initialize common functionality
	 *
	 * @since     1.1.0
	 */
	public function __construct()
	{
		DEFINE( 'PARTIAL_COMP_POST_STATUS', 'partial-comp' );
	
		add_action( 'init', array( $this, 'add_order_status' ) );
		add_filter( 'wc_order_statuses', array( $this, 'filter_order_status' ) );
		
		add_filter( 'woocommerce_email_classes', array( $this, 'add_email_class' ) );
		
		add_filter('woocommerce_display_item_meta', array($this, 'outputShippedInfoInMyAccount'), 999, 3);
	}
	
	/**
	 * Add new order status to Woocommerce
	 * Modified to new order statuses in Woo 2.2
	 *
	 * @since     1.0.0
	 */
	public function add_order_status()
	{ 
		if( version_compare( WC_VERSION, '2.2', '<' ) ){ // Woo 2.1 and below
		
			//Add Partially Completed order status if it doesn't already exist.
			if( taxonomy_exists( 'shop_order_status' ) ){
				if( !term_exists( 'partially-completed', 'shop_order_status' ) ){
					wp_insert_term( 'Partially Completed', 'shop_order_status' );
				}
			}
		
		} else {

			register_post_status( 'wc-'.PARTIAL_COMP_POST_STATUS, array(
				'label'                     => _x( 'Partially Completed', 'Order status', 'woocommerce' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Partially Completed <span class="count">(%s)</span>', 'Partially Completed <span class="count">(%s)</span>', 'woocommerce' )
			) );
		
		}
	}
	
	/**
	 * Add order status to new order status filter in Woo 2.2
	 * 
	 * @since 1.2
	 */
	function filter_order_status( $order_statuses )
	{
		$statuses = array();
		foreach( $order_statuses as $k => $v ){
			if( $k == 'wc-completed' ) $statuses['wc-'.PARTIAL_COMP_POST_STATUS] = _x( 'Partially Completed', 'Order status', 'woocommerce' );
			$statuses[$k] = $v;
		}
		return $statuses;
	}
	
	/**
	 * Add Partially Completed email to Woocommerce default emails
	 *
	 * @since 1.0.0
	 * @filter woocommerce_email_classes
	 */
	public function add_email_class( $emails )
	{
		$emails['WPO_Email_Partially_Completed_Order'] = include( WPO_DIR . 'admin/wpo-email.class.php' );
		return $emails;
	}
	
	public function outputShippedInfoInMyAccount($html, $item, $args)
	{
		$return = '';
		$shippedInfo = wc_get_order_item_meta($item->get_id(), 'shipped');
		if($shippedInfo){
			foreach($shippedInfo as $info){
				$return .= "<i class='smallertext not-bold primary-color'>Shipped on {$info['date']}</i>";
			}
		}
		
		return $html.$return;
	}
 
 }
 return new WPO_Common();