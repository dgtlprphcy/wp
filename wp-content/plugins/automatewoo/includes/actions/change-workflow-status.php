<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Change_Workflow_Status
 */
class Action_Change_Workflow_Status extends Action {

	public $required_data_items = [ 'workflow' ];


	function init() {
		$this->title = __( 'Change Workflow Status', 'automatewoo');
		$this->group = __( 'AutomateWoo', 'automatewoo');
	}


	function load_fields() {
		$status = ( new Fields\Select( false ) )
			->set_name('status')
			->set_title(__('Status', 'automatewoo'))
			->set_options([
				'publish' => __( 'Active', 'automatewoo' ),
				'aw-disabled' => __( 'Disabled', 'automatewoo' )
			])
			->set_required();

		$this->add_field($status);
	}


	function run() {
		/** @var Workflow $workflow */
		$workflow = $this->workflow->get_data_item('workflow');
		$status = Clean::string( $this->get_option('status') );

		if ( ! $status || ! $workflow ) {
			return;
		}

		$workflow->update_status( $status );
	}
}
