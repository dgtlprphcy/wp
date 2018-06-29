<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Model;
use AutomateWoo\Clean;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Advocate_Key
 * @since 1.1.4
 *
 * @property $advocate_id
 * @property $advocate_key
 * @property $created
 */
class Advocate_Key extends Model {

	/** @var string  */
	public $table_id = 'referral-advocate-keys';

	/** @var string  */
	public $object_type = 'referral-advocate-key';


	/**
	 * @param $id
	 */
	function __construct( $id = false ) {
		if ( $id ) $this->get_by( 'id', $id );
	}


	/**
	 * @return mixed
	 */
	function get_key() {
		return Clean::string( $this->advocate_key );
	}


	/**
	 * @return int
	 */
	function get_advocate_id() {
		return absint( $this->advocate_id );
	}


	/**
	 * @return bool
	 */
	function is_expired() {

		if ( ! AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			return false;
		}

		$expiry_date = $this->get_date_expires();

		if ( ! $expiry_date ) {
			return false;
		}

		return $expiry_date->getTimestamp() < time();
	}


	/**
	 * @return \DateTime|false
	 */
	function get_date_expires() {

		if ( ! AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			return false;
		}

		$expiry = AW_Referrals()->options()->get_advocate_key_expiry();
		$date = $this->get_date_created();

		if ( ! $date ) {
			return false;
		}

		$date->modify( "+$expiry weeks" );
		return $date;
	}


	/**
	 * @return Advocate
	 */
	function get_advocate() {
		return new Advocate( $this->get_advocate_id() );
	}


	/**
	 * @return \DateTime|false
	 */
	function get_date_created() {
		return $this->get_date_column( 'created' );
	}


	/**
	 * @param $date \DateTime
	 */
	function set_date_created( $date ) {
		$this->set_date_column( 'created', $date );
	}

}
