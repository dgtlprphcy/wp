<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Customer_Change_Role
 */
class Action_Customer_Change_Role extends Action {

	public $required_data_items = [ 'customer' ];


	function init() {
		$this->title = __( 'Change Customer Role', 'automatewoo' );
		$this->group = __( 'Customers', 'automatewoo' );
		$this->description = __( 'Please note that if the customer is a guest this action will do nothing.', 'automatewoo' );
	}


	function load_fields() {
		$user_type = new Fields\User_Role( false );
		$user_type->set_required();

		$this->add_field($user_type);
	}


	function run() {

		/** @var $customer Customer */
		if ( ! $customer = $this->workflow->get_data_item('customer') ) {
			return;
		}

		$role = Clean::string( $this->get_option('user_type') );

		if ( $user = $customer->get_user() ) {
			$user->set_role( $role );
		}
	}

}
