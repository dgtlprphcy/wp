<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Report_Logs
 */
class Report_Logs extends Admin_List_Table {

	public $_column_headers;
	public $max_items;


	function __construct() {
		parent::__construct([
			'singular' => __( 'Log', 'automatewoo' ),
			'plural' => __( 'Logs', 'automatewoo' ),
			'ajax' => false
		]);
	}


	function filters() {
		$this->output_workflow_filter();
		$this->output_customer_filter();
	}


	function no_items() {
		_e( 'No logs found.', 'automatewoo' );
	}


	/**
	 * @param $log Log
	 * @return string
	 */
	function column_cb( $log ) {
		return '<input type="checkbox" name="log_ids[]" value="' . absint( $log->get_id() ) . '" />';
	}


	/**
	 * @param Log $log
	 * @param mixed $column_name
	 * @return string
	 */
	function column_default( $log, $column_name ) {

		switch( $column_name ) {
			case 'id':
				echo '#' . $log->get_id();
				break;

			case 'workflow':
				return $this->format_workflow_title( $log->get_workflow() );
				break;

			case 'user':

				$data_layer = $log->get_data_layer( 'object' );

				if ( $data_layer->get_customer() ) {
					return Format::customer( $data_layer->get_customer() );
				}
				elseif ( $data_layer->get_guest() ) {
					return $this->format_guest( $data_layer->get_guest()->get_email() );
				}
				elseif ( $data_layer->get_user() ) {
					return $this->format_user( $data_layer->get_user() );
				}
				else {
					return $this->format_blank();
				}
				break;

			case 'time':
				return $this->format_date( $log->date );
				break;

			case 'actions':

				$url = add_query_arg([
					'action' => 'aw_modal_log_info',
					'log_id' => $log->get_id()
					], admin_url('admin-ajax.php') );

				echo '<a class="button view aw-button-icon js-open-automatewoo-modal" data-automatewoo-modal-type="ajax" href="' . $url . '">View</a>';

				break;
		}
	}

	/**
	 * get_columns function.
	 */
	function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'id'  => __( 'Log', 'automatewoo' ),
			'workflow'  => __( 'Workflow', 'automatewoo' ),
			'user' => __( 'Customer', 'automatewoo' ),
			'time' => __( 'Time', 'automatewoo' ),
			'actions' => '',
		];

		return $columns;
	}


	function prepare_items() {

		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];
		$current_page = absint( $this->get_pagenum() );
		$per_page = apply_filters( 'automatewoo_report_items_per_page', 20 );

		$this->get_items( $current_page, $per_page );

		$this->set_pagination_args([
			'total_items' => $this->max_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		]);
	}


	/**
	 * @param $current_page
	 * @param $per_page
	 */
	function get_items( $current_page, $per_page ) {

		$query = new Log_Query();
		$query->set_calc_found_rows( true );
		$query->set_limit( $per_page );
		$query->set_offset( ($current_page - 1 ) * $per_page );
		$query->set_ordering('date', 'DESC');

		if ( ! empty($_GET['_workflow']) ) {
			$query->where('workflow_id', absint( $_GET['_workflow'] ) );
		}

		if ( ! empty($_GET['_customer_user']) ) {

			$user_id = absint( $_GET['_customer_user'] );
			$customer = Customer_Factory::get_by_user_id( $user_id );

			// match by user OR customer
			$query->where_meta[] = [
				[
					'key' => 'user_id',
					'value' => $user_id
				],
				[
					'key' => '_data_layer_customer',
					'value' => $customer->get_id()
				]
			];
		}

		$this->items = $query->get_results();
		$this->max_items = $query->found_rows;

	}


	/**
	 * Retrieve the bulk actions
	 */
	function get_bulk_actions() {
		$actions = [
			'bulk_delete' => __( 'Delete', 'automatewoo' ),
		];

		return $actions;
	}


}
