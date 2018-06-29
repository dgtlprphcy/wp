<?php
/**
 * Update to 2.9.6
 * - Queue meta table
 */

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

class Database_Update_2_9_6 extends Database_Update {

	public $version = '2.9.6';


	/**
	 * @return bool
	 */
	protected function process() {

		$query = new Queue_Query();
		$query->where( 'data_items', '', '=' );
		$query->set_limit( 25 );
		$results = $query->get_results();

		if ( empty( $results ) ) {
			// no more items to process return complete...
			return true;
		}


		foreach ( $results as $queued_event ) {

			$queued_event->data_items = [
				'order' => '3845',
				'user' => '3'
			];
			$queued_event->save();
			$this->items_processed++;
		}

		return false;

	}

}

return new Database_Update_2_9_6();
