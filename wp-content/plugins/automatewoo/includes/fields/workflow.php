<?php

namespace AutomateWoo\Fields;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Workflow
 */
class Workflow extends Select {

	protected $name = 'workflow';

	/** @var array  */
	public $query_args = [];


	/**
	 * @param bool $show_placeholder
	 */
	function __construct( $show_placeholder = true ) {
		parent::__construct( $show_placeholder );
		$this->set_title( __( 'Workflow', 'automatewoo' ) );
	}


	/**
	 * @return array
	 */
	function get_options() {

		$args = array_merge([
			'post_type' => 'aw_workflow',
			'post_status' => 'any',
			'posts_per_page' => -1
		], $this->query_args );

		$workflows = new \WP_Query($args);

		$options = [];

		if ( $workflows->have_posts() ) {
			foreach( $workflows->posts as $workflow ) {
				$options[$workflow->ID] = $workflow->post_title;
			}
		}

		return $options;
	}


	/**
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	function add_query_arg( $key, $value ) {
		$this->query_args[$key] = $value;
		return $this;
	}

}
