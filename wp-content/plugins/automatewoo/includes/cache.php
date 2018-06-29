<?php

namespace AutomateWoo;

/**
 * @class Cache
 * @since 2.1.0
 */
class Cache {

	/** @var bool */
	static $enabled = true;


	/**
	 * @return int (hours)
	 */
	static function get_default_transient_expiration() {
		return apply_filters( 'automatewoo_cache_default_expiration', 6 );
	}


	/**
	 * @param $key
	 * @param $value
	 * @param bool|int $expiration - In hours. Optional.
	 * @return bool
	 */
	static function set_transient( $key, $value, $expiration = false ) {
		if ( ! self::$enabled ) return false;
		if ( ! $expiration ) $expiration = self::get_default_transient_expiration();
		return set_transient( 'aw_cache_' . $key, $value, $expiration * HOUR_IN_SECONDS );
	}


	/**
	 * @param $key
	 * @return bool|mixed
	 */
	static function get_transient( $key ) {
		if ( ! self::$enabled ) return false;
		return get_transient( 'aw_cache_' . $key );
	}


	/**
	 * @param $key
	 */
	static function delete_transient( $key ) {
		delete_transient( 'aw_cache_' . $key );
	}



	/**
	 * Only sets if key is not falsy
	 * @param string $key
	 * @param mixed $value
	 * @param string $group
	 */
	static function set( $key, $value, $group ) {
		if ( ! $key ) return;
		wp_cache_set( $key, $value, "automatewoo_$group" );
	}


	/**
	 * Only gets if key is not falsy
	 * @param $key
	 * @param $group
	 * @return bool|mixed
	 */
	static function get( $key, $group ) {
		if ( ! $key ) return false;
		return wp_cache_get( $key, "automatewoo_$group" );
	}


	/**
	 * @param $key
	 * @param $group
	 * @return bool
	 */
	static function exists( $key, $group ) {
		if ( ! $key ) return false;
		$found = false;
		wp_cache_get( $key, "automatewoo_$group", false, $found );
		return $found;
	}


	/**
	 * Only deletes if key is not falsy
	 * @param $key
	 * @param $group
	 */
	static function delete( $key, $group ) {
		if ( ! $key ) return;
		wp_cache_delete( $key, "automatewoo_$group" );
	}


}
