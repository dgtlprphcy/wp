<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Compat;
use AutomateWoo\Clean;

/**
 * @class Coupons
 */
class Coupons {

	const E_INVALID = 100;
	const E_COUPON_IS_OWN = 101;
	const E_CUSTOMER_IS_EXISTING = 102;
	const E_CUSTOMER_ALREADY_REFERRED = 103;


	/**
	 * A prefix is required to distinguish normal coupons from referral coupons
	 * @return string
	 */
	static function get_prefix() {
		return apply_filters( 'automatewoo/referrals/coupon_prefix', 'REF' );
	}


	/**
	 * A prefix is required to distinguish normal coupons from referral coupons
	 * @int string
	 */
	static function get_key_length() {
		return (int) apply_filters( 'automatewoo/referrals/coupon_key_length', 10 );
	}


	/**
	 * @param $coupon_data
	 * @param $coupon_code
	 * @return array
	 */
	static function catch_referral_coupons( $coupon_data, $coupon_code ) {

		if ( ! self::is_valid_referral_coupon( $coupon_code ) ) {
			return $coupon_data;
		}

		$coupon_data = [];

		switch ( AW_Referrals()->options()->offer_type ) {

			case 'coupon_discount':
				$coupon_data['discount_type'] = 'fixed_cart';
				break;

			case 'coupon_percentage_discount':
				$coupon_data['discount_type'] = 'percent_product';
				break;
		}

		$coupon_data['minimum_amount'] = Clean::string( AW_Referrals()->options()->offer_min_purchase );

		if ( version_compare( WC()->version, '3.0', '<' ) ) {
			$coupon_data['coupon_amount'] = Clean::string( AW_Referrals()->options()->offer_amount );
			$coupon_data['individual_use'] = 'yes';
		}
		else {
			$coupon_data['id'] = true;
			$coupon_data['amount'] = Clean::string( AW_Referrals()->options()->offer_amount );
			$coupon_data['individual_use'] = true;
		}

		return apply_filters( 'automatewoo/referrals/coupon_data', $coupon_data );
	}


	/**
	 * @param $valid bool
	 * @param $coupon \WC_Coupon
	 * @return bool
	 */
	static function validate_referral_coupon( $valid, $coupon ) {

		if ( ! $advocate_id = self::is_valid_referral_coupon( Compat\Coupon::get_code( $coupon ) ) )
			return $valid; // not a referral coupon

		if ( AW()->session_tracker->get_detected_user_id() ) {

			$validate = self::validate_referral_for_customer_id( AW()->session_tracker->get_detected_user_id(), $advocate_id );

			if ( is_wp_error( $validate ) ) {
				wc_add_notice( $validate->get_error_message(), 'error' );
				return false;
			}
		}
		else {
			// can't validate without the customer's email
		}

		return $valid;
	}


	/**
	 * @param $err
	 * @param int $err_code
	 * @param $coupon
	 * @return mixed
	 */
	static function filter_coupon_errors( $err, $err_code, $coupon ) {

		switch ( $err_code ) {
			case \WC_Coupon::E_WC_COUPON_INVALID_FILTERED:

				if ( self::is_valid_referral_coupon( Compat\Coupon::get_code( $coupon ) ) ) {
					$err = false; // error messages have been already added
				}

				break;

			case \WC_Coupon::E_WC_COUPON_NOT_EXIST:

				if ( self::matches_referral_coupon_pattern( Compat\Coupon::get_code( $coupon ) ) ) {
					$err = __('Your coupon is not valid. It may have expired.', 'automatewoo' );
				}

				break;
		}

		return $err;
	}



	/**
	 * @param $coupon_code
	 * @return bool
	 */
	static function matches_referral_coupon_pattern( $coupon_code ) {

		$matches_prefix = stripos( $coupon_code, self::get_prefix() ) === 0;

		// ignore length check if zero
		if ( self::get_key_length() > 0 ) {
			$matches_length = strlen( $coupon_code ) == ( strlen( self::get_prefix() ) + self::get_key_length() );
		}
		else {
			$matches_length = true;
		}

		return $matches_length && $matches_prefix;
	}


	/**
	 * Return advocate id if coupon exists
	 *
	 * @param $coupon_code
	 * @return false|int
	 */
	static function is_valid_referral_coupon( $coupon_code ) {

		if ( ! self::matches_referral_coupon_pattern( $coupon_code ) ) {
			 return false;
		}

		$advocate_key = substr( $coupon_code, strlen( self::get_prefix() ) );

		$key_object = AW_Referrals()->get_advocate_key_by_key( $advocate_key );

		if ( ! $key_object || $key_object->is_expired() )
			return false;

		return $key_object->get_advocate_id();
	}



	/**
	 * Check for user coupons (now that we have billing email). If a coupon is invalid, add an error.
	 *
	 * @param array $posted
	 */
	static function check_customer_coupons( $posted ) {

		if ( empty( WC()->cart->applied_coupons ) )
			return;

		foreach ( WC()->cart->applied_coupons as $code ) {

			if ( ! $advocate_id = self::is_valid_referral_coupon( $code ) )
				continue;

			$coupon = new \WC_Coupon( $code );

			if ( ! $coupon->is_valid() )
				return;

			$error = false;

			// support checkouts with no billing email field
			if ( $billing_email = sanitize_email( $posted['billing_email'] ) ) {

				$validate = self::validate_referral_for_customer_email( $billing_email, $advocate_id );

				if ( is_wp_error( $validate ) ) {
					wc_add_notice( $validate->get_error_message(), 'error' );
					$error = true;
				}
			}


			// validate by customer ID if not already invalid
			if ( ! $error && AW()->session_tracker->get_detected_user_id() ) {

				$validate = self::validate_referral_for_customer_id( AW()->session_tracker->get_detected_user_id(), $advocate_id );

				if ( is_wp_error( $validate ) ) {
					wc_add_notice( $validate->get_error_message(), 'error' );
					$error = true;
				}
			}


			if ( $error ) {
				// Remove the coupon
				WC()->cart->remove_coupon( $code );

				// Flag totals for refresh
				WC()->session->set( 'refresh_totals', true );
			}
		}
	}



	/**
	 * @param $customer_id
	 * @param $advocate_id
	 * @return \WP_Error|true
	 */
	static function validate_referral_for_customer_id( $customer_id, $advocate_id ) {

		$valid = true;

		try {

			if ( ! $advocate_id || ! $customer_id ) {
				throw new \Exception( self::get_error( self::E_INVALID ) );
			}

			if ( $advocate_id == $customer_id ) {
				throw new \Exception( self::get_error( self::E_COUPON_IS_OWN ) );
			}

			if ( AW_Referrals()->options()->allow_existing_customer_referrals ) {
				if ( count( AW_Referrals()->get_referred_orders_by_customer( $customer_id ) ) !== 0 ) {
					throw new \Exception( self::get_error( self::E_CUSTOMER_ALREADY_REFERRED ) );
				}
			}
			else {
				// previous orders for the user?
				if ( aw_get_customer_order_count( $customer_id ) !== 0 ) {
					throw new \Exception( self::get_error( self::E_CUSTOMER_IS_EXISTING ) );
				}
			}
		}
		catch ( \Exception $e ) {
			$valid = new \WP_Error( 'coupon-invalid', $e->getMessage() );
		}

		return apply_filters( 'automatewoo/referrals/validate_coupon_for_user', $valid, $customer_id, $advocate_id );
	}


	/**
	 * @param $customer_email
	 * @param $advocate_id
	 * @return bool|\WP_Error
	 */
	static function validate_referral_for_customer_email( $customer_email, $advocate_id ) {

		$valid = true;

		try {

			if ( ! $advocate_id || ! $customer_email ) {
				throw new \Exception( self::get_error( self::E_INVALID ) );
			}

			$customer_user = get_user_by( 'email', $customer_email );

			if ( $customer_user ) {
				// if email matches existing customer validated by ID
				return self::validate_referral_for_customer_id( $customer_user->ID, $advocate_id );
			}

			$advocate_user = get_user_by( 'id', $advocate_id );

			if ( $customer_email == $advocate_user->user_email ) {
				throw new \Exception( self::get_error( self::E_COUPON_IS_OWN ) );
			}


			if ( AW_Referrals()->options()->allow_existing_customer_referrals ) {
				if ( count( AW_Referrals()->get_referred_orders_by_customer( $customer_email ) ) !== 0 ) {
					throw new \Exception( self::get_error( self::E_CUSTOMER_ALREADY_REFERRED ) );
				}
			}
			else {
				// previous orders with the same email address?
				$orders = wc_get_orders([
					'customer' => $customer_email,
					'limit' => 1,
					'return' => 'ids'
				]);

				if ( ! empty( $orders ) ) {
					throw new \Exception( self::get_error( self::E_CUSTOMER_IS_EXISTING ) );
				}
			}

		}
		catch ( \Exception $e ) {
			$valid = new \WP_Error( 'coupon-invalid', $e->getMessage() );
		}

		return apply_filters( 'automatewoo/referrals/validate_coupon_for_guest', $valid, $customer_email, $advocate_id );
	}


	/**
	 * @param $error_code
	 * @return string
	 */
	static function get_error( $error_code = 100 ) {

		switch ( $error_code ) {

			case self::E_INVALID:
				$message = __( 'There is a problem with this referral coupon.', 'automatewoo-referrals' );
				break;

			case self::E_COUPON_IS_OWN:
				$message = __( 'It appears you are trying to use your own referral coupon.', 'automatewoo-referrals' );
				break;

			case self::E_CUSTOMER_IS_EXISTING:
				$message = __( 'You don\'t appear to be a new customer which is required to use a referral coupon.', 'automatewoo-referrals' );
				break;

			case self::E_CUSTOMER_ALREADY_REFERRED:
				$message = __( 'It appears you have already used a referral coupon before.', 'automatewoo-referrals' );
				break;

			default:
				$message = '';
				break;
		}

		return $message;
	}

}
