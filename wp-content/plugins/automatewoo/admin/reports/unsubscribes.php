<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Report_Unsubscribes
 */
class Report_Unsubscribes extends Admin_List_Table {

	public $_column_headers;
	public $max_items;


	function __construct() {
		parent::__construct([
			'singular' => __( 'Unsubscribe', 'automatewoo' ),
			'plural' => __( 'Unsubscribes', 'automatewoo' ),
			'ajax' => false
		]);
	}


	function filters() {
		$this->output_workflow_filter();
		$this->output_customer_filter();
	}


	/**
	 * @param $unsubscribe Unsubscribe
	 * @return string
	 */
	function column_cb( $unsubscribe ) {
		return '<input type="checkbox" name="unsubscribe_ids[]" value="' . absint( $unsubscribe->get_id() ) . '" />';
	}


	/**
	 * @param $unsubscribe Unsubscribe
	 * @param mixed $column_name
	 * @return string
	 */
	function column_default( $unsubscribe, $column_name ) {

		switch( $column_name ) {

			case 'workflow':
				return $this->format_workflow_title( AW()->get_workflow( $unsubscribe->get_workflow_id() ) );
				break;

			case 'email':
				return Format::customer( $unsubscribe->get_customer() );
				break;

			case 'time':
				return $this->format_date( $unsubscribe->date );
				break;
		}
	}


	function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'workflow'  => __( 'Workflow', 'automatewoo' ),
			'email' => __( 'Customer', 'automatewoo' ),
			'time' => __( 'Date', 'automatewoo' ),
		];

		return $columns;
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


	/**
	 * prepare_items function.
	 */
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
	 * Get Products matching stock criteria
	 */
	function get_items( $current_page, $per_page ) {

		$query = new Unsubscribe_Query();
		$query->set_calc_found_rows( true );
		$query->set_limit( $per_page );
		$query->set_offset( ($current_page - 1 ) * $per_page );
		$query->set_ordering('date', 'DESC');

		if ( ! empty( $_GET['_workflow'] ) ) {
			$query->where('workflow_id', absint( $_GET['_workflow'] ) );
		}

		if ( ! empty( $_GET['_customer_user'] ) ) {
			$query->where('user_id', absint( $_GET['_customer_user'] ) );
		}

		$res = $query->get_results();

		$this->items = $res;

		$this->max_items = $query->found_rows;
	}

}
