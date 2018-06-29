<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;
use AutomateWoo\Clean;
use AutomateWoo\Fields;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_New_Referral
 */
class Trigger_New_Referral extends Trigger_Abstract {


	function init() {
		$this->title = __( 'New Referral Created', 'automatewoo');
		parent::init();
	}


	/**
	 * Option fields
	 */
	function load_fields() {
		$initial_status = ( new Fields\Select() )
			->set_name( 'status' )
			->set_title( __( 'Initial Status', 'automatewoo-referrals' ) )
			->set_options( AW_Referrals()->get_referral_statuses() )
			->set_description( __('The initial status will usually only be either Pending or Potentially Fraudulent.', 'automatewoo-referrals' ) )
			->set_placeholder( __('Leave blank for any status', 'automatewoo-referrals' ) )
			->set_multiple();

		$this->add_field( $initial_status );
		$this->add_field_recheck_status( 'referral' );
	}


	/**
	 * hook in
	 */
	function register_hooks() {
		add_action( 'automatewoo/referrals/referral_created', [ $this, 'catch_hooks' ], 100, 1 );
	}


	/**
	 * @param Referral $referral
	 */
	function catch_hooks( $referral ) {
		$data = $this->get_referral_data_layer( $referral );
		$this->maybe_run( $data );
	}


	/**
	 * @param $workflow AutomateWoo\Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {
		/**
		 * @var $referral Referral
		 * @var $advocate AutomateWoo\Referrals\Advocate
		 * @var $user \WP_User|AutomateWoo\Order_Guest
		 * @var $order \WC_Order
		 */
		$referral = $workflow->get_data_item('referral');
		$user = $workflow->get_data_item('user');
		$advocate = $workflow->get_data_item('advocate');
		$order = $workflow->get_data_item('order'); // won't always have an order

		if ( ! $referral || ! $user || ! $advocate )
			return false;

		$status = Clean::recursive( $workflow->get_trigger_option( 'status' ) );

		if ( ! $this->validate_status_field( $status, $referral->status ) )
			return false;

		return true;
	}


	/**
	 * @param AutomateWoo\Workflow $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {
		// check parent
		if ( ! parent::validate_before_queued_event( $workflow ) )
			return false;

		if ( ! $referral = $workflow->get_data_item('referral') )
			return false;

		$status = Clean::recursive( $workflow->get_trigger_option( 'status' ) );

		// Option to validate order status
		if ( $workflow->get_trigger_option( 'recheck_status_before_queued_run' ) ) {
			if ( ! $this->validate_status_field( $status, $referral->status ) )
				return false;
		}

		return true;
	}

}
