<?php

namespace AutomateWoo;

/**
 * @class Temporary_Data
 * @since 2.9
 */
class Temporary_Data {

	/** @var array  */
	static $data = [];


	/**
	 * @param $type
	 * @param $key
	 * @param $value
	 */
	static function set( $type, $key, $value ) {
		self::setup_type( $type );
		self::$data[ $type ][ $key ] = $value;
	}


	/**
	 * @param $type
	 * @param $key
	 */
	static function delete( $type, $key ) {
		self::setup_type( $type );
		unset( self::$data[ $type ][ $key ] );
	}


	/**
	 * @param $type
	 * @param $key
	 * @return bool
	 */
	static function exists( $type, $key ) {
		self::setup_type( $type );
		return isset( self::$data[ $type ][ $key ] );
	}


	/**
	 * @param $type
	 * @param $key
	 * @return mixed
	 */
	static function get( $type, $key ) {
		self::setup_type( $type );

		if ( isset( self::$data[ $type ][ $key ] ) ) {
			return self::$data[ $type ][ $key ];
		}

		return false;
	}


	/**
	 * @param $type
	 */
	static function setup_type( $type ) {
		if ( ! isset( self::$data[ $type ] ) ) {
			self::$data[ $type ] = [];
		}
	}


	/**
	 * Remove all data and reset
	 */
	static function reset() {
		self::$data = [];
	}

}
