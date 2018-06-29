<?php

namespace AutomateWoo;

/**
 * @deprecated
 * @class Legacy_Background_Process_Handler
 * @since 2.6.1
 */
class Legacy_Background_Process_Handler {

	/**
	 * @param $hook
	 * @param $batch
	 * @param $args
	 */
	static function handle( $hook, $batch, $args ) {
		do_action( $hook, $batch, $args );
	}
}
