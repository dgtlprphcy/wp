<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Cart
 * @since 2.0
 *
 * @property string $status
 * @property string $user_id
 * @property string $guest_id
 * @property string $last_modified
 * @property string $created
 * @property array $items
 * @property array $coupons
 * @property string $total
 * @property string $token
 * @property string $currency
 */
class Cart extends Model {

	/** @var string */
	public $table_id = 'carts';

	/** @var string  */
	public $object_type = 'cart';

	/** @var float */
	public $calculated_total = 0;

	/** @var float */
	public $calculated_tax_total = 0;

	/** @var float */
	public $calculated_subtotal = 0;

	/** @var array */
	public $_items_language_adjusted;


	/**
	 * @param bool|int $id
	 */
	function __construct( $id = false ) {
		if ( $id ) {
			$this->get_by( 'id', $id );
		}
	}


	/**
	 * @return string
	 */
	function get_status() {
		return $this->status ? Clean::string( $this->status ) : 'abandoned';
	}


	/**
	 * @param $status - active|abandoned
	 */
	function set_status( $status ) {
		$this->status = $status;
	}


	/**
	 * Update status, triggers change hooks
	 * @param $new_status - active|abandoned
	 */
	function update_status( $new_status ) {

		$old_status = $this->get_status();

		if ( $new_status == $old_status ) {
			return;
		}

		$this->set_status( $new_status );
		$this->save();
		do_action( 'automatewoo/cart/status_changed', $this, $old_status, $new_status );
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
	 * @return bool|\DateTime
	 */
	function get_date_last_modified() {
		return $this->get_date_column( 'last_modified' );
	}


	/**
	 * @param \DateTime|string $date
	 */
	function set_date_last_modified( $date ) {
		$this->set_date_column( 'last_modified', $date );
	}


	/**
	 * @return bool|\DateTime
	 */
	function get_date_created() {
		return $this->get_date_column( 'created' );
	}


	/**
	 * @param \DateTime $date
	 */
	function set_date_created( $date ) {
		$this->set_date_column( 'created', $date );
	}


	/**
	 * @return float
	 */
	function get_total() {
		return (float) $this->total;
	}


	/**
	 * @param $total
	 */
	function set_total( $total ) {
		$this->total = $total;
	}


	/**
	 * @return string
	 */
	function get_token() {
		return $this->token;
	}


	/**
	 * @param bool $token (optional)
	 */
	function set_token( $token = false ) {
		if ( ! $token ) {
			$token = aw_generate_key( 32 );
		}

		$this->token = $token;
	}


	/**
	 * @return float
	 */
	function get_currency() {
		if ( $this->currency ) {
			return Clean::string( $this->currency );
		}
		return get_woocommerce_currency();
	}


	/**
	 * @param $currency
	 */
	function set_currency( $currency ) {
		$this->currency = $currency;
	}


	/**
	 * @return bool
	 */
	function has_coupons() {
		return sizeof( $this->get_coupons() ) > 0;
	}


	/**
	 * @return array
	 */
	function get_coupons() {
		return is_array( $this->coupons ) ? $this->coupons : [];
	}


	/**
	 * @param $coupons
	 */
	function set_coupons( $coupons ) {
		$this->coupons = $coupons;
	}


	/**
	 * @return bool
	 */
	function has_items() {
		return sizeof( $this->get_items() ) > 0;
	}


	/**
	 * @return array
	 */
	function get_items() {
		if ( Language::is_multilingual() ) {
			return $this->get_language_adjusted_items();
		}
		else {
			return is_array( $this->items ) ? $this->items : [];
		}
	}


	/**
	 * Adjust the cart items so are match the language of the cart
	 *
	 * @return array
	 */
	function get_language_adjusted_items() {

		if ( isset( $this->_items_language_adjusted ) ) {
			return $this->_items_language_adjusted;
		}

		$lang = $this->get_language();
		$items = is_array( $this->items ) ? $this->items : [];

		foreach ( $items as &$item ) {
			$item['product_id'] = icl_object_id( $item['product_id'], 'product', true, $lang );
			$item['variation_id'] = icl_object_id( $item['variation_id'], 'product', true, $lang );
		}

		$this->_items_language_adjusted = $items;
		return $items;
	}


	/**
	 * @param $items
	 */
	function set_items( $items ) {
		$this->_items_language_adjusted = null;
		$this->items = $items;
	}


	/**
	 * @return Guest|false
	 */
	function get_guest() {
		if ( ! $this->get_guest_id() ) {
			return false;
		}
		return AW()->get_guest( $this->get_guest_id() );
	}


	/**
	 * @return Customer|bool
	 */
	function get_customer() {
		if ( $this->get_user_id() ) {
			return Customer_Factory::get_by_user_id( $this->get_user_id() );
		}
		else {
			return Customer_Factory::get_by_guest_id( $this->get_guest_id() );
		}
	}

	/**
	 * @return string
	 */
	function get_language() {
		if ( $this->get_customer() ) {
			return $this->get_customer()->get_language();
		}
		return Language::get_default();
	}


	/**
	 * Updates the stored cart with the current time and cart items
	 */
	function sync() {

		$this->set_date_last_modified( new \DateTime() );
		$this->set_items( WC()->cart->get_cart_for_session() );

		$coupon_data = [];

		foreach( WC()->cart->get_applied_coupons() as $coupon_code ) {
			$coupon_data[$coupon_code] = [
				'discount_incl_tax' => WC()->cart->get_coupon_discount_amount( $coupon_code, false ),
				'discount_excl_tax' => WC()->cart->get_coupon_discount_amount( $coupon_code ),
				'discount_tax' => WC()->cart->get_coupon_discount_tax_amount( $coupon_code )
			];
		}

		$this->set_coupons( $coupon_data );
		$this->set_currency( get_woocommerce_currency() );

		if ( get_option( 'woocommerce_tax_display_cart' ) === 'excl' ) {
			$this->set_total( WC()->cart->cart_contents_total );
		}
		else {
			$this->set_total( WC()->cart->cart_contents_total + WC()->cart->tax_total );
		}

		if ( $this->get_status() == 'abandoned' ) {
			$this->update_status( 'active' );
		}
		else {
			$this->save();
		}
	}



	function calculate_totals() {

		$this->calculated_subtotal = 0;
		$this->calculated_tax_total = 0;
		$this->calculated_total = 0;

		$tax_display = get_option( 'woocommerce_tax_display_cart' );

		foreach( $this->get_items() as $item ) {
			$line_total = $tax_display === 'excl' ? $item[ 'line_subtotal' ] : $item['line_subtotal'] + $item['line_subtotal_tax'];
			$this->calculated_tax_total += $item['line_subtotal_tax'];
			$this->calculated_total += $line_total;
			$this->calculated_subtotal += $line_total;
		}

		foreach ( $this->get_coupons() as $coupon_code => $coupon ) {
			$coupon_discount = $tax_display === 'excl' ? $coupon[ 'discount_excl_tax' ] : $coupon[ 'discount_incl_tax' ];
			$this->calculated_total -= $coupon_discount;
			$this->calculated_tax_total -= $coupon['discount_tax'];
		}
	}


	/**
	 * Save
	 */
	function save() {

		if ( ! $this->exists ) {
			$this->set_date_created( new \DateTime() );
		}

		parent::save();
	}

}

