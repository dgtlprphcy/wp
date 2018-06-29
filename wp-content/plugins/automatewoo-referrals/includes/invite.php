<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Invite
 * @since 1.3
 *
 * @property $advocate_id
 * @property $referral_id
 * @property $date
 * @property $email
 */
class Invite extends AutomateWoo\Model {

	/** @var string  */
	public $table_id = 'referral-invites';

	/** @var string  */
	public $object_type = 'referral-invite';


	/**
	 * @param $id
	 */
	function __construct( $id = false ) {
		if ( $id ) $this->get_by( 'id', $id );
	}


	/**
	 * @return int
	 */
	function get_advocate_id() {
		return absint( $this->advocate_id );
	}


	/**
	 * @param $id
	 */
	function set_advocate_id( $id ) {
		$this->advocate_id = absint( $id );
	}


	/**
	 * @param \DateTime $date
	 */
	function set_date( $date ) {
		if ( $date instanceof \DateTime ) {
			$this->date = $date->format( AutomateWoo\Format::MYSQL );
		}
	}


	/**
	 * @return \DateTime|false
	 */
	function get_date() {
		if ( ! $this->date ) {
			return false;
		}
		return new \DateTime( $this->date );
	}


	/**
	 * @return string
	 */
	function get_email() {
		return (string) $this->email;
	}


	/**
	 * @param string $email
	 */
	function set_email( $email ) {
		$this->email = $email;
	}


}