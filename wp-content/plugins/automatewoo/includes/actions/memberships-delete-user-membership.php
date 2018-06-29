<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Memberships_Delete_User_Membership
 * @since 2.9
 */
class Action_Memberships_Delete_User_Membership extends Action_Memberships_Abstract {

	public $required_data_items = [ 'user' ];


	function init() {
		$this->title = __( "Delete Membership For User", 'automatewoo' );
		parent::init();
	}


	function load_fields() {

		$plans = Memberships_Helper::get_membership_plans();

		$plan = ( new Fields\Select( false ) )
			->set_name( 'plan' )
			->set_title( __( 'Plan', 'automatewoo' ) )
			->set_options( $plans )
			->set_required();

		$this->add_field( $plan );

	}


	function run() {

		/** @var $user Order_Guest|\WP_User */
		$user = $this->workflow->get_data_item( 'user' );
		$plan_id = absint( $this->get_option( 'plan' ) );

		if ( ! $user instanceof \WP_User || ! $plan_id ) {
			return;
		}

		$membership = wc_memberships_get_user_membership( $user->ID, $plan_id );

		if ( ! $membership ) {
			$this->workflow->add_action_log_note( $this, __( 'The user did not have membership that matched the selected plan.', 'automatewoo' ) );
			return;
		}

		$membership_id = $membership->get_id();

		$success = wp_delete_post( $membership_id, true );

		if ( $success ) {
			$this->workflow->add_action_log_note( $this, sprintf( __( 'Deleted membership #%s', 'automatewoo' ), $membership_id ) );
		}
		else {
			$this->workflow->add_action_log_note( $this, sprintf( __( 'Failed deleting membership #%s', 'automatewoo' ), $membership_id ) );
		}
	}

}
