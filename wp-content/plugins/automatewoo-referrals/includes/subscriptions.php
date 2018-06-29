<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Compat;

/**
 * Allow a users referral credit to be applied to a recurring subscription payment
 *
 * @class Subscriptions
 * @since 1.2
 */
class Subscriptions {

	/**
	 * @param $order \WC_Order
	 * @param $subscription \WC_Subscription
	 *
	 * @return \WC_Order
	 */
	static function maybe_add_referral_credit( $order, $subscription ) {

		if ( ! $subscription->payment_method_supports( 'subscription_amount_changes' ) )
			return $order;

		$credit = AW_Referrals()->store_credit->get_available_credit( $order->get_user_id() );

		if ( ! $credit ) {
			return $order;
		}

		AW_Referrals()->store_credit->add_store_credit_to_order( $order, $credit );

		AW_Referrals()->store_credit->reduce_store_credit_in_order( Compat\Order::get_id( $order ) );

		return $order;
	}


	/**
	 * @param float $amount
	 * @return float
	 */
	static function maybe_add_pre_existing_renewal_credit_to_cart( $amount ) {

		if ( WC()->cart && $item = wcs_cart_contains_renewal() ) {

			if ( isset( $item['subscription_renewal']['aw_pre_existing_credit'] ) ) {
				$amount += (float) $item['subscription_renewal']['aw_pre_existing_credit'];
			}
		}

		return $amount;
	}


	/**
	 * @param array $item
	 * @return array
	 */
	static function maybe_store_pre_existing_renewal_credit( $item ) {

		if ( ! isset( $item['subscription_renewal']['renewal_order_id'] ) ) {
			return $item;
		}

		if ( ! $order = wc_get_order( $item['subscription_renewal']['renewal_order_id'] ) ) {
			return $item;
		}

		$item['subscription_renewal']['aw_pre_existing_credit'] = AW_Referrals()->store_credit->get_store_credit_amount_in_order( $order );

		return $item;
	}


	/**
	 * Reduce store credit for renewal orders, as the store credit may have already been applied to the original order
	 * @param $order_id
	 */
	static function maybe_reduce_renewal_order_store_credit( $order_id ) {

		if ( ! $order = wc_get_order( $order_id ) ) {
			return;
		}

		if ( ! $cart_item = wcs_cart_contains_renewal() ) {
			return;
		}

		// don't check the _aw_referrals_credit_processed meta as this will probably already be true from the original order
		if ( ! isset( $cart_item['subscription_renewal']['aw_pre_existing_credit'] ) ) {
			return;
		}

		$pre_existing_credit = $cart_item['subscription_renewal']['aw_pre_existing_credit'];
		$total_credit_used = AW_Referrals()->store_credit->get_store_credit_amount_in_order( $order );

		AW_Referrals()->store_credit->reduce_advocate_store_credit( $order->get_user_id(), $total_credit_used - $pre_existing_credit );

		Compat\Order::update_meta( $order, '_aw_referrals_credit_processed', true );
	}


	/**
	 * Override WC_Subscriptions_Payment_Gateways::gateway_scheduled_subscription_payment()
	 */
	static function override_gateway_payment_method() {
		remove_action( 'woocommerce_scheduled_subscription_payment', [ 'WC_Subscriptions_Payment_Gateways', 'gateway_scheduled_subscription_payment' ], 10 );
		add_action( 'woocommerce_scheduled_subscription_payment', [ __CLASS__, 'gateway_scheduled_subscription_payment' ], 10 );
	}


	/**
	 * Replacement for WC_Subscriptions_Payment_Gateways::gateway_scheduled_subscription_payment()
	 */
	static function gateway_scheduled_subscription_payment( $subscription_id, $deprecated = null ) {

		// Passing the old $user_id/$subscription_key parameters
		if ( null != $deprecated ) {
			_deprecated_argument( __METHOD__, '2.0', 'Second parameter is deprecated' );
			$subscription = wcs_get_subscription_from_key( $deprecated );
		} else {
			$subscription = wcs_get_subscription( $subscription_id );
		}

		if ( ! $subscription->is_manual() && $subscription->get_total() > 0 && ! empty( $subscription->payment_method ) ) {

			/** @var $last_renewal_order\WC_Order */
			$last_renewal_order = $subscription->get_last_order( 'all' );

			if ( ! empty( $last_renewal_order ) ) {
				if ( $last_renewal_order->needs_payment() ) {
					do_action( 'woocommerce_scheduled_subscription_payment_' . $subscription->payment_method, $last_renewal_order->get_total(), $last_renewal_order );
				}
				else {
					$last_renewal_order->payment_complete();
				}
			}
		}
	}

}
