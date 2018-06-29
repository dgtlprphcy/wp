<?php

namespace AutomateWoo\Background_Processes;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	include_once( AW()->lib_path( '/wp-async-request.php' ) );
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	include_once( AW()->lib_path( '/wp-background-process.php' ) );
}

/**
 * Base background process class
 */
abstract class Base extends \WP_Background_Process {

	/** @var string */
	public $action;

	/** @var string */
	protected $prefix = 'aw';


	/**
	 * @return string
	 */
	abstract function get_title();


	/**
	 * @return boolean
	 */
	public function has_queued_items() {
		return false === $this->is_queue_empty();
	}


	/**
	 * Use this instead of dispatch to start process
	 * @return bool|\WP_Error
	 */
	public function start() {

		if ( empty( $this->data ) ) {
			$this->log( 'Started process but there were no items.' );
			return false;
		}

		$count = count( $this->data );
		$this->save();
		$dispatched = $this->dispatch();

		if ( is_wp_error( $dispatched ) ) {
			$this->log( sprintf( 'Unable to start process: %s', $dispatched->get_error_message() ) );
		}
		else {
			$this->log( sprintf( 'Process started for %s items.', $count ) );
		}

		return $dispatched;
	}


	/**
	 * Process completed
	 */
	protected function complete() {
		$this->log( 'Process completed.' );
		parent::complete();
	}


	/**
	 * Reduce time limit to 10s
	 * @return bool
	 */
	protected function time_exceeded() {
		$finish = $this->start_time + apply_filters( 'automatewoo/background_process/time_limit', 10 ); // 10 seconds
		$return = false;

		if ( time() >= $finish ) {
			$return = true;
		}

		return $return;
	}


	/**
	 * Reduce memory limit
	 * @return bool
	 */
	protected function memory_exceeded() {

		// use only 40% of max memory
		$memory_limit_percentage = apply_filters( 'automatewoo/background_process/memory_limit_percentage', 0.4 );

		$memory_limit = $this->get_memory_limit() * $memory_limit_percentage;
		$current_memory = memory_get_usage( true );
		$return = false;

		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}

		return $return;
	}


	/**
	 * Handle
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	protected function handle() {
		$this->lock_process();

		do {
			$batch = $this->get_batch();

			foreach ( $batch->data as $key => $value ) {
				$task = $this->task( $value );

				if ( false !== $task ) {
					$batch->data[ $key ] = $task;
				} else {
					unset( $batch->data[ $key ] );
				}

				if ( $this->time_exceeded() || $this->memory_exceeded() ) {
					// Batch limits reached.
					break;
				}
			}

			// Update or delete current batch.
			if ( ! empty( $batch->data ) ) {
				$this->update( $batch->key, $batch->data );
			} else {
				$this->delete( $batch->key );
			}
		} while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() );

		// throttle process here with sleep to try and prevent crashing mysql
		sleep( 5 );

		$this->unlock_process();

		// Start next batch or complete process.
		if ( ! $this->is_queue_empty() ) {
			$this->dispatch();
		} else {
			$this->complete();
		}
	}


	/**
	 * @param $message
	 */
	public function log( $message ) {
		$logger = new \WC_Logger();
		$logger->add( 'automatewoo-background-process', $this->action. ': ' . $message );
	}


	/**
	 * over-ridden due to issue https://github.com/A5hleyRich/wp-background-processing/issues/7
	 *
	 * this method actually creates a new batch rather it doesn't replace existing queued items
	 *
	 * @return $this
	 */
	public function save() {
		$key = $this->generate_key();

		if ( ! empty( $this->data ) ) {
			update_site_option( $key, $this->data );
		}

		$this->data = [];
		return $this;
	}


}
