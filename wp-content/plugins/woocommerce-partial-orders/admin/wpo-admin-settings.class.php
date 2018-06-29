<?php
/**
 * Plugins settings
 *
 * @package   Woocommerce Partial Orders
 * @author    Code Ninjas 
 * @link      http://codeninjas.co
 * @copyright 2014 Code Ninjas
 */

class WPO_Admin_Settings {

	protected $settings;

	/**
	 * Initialize settings
	 *
	 * @since     1.1.0
	 */
	public function __construct()
	{		
		$this->settings = $this->initialise_settings();		
		
		add_filter( 'woocommerce_inventory_settings', array( $this, 'add_settings' ) );
		
	}
	
	/**
	 * Plugins settings options
	 *
	 * @since 1.0.0
 	 */
	private function initialise_settings()
	{
		$settings = array(
                
			array( 'title' => __( 'Partial Orders Options', 'woocommerce' ), 'type' => 'title','desc' => '', 'id' => 'partial_orders' ),
			
			array(
				'title'     => __( '', 'woocommerce' ),
				'desc'      => __( 'Automatically set order status as Partially Completed / Completed', 'woocommerce' ),
				'desc_tip'  => __( 'Automatically set the order status as Partially Completed / Completed when some/all products have been shipped', 'woocommerce' ),
				'id'        => 'woocommerce_partial_orders_set_order_status',
				'default'   => 'yes',
				'type'      => 'checkbox'
			),
			
			array(
				'title'     => __( '', 'woocommerce' ),
				'desc'      => __( 'Automatically mark items as shipped when order status changed to Completed', 'woocommerce' ),
				'desc_tip'  => __( 'Automatically set the remaining quantity of all items as shipped when the order status is manually set to Completed.  This will not overwrite any quantity shipped previously.', 'woocommerce' ),
				'id'        => 'woocommerce_partial_orders_set_items_shipped_on_completed',
				'default'   => 'yes',
				'type'      => 'checkbox'
			),
			
			array(
				'title'     => __( '', 'woocommerce' ),
				'desc'      => __( 'Allow unsetting products as shipped once set to shipped', 'woocommerce' ),
				'desc_tip'  => __( 'Set whether to allow the option to set a product as not shipped once it has been set as shipped.', 'woocommerce' ),
				'id'        => 'woocommerce_partial_orders_mark_not_shipped',
				'default'   => 'no',
				'type'      => 'checkbox'
			),
			
			array( 'type' => 'sectionend', 'id' => 'partial_orders' ),
			
		);
		
		return $settings;
	}
	
	/**
	 * Add settings to Woocommerce's Inventory settings
	 *
	 * @since 1.0.0
 	 */
	 public function add_settings( $options )
	 {	
		return array_merge( $options, $this->settings );
	 }
	
}
return new WPO_Admin_Settings();