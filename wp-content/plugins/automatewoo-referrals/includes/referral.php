<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;
use AutomateWoo\Format;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Referral
 *
 * @property $id int
 * @property $advocate_id int
 * @property $order_id int
 * @property $user_id int
 * @property $date
 * @property $offer_type
 * @property $offer_amount
 * @property $reward_type
 * @property $reward_amount
 * @property $reward_amount_remaining
 * @property $status string
 */
class Referral extends AutomateWoo\Model {

	/** @var string  */
	public $table_id = 'referrals';

	/** @var string  */
	public $object_type = 'referral';

	/** @var Advocate|null|false */
	private $advocate;


	/**
	 * @param $id
	 */
	function __construct( $id = false ) {
		if ( $id ) $this->get_by( 'id', $id );
	}


	/**
	 * @return string;
	 */
	function get_status() {
		return $this->status;
	}


	/**
	 * @param string|array $status
	 * @return bool
	 */
	function has_status( $status ) {
		return in_array( $this->get_status(), (array) $status );
	}


	/**
	 * @return int
	 */
	function get_advocate_id() {
		return absint( $this->advocate_id );
	}


	/**
	 * @param $id
	 */
	function set_advocate_id( $id ) {
		$this->advocate_id = $id;
	}


	/**
	 * @return string
	 */
	function get_reward_type() {
		return $this->reward_type;
	}


	/**
	 * @param $type string
	 */
	function set_reward_type( $type ) {
		$this->reward_type = $type;
	}


	/**
	 * @return Advocate|false
	 * TODO cache elsewhere
	 */
	function get_advocate() {
		if ( ! isset( $this->advocate ) ) {
			$this->advocate = new Advocate( $this->get_advocate_id() );

			if ( ! $this->advocate->exists )
				$this->advocate = false;
		}

		return $this->advocate;
	}


	/**
	 * @return int
	 */
	function get_order_id() {
		return absint( $this->order_id );
	}


	/**
	 * @param $id
	 */
	function set_order_id( $id ) {
		$this->order_id = $id;
	}


	/**
	 * @param $id
	 */
	function set_user_id( $id ) {
		$this->user_id = $id;
	}


	/**
	 * @return int
	 */
	function get_user_id() {
		return absint( $this->user_id );
	}


	/**
	 * @param \DateTime $datetime
	 */
	function set_date( $datetime ) {
		$this->set_date_column( 'date', $datetime );
	}


	/**
	 * @return bool|\DateTime
	 */
	function get_date() {
		return $this->get_date_column( 'date' );
	}


	/**
	 * @param string $amount
	 */
	function set_initial_reward_amount( $amount ) {
		$amount = Format::decimal( $amount );
		$this->reward_amount = $amount;
		$this->reward_amount_remaining = $amount;
	}


	/**
	 * @return \WC_Order|false
	 */
	function get_order() {
		return wc_get_order( $this->get_order_id() );
	}


	/**
	 * @return int
	 */
	function get_discounted_amount() {

		$order = $this->get_order();

		foreach ( $order->get_items( 'coupon' ) as $coupon ) {
			if ( Coupons::is_valid_referral_coupon( $coupon['name'] ) ) {
				return $coupon['discount_amount'];
			}
		}

		return 0;
	}


	/**
	 * @return string|false
	 */
	function get_customer_ip_address() {

		if ( AW_Referrals()->options()->get_reward_event() === 'purchase' ) {
			if ( $order = $this->get_order() ) {
				return AutomateWoo\Compat\Order::get_customer_ip( $order );
			}
		}
		elseif ( AW_Referrals()->options()->get_reward_event() === 'signup' ) {
			if ( $user = $this->get_customer() ) {
				return get_user_meta( $user->ID, '_automatewoo_referral_ip_address', true );
			}
		}

		return false;
	}


	/**
	 * @return string|false
	 */
	function get_advocate_ip_address() {

		if ( $advocate = $this->get_advocate() ) {
			return $advocate->get_stored_ip();
		}
		return false;
	}


	/**
	 * @return bool
	 */
	function is_customer_registered_user() {
		if ( $order = $this->get_order() ) {
			return $order->get_user_id() !== 0;
		}
		return false;
	}


	/**
	 * If customer is a user return WP_User. False for guests
	 *
	 * @return \WP_User|false
	 */
	function get_customer() {
		if ( AW_Referrals()->options()->get_reward_event() === 'purchase' ) {
			if ( $order = $this->get_order() ) {
				return $order->get_user();
			}
		}
		elseif ( AW_Referrals()->options()->get_reward_event() === 'signup' ) {
			return get_user_by( 'id', $this->get_user_id() );
		}

		return false;
	}


	/**
	 * @return string|false
	 */
	function get_customer_name() {

		if ( $order = $this->get_order() ) {
			if ( AutomateWoo\Compat\Order::get_billing_first_name( $order ) ) {
				return $order->get_formatted_billing_full_name();
			}

			if ( $user = $order->get_user() ) {
				return sprintf( _x( '%1$s %2$s', 'full name', 'automatewoo' ),  $user->first_name, $user->last_name );
			}
		}

		return false;
	}


	/**
	 * @return string|false
	 */
	function get_advocate_name() {
		if ( $advocate = $this->get_advocate() ) {
			return $advocate->get_user()->first_name . ' ' . $advocate->get_user()->last_name;
		}
		return false;
	}


	/**
	 * @return string|false
	 */
	function get_status_name() {
		$statuses = AW_Referrals()->get_referral_statuses();

		if ( empty( $statuses[ $this->status ] ) )
			return false;

		return $statuses[ $this->status ];
	}


	/**
	 * @return float
	 */
	function get_reward_amount() {
		return Format::round( $this->reward_amount );
	}


	/**
	 * @return float
	 */
	function get_reward_amount_remaining() {
		return Format::round( $this->reward_amount_remaining );
	}



	/**
	 * @return bool
	 */
	function is_reward_store_credit() {
		return in_array( $this->get_reward_type(), [ 'credit', 'credit_percentage' ] );
	}


	/**
	 * @return bool
	 */
	function ip_addresses_match() {

		if ( ! $this->get_customer_ip_address() ) {
			return false;
		}

		return $this->get_advocate_ip_address() == $this->get_customer_ip_address();
	}


	/**
	 * @param $new_status
	 */
	function update_status( $new_status ) {

		if ( $new_status == $this->status ) {
			return; // bail if status has not changed
		}

		$old_status = $this->status;
		$this->status = $new_status;
		$this->save();

		do_action( 'automatewoo/referrals/referral_status_changed', $this, $old_status, $new_status );
	}


	/**
	 *
	 */
	function is_potential_fraud() {

		if ( $this->ip_addresses_match() ) {
			return true;
		}

		return false;
	}


}