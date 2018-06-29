<?php

namespace AutomateWoo;

/**
 * Cron manager
 * @class Cron
 * @package AutomateWoo
 */
class Cron {

	/** @var array : worker => schedule */
	static $workers = [
		'two_minute' => 'automatewoo_two_minutes',
		'five_minute' => 'automatewoo_five_minutes',
		'fifteen_minute' => 'automatewoo_fifteen_minutes',
		'thirty_minute' => 'automatewoo_thirty_minutes',
		'hourly' => 'hourly',
		'four_hourly' => 'automatewoo_four_hours',
		'daily' => 'daily',
		'two_days' => 'automatewoo_two_days',
		'weekly' => 'automatewoo_weekly'
	];

	static $strict_workers = [
		'daily' => 'daily'
	];


	/**
	 * Init cron
	 */
	static function init() {

		add_filter( 'cron_schedules', [ __CLASS__, 'add_schedules' ], 100 ); // set a high priority to fix issue from #141

		foreach ( self::$workers as $worker => $schedule ) {
			add_action( 'automatewoo_' . $worker . '_worker', [ __CLASS__, 'before_worker' ], 1 );
		}

		foreach ( self::$strict_workers as $worker => $schedule ) {
			add_action( 'automatewoo_' . $worker . '_worker_strict', [ __CLASS__, 'before_strict_worker' ], 1 );
		}

		add_action( 'admin_init', [ __CLASS__, 'add_events' ] );
	}


	/**
	 * Prevents workers from working if they have done so in the past 4 minutes
	 */
	static function before_worker() {

		if ( self::is_worker_locked( current_action() ) ) {
			// block action
			remove_all_actions( current_action() );
		}
		else {
			@set_time_limit(300); // 5 min

			// allow action
			self::update_worker_lock( current_action() );
		}
	}


	/**
	 * Before running a strict worker
	 */
	static function before_strict_worker() {

		$action = current_action();

		$last_run = self::get_last_run( $action );
		$interval = self::get_worker_interval( $action );

		if ( $last_run ) {
			// block strict worker has been run early
			if ( $last_run > ( time() - $interval ) ) {
				remove_all_actions( $action );
				return;
			}
		}

		self::update_last_run( $action );
	}


	/**
	 * @param $action
	 * @return string|bool
	 */
	static function get_last_run( $action ) {
		$last_runs = get_option('aw_workers_last_run');
		return ( is_array( $last_runs ) && isset( $last_runs[$action] ) ) ? $last_runs[$action] : false;
	}


	/**
	 * @param $action
	 */
	static function update_last_run( $action ) {
		$last_runs = get_option('aw_workers_last_run');

		if ( ! $last_runs ) $last_runs = [];

		$last_runs[$action] = time();

		update_option( 'aw_workers_last_run', $last_runs );
	}


	/**
	 * @param $action
	 * @return int|false
	 */
	static function get_worker_interval( $action ) {
		$schedules = wp_get_schedules();
		$schedule = wp_get_schedule( $action );

		if ( isset( $schedules[$schedule] ) ) {
			return $schedules[$schedule]['interval'];
		}

		return false;
	}


	/**
	 * @since 2.1.5
	 * @return string
	 */
	static function get_worker_lock() {
		$worker_lock = get_option( 'automatewoo_worker_lock' );
		return is_array( $worker_lock ) ? $worker_lock : [];
	}


	/**
	 * Checks if workers started running less than 4 minutes ago
	 *
	 * @since 2.1.5
	 * @param $action
	 * @return bool
	 */
	static function is_worker_locked( $action ) {

		if ( AW()->debug )
			return false;

		$worker_lock = self::get_worker_lock();

		if ( empty( $worker_lock[$action] ) ) return false;

		$time_last_run = $worker_lock[$action];

		$time_unblocked = new \DateTime();
		$time_unblocked->modify( '-4 minutes' );

		return $time_last_run > $time_unblocked->getTimestamp();
	}


	/**
	 * @since 2.1.5
	 * @param $action
	 */
	static function update_worker_lock( $action ) {

		$worker_lock = self::get_worker_lock();

		$worker_lock[$action] = current_time( 'timestamp', true );

		update_option( 'automatewoo_worker_lock', $worker_lock, false );
	}


	/**
	 * Add cron workers
	 */
	static function add_events() {

		foreach ( self::$workers as $worker => $schedule ) {
			$hook = 'automatewoo_' . $worker . '_worker';

			if ( ! wp_next_scheduled( $hook ) ) {
				wp_schedule_event( time(), $schedule, $hook );
			}
		}

		foreach ( self::$strict_workers as $worker => $schedule ) {
			$strict_hook = 'automatewoo_' . $worker . '_worker_strict';

			if ( ! wp_next_scheduled( $strict_hook ) ) {
				wp_schedule_event( time(), $schedule, $strict_hook );
			}
		}
	}


	/**
	 * @param $schedules
	 * @return mixed
	 */
	static function add_schedules( $schedules ) {

		$schedules['automatewoo_two_minutes'] = [
			'interval' => 120,
			'display' => __( 'Two minutes', 'automatewoo' )
		];

		$schedules['automatewoo_five_minutes'] = [
			'interval' => 300,
			'display' => __( 'Five minutes', 'automatewoo' )
		];

		$schedules['automatewoo_fifteen_minutes'] = [
			'interval' => 900,
			'display' => __( 'Fifteen minutes', 'automatewoo' )
		];

		$schedules['automatewoo_thirty_minutes'] = [
			'interval' => 1800,
			'display' => __( 'Thirty minutes', 'automatewoo' )
		];

		$schedules['automatewoo_two_days'] = [
			'interval' => 172800,
			'display' => __( 'Two days', 'automatewoo' )
		];

		$schedules['automatewoo_four_hours'] = [
			'interval' => 14400,
			'display' => __( 'Four hours', 'automatewoo' )
		];

		$schedules['automatewoo_weekly'] = [
			'interval' => 604800,
			'display' => __('Once weekly', 'automatewoo' )
		];

		return $schedules;
	}

}
