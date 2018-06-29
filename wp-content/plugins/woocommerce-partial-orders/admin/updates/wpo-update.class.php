<?php
/**
 * Plugin update class
 *
 * @package   Woocommerce Partial Orders
 * @author    Code Ninjas 
 * @link      http://codeninjas.co
 * @copyright 2014 Code Ninjas
 */
class WPO_Updates{

	private $plugin_id = 'ab518c0d-7ebb-4d16-8066-d4a89d54d433';

	/**
	 * Initialize updates
	 *
	 * @since     1.1.0
	 */
	public function __construct()
	{		
		$this->automatic_updates_init();
		add_action( 'admin_init', array( $this, 'update_check' ) );
	}
	
	/**
	 * Update database to current version of plugin
	 *
	 * @since     1.1.0
	 */
	public function update_check()
	{ 
		// Get the db version
		$db_version = (float)get_site_option( 'woocommerce_partial_orders_db_version', 0 );
		$plugin_version = (float)WPO_VERSION;
		
		if( $db_version == $plugin_version ) return false;
		
		//Update scripts
		if( $db_version < 1.1 ) include 'wpo-update-1-1.php';
		
		update_site_option( 'woocommerce_partial_orders_db_version', $plugin_version );
			
	}
	
	/**
	 * Automatic updates 
	 * Full credit to Janis Elsts @ http://w-shadow.com/ for this class 
	 *
	 * @since     1.1.0
	 */
	public function automatic_updates_init()
	{
		include 'automatic-updates.class.php';
		$wpo_automatic_updates = new PluginUpdateChecker(
			'http://updates.codeninjas.co?key=' . $this->plugin_id,
			WPO_FULL_PATH,
			'woocommerce-partial-orders'
		);
		//$wpo_automatic_updates->checkForUpdates();
	}

}
		
return new WPO_Updates();