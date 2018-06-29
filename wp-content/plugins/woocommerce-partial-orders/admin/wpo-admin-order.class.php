<?php
/**
 * Plugins settings
 *
 * @package   Woocommerce Partial Orders
 * @author    Code Ninjas 
 * @link      http://codeninjas.co
 * @copyright 2014 Code Ninjas
 */

class WPO_Admin_Order {

	/**
	 * Initialize order functionality
	 *
	 * @since     1.1.0
	 */
	public function __construct()
	{	
		add_action( 'woocommerce_admin_order_item_headers', array( $this, 'output_order_items_table_shipped_th' ) );
		add_action( 'woocommerce_admin_order_item_values', array( $this, 'output_order_items_table_shipped_td' ), 10, 3 );
		
		add_action( 'wp_ajax_wpo_bulk_set_shipped', array( $this, 'ajax_bulk_set_items_shipped' ) );
		add_action( 'wp_ajax_wpo_bulk_unset_shipped', array( $this, 'ajax_bulk_unset_items_shipped' ) );
		
		add_action( 'wp_ajax_wpo_set_item_shipped_dialog_content', array( $this, 'ajax_set_item_shipped_dialog_content' ) );
		add_action( 'wp_ajax_wpo_set_item_shipped', array( $this, 'ajax_set_item_shipped' ) );
		add_action( 'wp_ajax_wpo_unset_item_shipped', array( $this, 'ajax_unset_item_shipped' ) );
		add_action( 'wp_ajax_wpo_send_partial_orders_email', array( $this, 'ajax_send_partial_orders_emails' ) );
		
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'filter_order_item_meta' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'set_all_order_items_as_shipped' ) );
	}
	
	/**
	 * Output shipped column heading for order items table
	 *
	 * @since     1.0.0
	 */
	public function output_order_items_table_shipped_th()
	{
		echo '
		<th class="center partial-orders" width="15%">
			'.__('Shipped', 'woocommerce').'<br />
			<small>'.__('(Click to change)', 'woocommerce').'</small>
		</th>';
	}
	
	/**
	 * Output shipped values in order items table
	 *
	 * @param type $product
	 * @param type $item
	 * @param type $item_id 
	 *
	 * @since     1.0.0
	 */
	public function output_order_items_table_shipped_td( $product, $item, $item_id )
	{	
		global $post_id;
		
		if( !is_null( $product ) ){
		
			echo "<td class='center partial-orders' onclick='woocommerce_partial_orders_create_dialog(\"$item_id\", \"$post_id\");' style='cursor: pointer;'>";
			//var_dump(wc_get_order_item_meta($item_id, 'shipped'));//wc_get_order_item_meta
			$this->output_order_item_shipped_values( $item, FALSE );
			echo '</td>';
		
		} else {
			echo "<td>&nbsp;</td>";
		}
	}
	
	/**
	 * Get the total quantity shipped for the item passed
	 *
	 * @param	array	$item
	 *
	 * @since 1.1.0
	 */
	public function output_order_item_shipped_values($item, $return_output = FALSE)
	{
		$output = '';
	
		$shippedInfo = wc_get_order_item_meta($item->get_id(), 'shipped');
			
		if($shippedInfo){
		//if( isset( $item['shipped'] ) && !empty( $item['shipped'][0] ) ){
			
			//$shipped_info = ( isset( $item['shipped'] ) AND !empty( $item['shipped'][0] ) ) ? maybe_unserialize( $item['shipped'][0] ) : FALSE;
						
			//all or some shipped?
			$quantity_shipped = $this->get_total_quantity_shipped( $shippedInfo );  
			
			if( count($shippedInfo > 1) ){ //multiple shippings
				$shipped_date = $shippedInfo[ count( $shippedInfo ) - 1 ]['date']; //date of last shipped item
			} else {
				$shipped_date = $shippedInfo[0]['date'];
			}
			
			if( $quantity_shipped == $item->get_quantity() ){ //all shipped
			
				$output .= '<img src="' . WPO_URI . '/admin/assets/images/icon-tick.png" alt="Shipped" /><br />';
				
				if( strtotime( $shipped_date ) == strtotime( 'today' ) ){ //today
					$output .= "<small>Today</small>";
				} else if( strtotime( $shipped_date ) == strtotime( '-1 day', strtotime('today') ) ){ //yesterday
					$output .= "<small>Yesterday</small>";
				} else { 
					$output .= "<small>$shipped_date</small>"; //date
				}
				
			} else { //some shipped
				
				$output .= '<img src="' . WPO_URI . '/admin/assets/images/icon-cross.png"  style="cursor: pointer;" /><br />';
				$output .= "<small>$quantity_shipped shipped</small>";
			
			}
			
		} else {			
			//not shipped
			$output .= '<img src="' . WPO_URI . '/admin/assets/images/icon-cross.png"  style="cursor: pointer;" />';
		}
		
		if( $return_output ) return $output;
		
		echo $output;
	}
	
	/**
	 * Get the total quantity shipped for the item passed
	 *
	 * @param	array	$item
	 *
	 * @since 1.1.0
	 */
	public function get_total_quantity_shipped( $shipped_info )
	{			
		//get total quantity shipped of this item
		$quantity_shipped = 0; 
		if( $shipped_info AND !empty( $shipped_info ) ){
			foreach( $shipped_info as $info ){
				$quantity_shipped += $info['qty'];
			}
		}
		
		return $quantity_shipped;
	}
	
	/*
	 * Filter hidden order item meta list to add shipping meta
	 *
	 * @since 1.0.0
	 */
	public function filter_order_item_meta( $meta )
	{
		$meta[] = "shipped";
		return $meta;
	}
	
	/*
	 * Outputs the HTML of the diablog box to update order items shipped status
	 *
	 * @since 1.1.0
	 */
	public function ajax_set_item_shipped_dialog_content()
	{	
		$item_id = $_POST['item_id'];
		$order_id = $_POST['order_id'];
		
		//get shipped info for this item in the order
		$order = new WC_Order( $order_id );
		$item = $order->get_item_meta( $item_id );
		$product = get_product( $item['_product_id'][0] );
		$item_quantity = $item['_qty'][0];
		
		$shipped_info = ( isset( $item['shipped'] ) ) ? maybe_unserialize( $item['shipped'][0] ) : FALSE;
		$quantity_shipped = $this->get_total_quantity_shipped( $shipped_info );
		
		//update shipped info form if not all quantity shipped
		if( $quantity_shipped < $item_quantity ) include 'views/order-item-shipped-meta-form.phtml';
		
		//shipped info history
		if( $shipped_info ) include 'views/order-item-shipped-meta-history.phtml';
		
		die();
	}
	
	/**
	 * Sets an item as shipped witht he given date and quantity.
	 * If not quantity is passed, will set the remaining qty of the item as shipped.
	 *
	 * @since 1.2
	 */
	public function set_item_shipped( $order_id, $item_id, $shipped_quantity = 0, $shipped_date = NULL )
	{
		$order = new WC_Order( $order_id );
		//$item = $order->get_item_meta( $item_id );
		$item = new WC_Order_Item_Product($item_id);
		
		$shippedInfo = wc_get_order_item_meta($item->get_id(), 'shipped');
		
		if( is_null( $shipped_date ) ) $shipped_date = date( 'M j, Y' ); //now
		
		$shipped_info = ( isset( $item['shipped'] ) && !empty( $item['shipped'][0] ) ) ? maybe_unserialize( $item['shipped'][0] ) : array();
		
		if( $shipped_quantity == 0 ){
			$total_shipped = $this->get_total_quantity_shipped( $shippedInfo ); //get remaining quantity
			$shipped_quantity = (int)$item->get_quantity() - $total_shipped;
			
		}
		
		if( $shipped_quantity > 0 ){
			$shippedInfo[] = array(
				'qty' => $shipped_quantity,
				'date' => $shipped_date
			);		
		
			wc_update_order_item_meta( $item_id, 'shipped', $shippedInfo );
			
			//save what was shipped so the email can be sent
			$email_info = get_post_meta( $order_id, 'partial_orders_email', TRUE ); 
			if( !empty( $email_info ) ){
			
				if( array_key_exists( $item_id, $email_info ) ){ //item shipped before
					
					if( array_key_exists( $shipped_date, $email_info[$item_id] ) ){ //shipped date already exists
						
						$email_info[$item_id][$shipped_date] += (int)$shipped_quantity;
						
					} else{
						$email_info[$item_id][$shipped_date] = (int)$shipped_quantity;
					}
					
				} else {
					$email_info[$item_id] = array( $shipped_date => (int)$shipped_quantity );
				}
			
			} else {
				$email_info = array( $item_id => array( $shipped_date => (int)$shipped_quantity ) );
			}
			update_post_meta( $order_id, 'partial_orders_email', $email_info );
			
			$item['shipped'][0] = $shippedInfo; //save a db call and update it here
		}
		
		return $item;

	}
	
	/*
	 * Ajax set items as shipped and return output
	 *
	 * @since 1.1.0
	 */
	public function ajax_set_item_shipped()
	{
		parse_str($_POST['form_data']);
		
		$item = $this->set_item_shipped( $order_id, $item_id, $shipped_quantity, $shipped_date );
		
		$output = $this->output_order_item_shipped_values( $item, TRUE );
		
		//set order status
		$order_status = $this->set_order_status( $order_id );
		
		$email_info = ( $order_status != 'completed' ) ? $this->get_formatted_email_info( $order_id ) : '';
		
		echo json_encode( array( 'output' => $output, 'order_status' => $order_status, 'email_info' => $email_info ) );
			
		die();
		
	}
	
	/*
	 * Ajax bulk set items as shipped and return output
	 *
	 * @since 1.2
	 */
	public function ajax_bulk_set_items_shipped()
	{
		$order_id = $_POST['order_id'];
		$order_item_ids = $_POST['order_item_ids'];
		
		$return = array();
		foreach( $order_item_ids as $item_id ){
			$item = $this->set_item_shipped( $order_id, $item_id );
		
			$output = $this->output_order_item_shipped_values( $item, TRUE );
			
			$return['items'][] = array('item_id' => $item_id, 'output' => $output);
		}
		
		$return['new_status'] = $this->set_order_status( $order_id );
		
		$return['email_info'] = ( $return['new_status'] != 'completed' ) ? $this->get_formatted_email_info( $order_id ) : '';
		
		echo json_encode( $return );
	
		die();
		
	}
	
	public function unset_item_shipped( $order_id, $item_id )
	{
		$item = new WC_Order_Item_Product($item_id);
		
		wc_update_order_item_meta( $item_id, 'shipped', '' );
		
		$email_info = get_post_meta( $order_id, 'partial_orders_email', TRUE );
		if( !empty( $email_info ) ){
			if( array_key_exists( $item_id, $email_info ) ){
				unset( $email_info[$item_id] );
				update_post_meta( $order_id, 'partial_orders_email', $email_info );
			}
		}
		
		return $item;
	}
	
	/*
	 * Set item as not shipped
	 *
	 * @since 1.1.0
	 */
	public function ajax_unset_item_shipped()
	{
		$item_id = $_POST['item_id'];
		$order_id = $_POST['order_id'];
		
		$item = $this->unset_item_shipped( $order_id, $item_id );
		
		$output = $this->output_order_item_shipped_values( $item, TRUE );
		
		//set order status
		$order_status = $this->set_order_status( $order_id );
		
		//show 'send items shipped email' button?
		$email_info = $this->get_formatted_email_info( $order_id );
		
		echo json_encode( array( 'output' => $output, 'order_status' => $order_status, 'email_info' => $email_info ) );
		
		die();
	}
	
	/*
	 * Ajax set items as not shipped and return output
	 *
	 * @since 1.2
	 */
	public function ajax_bulk_unset_items_shipped()
	{
		$order_id = $_POST['order_id'];
		$order_item_ids = $_POST['order_item_ids'];
		
		$return = array();
		foreach( $order_item_ids as $item_id ){
			$item = $this->unset_item_shipped( $order_id, $item_id );
		
			$output = $this->output_order_item_shipped_values( $item, TRUE );
			
			$return['items'][] = array('item_id' => $item_id, 'output' => $output);
		}
		
		$return['new_status'] = $this->set_order_status( $order_id );
		
		$return['email_info'] = $this->get_formatted_email_info( $order_id );
		
		echo json_encode( $return );
	
		die();
	}
	
	public function get_formatted_email_info( $order_id )
	{
		$return = '';
		
		$order = new WC_Order( $order_id );
		$order_items = $order->get_items();
		$email_info = get_post_meta( $order_id, 'partial_orders_email', TRUE );
		
		if( $email_info ){
			foreach( $email_info as $item_id => $info ){
				$product_name = $order_items[$item_id]['name'];
				foreach( $info as $date => $qty ){
					$return .= $qty.' '.$product_name.' shipped on '.$date.'<br />';
				}
			}
			
		}
		
		return $return;
	}
	
	/*
	 * Set status of order depending on shipped status 
	 *
	 * @since 1.0.0
	 */
	public function set_order_status( $order_id )
	{
		$new_order_status = FALSE;
		
		if( get_option( 'woocommerce_partial_orders_set_order_status', 'yes' ) == 'yes' ){
			
			//check order items and see what status we need
			$order = new WC_Order( $order_id );
			$order_items = $order->get_items();
			
			$order_statuses = array();
			foreach( $order_items as $order_item ){
				
				$item_meta = $order_item['item_meta'];
				$shipped_info = ( isset( $item_meta['shipped'] ) ) ? maybe_unserialize( $item_meta['shipped'][0] ) : FALSE;
				$quantity_shipped = $this->get_total_quantity_shipped( $shipped_info );
				
				if( $quantity_shipped == $item_meta['_qty'][0] ) $order_statuses[] = 'shipped'; //all shipped
				elseif( $quantity_shipped > 0 ) $order_statuses[] = 'partially shipped'; //some shipped
				else $order_statuses[] = false; // nothing shipped
				
			}
			
			//set the order status
			if( in_array( 'partially shipped', $order_statuses ) ){
			
				$new_order_status = PARTIAL_COMP_POST_STATUS;
				
			} elseif(  in_array( false, $order_statuses ) ){
			
				if( in_array( 'shipped', $order_statuses ) ){
					$new_order_status = PARTIAL_COMP_POST_STATUS;
				}
				else $new_order_status = 'processing';
				
			}
			else{
			
				$new_order_status = 'completed';
				
			}
			
			$order->update_status( $new_order_status );
				
		}
		
		return $new_order_status;
	}
	
	/*
	 * Automatically set all remaining quantity of order items as shipped when order status is manually changed to completed
	 *
	 * @since 1.0.0
	 */
	public function set_all_order_items_as_shipped( $order_id )
	{
		if( defined( 'DOING_AJAX' ) AND DOING_AJAX ) return; //exit if status is being changed ajaxly
		
		if( get_option( 'woocommerce_partial_orders_set_items_shipped_on_completed' ) == 'yes' ){
	
			$order = new WC_Order( $order_id );
			$order_items = $order->get_items();

			foreach( $order_items as $item_id => $order_item ){
			
				$this->set_item_shipped( $order_id, $item_id );
			
			}
		
		}
	
	}
	
	public function ajax_send_partial_orders_emails(){
		$order_id = $_POST['order_id'];
		$email_info = get_post_meta( $order_id, 'partial_orders_email', TRUE );
		
		$mailer = WC()->mailer();
		$mails = $mailer->get_emails();
		$mails['WPO_Email_Partially_Completed_Order']->trigger( $order_id, $email_info );	
		
		//delete the saved information
		update_post_meta( $order_id, 'partial_orders_email', '' );
		
		echo "1";
		die();
	}

}
return new WPO_Admin_Order();
