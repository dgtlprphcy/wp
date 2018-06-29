<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Subscription_Payment_Failed
 * @since 2.1.0
 */
class Trigger_Subscription_Payment_Failed extends Trigger_Abstract_Subscriptions {


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Subscription Payment Failed', 'automatewoo' );
	}


	function load_fields() {

		$skip_first = new Fields\Checkbox();
		$skip_first->set_name('skip_first');
		$skip_first->set_title( __( 'Skip first payment', 'automatewoo'  ) );

		$this->add_field_subscription_products();
		$this->add_field($skip_first);
	}


	function register_hooks() {
		add_action( 'woocommerce_subscription_payment_failed', [ $this, 'trigger_for_subscription' ], 20, 1 );
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

		$skip_first = absint( $workflow->get_trigger_option('skip_first') );

		if ( $skip_first ) {
			// since this is a failed payment trigger the payment count only needs greater than 0
			if ( $subscription->get_completed_payment_count() == 0 ) return false;
		}

		if ( ! $this->validate_subscription_products_field( $workflow ) )
			return false;

		return true;
	}

}
