<?php
/**
 * Plugin Name: AutomateWoo - Referrals Add-on
 * Plugin URI: http://automatewoo.com
 * Description: Refer a Friend add-on for AutomateWoo.
 * Version: 1.7.4
 * Author: AutomateWoo
 * Author URI: http://automatewoo.com
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: automatewoo-referrals
 */

// Copyright (c) AutomateWoo. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @class AW_Referrals_Plugin_Data
 */
class AW_Referrals_Plugin_Data {

	function __construct() {
		$this->id = 'automatewoo-referrals';
		$this->name = __( 'AutomateWoo - Referrals Add-on', 'automatewoo-referrals' );
		$this->version = '1.7.4';
		$this->file = __FILE__;
		$this->min_php_version = '5.4';
		$this->min_automatewoo_version = '3.2.4';
		$this->min_woocommerce_version = '2.6';
	}
}



/**
 * @class AW_Referrals_Loader
 */
class AW_Referrals_Loader {

	/** @var AW_Referrals_Plugin_Data */
	static $data;

	static $errors = array();


	/**
	 * @param AW_Referrals_Plugin_Data $data
	 */
	static function init( $data ) {
		self::$data = $data;

		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'load' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
	}


	static function load() {
		self::check();
		if ( empty( self::$errors ) ) {
			include 'includes/automatewoo-referrals.php';
		}
	}


	static function load_textdomain() {
		load_plugin_textdomain( 'automatewoo-referrals', false, "automatewoo-referrals/languages" );
	}


	/**
	 * @return bool
	 */
	static function check() {

		if ( version_compare( phpversion(), self::$data->min_php_version, '<' ) ) {
			self::$errors[] = sprintf( __( '<strong>%s</strong> requires PHP version %s+.' , 'automatewoo-referrals' ), self::$data->name, self::$data->min_php_version );
		}

		if ( ! self::is_automatewoo_active() ) {
			self::$errors[] = sprintf( __( '<strong>%s</strong> requires AutomateWoo to be installed and activated.' , 'automatewoo-referrals' ), self::$data->name );
		}
		elseif ( ! self::is_automatewoo_version_ok() ) {
			self::$errors[] = sprintf(__( '<strong>%s</strong> requires AutomateWoo version %s or later. Please update to the latest version.', 'automatewoo-referrals' ), self::$data->name, self::$data->min_automatewoo_version );
		}

		if ( ! self::is_woocommerce_version_ok() ) {
			self::$errors[] = sprintf(__( '<strong>%s</strong> requires WooCommerce version %s or later.', 'automatewoo-referrals' ), self::$data->name, self::$data->min_woocommerce_version );
		}
	}


	/**
	 * @return bool
	 */
	static function is_automatewoo_active() {
		return function_exists( 'AW' );
	}


	/**
	 * @return bool
	 */
	static function is_automatewoo_version_ok() {
		if ( ! function_exists( 'AW' ) ) return false;
		return version_compare( AW()->version, self::$data->min_automatewoo_version, '>=' );
	}


	/**
	 * @return bool
	 */
	static function is_woocommerce_version_ok() {
		if ( ! function_exists( 'WC' ) ) return false;
		if ( ! self::$data->min_woocommerce_version ) return true;
		return version_compare( WC()->version, self::$data->min_woocommerce_version, '>=' );
	}


	static function admin_notices() {
		if ( empty( self::$errors ) ) return;
		echo '<div class="notice notice-warning"><p>';
		echo implode( '<br>', self::$errors );
		echo '</p></div>';
	}


}

AW_Referrals_Loader::init( new AW_Referrals_Plugin_Data() );
