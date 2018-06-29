<?php

namespace AutomateWoo\Rules;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Customer_Role
 */
class Customer_Role extends Abstract_Select {

	public $data_item = 'customer';


	function init() {
		$this->title = __( 'Customer User Role', 'automatewoo' );
		$this->group = __( 'Customer', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_select_choices() {
		global $wp_roles;

		if ( ! isset( $this->select_choices ) ) {
			$this->select_choices = [];

			foreach( $wp_roles->roles as $key => $role ) {
				$this->select_choices[$key] = $role['name'];
			}

			$this->select_choices['guest'] = __( 'Guest', 'automatewoo' );
		}

		return $this->select_choices;
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $customer, $compare, $value ) {
		return $this->validate_select( $customer->get_role(), $compare, $value );
	}

}

return new Customer_Role();
