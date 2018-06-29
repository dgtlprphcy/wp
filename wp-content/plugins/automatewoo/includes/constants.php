<?php

namespace AutomateWoo;

/**
 * @class Constants
 */
class Constants {


	static function init() {
		self::set_defaults();
	}


	static function set_defaults() {

		if ( ! defined('AW_PREVENT_WORKFLOWS') ) {
			define( 'AW_PREVENT_WORKFLOWS', false );
		}

		if ( ! defined('AUTOMATEWOO_DISABLE_ASYNC_ORDER_CREATED') ) {
			define( 'AUTOMATEWOO_DISABLE_ASYNC_ORDER_CREATED', false );
		}

		if ( ! defined('AUTOMATEWOO_DISABLE_ASYNC_CUSTOMER_NEW_ACCOUNT') ) {
			define( 'AUTOMATEWOO_DISABLE_ASYNC_CUSTOMER_NEW_ACCOUNT', false );
		}

	}

}
