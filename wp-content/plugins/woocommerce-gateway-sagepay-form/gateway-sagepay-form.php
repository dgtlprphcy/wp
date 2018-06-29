<?php
/*
Plugin Name: WooCommerce SagePay Form and SagePay Direct Gateway
Plugin URI: http://woothemes.com/woocommerce
Description: Extends WooCommerce. Provides a SagePay Form / SagePay Direct gateway for WooCommerce. http://www.sagepay.com. For support please contact http://support.woothemes.com.
Version: 3.7.2
Author: Andrew Benbow
Author URI: http://www.addonenterprises.com
*/

/*  Copyright 2013  Add On Enterprises  (email : support@addonenterprises.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
    require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '6bc0cca47d0274d8ef9b164f6fbec1cc', '18599' );


/**
 * Init SagePay Gateway after WooCommerce has loaded
 */
add_action( 'plugins_loaded', 'init_sagepay_gateway', 0 );

/**
 * Localization
 */
load_plugin_textdomain( 'woocommerce_sagepayform', false, dirname( plugin_basename( __FILE__ ) ) . '/' );

function init_sagepay_gateway() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
    	return;
    }

    /**
     * Defines
     */
    define( 'SAGESUPPORTURL' , 'http://support.woothemes.com/' );
    define( 'SAGEDOCSURL' , 'https://docs.woothemes.com/document/sagepay-form/');
    define( 'SAGEPLUGINPATH', plugin_dir_path( __FILE__ ) );
    define( 'SAGEPLUGINURL', plugin_dir_url( __FILE__ ) );

    /**
     * Add SagePay Form mCrypt notice
     * Only required in admin
     */
    if( is_admin() ) {
        include('classes/sagepay-form-admin-notice-class.php');
    }

    /**
     * Add SagePay Direct CC Type notice
     * Only required in admin
     */
    if( is_admin() ) {
        include('classes/sagepay-direct-admin-notice-class.php');
    }

    /**
     * Load common functions
     */
    include('classes/sagepay-common-functions-class.php');

    /**
     * add_sagepay_form_gateway function.
     *
     * @access public
     * @param mixed $methods
     * @return void
     */
	include('classes/sagepay-form-class.php');

    function add_sagepay_form_gateway($methods) {
        $methods[] = 'WC_Gateway_Sagepay_Form';
        return $methods;
    }

    add_filter( 'woocommerce_payment_gateways', 'add_sagepay_form_gateway' );

    /**
     * add_sagepay_direct_gateway function.
     *
     * @access public
     * @param mixed $methods
     * @return void
     */
    include('classes/sagepay-direct-class.php');
    include('classes/sagepay-direct-class-addons.php');

    function add_sagepay_direct_gateway($methods) {
        
        if ( class_exists( 'WC_Subscriptions_Order' ) ) {
            $methods[] = 'WC_Gateway_Sagepay_Direct_AddOns';
        } else {
            $methods[] = 'WC_Gateway_Sagepay_Direct';
        }
        return $methods;
        
    }

    add_filter('woocommerce_payment_gateways', 'add_sagepay_direct_gateway' );


    /**
     * SagePay Direct Dismissible Notices
     */
    add_action( 'wp_ajax_dismiss_sagepaydirect_ssl_nag', 'dismiss_sagepaydirect_ssl_nag' );

    function dismiss_sagepaydirect_ssl_nag() {
        update_option( 'sagepaydirect-ssl-nag-dismissed', 1 );
    }

    add_action( 'wp_ajax_dismiss_sagepaydirect_cctype_nag', 'dismiss_sagepaydirect_cctype_nag' );

    function dismiss_sagepaydirect_cctype_nag() {
        update_option( 'sagepaydirect-cctype-nag-dismissed', 1 );
    }

    // Enqueue the dismiss script
    add_action( 'admin_enqueue_scripts', 'sagepaydirect_dismiss_assets' );

    function sagepaydirect_dismiss_assets() {
        wp_enqueue_script( 'sagepaydirect-nag-dismiss', plugins_url( '/', __FILE__ ) . '/assets/js/dismiss.js', array( 'jquery' ), '1.0', true  );
    }

    // Add 'Capture Authorised Payment' to WooCommerce Order Actions
    add_filter( 'woocommerce_order_actions', 'sagepay_woocommerce_order_actions' );

    /**
     * [sage_woocommerce_order_actions description]
     * Add Capture option to the Order Actions dropdown.
     */
    function sagepay_woocommerce_order_actions( $orderactions ) {
        global $post;
        $id = $post->ID;

        $payment_status = get_post_meta( $id, '_SagePayDirectPaymentStatus', TRUE );

        if( isset($payment_status) && ( $payment_status === 'AUTHENTICATED' || $payment_status === 'REGISTERED' ) ) {
            $orderactions['sage_process_payment'] = 'Capture Authorised Payment';
        }

        return $orderactions;
    }

    add_action( 'init', 'sagepay_register_order_status' );
    /**
     * New order status for WooCommerce 2.2 or later
     *
     * @return void
     */
    function sagepay_register_order_status() {
        register_post_status( 'wc-fraud-screen', array(
            'label'                     => _x( 'Fraud Screen', 'Order status', 'woocommerce_sagepayform' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Fraud Screening Required <span class="count">(%s)</span>', 'Fraud Screening Required <span class="count">(%s)</span>', 'woocommerce_sagepayform' )
        ) );
    }

    add_filter( 'wc_order_statuses', 'sagepay_order_statuses' );
    /**
     * Set wc-fraud-screen in WooCommerce order statuses.
     *
     * @param  array $order_statuses
     * @return array
     */
    function sagepay_order_statuses( $order_statuses ) {
        $order_statuses['wc-fraud-screen'] = _x( 'Fraud Screen', 'Order status', 'woocommerce_sagepayform' );

        return $order_statuses;
    }

    add_action( 'woocommerce_email_customer_details', 'sage_woocommerce_email_customer_details', 99, 2 );

    function sage_woocommerce_email_customer_details ( $order, $sent_to_admin = false ) {

        // WC 3.0 compatibility
        $payment_method = is_callable( array( $order, 'get_payment_method' ) ) ? $order->get_payment_method() : $order->payment_method;

        if ( $payment_method === 'sagepaydirect' ) {

            $sageresult = version_compare( WC_VERSION, '3.0', '<' ) ? get_post_meta( $order->id, '_sageresult' ) : $order->get_meta( '_sageresult', true );

            $sageresult_output = '';

            if( isset( $sageresult ) && $sageresult !== '' && $sent_to_admin ) {

                $sageresult_output = '<h3>Sage Transaction Details</h3>';

                foreach ( $sageresult as $key => $value ) {
                    $sageresult_output .= $key . ' : ' . $value . "\r\n" . "<br />";
                }

                echo $sageresult_output;

            }

        }

    }
}