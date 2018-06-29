<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Referral_Status_Changed
 */
class Trigger_Referral_Status_Changed extends Trigger_Abstract {


	function init() {
		$this->title = __( 'Referral Status Changed', 'automatewoo');
		parent::init();
	}


	/**
	 * Option fields
	 */
	function load_fields() {
		$from_status = ( new AutomateWoo\Fields\Select() )
			->set_name( 'from_status' )
			->set_title( __( 'From status', 'automatewoo-referrals' ) )
			->set_options( AW_Referrals()->get_referral_statuses() )
			->set_placeholder( __('Leave blank for any status', 'automatewoo-referrals' ) )
			->set_multiple();

		$to_status = clone $from_status;
		$to_status
			->set_name( 'to_status' )
			->set_title( __( 'To status', 'automatewoo-referrals' ) );

		$this->add_field( $from_status );
		$this->add_field( $to_status );
		$this->add_field_recheck_status( 'referral' );
		$this->add_field_advocate_limit();
	}


	/**
	 * hook in
	 */
	function register_hooks() {
		add_action( 'automatewoo/referrals/referral_status_changed', [ $this, 'catch_hooks' ], 100, 3 );
	}


	/**
	 * @param Referral $referral
	 * @param string $old_status
	 * @param string $new_status
	 */
	function catch_hooks( $referral, $old_status, $new_status ) {
		AutomateWoo\Temporary_Data::set( 'referral_old_status', $referral->get_id(), $old_status );
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
		 * @var $advocate Advocate
		 * @var $user \WP_User|AutomateWoo\Order_Guest
		 * @var $order \WC_Order
		 */
		$referral = $workflow->get_data_item('referral');
		$user = $workflow->get_data_item('user');
		$advocate = $workflow->get_data_item('advocate');
		$order = $workflow->get_data_item('order'); // won't always have an order

		if ( ! $referral || ! $user || ! $advocate )
			return false;

		// Get options
		$from_status = aw_clean( $workflow->get_trigger_option('from_status') );
		$to_status = aw_clean( $workflow->get_trigger_option('to_status') );
		$advocate_limit = absint( $workflow->get_trigger_option('limit_per_advocate') );
		$old_status = AutomateWoo\Temporary_Data::get( 'referral_old_status', $referral->get_id() );

		if ( ! $this->validate_status_field( $from_status, $old_status ) )
			return false;

		if ( ! $this->validate_status_field( $to_status, $referral->get_status() ) )
			return false;

		if ( ! $this->validate_limit_per_advocate( $advocate_limit, $workflow->get_id(), $advocate->get_id() ) )
			return false;

		return true;
	}


	/**
	 * @param AutomateWoo\Workflow $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {

		if ( ! parent::validate_before_queued_event( $workflow ) )
			return false;

		// Get options
		$advocate_limit = absint( $workflow->get_trigger_option('limit_per_advocate') );
		$to_status = aw_clean( $workflow->get_trigger_option('to_status') );

		// get data
		$referral = $workflow->get_data_item('referral');
		$advocate = $workflow->get_data_item('advocate');

		if ( ! $referral || ! $advocate )
			return false;

		// Option to validate order status
		if ( $workflow->get_trigger_option( 'recheck_status_before_queued_run' ) ) {
			if ( ! $this->validate_status_field( $to_status, $referral->status ) )
				return false;
		}

		if ( ! $this->validate_limit_per_advocate( $advocate_limit, $workflow->get_id(), $advocate->get_user_id() ) )
			return false;

		return true;
	}

}
