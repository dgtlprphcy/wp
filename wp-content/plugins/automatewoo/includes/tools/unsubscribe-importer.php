<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Tool_Unsubscribe_Importer
 * @since 2.7.5
 */
class Tool_Unsubscribe_Importer extends Tool {

	public $id = 'unsubscribe_importer';


	/**
	 * Constructor
	 */
	function __construct() {

		$this->title = __( 'Unsubscribe Importer', 'automatewoo' );
		$this->description = __( "Unsubscribe users from a workflow by importing email addresses.", 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_form_fields() {

		$fields = [];

		$fields[] = ( new Fields\Workflow() )
			->set_name_base(' args' )
			->set_required();

		$fields[] = ( new Fields\Text_Area() )
			->set_name( 'emails' )
			->set_title( __( 'Emails', 'automatewoo' ) )
			->set_name_base( 'args' )
			->set_rows( 20 )
			->set_placeholder( __( 'Add one email per line...', 'automatewoo' ) )
			->set_required();

		return $fields;
	}


	/**
	 * Parse emails but don't actually check if they are valid
	 *
	 * @param $emails
	 * @return array
	 */
	function parse_emails( $emails ) {

		$emails = explode( PHP_EOL, $emails );
		$emails = array_map( 'trim', $emails );

		return $emails;
	}


	/**
	 * @param $args
	 * @return bool|\WP_Error
	 */
	function validate_process( $args ) {

		$args = $this->sanitize_args( $args );

		if ( empty( $args['workflow'] ) || empty( $args['emails'] ) ) {
			return new \WP_Error( 1, __( 'Missing a required field.', 'automatewoo') );
		}

		$workflow = AW()->get_workflow( $args['workflow'] );

		if ( ! $workflow ) {
			return new \WP_Error( 2, __( 'Error with the selected workflow.', 'automatewoo') );
		}

		$emails = $this->parse_emails( $args['emails'] );

		foreach( $emails as $email ) {
			if ( ! is_email( $email ) ) {
				return new \WP_Error( 3, sprintf( __( '%s is not a valid email.', 'automatewoo' ), $email ) );
			}
		}

		return true;
	}


	/**
	 * @param $args
	 * @return bool|\WP_Error
	 */
	function process( $args ) {

		$args = $this->sanitize_args( $args );
		$workflow = AW()->get_workflow( $args['workflow'] );
		$emails = $this->parse_emails( $args['emails'] );

		if ( ! $workflow || empty( $emails ) ) {
			return new \WP_Error( 2, __( 'Could not process...', 'automatewoo') );
		}

		foreach ( $emails as $email ) {
			if ( ! $workflow->is_unsubscribed( $email ) ) {
				$workflow->unsubscribe_email( $email );
			}
		}

		return true;
	}


	/**
	 * @param $args
	 */
	function display_confirmation_screen( $args ) {

		$args = $this->sanitize_args( $args );

		$workflow = AW()->get_workflow( $args[ 'workflow' ] );
		$emails = $this->parse_emails( $args['emails'] );

		$number_to_preview = 25;

		echo '<p>' .
			sprintf(
				__( 'Are you sure you want to unsubscribe <strong>%s users</strong> for the workflow <strong>%s</strong>.This can not be undone.', 'automatewoo' ),
				count( $emails ), $workflow->title )
			. '</p>';


		echo '<p>';

		foreach ( $emails as $i => $email ) {

			if ( $i == $number_to_preview )
				break;

			echo $email . '<br>';
		}

		if ( count( $emails ) > $number_to_preview ) {
			echo '+ ' . ( count( $emails ) - $number_to_preview ) . ' more emails...';
		}

		echo '</p>';
	}


	/**
	 * @param array $args
	 * @return array
	 */
	function sanitize_args( $args ) {

		if ( isset( $args['workflow'] ) ) {
			$args['workflow'] = absint( $args[ 'workflow' ] );
		}

		if ( isset( $args['emails'] ) ) {
			$args['emails'] = Clean::textarea( $args['emails'] );
		}

		return $args;
	}

}

return new Tool_Unsubscribe_Importer();
