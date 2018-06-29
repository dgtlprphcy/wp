<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Compat;
use AutomateWoo\Clean;
use AutomateWoo\Format;

/**
 * @class Referral_Manager
 */
class Referral_Manager {


	/**
	 * @param string|bool $status
	 * @return int
	 */
	static function get_referrals_count( $status = false ) {

		$query = new Referral_Query();

		if ( $status ) {
			if ( ! array_key_exists( $status, AW_Referrals()->get_referral_statuses() ) ) {
				return 0;
			}
			$query->where( 'status', $status );
		}

		return $query->get_count();
	}


	/**
	 * @param $order_id
	 */
	static function check_order_for_referral( $order_id ) {

		if ( ! $order = wc_get_order( $order_id ) )
			return;

		if ( Compat\Order::get_meta( $order, '_aw_referral_processed' ) )
			return;

		$advocate_id = false;


		if ( AW_Referrals()->options()->type === 'coupon' ) {
			$advocate_id = self::check_for_coupon_based_referral( $order );
		}
		elseif ( AW_Referrals()->options()->type === 'link' ) {
			$advocate_id = self::check_for_link_based_referral( $order );
		}


		if ( $advocate_id ) {
			$advocate = new Advocate( $advocate_id );
			$referral = self::create_referral_for_purchase( $order, $advocate );

			if ( $referral ) {
				Compat\Order::update_meta( $order, '_aw_referral_id', $referral->get_id() );
			}
		}

		Compat\Order::update_meta( $order, '_aw_referral_processed', true );
	}


	/**
	 * @param int $user_id
	 */
	static function check_signup_for_referral( $user_id ) {

		$user = get_user_by( 'id', $user_id );

		update_user_meta( $user_id, '_automatewoo_referral_ip_address', \WC_Geolocation::get_ip_address() );

		if ( AW_Referrals()->options()->type !== 'link' ) {
			return;
		}

		$advocate_key = self::get_advocate_key_from_cookie();

		if ( $advocate_key ) {

			$advocate = new Advocate( $advocate_key->get_advocate_id() );

			if ( $advocate ) {
				self::create_referral_for_signup( $user, $advocate );
			}
		}

	}


	/**
	 * Coupons have already passed validation at this point
	 *
	 * @param \WC_Order $order
	 * @return false|int
	 */
	static function check_for_coupon_based_referral( $order ) {
		foreach ( $order->get_items( 'coupon' ) as $coupon ) {
			if ( $advocate_id = Coupons::is_valid_referral_coupon( $coupon['name'] ) ) {
				return $advocate_id;
			}
		}
		return false;
	}


	/**
	 * Retrieve and reset advocate key from cookie
	 * @return Advocate_Key|bool
	 */
	static function get_advocate_key_from_cookie() {

		if ( empty( $_COOKIE['aw_referral_key'] ) )
			return false;

		$advocate_key = Clean::string( $_COOKIE['aw_referral_key'] );

		// clear the coupon validation as referrals are only valid for the first order
		// otherwise this validation will run on the customers next order unnecessarily
		if ( ! headers_sent() ) {
			wc_setcookie( 'aw_referral_key', '', time() - HOUR_IN_SECONDS );
		}

		$key_object = AW_Referrals()->get_advocate_key_by_key( $advocate_key );

		if ( ! $key_object || $key_object->is_expired() )
			return false;

		return $key_object;
	}


	/**
	 * Links have NOT passed any validation at this point
	 *
	 * @param \WC_Order $order
	 * @return false|int
	 */
	static function check_for_link_based_referral( $order ) {

		$key = self::get_advocate_key_from_cookie();

		// validate the referral key
		if ( self::is_valid_referral_for_order( $order, $key ) ) {
			return $key->get_advocate_id();
		}
	}


	/**
	 * @param Advocate $advocate
	 * @return Referral
	 */
	static function create_base_referral( $advocate ) {

		$referral = new Referral();

		$referral->set_advocate_id( $advocate->get_id() );
		$referral->set_date( new \DateTime() );
		$referral->set_reward_type( AW_Referrals()->options()->reward_type );

		return $referral;
	}


	/**
	 * @param $order \WC_Order
	 * @param $advocate Advocate
	 * @return Referral
	 */
	static function create_referral_for_purchase( $order, $advocate ) {

		$referral = self::create_base_referral( $advocate );

		$referral->set_order_id( Compat\Order::get_id( $order ) );
		$referral->set_user_id( $order->get_user_id() );

		if ( AW_Referrals()->options()->type === 'coupon' ) {
			$referral->offer_type = AW_Referrals()->options()->offer_type;
			$referral->offer_amount = AW_Referrals()->options()->offer_amount;
		}

		$reward_amount = self::get_referral_reward_amount( $advocate, $order );

		$referral->set_initial_reward_amount( $reward_amount );

		$referral->save();

		if ( $referral->is_potential_fraud() ) {
			$referral->update_status( 'potential-fraud' );
		}
		else {
			$referral->update_status( 'pending' );
		}

		do_action( 'automatewoo/referrals/referral_created', $referral );

		return $referral;
	}



	/**
	 * @param $user \WP_User
	 * @param $advocate Advocate
	 * @return Referral|false
	 */
	static function create_referral_for_signup( $user, $advocate ) {

		if ( AW_Referrals()->options()->type !== 'link' ) {
			return false;
		}

		$referral = self::create_base_referral( $advocate );

		$referral->set_user_id( $user->ID );

		$reward_amount = self::get_referral_reward_amount( $advocate );

		$referral->set_initial_reward_amount( $reward_amount );

		$referral->save();

		if ( $referral->is_potential_fraud() ) {
			$referral->update_status( 'potential-fraud' );
		}
		elseif ( AW_Referrals()->options()->auto_approve ) {
			$referral->update_status( 'approved' );
		}
		else {
			$referral->update_status( 'pending' );
		}

		do_action( 'automatewoo/referrals/referral_created', $referral );

		return $referral;
	}


	/**
	 * @param Advocate $advocate
	 * @param bool|\WC_Order $order
	 * @return float
	 */
	static function get_referral_reward_amount( $advocate, $order = false ) {

		$reward_amount = 0;

		switch ( AW_Referrals()->options()->reward_type ) {

			case 'credit':
				$reward_amount = AW_Referrals()->options()->reward_amount;
				break;

			case 'credit_percentage':
				if ( $order ) {
					$reward_percentage = AW_Referrals()->options()->reward_amount;
					$reward_amount = $order->get_total() * $reward_percentage / 100;
				}
				break;
		}

		return Format::round( apply_filters( 'automatewoo/referrals/reward_amount', $reward_amount, $advocate, $order ) );
	}


	/**
	 * @param $order_id
	 * @param $old_status
	 * @param $new_status
	 */
	static function update_referral_status_on_order_status_change( $order_id, $old_status, $new_status ) {

		if ( ! $order = wc_get_order( $order_id ) )
			return;

		if ( ! $referral_id = Compat\Order::get_meta( $order, '_aw_referral_id' ) )
			return;

		$referral = AW_Referrals()->get_referral( $referral_id );

		if ( ! $referral )
			return;

		switch ( $new_status ) {

			case 'cancelled':
			case 'failed':
			case 'refunded':
				$referral->update_status( 'rejected' );
				break;

			case 'pending':
			case 'processing':
			case 'on-hold':
				if ( $referral->is_potential_fraud() ) {
					$referral->update_status( 'potential-fraud' );
				}
				else {
					$referral->update_status( 'pending' );
				}
				break;

			case 'completed':
				if ( AW_Referrals()->options()->auto_approve && ! $referral->is_potential_fraud() ) {
					$referral->update_status( 'approved' );
				}
				break;
		}
	}



	/**
	 * Validation for link tracking referrals, at this point the referral order has been created but not processed
	 *
	 * @param \WC_Order $order
	 * @param Advocate_Key $advocate_key
	 * @return bool
	 */
	static function is_valid_referral_for_order( $order, $advocate_key ) {

		if ( ! $advocate_key || ! $order ) {
			return false;
		}

		if ( $order->get_user_id() !== 0 ) {

			if ( $advocate_key->get_advocate_id() == $order->get_user_id() ) {
				return false; // advocate using their own coupon
			}

			if ( AW_Referrals()->options()->allow_existing_customer_referrals ) {

				// existing customer referrals are allowed, but limit to 1 referred order per customer
				if ( ! self::is_referred_order_customers_first( $order ) ) {
					return false;
				}

			}
			else {
				// any previous orders for the user, not including this one?
				if ( aw_get_customer_order_count( $order->get_user_id() ) > 1 )
					return false;
			}
		}
		else {

			if ( AW_Referrals()->options()->allow_existing_customer_referrals ) {

				if ( ! self::is_referred_order_customers_first( $order ) ) {
					return false;
				}

			}
			else {
				// any previous orders with the same email address?
				$orders = wc_get_orders([
					'customer' => Compat\Order::get_billing_email( $order ),
					'limit' => 1,
					'return' => 'ids',
					'exclude' => [ Compat\Order::get_id( $order ) ]
				]);

				if ( ! empty( $orders ) )
					return false;
			}
		}

		return true;
	}


	/**
	 * Is the order the customers first/only referred order
	 *
	 * @param \WC_Order $order
	 * @return bool
	 */
	static function is_referred_order_customers_first( $order ) {

		$customer = $order->get_user_id() ? $order->get_user_id() : Compat\Order::get_billing_email( $order );

		$referrals = AW_Referrals()->get_referred_orders_by_customer( $customer );

		aw_array_remove_value( $referrals, Compat\Order::get_id( $order ) );

		return count( $referrals ) === 0;
	}

}