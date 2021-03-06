<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Abandoned_Cart_Guest
 */
class Trigger_Abandoned_Cart_Guest extends Trigger_Abstract_Abandoned_Cart {

	public $supplied_data_items = [ 'cart', 'guest' ];


	function load_admin_details() {
		$this->title = __( 'Cart Abandoned - Guests Only', 'automatewoo' );
		$this->description = __( 'This trigger fires when a cart belonging to a registered customer or a guest customer is abandoned.', 'automatewoo' );
		parent::load_admin_details();
	}


	/**
	 * @param Cart $cart
	 */
	function cart_abandoned( $cart ) {
		if ( ! $cart->get_user_id() && $cart->has_items() ) {
			$this->maybe_run([
				'guest' => $cart->get_guest(),
				'cart' => $cart
			]);
		}
	}


	/**
	 * @param Cart $cart
	 */
	function maybe_clear_queued_emails( $cart ) {
		if ( ! $cart->get_user_id() ) {
			parent::maybe_clear_queued_emails( $cart );
		}
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {
		$guest = $workflow->data_layer()->get_guest();
		$cart = $workflow->data_layer()->get_cart();

		if ( ! $guest || ! $cart ) {
			return false;
		}

		if ( $cart->get_user_id() ) {
			return false;
		}

		return parent::validate_workflow( $workflow );
	}

}
