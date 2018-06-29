<?php

namespace AutomateWoo\Admin\Controllers;

use AutomateWoo\Clean;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Logs
 */
class Logs extends Base {


	function handle() {

		$action = $this->get_current_action();

		switch ( $action ) {
			case 'bulk_delete':
				$this->action_bulk_edit( str_replace( 'bulk_', '', $action ) );
				$this->output_list_table();
				break;

			default:
				$this->output_list_table();
				break;
		}
	}


	private function output_list_table() {

		include_once AW()->admin_path( '/reports/logs.php' );

		$table = new \AutomateWoo\Report_Logs();
		$table->prepare_items();
		$table->nonce_action = $this->get_nonce_action();

		$sidebar_content = '<p>' . __( 'Every time a workflow runs a log entry is created. Logs are used by some triggers to determine when they should and should not fire. For this reason deleting logs should generally be avoided.', 'automatewoo' ) . '</p>';

		$this->output_view( 'page-table-with-sidebar', [
			'table' => $table,
			'sidebar_content' => $sidebar_content
		]);
	}


	/**
	 * @param $action
	 */
	private function action_bulk_edit( $action ) {

		$this->verify_nonce_action();

		$ids = Clean::ids( aw_request( 'log_ids' ) );

		if ( empty( $ids ) ) {
			$this->add_error( __('Please select some logs to bulk edit.', 'automatewoo') );
			return;
		}

		foreach ( $ids as $id ) {

			if ( ! $log = AW()->get_log( $id ) ) {
				continue;
			}

			switch ( $action ) {
				case 'delete':
					$log->delete();
					break;
			}
		}

		$this->add_message( __('Bulk edit completed.', 'automatewoo' ) );
	}
}

return new Logs();
