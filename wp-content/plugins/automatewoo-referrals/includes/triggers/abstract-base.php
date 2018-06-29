<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Abstract
 */
abstract class Trigger_Abstract extends AutomateWoo\Trigger {

	public $supplied_data_items = [ 'referral', 'advocate', 'user', 'order' ];


	function init() {
		$this->group = __( 'Referrals', 'automatewoo' );
		$this->description = __( "This trigger essentially has two customers, the advocate and the friend. All variables and rules that are 'customer' based are for the 'friend customer' and the variables and rules that are 'advocate' based are apply to the 'advocate customer'.", 'automatewoo-referrals' );

		// if using signups referrals we don't have any order data
		if ( AW_Referrals()->options()->get_reward_event() === 'signup' ) {
			unset( $this->supplied_data_items[ 'order' ] );
		}
	}


	/**
	 * @param Referral $referral
	 * @return array
	 */
	function get_referral_data_layer( $referral ) {

		$data = [
			'referral' => $referral,
			'advocate' => $referral->get_advocate(),
		];

		if ( AW_Referrals()->options()->get_reward_event() === 'purchase' ) {
			$order = $referral->get_order();
			$data['user'] = AW()->order_helper->prepare_user_data_item( $order );
			$data['order'] = $order;
		}
		elseif ( AW_Referrals()->options()->get_reward_event() === 'signup' ) {
			$data['user'] = get_userdata( $referral->get_user_id() );
		}

		return $data;
	}


	function add_field_advocate_limit() {
		$field = ( new AutomateWoo\Fields\Number() )
			->set_name( 'limit_per_advocate' )
			->set_title( __( 'Limit per advocate', 'automatewoo' ))
			->set_description( __( 'Limit how many times this workflow will ever run for each advocate.', 'automatewoo'  ) )
			->set_placeholder( __( 'Leave blank for no limit', 'automatewoo'  ) );

		$this->add_field( $field );
	}


	/**
	 * @param $workflow_id int
	 * @param $advocate_id int
	 * @return int
	 */
	function get_times_run_for_advocate( $workflow_id, $advocate_id ) {

		$query = ( new AutomateWoo\Log_Query() )
			->where( 'workflow_id', $workflow_id )
			->where( 'advocate_id', $advocate_id );

		return $query->get_count();
	}


	/**
	 * @param $limit
	 * @param $workflow_id int
	 * @param $advocate_id int
	 *
	 * @return bool
	 */
	protected function validate_limit_per_advocate( $limit, $workflow_id, $advocate_id ) {

		if ( ! $limit  ) return true;

		if ( $limit <= $this->get_times_run_for_advocate( $workflow_id, $advocate_id ) )
			return false;

		return true;
	}

}
