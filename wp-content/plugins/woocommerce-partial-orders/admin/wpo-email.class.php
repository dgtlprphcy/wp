<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WPO_Email_Partially_Completed_Order' ) ){

class WPO_Email_Partially_Completed_Order extends WC_Email {

	function __construct() {

		$this->id 				= 'wpo_partially_completed_order';
		$this->title 			= __( 'Items Shipped', 'woocommerce' );
		$this->description		= __( 'Send an update email to customers with items that were just shipped', 'woocommerce' );

		$this->template_base 	= WPO_DIR . 'emails/'; 
		$this->template_html 	= 'items-shipped.php';
		$this->template_plain 	= 'plain/items-shipped.php';

		$this->subject 			= __( 'Part of your order from {site_title} has been shipped!', 'woocommerce');
		$this->heading      	= __( 'These items are on their way to you!', 'woocommerce');
		
		// Triggers

		// Call parent constructor
		parent::__construct();
	}
	
	function trigger( $order, $shipped_info = NULL ) {
	
		if ( ! is_object( $order ) ) {
			$order = new WC_Order( absint( $order ) );
		}
	
		if ( $order ) {
			$this->object 		= $order;
			$this->recipient	= $this->object->billing_email;

			$this->find[] = '{order_date}';
			$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );
			
			//get order items
			$order_items = $this->object->get_items();
			$shipped_items = array();
			foreach( $shipped_info as $item_id => $shipping_info ){
				//$item_name = $order_items[$item_id]['name'];
				$shipped_items[$item_id] = $order_items[$item_id];
				foreach( $shipping_info as $date => $qty ){
					$shipped_items[$item_id]['shipped_info'][] = array(
						'date_shipped' => $date,
						'quantity_shipped' => $qty
					);
				
				}
			}
			$this->object->shipped_items = $shipped_items;
			
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() || is_null( $shipped_info ) ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->format_string( $this->get_content() ), $this->get_headers(), $this->get_attachments() );
		
	}
	
	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		ob_start();
		wc_get_template(
			'items-shipped.php',
			array(
				'order' 		=> $this->object,
				'shipped_items'	=> $this->object->shipped_items,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => false
			),
			'woocommerce/emails/', //template path in theme
			$this->template_base.'/' //default template path
		);
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		wc_get_template( 
			'items-shipped.php', 
			array(
				'order' 		=> $this->object,
				'shipped_items'	=> $this->object->shipped_items,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => true
			),
			'woocommerce/emails/plain/', //template path in theme
			$this->template_base.'plain/' //default template path
		);
		return ob_get_clean();
	}

}

}

return new WPO_Email_Partially_Completed_Order();