<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Integrations;

/**
 * @class Hooks
 * @since 1.2.14
 */
class Hooks {

	/**
	 * Add 'init' actions here means we can load less files at 'init'
	 */
	function __construct() {

		add_filter( 'automatewoo/factories', [ $this , 'factories' ] );

		// Referrals
		if ( AW_Referrals()->options()->get_reward_event() === 'purchase' ) {
			add_action( 'woocommerce_checkout_order_processed', [ 'AutomateWoo\Referrals\Referral_Manager', 'check_order_for_referral' ], 1000 );
			add_action( 'woocommerce_order_status_changed', [ 'AutomateWoo\Referrals\Referral_Manager', 'update_referral_status_on_order_status_change' ], 20, 3 );
		}
		elseif ( AW_Referrals()->options()->get_reward_event() === 'signup' ) {
			add_action( 'automatewoo/user_registered', [ 'AutomateWoo\Referrals\Referral_Manager', 'check_signup_for_referral' ], 100 );
		}

		// Referral coupons
		if ( AW_Referrals()->options()->type === 'coupon' ) {

			add_filter( 'woocommerce_get_shop_coupon_data', [ 'AutomateWoo\Referrals\Coupons', 'catch_referral_coupons' ], 10, 2 );
			add_filter( 'woocommerce_coupon_is_valid', [ 'AutomateWoo\Referrals\Coupons', 'validate_referral_coupon' ], 10, 2 );
			add_filter( 'woocommerce_coupon_error', [ 'AutomateWoo\Referrals\Coupons', 'filter_coupon_errors' ], 10, 3 );

			add_action( 'woocommerce_after_checkout_validation', [ 'AutomateWoo\Referrals\Coupons', 'check_customer_coupons' ] );
		}

		// advocate keys
		add_action( 'automatewoo/referrals/clean_advocate_keys', [ 'AutomateWoo\Referrals\Advocate_Key_Manager', 'clean_advocate_keys' ] );

		// Workflows
		add_filter( 'automatewoo/triggers', [ 'AutomateWoo\Referrals\Workflows', 'triggers' ], 5 );
		add_filter( 'automatewoo/data_types/includes', [ 'AutomateWoo\Referrals\Workflows', 'inject_data_types' ] );
		add_filter( 'automatewoo/variables', [ 'AutomateWoo\Referrals\Workflows', 'inject_variables' ] );
		add_filter( 'automatewoo/rules/includes', [ 'AutomateWoo\Referrals\Workflows', 'include_rules' ] );
		add_filter( 'automatewoo/preview_data_layer', [ 'AutomateWoo\Referrals\Workflows', 'inject_preview_data' ] );
		add_filter( 'automatewoo/log/data_layer_storage_keys', [ 'AutomateWoo\Referrals\Workflows', 'log_data_layer_storage_keys' ] );
		add_filter( 'automatewoo/formatted_data_layer', [ 'AutomateWoo\Referrals\Workflows', 'filter_formatted_data_layer' ], 10, 2 );

		// background processes
		add_filter( 'automatewoo/background_processes/includes', [ $this, 'register_background_processes' ] );

		// Subscriptions
		if ( Integrations::subscriptions_enabled() && AW_Referrals()->options()->use_credit_on_subscription_renewals ) {

			add_filter( 'wcs_renewal_order_created', [ 'AutomateWoo\Referrals\Subscriptions', 'maybe_add_referral_credit' ], 10, 2 );

			// handle renewals
			add_filter( 'woocommerce_order_again_cart_item_data', [ 'AutomateWoo\Referrals\Subscriptions', 'maybe_store_pre_existing_renewal_credit' ] );
			add_filter( 'automatewoo/referrals/available_credit', [ 'AutomateWoo\Referrals\Subscriptions', 'maybe_add_pre_existing_renewal_credit_to_cart' ] );
			add_action( 'woocommerce_checkout_order_processed', [ 'AutomateWoo\Referrals\Subscriptions', 'maybe_reduce_renewal_order_store_credit' ], 90 );

			// ensure the renewal order total is passed through to the payment hooks, fixed in subscription v2.1
			if ( version_compare( \WC_Subscriptions::$version, '2.1.0', '<' ) ) {
				add_action( 'woocommerce_scheduled_subscription_payment', [ 'AutomateWoo\Referrals\Subscriptions', 'override_gateway_payment_method' ], 5 );
			}
		}
	}


	/**
	 * @param array $includes
	 * @return array
	 */
	function register_background_processes( $includes ) {
		$includes[ 'referrals_invite_emails' ] = AW_Referrals()->path('/includes/background-processes/invite-emails.php' );
		return $includes;
	}


	/**
	 * @param array $types
	 * @return array
	 */
	function factories( $types ) {
		$types[ 'referral' ] = 'AutomateWoo\Referrals\Referral_Factory';
		$types[ 'referral-advocate-key' ] = 'AutomateWoo\Referrals\Advocate_Key_Factory';
		$types[ 'referral-invite' ] = 'AutomateWoo\Referrals\Invite_Factory';
		return $types;
	}

}
