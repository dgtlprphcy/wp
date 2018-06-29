<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Unsubscribe
 * @since 2.1.0
 *
 * @property $workflow_id int
 * @property $user_id int
 * @property $email string
 * @property $date string
 */
class Unsubscribe extends Model {

	/** @var string */
	public $table_id = 'unsubscribes';

	/** @var string  */
	public $object_type = 'unsubscribe';


	/**
	 * @param bool|int $id
	 */
	function __construct( $id = false ) {
		if ( $id ) $this->get_by( 'id', $id );
	}


	/**
	 * @param $id
	 */
	function set_workflow_id( $id ) {
		$this->workflow_id = $id;
	}


	/**
	 * @return int
	 */
	function get_workflow_id() {
		return (int) $this->workflow_id;
	}


	/**
	 * @param $id
	 */
	function set_user_id( $id ) {
		$this->user_id = $id;
	}


	/**
	 * @return int
	 */
	function get_user_id() {
		return (int) $this->user_id;
	}


	/**
	 * @param $email
	 */
	function set_email( $email ) {
		$this->email = strtolower( $email );
	}


	/**
	 * @return string
	 */
	function get_email() {
		return $this->email;
	}


	/**
	 * @param $date
	 */
	function set_date( $date ) {
		$this->date = $date;
	}


	/**
	 * @return string
	 */
	function get_date() {
		return $this->date;
	}


	/**
	 * @return Customer|bool
	 */
	function get_customer() {
		if ( $this->get_user_id() ) {
			return Customer_Factory::get_by_user_id( $this->get_user_id() );
		}
		else {
			return Customer_Factory::get_by_email( $this->get_email() );
		}
	}

}

