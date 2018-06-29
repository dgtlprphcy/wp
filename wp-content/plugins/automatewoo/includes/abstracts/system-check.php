<?php
/**
 * @class 		AW_System_Check
 * @package		AutomateWoo/Abstracts
 * @since 		2.3
 */

abstract class AW_System_Check {

	/** @var string */
	public $title;

	/** @var string */
	public $description;

	/** @var bool */
	public $high_priority = false;


	/**
	 * @return array
	 */
	abstract function run();


	/**
	 * @param string $message
	 * @return array
	 */
	function success( $message = '' ) {
		return [
			'success' => true,
			'message' => $message
		];
	}


	/**
	 * @param string $message
	 * @return array
	 */
	function error( $message = '' ) {
		return [
			'success' => false,
			'message' => $message
		];
	}

}