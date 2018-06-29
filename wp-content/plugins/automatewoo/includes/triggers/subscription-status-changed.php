<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Subscription_Status_Changed
 * @since 2.1.0
 */
class Trigger_Subscription_Status_Changed extends Trigger_Abstract_Subscriptions {

	/** @var bool */
	public $_doing_payment = false;


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Subscription Status Changed', 'automatewoo' );
	}


	function load_fields() {

		$from = ( new Fields\Subscription_Status() )
			->set_title( __( 'Status changes from', 'automatewoo'  ) )
			->set_name( 'subscription_status_from' )
			->set_description( __( 'Select which subscription status changes will trigger this workflow. Leave blank for any subscription status.', 'automatewoo' ) )
			->set_multiple();

		$to = clone $from;

		$to->set_title( __( 'Status changes to', 'automatewoo'  ) )
			->set_name( 'subscription_status_to' );

		$recheck_status = ( new Fields\Checkbox() )
			->set_name('validate_order_status_before_queued_run')
			->set_title( __( 'Recheck status before run', 'automatewoo' ) )
			->set_description( __( "This is useful for workflows that are not run immediately as it ensures the status of the subscription hasn't changed since initial trigger." , 'automatewoo' ) )
			->set_default_to_checked();

		$this->add_field_subscription_products();
		$this->add_field( $from );
		$this->add_field( $to );
		$this->add_field( $recheck_status );
	}



	function register_hooks() {
		// Whenever a renewal payment is due subscription is placed on hold and then back to active if successful
		// Block this trigger while this happens
		add_action( 'woocommerce_scheduled_subscription_payment', [ $this, 'before_payment' ], 0, 1 );
		add_action( 'woocommerce_scheduled_subscription_payment', [ $this, 'after_payment' ], 1000, 1 );

		add_action( 'woocommerce_subscription_status_updated', [ $this, 'catch_hooks' ], 10, 3 );
	}


	/**
	 * @param $subscription_id
	 */
	function before_payment( $subscription_id ) {
		$this->_doing_payment = true;
	}


	/**
	 * @param $subscription_id
	 */
	function after_payment( $subscription_id ) {

		$this->_doing_payment = false;

		$subscription = wcs_get_subscription( $subscription_id );

		if ( ! $subscription->has_status( 'active' ) ) {
			// if status was changed (no longer active) during payment trigger now
			$this->catch_hooks( $subscription, $subscription->get_status(), 'active' );
		}
	}


	/**
	 * @param $subscription \WC_Subscription
	 * @param string $new_status
	 * @param string $old_status
	 */
	function catch_hooks( $subscription, $new_status, $old_status ) {

		if ( $this->_doing_payment ) return;

		// use temp data to store the real status changed, fixes rare issue with custom status transitions
		Temporary_Data::set( 'subscription_old_status', Compat\Subscription::get_id( $subscription ), $old_status );
		Temporary_Data::set( 'subscription_new_status', Compat\Subscription::get_id( $subscription ), $new_status );

		$this->trigger_for_subscription( $subscription );
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$subscription = $workflow->data_layer()->get_subscription();

		if ( ! $subscription ) {
			return false;
		}

		$status_from = Clean::recursive( $workflow->get_trigger_option('subscription_status_from') );
		$status_to = Clean::recursive( $workflow->get_trigger_option('subscription_status_to') );
		$old_status = Temporary_Data::get( 'subscription_old_status', Compat\Subscription::get_id( $subscription ) );
		$new_status = Temporary_Data::get( 'subscription_new_status', Compat\Subscription::get_id( $subscription ) );

		if ( ! $this->validate_status_field( $status_from, $old_status ) ) {
			return false;
		}

		if ( ! $this->validate_status_field( $status_to, $new_status ) ) {
			return false;
		}

		if ( ! $this->validate_subscription_products_field( $workflow ) ) {
			return false;
		}

		return true;
	}



	/**
	 * Ensures 'to' status has not changed while sitting in queue
	 *
	 * @param $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {

		// check parent
		if ( ! parent::validate_before_queued_event( $workflow ) )
			return false;

		$subscription = $workflow->data_layer()->get_subscription();

		if ( ! $subscription ) {
			return false;
		}

		// Option to validate order status
		if ( $workflow->get_trigger_option('validate_order_status_before_queued_run') ) {

			$status_to = $workflow->get_trigger_option('subscription_status_to');

			if ( ! $this->validate_status_field( $status_to, $subscription->get_status() ) ) {
				return false;
			}
		}

		return true;
	}

}
