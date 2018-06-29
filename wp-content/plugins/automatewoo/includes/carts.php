<?php

namespace AutomateWoo;

/**
 * Carts management class
 * @class Carts
 */
class Carts {

	/** @var bool - used when restoring carts so that we don't fire unnecessary db queries */
	static $_prevent_store_cart = false;


	/**
	 * @return array
	 */
	static function get_statuses() {
		return apply_filters( 'automatewoo/cart/statuses', [
			'active' => __( 'Active', 'automatewoo' ),
			'abandoned' => __( 'Abandoned', 'automatewoo' )
		]);
	}


	/**
	 * Check if any active carts have been abandoned, runs every 2 minutes
	 */
	static function check_for_abandoned_carts() {

		/** @var Background_Processes\Abandoned_Carts $process */
		$process = Background_Processes::get( 'abandoned_carts' );
		$process->cancel_process(); // restart to avoid duplicates

		$cart_abandoned_timeout = absint( AW()->options()->abandoned_cart_timeout ); // mins

		$timeout_date = new \DateTime();
		$timeout_date->modify("-$cart_abandoned_timeout minutes" );

		$query = new Cart_Query();
		$query->where('status', 'active' )
			->where( 'last_modified', $timeout_date, '<' )
			->set_limit( 100 )
			->set_return( 'ids' );

		if ( ! $carts = $query->get_results() ) {
			return;
		}

		$process->data( $carts )->start();
	}


	/**
	 * Logic to determine whether we should save the cart on certain hooks
	 */
	static function maybe_store_cart() {

		if ( did_action( 'wp_logout' ) ) return; // don't clear the cart after logout
		if ( is_admin() ) return;
		if ( self::$_prevent_store_cart ) return;

		$last_checkout = WC()->session->get('automatewoo_checkout_processed_time');

		// ensure checkout has not been processed in the last 5 minutes
		// this is a fallback for a rare case when the cart session is not cleared after checkout
		if ( $last_checkout && $last_checkout > ( time() - 5 * MINUTE_IN_SECONDS ) ) {
			return;
		}

		if ( $user_id = AW()->session_tracker->get_detected_user_id() ) {
			self::store_user_cart( $user_id );
		}
		elseif ( $guest = AW()->session_tracker->get_current_guest() ) {
			// Store a guest cart if the guest has been stored in the database
			self::store_guest_cart( $guest );
			$guest->do_check_in();
		}
	}


	/**
	 * Attempts to update or insert carts for guests
	 *
	 * @param Guest $guest
	 * @return bool
	 */
	static function store_guest_cart( $guest ) {

		if ( ! $guest )
			return false;

		$cart = $guest->get_cart();

		if ( $cart ) {
			if ( 0 === sizeof( WC()->cart->get_cart() ) ) {
				$cart->delete();
			}
			else {
				$cart->sync();
			}
		}
		else {
			// cart is empty
			if ( 0 === sizeof( WC()->cart->get_cart() ) )
				return false;

			// create new cart
			$cart = new Cart();
			$cart->set_guest_id( $guest->get_id() );
			$cart->set_token();
			$cart->sync();
		}

		return true;
	}


	/**
	 * Attempts to store cart for a registered user whether they are logged in or not
	 *
	 * @param bool $user_id
	 * @return bool
	 */
	static function store_user_cart( $user_id = false ) {

		if ( ! $user_id ) {
			// get user
			if ( ! $user_id = AW()->session_tracker->get_detected_user_id() )
				return false;
		}

		// If user is logged out their WC cart gets emptied
		// at this point we are tracking them via cookie
		// so it doesn't make sense to clear their abandoned cart
		if ( ! is_user_logged_in() && 0 === sizeof( WC()->cart->get_cart() ) ) {
			return false;
		}

		// does this user already have a stored cart?
		$existing_cart = Cart_Factory::get_by_user_id( $user_id );


		// if cart already exists
		if ( $existing_cart ) {

			// delete cart if empty otherwise update it
			if ( 0 === sizeof( WC()->cart->get_cart() ) ) {
				$existing_cart->delete();
			}
			else {
				$existing_cart->sync();
			}

			return true;
		}
		else {
			// if the cart doesn't already exist
			// and there are no items in cart no there is no need to insert
			if ( 0 === sizeof( WC()->cart->get_cart() ) )
				return false;

			// create a new stored cart for the user
			$cart = new Cart();
			$cart->set_user_id( $user_id );
			$cart->set_token();
			$cart->sync();

			return true;
		}

	}


	/**
	 * This event will fire when an order is placed and the cart is emptied NOT when a user empties their cart.
	 */
	static function cart_emptied() {

		if ( did_action( 'wp_logout' ) ) {
			return; // don't clear cart after logout
		}

		// Ensure carts are cleared for users and guests registered at checkout
		$user_id = AW()->session_tracker->get_detected_user_id();
		$guest = AW()->session_tracker->get_current_guest();

		if ( $user_id ) {
			$cart = Cart_Factory::get_by_user_id( $user_id );
			if ( $cart ) {
				$cart->delete();
			}
		}

		if ( $guest ) {
			$guest->delete_cart();
		}
	}


	/**
	 * Ensure the stored abandoned cart is removed when an order is created.
	 * Clears even if payment has not gone through.
	 *
	 * @param $order_id
	 */
	static function empty_after_order_created( $order_id ) {

		WC()->session->set( 'automatewoo_checkout_processed_time', time() );

		$order = wc_get_order( $order_id );
		$user_id = $order->get_user_id();

		if ( $user_id ) {
			$cart = Cart_Factory::get_by_user_id( $user_id );
			if ( $cart ) {
				$cart->delete();
			}
		}

		// clear by email
		if ( $guest = Guest_Factory::get_by_email( Clean::email( Compat\Order::get_billing_email( $order ) ) ) ) {
			$guest->delete_cart();
		}

		// clear by session key
		if ( $guest = AW()->session_tracker->get_current_guest() ) {
			$guest->delete_cart();
		}
	}


	/**
	 * Restores a cart into the current session
	 * @param bool $cart_token
	 * @return bool
	 */
	static function restore_cart( $cart_token ) {

		if ( ! $cart_token ) {
			return false;
		}

		$cart = Cart_Factory::get_by_token( $cart_token );

		if ( ! $cart->exists || ! $cart->get_items() ) {
			return false;
		}

		// block cart storage hooks
		self::$_prevent_store_cart = true;
		$notices_backup = wc_get_notices();


		// merge restored items with existing
		$existing_items = WC()->cart->get_cart_for_session();

		foreach ( $cart->get_items() as $item_key => $item ) {
			// item already exists in cart
			if ( isset( $existing_items[$item_key] ) )
				continue;

			WC()->cart->add_to_cart( $item['product_id'], $item['quantity'], $item['variation_id'], $item['variation']  );
		}

		// restore coupons
		foreach ( $cart->get_coupons() as $coupon_code => $coupon_data ) {
			if ( ! WC()->cart->has_discount( $coupon_code ) ) {
				WC()->cart->add_discount( $coupon_code );
			}
		}


		// clear show notices for added coupons or products
		WC()->session->set( 'wc_notices', $notices_backup );

		// unblock cart storing and store the restored cart
		self::$_prevent_store_cart = false;
		self::maybe_store_cart();

		return true;
	}


	/**
	 * Delete stored carts older than 60 days
	 */
	static function clean_stored_carts() {
		global $wpdb;

		$delay_date = new \DateTime();
		$delay_date->modify("-60 days");

		$table = AW()->database_tables()->get_table( 'carts' );

		$wpdb->query( $wpdb->prepare("
			DELETE FROM ". $table->name . "
			WHERE last_modified < %s",
			$delay_date->format( Format::MYSQL )
		));
	}

}
