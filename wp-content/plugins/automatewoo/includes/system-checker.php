<?php

namespace AutomateWoo;

/**
 * @class System_Checker
 * @since 2.3
 */
class System_Checker {

	/** @var array */
	private $system_checks;

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'admin_init', [ $this, 'maybe_background_check' ] );
		add_action( 'admin_notices', [ $this, 'maybe_display_notices' ] );
	}


	/**
	 * @return array
	 */
	function get_checks() {

		if ( ! isset( $this->system_checks ) ) {

			$path = AW()->path( '/includes/system-checks/' );

			$includes = apply_filters( 'automatewoo/system_checks', [
				'cron_running' => $path . 'cron-running.php',
				'database_tables_exist' => $path . 'database-tables-exist.php',
			]);

			foreach ( $includes as $system_check_id => $include ) {
				$class = include_once $include;
				$this->system_checks[ $system_check_id ] = $class;
			}
		}

		return $this->system_checks;
	}


	/**
	 * Maybe background check for high priority issues
	 */
	function maybe_background_check() {

		// don't do system check if just installed
		if ( did_action( 'automatewoo_installed' ) )
			return;

		if ( get_transient('automatewoo_background_system_check') || ! AW()->options()->enable_background_system_check )
			return;

		foreach( $this->get_checks() as $check ) {

			/** @var \AW_System_Check $check */
			if ( ! $check->high_priority )
				continue;

			$response = $check->run();

			if ( $response['success'] == false ) {
				set_transient( 'automatewoo_background_system_check_errors', true, DAY_IN_SECONDS );
				continue;
			}
		}

		set_transient( 'automatewoo_background_system_check', true, DAY_IN_SECONDS * 4 );
	}


	/**
	 *
	 */
	function maybe_display_notices() {
		if ( ! get_transient('automatewoo_background_system_check_errors') || ! current_user_can('manage_woocommerce') ) return;

		$strong = __( 'AutomateWoo system check has found issues.', 'automatewoo' );
		$more = sprintf( __( '<a href="%s">View details</a>', 'automatewoo' ), Admin::page_url( 'system-check' ) );

		Admin::notice('error is-dismissible', $strong, $more, 'aw-notice-system-error' );
	}


}
