<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Compat;
use AutomateWoo\Clean;
use AutomateWoo\Format;
use AutomateWoo\Temporary_Data;

/**
 * @class Store_Credit
 */
class Store_Credit {

	/** @var string */
	public $credit_name;

	/** @var string */
	public $coupon_code;


	/**
	 * Constructor
	 */
	function __construct() {

		$this->credit_name = esc_html( apply_filters( 'automatewoo/referrals/referral_credit_name', __( 'Referral Credit', 'automatewoo-referrals' ) ) );
		$this->coupon_code = apply_filters( 'woocommerce_coupon_code', $this->credit_name );

		add_action( 'woocommerce_check_cart_items', [ $this, 'maybe_add_credit_coupon' ], 50 );
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'reorder_cart_coupons' ] );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'reduce_store_credit_in_order' ], 100 );

		add_filter( 'woocommerce_cart_totals_coupon_label', [ $this, 'filter_cart_coupon_label' ], 10, 2 );
		add_filter( 'woocommerce_cart_totals_coupon_html', [ $this, 'filter_cart_coupon_html' ], 10, 2 );
		add_filter( 'woocommerce_coupon_message', [ $this, 'filter_coupon_message' ], 20, 3 );
		add_filter( 'woocommerce_coupon_error', [ $this, 'filter_coupon_error' ], 20, 3 );

		add_filter( 'woocommerce_get_shop_coupon_data', [ $this, 'filter_store_credit_coupon' ], 10, 2 );
	}


	/**
	 * @param $user_id
	 * @return float
	 */
	function get_available_credit( $user_id ) {

		// cached in memory
		if ( ! $credit = Temporary_Data::get( 'referrals_available_credit', $user_id ) ) {

			$credit = 0;

			$referrals = AW_Referrals()->get_available_referrals_by_user( $user_id );

			if ( $referrals ) {
				foreach ( $referrals as $referral ) {
					if ( $referral->is_reward_store_credit() ) {
						$credit += $referral->get_reward_amount_remaining();
					}
				}
			}

			$credit = Format::round( $credit );

			Temporary_Data::set( 'referrals_available_credit', $user_id, $credit );
		}

		return apply_filters( 'automatewoo/referrals/available_credit', $credit, $user_id );
	}



	/**
	 * @param $user_id
	 * @return float
	 */
	function get_total_credit( $user_id ) {

		$total_credit = 0;

		$query = ( new Referral_Query() )
			->where( 'advocate_id', $user_id )
			->where( 'status', 'approved' );

		$referrals = $query->get_results();

		if ( ! $referrals )
			return 0;

		foreach ( $referrals as $referral ) {
			if ( $referral->is_reward_store_credit() ) {
				$total_credit += $referral->get_reward_amount();
			}
		}

		return (float) $total_credit;
	}


	/**
	 * Add or remove store credit coupon
	 */
	function maybe_add_credit_coupon() {

		$total_credit = $this->get_available_credit( get_current_user_id() );

		if ( $total_credit && is_user_logged_in() ) {

			if ( ! WC()->cart->has_discount( $this->coupon_code ) ) {
				WC()->cart->add_discount( $this->coupon_code );
			}
		}
		else {
			if ( WC()->cart->has_discount( $this->coupon_code ) ) {
				WC()->cart->remove_coupon( $this->coupon_code );
			}
		}
	}


	/**
	 * Store credit should be last
	 */
	function reorder_cart_coupons() {
		// ensure referral credit is added after all other coupons
		if ( isset( WC()->cart->coupons[ $this->coupon_code ] ) ) {
			$coupon = WC()->cart->coupons[ $this->coupon_code ];
			unset( WC()->cart->coupons[ $this->coupon_code ] );
			WC()->cart->coupons[ $this->coupon_code ] = $coupon;
		}

		if ( in_array( $this->coupon_code, WC()->cart->applied_coupons ) ) {
			aw_array_remove_value( WC()->cart->applied_coupons, $this->coupon_code );
			WC()->cart->applied_coupons[] = $this->coupon_code ;
		}
	}


	/**
	 * @param $msg
	 * @param $msg_code
	 * @param \WC_Coupon $coupon
	 * @return bool
	 */
	function filter_coupon_message( $msg, $msg_code, $coupon ) {
		if ( ! $this->is_store_credit_coupon( $coupon ) ) {
			return $msg;
		}
		return false;
	}


	/**
	 * @param $msg
	 * @param $msg_code
	 * @param \WC_Coupon $coupon
	 * @return bool
	 */
	function filter_coupon_error( $msg, $msg_code, $coupon ) {
		if ( ! $this->is_store_credit_coupon( $coupon ) ) {
			return $msg;
		}
		return false;
	}



	/**
	 * @param bool $coupon_data
	 * @param $coupon_code
	 * @return array|bool
	 */
	function filter_store_credit_coupon( $coupon_data, $coupon_code ) {

		if ( ! $this->is_store_credit_coupon( $coupon_code ) ) {
			return $coupon_data;
		}

		if ( version_compare( WC()->version, '3.0', '<' ) ) {
			$coupon_data = [
				'discount_type' => 'fixed_cart',
				'coupon_amount' => $this->get_available_credit( get_current_user_id() ),
				'individual_use' => 'no',
				'exclude_sale_items' => 'no',
			];
		}
		else {
			$coupon_data = [
				'id' => true,
				'type' => 'fixed_cart',
				'amount' => $this->get_available_credit( get_current_user_id() ),
				'individual_use' => false,
				'exclude_sale_items' => false,
			];
		}

		$coupon_data['minimum_amount'] = Clean::string( AW_Referrals()->options()->reward_min_purchase );

		return apply_filters( 'automatewoo/referrals/store_credit/coupon_data', $coupon_data );
	}


	/**
	 * @param $label
	 * @param \WC_Coupon $coupon
	 * @return string
	 */
	function filter_cart_coupon_label( $label, $coupon ) {
		if ( $this->is_store_credit_coupon( $coupon ) ) {
			return $this->credit_name;
		}

		return $label;
	}


	/**
	 * @param $html
	 * @param \WC_Coupon $coupon
	 * @return string
	 */
	function filter_cart_coupon_html( $html, $coupon ) {
		if ( ! $this->is_store_credit_coupon( $coupon ) ) {
			return $html;
		}

		$amount = WC()->cart->get_coupon_discount_amount( Compat\Coupon::get_code( $coupon ), $this->is_display_ex_tax() );
		$html = '-' . wc_price( $amount );

		if ( $this->is_display_ex_tax() ) {
			$html .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
		}

		return $html;
	}


	/**
	 * @param $coupon \WC_Coupon|string - coupon code object or coupon name
	 * @return bool
	 */
	function is_store_credit_coupon( $coupon ) {
		if ( is_a( $coupon, 'WC_Coupon' ) ) {
			return Compat\Coupon::get_code( $coupon ) == $this->coupon_code;
		}
		else {
			return $coupon === $this->coupon_code;
		}
	}


	/**
	 * @param $order_id
	 * @return void
	 */
	function reduce_store_credit_in_order( $order_id ) {

		if ( ! $order = wc_get_order( $order_id ) )
			return;

		if ( Compat\Order::get_meta( $order, '_aw_referrals_credit_processed' ) )
			return;

		if ( ! $credit_used = $this->get_store_credit_amount_in_order( $order ) )
			return;

		$this->reduce_advocate_store_credit( $order->get_user_id(), $credit_used );

		Compat\Order::update_meta( $order, '_aw_referrals_credit_processed', true );
	}


	/**
	 * @param int $advocate_id
	 * @param float $credit_amount
	 */
	function reduce_advocate_store_credit( $advocate_id, $credit_amount ) {

		$referrals = AW_Referrals()->get_available_referrals_by_user( $advocate_id );

		// Loop through available referrals until the store credit value has been used
		foreach ( $referrals as $referral ) {

			/** @var $referral Referral */

			if ( $credit_amount <= 0 )
				break;

			if ( ! $referral->is_reward_store_credit() )
				continue;

			if ( $referral->get_reward_amount_remaining() < $credit_amount ) {
				$credit_amount -= $referral->get_reward_amount_remaining();
				$referral->reward_amount_remaining = 0;
				$referral->save();
			}
			else {
				$referral->reward_amount_remaining = $referral->get_reward_amount_remaining() - $credit_amount;
				$credit_amount = 0;
				$referral->save();
			}
		}
	}


	/**
	 * @param $order \WC_Order
	 * @return bool|array|\WC_Order_Item_Coupon
	 */
	function get_store_credit_coupon_from_order( $order ) {
		foreach ( $order->get_items( 'coupon' ) as $coupon ) {
			if ( $coupon['name'] == $this->coupon_code ) {
				return $coupon;
			}
		}
		return false;
	}


	/**
	 * @param $order
	 * @return float
	 */
	function get_store_credit_amount_in_order( $order ) {

		if ( ! $coupon = $this->get_store_credit_coupon_from_order( $order ) ) {
			return 0;
		}

		if ( version_compare( WC()->version, '3.0', '<' ) ) {
			return $coupon['discount_amount'] + $coupon['discount_amount_tax'];
		}
		else {
			return $coupon->get_discount() + $coupon->get_discount_tax();
		}
	}


	/**
	 * Apply store credit to order after it has been created
	 * @param \WC_Order $order
	 * @param float $available_credit
	 * @return float - amount of credit used (inc tax)
	 */
	function add_store_credit_to_order( $order, $available_credit ) {

		// ensure the order doesn't already have a store credit coupon
		if ( $coupon = $this->get_store_credit_coupon_from_order( $order ) ) {
			return 0;
		}

		if ( version_compare( WC()->version, '3.0', '<' ) ) {
			return $this->legacy_add_store_credit_to_order( $order, $available_credit );
		}

		$items = $order->get_items();

		$used_credit_total = 0;
		$user_credit_total_tax = 0;

		foreach ( $items as $item ) {
			/** @var $item \WC_Order_Item_Product */

			$price = floatval( $item->get_total() ) + floatval( $item->get_total_tax() );

			if ( $available_credit < 0 ) {
				break;
			}

			$discount_amount = min( $price, $available_credit );
			$available_credit -= $discount_amount;

			if ( wc_tax_enabled() ) {

				$tax_rates = $this->get_order_tax_rates( $order, $item->get_tax_class() );
				$discount_taxes = \WC_Tax::calc_tax( $discount_amount, $tax_rates, true, true );
				$discount_tax = wc_round_tax_total( array_sum( $discount_taxes ) );
				$discount_amount_ex_tax = $discount_amount - $discount_tax;

				$item->set_total( $item->get_total() - $discount_amount_ex_tax );
				$item->set_total_tax( $item->get_total_tax() - $discount_tax );

				$used_credit_total += $discount_amount_ex_tax;
				$user_credit_total_tax += $discount_tax;
			}
			else {
				$item->set_total( $item->get_total() - $discount_amount );
				$used_credit_total += $discount_amount;
			}

			$item->save();
		}

		$used_credit_total = wc_format_decimal( $used_credit_total );
		$user_credit_total_tax = wc_format_decimal( $user_credit_total_tax );

		$coupon = new \WC_Order_Item_Coupon();
		$coupon->set_code( $this->coupon_code );
		$coupon->set_discount( $used_credit_total );
		$coupon->set_discount_tax( $user_credit_total_tax );

		$order->add_item( $coupon );

		$order->calculate_totals();

		return $used_credit_total + $user_credit_total_tax;
	}


	/**
	 * @param \WC_Order $order
	 * @param string $tax_class
	 * @return array|bool
	 */
	private function get_order_tax_rates( $order, $tax_class = '' ) {

		if ( ! wc_tax_enabled() ) {
			return false;
		}

		$tax_based_on = get_option( 'woocommerce_tax_based_on' );

		$country = '';
		$state = '';
		$postcode = '';
		$city = '';

		if ( 'billing' === $tax_based_on ) {
			$country = Compat\Order::get_billing_country( $order );
			$state = Compat\Order::get_billing_state( $order );
			$postcode = Compat\Order::get_billing_postcode( $order );
			$city = Compat\Order::get_billing_city( $order );
		}
		elseif ( 'shipping' === $tax_based_on ) {
			$country = Compat\Order::get_shipping_country( $order );
			$state = Compat\Order::get_shipping_state( $order );
			$postcode = Compat\Order::get_shipping_postcode( $order );
			$city = Compat\Order::get_shipping_city( $order );
		}

		// Default to base
		if ( 'base' === $tax_based_on || empty( $country ) ) {
			$default  = wc_get_base_location();
			$country  = $default['country'];
			$state    = $default['state'];
		}

		$tax_rates = \WC_Tax::find_rates([
			'country'=> $country,
			'state' => $state,
			'postcode' => $postcode,
			'city' => $city,
			'tax_class' => $tax_class
		]);

		return $tax_rates;
	}


	/**
	 * Apply store credit to order after it has been created
	 * @param \WC_Order $order
	 * @param float $available_credit
	 * @return float
	 */
	private function legacy_add_store_credit_to_order( $order, $available_credit ) {

		$items = $order->get_items();

		$used_credit_total = 0;
		$user_credit_total_tax = 0;

		foreach ( $items as $item_id => $item ) {

			$product = $order->get_product_from_item( $item );

			$update_args = [
				'qty' => $item['qty'],
				'totals' => []
			];

			$price = $item['line_total'] + $item['line_tax'];

			if ( $available_credit < 0 ) {
				break;
			}

			$discount_amount = min( $price, $available_credit );
			$available_credit -= $discount_amount;

			if ( wc_tax_enabled() ) {

				$tax_rates = $this->get_order_tax_rates( $order, $item['tax_class'] );
				$discount_taxes = \WC_Tax::calc_tax( $discount_amount, $tax_rates, true, true );
				$discount_tax_total = wc_round_tax_total( array_sum( $discount_taxes ) );

				$update_args['totals']['total'] = $item['line_total'] - ( $discount_amount - $discount_tax_total );
				$update_args['totals']['tax'] = $item['line_tax'] - $discount_tax_total;

				$used_credit_total += $discount_amount - $discount_tax_total;
				$user_credit_total_tax += $discount_tax_total;
			}
			else {
				$update_args['totals']['total'] = $item['line_total'] - $discount_amount;
				$used_credit_total += $discount_amount;
			}

			$order->update_product( $item_id, $product, $update_args );
		}

		$used_credit_total = wc_format_decimal( $used_credit_total );
		$user_credit_total_tax = wc_format_decimal( $user_credit_total_tax );

		$order->calculate_totals();
		$order->add_coupon( $this->coupon_code, $used_credit_total, $user_credit_total_tax );

		return $used_credit_total + $user_credit_total_tax;
	}


	/**
	 * @return bool
	 */
	private function is_display_ex_tax() {
		return get_option( 'woocommerce_tax_display_cart' ) === 'excl';
	}

}
