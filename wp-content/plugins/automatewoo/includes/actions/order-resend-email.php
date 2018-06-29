<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @class Action_Order_Resend_Email
 * @since 2.2
 */
class Action_Order_Resend_Email extends Action {

	public $required_data_items = [ 'order' ];


	function init() {
		$this->title = __( 'Resend Order Email', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
		$this->description = __( 'Please note that email tracking is not currently supported on this action.', 'automatewoo' );
	}


	function load_fields() {

		$options = [];
		$mailer = WC()->mailer();
		$available_emails = [ 'new_order', 'cancelled_order', 'customer_processing_order', 'customer_completed_order', 'customer_invoice', 'customer_refunded_order' ];
		$mails = $mailer->get_emails();

		if ( ! empty( $mails ) ) {
			foreach ( $mails as $mail ) {
				if ( in_array( $mail->id, $available_emails ) ) {
					$options[$mail->id] = $mail->title;
				}
			}
		}

		$email = new Fields\Select(true);
		$email->set_name( 'email' );
		$email->set_title( __('Email', 'automatewoo') );
		$email->set_required();
		$email->set_options( $options );

		$this->add_field($email);
	}


	/**
	 * @return void
	 */
	function run() {

		$order_email = Clean::string( $this->get_option( 'email' ) );
		$order = $this->workflow->get_data_item('order');

		if ( ! $order_email || ! $order )
			return;

		do_action( 'woocommerce_before_resend_order_emails', $order );

		// Ensure gateways are loaded in case they need to insert data into the emails
		WC()->payment_gateways();
		WC()->shipping();

		// Load mailer
		$mailer = WC()->mailer();

		$mails = $mailer->get_emails();

		if ( ! empty( $mails ) ) {
			foreach ( $mails as $mail ) {
				if ( $mail->id == $order_email ) {
					$mail->trigger( $order->id );
					$order->add_order_note( sprintf( __( '%s email notification sent by AutomateWoo workflow #%d', 'woocommerce' ), $mail->title, $this->workflow->get_id()  ), false, true );
				}
			}
		}

		do_action( 'woocommerce_after_resend_order_email', $order, $order_email );
	}
}
