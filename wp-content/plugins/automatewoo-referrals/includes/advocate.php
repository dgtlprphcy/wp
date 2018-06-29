<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Advocate
 */
class Advocate {

	/** @var \WP_User */
	private $user;

	/** @var bool  */
	public $exists = false;


	/**
	 * Immediately loads the advocate user data or sets up the user as an advocate
	 * @param int|bool|\WP_User $user
	 */
	function __construct( $user ) {

		if ( $user instanceof \WP_User ) {
			$this->user = $user;
		}

		if ( is_numeric( $user ) ) {
			$this->user = get_user_by( 'id', $user );
		}

		if ( $this->user ) {
			$this->exists = true;
		}

	}


	/**
	 * The advocate ID is the same as the advocate's user ID
	 * @return int
	 */
	function get_id() {
		return $this->user ? $this->user->ID : 0;
	}


	/**
	 * @return \WP_User
	 */
	function get_user() {
		return $this->user;
	}


	/**
	 * @return int
	 */
	function get_user_id() {
		return $this->get_id();
	}


	/**
	 * @return string|false
	 */
	function get_name() {
		return $this->user->first_name . ' ' . $this->user->last_name;
	}

	/**
	 * @return string|false
	 */
	function get_first_name() {
		return $this->user->first_name;
	}


	/**
	 * @return string|false
	 */
	function get_last_name() {
		return $this->user->last_name;
	}


	/**
	 * @return string
	 */
	function get_email() {
		return $this->user->user_email;
	}


	/**
	 * @return false|string
	 */
	function get_advocate_key() {

		// non persistently cached
		$cache = AutomateWoo\Temporary_Data::get( 'advocate_current_key', $this->get_id() );

		if ( $cache ) {
			return $cache;
		}

		// find a key for the advocate
		$query = new Advocate_Key_Query();
		$query->where( 'advocate_id', $this->get_id() );
		$query->set_limit( 1 );

		if ( AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			// if advocate keys are set to expire make sure the key is not stale
			$expiry = new \DateTime();
			$expiry->modify( apply_filters( 'automatewoo/referrals/advocate_key_stale_timeout', '-24 hours' ) );

			$query->where( 'created', $expiry, '>' );
		}

		$results = $query->get_results();

		if ( $results ) {
			$key = current( $results )->advocate_key;
		}
		else {
			$key = $this->create_advocate_key();
		}

		AutomateWoo\Temporary_Data::set( 'advocate_current_key', $this->get_id(), $key );

		return $key;
	}


	/**
	 * @return string
	 */
	private function create_advocate_key() {

		$key = $this->generate_advocate_key();

		$object = new Advocate_Key();
		$object->advocate_id = $this->get_id();
		$object->set_date_created( new \DateTime() );
		$object->advocate_key = $key;
		$object->save();

		return $key;
	}


	/**
	 * @return string
	 */
	private function generate_advocate_key() {

		$key = strtolower( aw_generate_key( Coupons::get_key_length() ) );

		if ( $this->advocate_key_exists( $key ) ) {
			return $this->generate_advocate_key();
		}

		return apply_filters( 'automatewoo/referrals/generate_advocate_key', $key, $this );
	}


	/**
	 * @param $key
	 * @return bool
	 */
	private function advocate_key_exists( $key ) {
		$query = new Advocate_Key_Query();
		$query->where( 'advocate_key', $key );
		return $query->has_results();
	}


	/**
	 * @return bool
	 */
	function is_valid() {

		if ( ! $this->user )
			return false;

		return true;
	}


	/**
	 *
	 */
	function store_ip() {
		update_user_meta( $this->get_id(), '_aw_referrals_advocate_ip', \WC_Geolocation::get_ip_address() );
	}


	/**
	 * @return false|string
	 */
	function get_stored_ip() {
		return get_user_meta( $this->get_id(), '_aw_referrals_advocate_ip', true );
	}



	/**
	 * @return string
	 */
	function get_shareable_coupon() {

		if ( AW_Referrals()->options()->type === 'coupon' ) {
			return strtoupper( Coupons::get_prefix() . $this->get_advocate_key() );
		}

		return false;
	}


	/**
	 * @param string|bool $url - If blank home_url() will be used
	 * @return string|false
	 */
	function get_shareable_link( $url = false ) {

		if ( AW_Referrals()->options()->type !== 'link' ) {
			return false;
		}

		if ( ! $url ) {
			$url = home_url();
		}

		return add_query_arg( [ AW_Referrals()->options()->share_link_parameter => $this->get_advocate_key() ], $url );
	}


	/**
	 * @return string
	 */
	function get_facebook_share_url() {
		return add_query_arg([
			'u' => urlencode( $this->get_social_share_url() ),
			'quote' => urlencode( $this->process_share_text( AW_Referrals()->options()->social_share_text ) )
		], 'https://www.facebook.com/sharer/sharer.php' );
	}


	/**
	 * @return string
	 */
	function get_twitter_share_url() {

		$text = AW_Referrals()->options()->social_share_text_twitter ? AW_Referrals()->options()->social_share_text_twitter : AW_Referrals()->options()->social_share_text;

		return add_query_arg([
			'text' => urlencode( $this->process_share_text( $text ) ),
			'url' => urlencode( $this->get_social_share_url() )
		], 'https://twitter.com/intent/tweet' );
	}


	/**
	 * @return string
	 */
	function get_social_share_url() {

		$option = trim( AW_Referrals()->options()->social_share_url );
		$url = $option ? $option : home_url();

		if ( AW_Referrals()->options()->type === 'link' ) {
			$url = $this->get_shareable_link( $url );
		}

		return $url;
	}


	/**
	 * @return float
	 */
	function get_current_credit() {
		return AW_Referrals()->store_credit->get_available_credit( $this->get_id() );
	}


	/**
	 * @return float
	 */
	function get_total_credit() {
		return AW_Referrals()->store_credit->get_total_credit( $this->get_id() );
	}


	/**
	 * @param string|array|bool $status - optional
	 * @return int
	 */
	function get_referral_count( $status = false ) {

		$query = ( new Referral_Query() );
		$query->where( 'advocate_id', $this->get_id() );

		if ( $status ) {
			$query->where( 'status', $status );
		}

		return $query->get_count();
	}


	/**
	 * @return float
	 */
	function get_referral_revenue() {

		$query = ( new Referral_Query() )
			->where( 'status', 'approved' )
			->where( 'advocate_id', $this->get_id() );

		if ( ! $referrals = $query->get_results() )
			return 0;

		$order_ids = wp_list_pluck( $referrals, 'order_id' );
		$order_ids = array_map( 'absint', $order_ids );

		global $wpdb;

		$spent = $wpdb->get_var( "SELECT SUM(meta.meta_value)
			FROM $wpdb->posts as posts
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			WHERE   posts.ID       		 IN (" . implode( ",", $order_ids ) . ")
			AND     posts.post_type     IN ('" . implode( "','", wc_get_order_types( 'reports' ) ) . "')
			AND     posts.post_status   IN ( 'wc-completed', 'wc-processing' )
			AND     meta.meta_key       = '_order_total'
		" );

		return $spent;
	}


	/**
	 * @return int
	 */
	function get_invites_count() {
		$query = ( new Invite_Query() )
			->where( 'advocate_id', $this->get_id() );

		return $query->get_count();
	}


	/**
	 * @param $text
	 * @return mixed
	 */
	function process_share_text( $text ) {
		return Option_Variables::process( $text, $this );
	}


	/**
	 * @return bool
	 */
	function is_paying_customer() {
		return (bool) get_user_meta( $this->get_id(), 'paying_customer', true );
	}



	/**
	 * @return string
	 * @deprecated
	 */
	function get_advocate_share_coupon() {
		return $this->get_shareable_coupon();
	}


}