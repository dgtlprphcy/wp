<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Active_Campaign_Update_Contact_Field
 */
class Action_Active_Campaign_Update_Contact_Field extends Action_Active_Campaign_Abstract {


	function init() {
		$this->title = __( 'Update Contact Custom Field', 'automatewoo' );
		parent::init();
	}


	function load_fields() {

		$field_options = [];

		foreach ( Integrations::activecampaign()->get_contact_custom_fields() as $field ) {
			$field_options[ $field->id ] = $field->title;
		}

		$field = ( new Fields\Select() )
			->set_name( 'field' )
			->set_title( __( 'Field', 'automatewoo' ) )
			->set_options( $field_options )
			->set_required();

		$value = ( new Fields\Text() )
			->set_name( 'value' )
			->set_title( __( 'Value', 'automatewoo' ) )
			->set_variable_validation();

		$this->add_contact_email_field();
		$this->add_field( $field );
		$this->add_field( $value );
	}


	/**
	 * @return void
	 */
	function run() {

		$email = Clean::email( $this->get_option( 'email', true ) );
		$field_id = Clean::string( $this->get_option( 'field' ) );
		$value = Clean::string( $this->get_option( 'value', true ) );

		$api = Integrations::activecampaign();

		if ( ! $api->is_contact( $email ) ) {
			$this->workflow->add_action_log_note( $this, __( 'Failed because contact did not exist.', 'automatewoo' ) );
			return;
		}

		$contact = [
			'email' => $email,
			"field[$field_id,0]" => $value
		];

		$api->request( 'contact/sync', $contact );
	}

}
