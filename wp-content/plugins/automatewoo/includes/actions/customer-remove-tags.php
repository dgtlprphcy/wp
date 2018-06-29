<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Customer_Remove_Tags
 */
class Action_Customer_Remove_Tags extends Action_Customer_Add_Tags {

	function init() {
		parent::init();
		$this->title = __( 'Remove Tags From Customer', 'automatewoo' );
	}


	function run() {

		/** @var $customer Customer */
		if ( ! $customer = $this->workflow->get_data_item('customer') ) {
			return;
		}

		$tags = Clean::recursive( $this->get_option( 'user_tags' ) );

		if ( ! $customer->is_registered() || empty( $tags ) ) {
			return;
		}

		wp_remove_object_terms( $customer->get_user_id(), $tags, 'user_tag' );
	}

}
