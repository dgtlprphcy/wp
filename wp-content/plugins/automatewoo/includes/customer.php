<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Customer
 * @since 3.0.0
 *
 * @property string $user_id
 * @property string $guest_id
 * @property string $id_key
 * @property string $last_purchased
 */
class Customer extends Model {

	/** @var string */
	public $table_id = 'customers';

	/** @var string  */
	public $object_type = 'customer';


	/**
	 * @param bool|int $id
	 */
	function __construct( $id = false ) {
		if ( $id ) {
			$this->get_by( 'id', $id );
		}
	}


	/**
	 * @return int
	 */
	function get_user_id() {
		return (int) $this->user_id;
	}


	/**
	 * @param $user_id
	 */
	function set_user_id( $user_id ) {
		$this->user_id = $user_id;
	}


	/**
	 * @return int
	 */
	function get_guest_id() {
		return (int) $this->guest_id;
	}


	/**
	 * @param $guest_id
	 */
	function set_guest_id( $guest_id ) {
		$this->guest_id = $guest_id;
	}


	/**
	 * @return string
	 */
	function get_key() {
		return Clean::string( $this->id_key );
	}


	/**
	 * @param string $key
	 */
	function set_key( $key ) {
		$this->id_key = $key;
	}


	/**
	 * @return bool|\DateTime
	 */
	function get_date_last_purchased() {
		return $this->get_date_column( 'last_purchased' );
	}


	/**
	 * @param \DateTime|string $date
	 */
	function set_date_last_purchased( $date ) {
		$this->set_date_column( 'last_purchased', $date );
	}


	/**
	 * @return Guest
	 */
	function get_guest() {
		return Guest_Factory::get( $this->get_guest_id() );
	}


	/**
	 * @return \WP_User
	 */
	function get_user() {
		return get_userdata( $this->get_user_id() );
	}


	/**
	 * @return bool
	 */
	function is_registered() {
		return $this->get_user_id() !== 0;
	}


	/**
	 * @return string
	 */
	function get_email() {
		return Clean::email( $this->get_linked_prop( 'email' ) );
	}


	/**
	 * @return string
	 */
	function get_first_name() {
		return $this->get_linked_prop( 'first_name' );
	}


	/**
	 * @return string
	 */
	function get_last_name() {
		return $this->get_linked_prop( 'last_name' );
	}


	/**
	 * @return string
	 */
	function get_full_name() {
		return sprintf( _x( '%1$s %2$s', 'full name', 'automatewoo' ), $this->get_first_name(), $this->get_last_name() );
	}


	/**
	 * @return string
	 */
	function get_billing_country() {
		return $this->get_linked_prop( 'billing_country' );
	}


	/**
	 * @return string
	 */
	function get_billing_state() {
		return $this->get_linked_prop( 'billing_state' );
	}


	/**
	 * @return string
	 */
	function get_billing_phone() {
		return $this->get_linked_prop( 'billing_phone' );
	}


	/**
	 * @return string
	 */
	function get_billing_postcode() {
		return $this->get_linked_prop( 'billing_postcode' );
	}


	/**
	 * @return string
	 */
	function get_billing_city() {
		return $this->get_linked_prop( 'billing_city' );
	}


	/**
	 * @return string
	 */
	function get_billing_address_1() {
		return $this->get_linked_prop( 'billing_address_1' );
	}


	/**
	 * @return string
	 */
	function get_billing_address_2() {
		return $this->get_linked_prop( 'billing_address_2' );
	}


	/**
	 * @return string
	 */
	function get_billing_company() {
		return $this->get_linked_prop( 'billing_company' );
	}


	/**
	 * @param bool $include_name
	 * @return array
	 */
	function get_address( $include_name = true ) {
		$args = [];

		if ( $include_name ) {
			$args['first_name'] = $this->get_first_name();
			$args['last_name'] = $this->get_last_name();
		}

		$args['company'] = $this->get_billing_company();
		$args['address_1'] = $this->get_billing_address_1();
		$args['address_2' ] = $this->get_billing_address_2();
		$args['city'] = $this->get_billing_city();
		$args['state'] = $this->get_billing_state();
		$args['postcode'] = $this->get_billing_postcode();
		$args['country'] = $this->get_billing_country();

		return $args;
	}

	/**
	 * @param bool $include_name
	 * @return string
	 */
	function get_formatted_billing_address( $include_name = true ) {
		return WC()->countries->get_formatted_address( $this->get_address( $include_name ) );
	}


	/**
	 * It's worth noting that guest meta does not become user meta when a guest creates an account
	 *
	 * @param string $key
	 * @return mixed
	 */
	function get_meta( $key ) {

		if ( ! $key ) return false;

		if ( $this->is_registered() ) {
			return get_user_meta( $this->get_user_id(), $key, true );
		}
		elseif ( $guest = $this->get_guest() ) {
			return $guest->get_meta( $key );
		}
	}


	/**
	 * @param string $key
	 * @param $value
	 * @return mixed
	 */
	function update_meta( $key, $value ) {

		if ( ! $key ) return false;

		if ( $this->is_registered() ) {
			update_user_meta( $this->get_user_id(), $key, $value );
		}
		elseif ( $guest = $this->get_guest() ) {
			$guest->update_meta( $key, $value );
		}
	}


	/**
	 * @return int
	 */
	function get_order_count() {
		if ( $this->is_registered() ) {
			return aw_get_customer_order_count( $this->get_user_id() );
		}
		elseif ( $this->get_guest() ) {
			return aw_get_order_count_by_email( $this->get_guest()->get_email() );
		}
		return 0;
	}


	/**
	 * @return int
	 */
	function get_total_spent() {
		if ( $this->is_registered() ) {
			return wc_get_customer_total_spent( $this->get_user_id() );
		}
		elseif ( $this->get_guest() ) {
			return aw_get_total_spent_by_email( $this->get_guest()->get_email() );
		}
		return 0;
	}


	/**
	 * @return string
	 */
	function get_role() {
		if ( $this->is_registered() && $user = $this->get_user() ) {
			return current( $user->roles );
		}
		else {
			return 'guest';
		}
	}


	/**
	 * @return string
	 */
	function get_language() {

		if ( ! Language::is_multilingual() ) {
			return '';
		}

		if ( $this->is_registered() ) {
			return Language::get_user_language( $this->get_user() );
		}
		else {
			return Language::get_guest_language( $this->get_guest() );
		}
	}


	/**
	 * @param $prop
	 * @return mixed
	 */
	function get_linked_prop( $prop ) {

		$guest = false;
		$user = false;

		if ( $this->is_registered() ) {
			if ( ! $user = $this->get_user() ) {
				return false;
			}
		}
		else {
			if ( ! $guest = $this->get_guest() ) {
				return false;
			}
		}

		switch ( $prop ) {
			case 'email':
				return $this->is_registered() ? $user->user_email : $guest->get_email();
				break;
			case 'first_name':
				return $this->is_registered() ? $user->first_name : $guest->get_first_name();
				break;
			case 'last_name':
				return $this->is_registered() ? $user->last_name : $guest->get_last_name();
				break;
			case 'billing_country':
				return $this->is_registered() ? $user->billing_country : $guest->get_country();
				break;
			case 'billing_state':
				return $this->is_registered() ? $user->billing_state : $guest->get_state();
				break;
			case 'billing_phone':
				return $this->is_registered() ? $user->billing_phone : $guest->get_phone();
				break;
			case 'billing_company':
				return $this->is_registered() ? $user->billing_company : $guest->get_company();
				break;
			case 'billing_address_1':
				return $this->is_registered() ? $user->billing_address_1 : $guest->get_address_1();
				break;
			case 'billing_address_2':
				return $this->is_registered() ? $user->billing_address_2 : $guest->get_address_2();
				break;
			case 'billing_postcode':
				return $this->is_registered() ? $user->billing_postcode : $guest->get_postcode();
				break;
			case 'billing_city':
				return $this->is_registered() ? $user->billing_city : $guest->get_city();
				break;
		}

	}


	/**
	 * @param string $status (hold|approve|all)
	 * @return int
	 */
	function get_review_count( $status = 'approve' ) {

		$cache_key = "customer_review_count_$status";

		if ( Temporary_Data::exists( $cache_key, $this->get_id() ) ) {
			return Temporary_Data::get( $cache_key, $this->get_id() );
		}

		$query_args = [
			'post_type' => 'product',
			'status' => $status,
			'count' => true
		];

		if ( $this->is_registered() ) {
			$query_args['user_id'] = $this->get_user_id();
		}
		else {
			$query_args['author_email'] = $this->get_email();
		}

		$comment_count = get_comments( $query_args );

		Temporary_Data::set( $cache_key, $this->get_id(), $comment_count );

		return $comment_count;
	}


}

