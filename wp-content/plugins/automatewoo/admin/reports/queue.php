<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Report_Queue
 */
class Report_Queue extends Admin_List_Table {

	public $_column_headers;

	public $max_items;


	function __construct() {
		parent::__construct([
			'singular' => __( 'Event', 'automatewoo' ),
			'plural' => __( 'Events', 'automatewoo' ),
			'ajax' => false
		]);
	}


	function filters() {
		$this->output_workflow_filter();
	}


	/**
	 * @param $queued_event Queued_Event
	 * @return string
	 */
	function column_cb( $queued_event ) {
		return '<input type="checkbox" name="queued_event_ids[]" value="' . absint( $queued_event->get_id() ) . '" />';
	}


	/**
	 * @param $queued_event Queued_Event
	 * @param mixed $column_name
	 * @return string
	 */
	function column_default( $queued_event, $column_name ) {

		$workflow = $queued_event->get_workflow();
		$workflow->set_data_layer( $queued_event->get_data_layer(), true );

		switch( $column_name ) {

			case 'queued_event_id':
				echo '#' . $queued_event->get_id() . '';
				echo ( $queued_event->failed ? '<span class="error-message"> - ' . esc_attr__( 'Failed', 'automatewoo' ) . '</span>' : '' );
				break;

			case 'workflow':
				return $this->format_workflow_title( $workflow );
				break;


			case 'user':

				if ( ! $workflow ) {
					return $this->format_blank();
				}

				if ( $customer = $workflow->data_layer()->get_customer() ) {
					return Format::customer( $customer );
				}
                elseif ( $guest = $workflow->data_layer()->get_guest() ) {
					$customer = Customer_Factory::get_by_guest_id( $guest->get_id() );
					return Format::customer( $customer );
				}
				else {
					return $this->format_blank();
				}

				break;

			case 'date':

			    if ( ! $due_date = $queued_event->get_date_due() ) {
			        return $this->format_blank();
                }

				if ( $due_date->getTimestamp() > time() ) {
					return $this->format_date( $due_date );
				}
				else {
					return __( 'now', 'automatewoo' );
				}

				break;

			case 'actions':

                $modal_url = add_query_arg([
                    'action' => 'aw_modal_queue_info',
                    'queued_event_id' => $queued_event->get_id()
                ], admin_url('admin-ajax.php') );

				$run_url = wp_nonce_url(
					add_query_arg([
						'action' => 'run_now',
						'queued_event_id' => $queued_event->get_id()
					]),
					$this->nonce_action
				);

				?>
                <a class="button view aw-button-icon js-open-automatewoo-modal" data-automatewoo-modal-type="ajax" href="<?php echo $modal_url ?>"><?php _e( 'View', 'automatewoo' ) ?></a>
                <a class="button" href="<?php echo $run_url; ?>"><?php $queued_event->is_failed() ? esc_attr_e( 'Retry', 'automatewoo' ) : esc_attr_e( 'Run Now', 'automatewoo' ) ?></a>
				<?php

				break;

		}
	}

	/**
	 * get_columns function.
	 */
	function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'queued_event_id' => __( 'Queued Event', 'automatewoo' ),
			'workflow' => __( 'Workflow', 'automatewoo' ),
			'user' => __( 'Customer', 'automatewoo' ),
			'date' => __( 'Run Date', 'automatewoo' ),
			'actions' => '',
		];

		return $columns;
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
			'per_page' => $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		]);
	}



	/**
	 * Get Products matching stock criteria
	 */
	function get_items( $current_page, $per_page ) {

		$query = new Queue_Query();
		$query->set_calc_found_rows( true );
		$query->set_limit( $per_page );
		$query->set_offset( ($current_page - 1 ) * $per_page );
		$query->set_ordering('date', 'ASC');

		if ( ! empty( $_GET[ '_workflow' ] ) ) {
			$query->where( 'workflow_id', absint( $_GET['_workflow'] ) );
		}

		$res = $query->get_results();

		$this->items = $res;

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
