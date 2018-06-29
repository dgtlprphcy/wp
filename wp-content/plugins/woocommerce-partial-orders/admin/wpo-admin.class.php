<?php
/**
 * Admin entry class
 *
 * @package   Woocommerce Partial Orders
 * @author    Code Ninjas 
 * @link      http://codeninjas.co
 * @copyright 2014 Code Ninjas
 */

class WPO_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.1.0
	 */
	protected static $instance = null;

	/**
	 * Initialize Admin
	 *
	 * @since     1.1.0
	 */
	public function __construct()
	{	
		//Includes
		include_once 'updates/wpo-update.class.php';
		include_once 'wpo-admin-settings.class.php';
		include_once 'wpo-admin-order.class.php';
	
		//Scripts / Styles
		add_action( 'admin_print_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_print_scripts', array( $this, 'output_inline_js' ) );
		
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance()
	{
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/**
	 * Register and enqueue admin-specific stylesheets.
	 *
	 * @since     1.0.0
	 */
	public function enqueue_admin_styles()
	{	
            $screen = get_current_screen();
            switch ($screen->id) {
                case 'shop_order':
                    wp_enqueue_style( 'wp-jquery-ui-dialog' );
                    wp_enqueue_style('wpo-order-edit-css', WPO_URI . '/admin/assets/css/order_edit.css');
                    break;
                case 'edit-shop_order';
                    wp_enqueue_style('wpo-order-edit-css', WPO_URI . '/admin/assets/css/order_edit.css');
                    break;
            }
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 */
	public function enqueue_admin_scripts()
	{
		$screen = get_current_screen();
            
		switch ($screen->id) {
			case 'shop_order':
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_script('wpo-order-edit-js', WPO_URI . '/admin/assets/js/order_edit.js');
				break;
		}
			
	}
	
	/**
	 * Output inline JS on individual pages
	 *
	 * @since     1.0.0
	 */
	public function output_inline_js()
	{	
		$screen = get_current_screen();
		
		switch ($screen->id) {
			case 'shop_order':
				global $woocommerce, $post;
                            
                                //pre woocommerce 2.6 options
				/*$dropdown_options = array(
                                    '<option value=\"bulk_set_shipped\">Set as shipped</option>'
				);
                                
                                //woocommerce 2/6+ button
                                $button_options = '<button id="wpo_bulk_set_shipped" type="button" class="button">Set as shipped</button>';
				
				if( get_option( 'woocommerce_partial_orders_mark_not_shipped', 'no' ) == 'yes' ){
                                    $dropdown_options[] = '<option value=\"bulk_unset_shipped\">Unset as shipped</option>';
                                    $button_options .= '<button id="wpo_bulk_unset_shipped" type="button" class="button">Unset as shipped</button>';
				}
				
				if( version_compare( WC_VERSION, '2.6', '<' ) ){ // Woo 2.1 and below
                                    wc_enqueue_js("$('.wc-order-bulk-actions .bulk-actions select').append('<optgroup label=\"Partial Orders\">".implode( '', $dropdown_options )."</optgroup>'); ");
				} else {
                                    wc_enqueue_js("
                                        $('.wc-order-item-bulk-edit').prepend('{$button_options}');
                                    ");
				}*/
                            
                                $button_options = '<button id="wpo_bulk_set_shipped" type="button" class="button">Set as shipped</button>';
				
				if( get_option( 'woocommerce_partial_orders_mark_not_shipped', 'no' ) == 'yes' ){
                                    $button_options .= ' <button id="wpo_bulk_unset_shipped" type="button" class="button">Unset as shipped</button>';
				}
                            
                                wc_enqueue_js("
                                    $('.wc-order-item-bulk-edit').prepend('{$button_options}');
                                    $('#wpo_bulk_set_shipped').on('click', {action: 'set'}, wpo_set_items);  
                                    $('#wpo_bulk_unset_shipped').on('click', {action: 'unset'}, wpo_set_items);
                                ");
				
				//output send email button if we have something to send
				$order = new WC_Order( $post->ID );
				$order_items = $order->get_items();
				
				$email_info = get_post_meta( $post->ID, 'partial_orders_email', TRUE );	
				
				
				if( !$email_info ){
					$style = 'display:none;';
					$tooltip = '';
				} else {
					$style = '';
					$tooltip = '';
					foreach( $email_info as $item_id => $info ){
						$product_name = $order_items[$item_id]['name'];
						foreach( $info as $date => $qty ){
							$tooltip .= $qty.' '.$product_name.' shipped on '.$date.'<br />';
						}
					}
					
				}
				
				wc_enqueue_js("
					$('.wc-order-bulk-actions .add-items').prepend('<button data-tip=\"$tooltip\" type=\"button\" class=\"tips button button-primary send-partial-orders-email\" style=\"$style\">Send \'Items Shipped\' email</button>');
					
					$('.send-partial-orders-email').on('click', function(){
						
						$('.send-partial-orders-email').replaceWith('<img src=\"images/loading.gif\" style=\"vertical-align: middle;\"/> Sending email ...');
						
						var data = {
							order_id: ".$post->ID.",
							action:   'wpo_send_partial_orders_email',
							security: woocommerce_admin_meta_boxes.order_item_nonce
						};
						
						$.ajax({
							url:  woocommerce_admin_meta_boxes.ajax_url,
							data: data,
							type: 'POST',
							success: function( response ) {
								window.location.reload();
							}
						});
					});
					
					/*$( '.send-partial-orders-email' ).tipTip({
						'attribute': 'data-tip',
						'fadeIn': 50,
						'fadeOut': 50,
						'delay': 0,
						'defaultPosition': 'top'
					});*/
					
					wpo_init_tooltip();
		
				");
				
				
			break;
		}

	}

}