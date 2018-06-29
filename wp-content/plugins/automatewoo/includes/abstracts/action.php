<?php

namespace AutomateWoo;

/**
 * @class Action
 */
abstract class Action {

	/** @var string */
	public $title;

	/** @var string */
	public $name;

	/** @var string */
	public $description;

	/** @var string */
	public $group;

	/** @var array */
	public $fields;

	/** @var array */
	public $options;

	/** @var Workflow */
	public $workflow;

	/** @var array */
	public $required_data_items = [];

	/** @var bool */
	public $can_be_previewed = false;


	/**
	 * @return void
	 */
	abstract function load_fields();


	/**
	 * @return void
	 */
	abstract function run();


	function __construct() {
		$this->group = __( 'Other', 'automatewoo' );
		$this->init();
	}


	/**
	 * Sets title and group
	 */
	abstract function init();


	/**
	 * @return string
	 */
	function get_name() {
		return $this->name;
	}


	/**
	 * @param string $name
	 */
	function set_name( $name ) {
		$this->name = $name;
	}


	/**
	 * @param bool $prepend_group
	 * @return string
	 */
	function get_title( $prepend_group = false ) {
		if ( $prepend_group && $this->group != 'Other' ) {
			return $this->group . ' - ' . $this->title;
		}

		return $this->title;
	}


	/**
	 * @return string|null
	 */
	function get_description() {
		return $this->description;
	}


	/**
	 * @return string
	 */
	function get_description_html() {

		if ( ! $this->get_description() )
			return '';

		return '<p class="aw-field-description">' . $this->get_description() .'</p>';
	}


	/**
	 * Should only be called from inside the load_fields() method
	 * @param $field Fields\Field
	 */
	function add_field( $field ) {
		$field->set_name_base( 'aw_workflow_data[actions]' );
		$this->fields[ $field->get_name() ] = $field;
	}


	/**
	 * @param $name
	 * @return Fields\Field|false
	 */
	function get_field( $name ) {

		$this->get_fields();

		if ( ! isset( $this->fields[$name] ) )
			return false;

		return $this->fields[$name];
	}


	/**
	 * @return Fields\Field[]
	 */
	function get_fields() {

		if ( ! isset( $this->fields ) ) {
			$this->fields = [];
			$this->load_fields();
		}

		return $this->fields;
	}


	/**
	 * @param $options
	 */
	function set_options( $options ) {
		$this->options = $options;
	}


	/**
	 * @param bool $field
	 * @param bool $replace_vars
	 * @param bool $allow_html
	 *
	 * @return mixed
	 */
	function get_option( $field, $replace_vars = false, $allow_html = false ) {

		$field = wp_check_invalid_utf8( $field );
		$value = false;

		if ( isset( $this->options[$field] ) ) {
			if ( $replace_vars ) {
				$value = $this->workflow->variable_processor()->process_field( $this->options[$field], $allow_html );
			}
			else {
				$value = $this->options[$field];
			}
		}

		return apply_filters( 'automatewoo_action_option', $value, $field, $replace_vars, $this );
	}


	/**
	 * @param $field_name
	 * @param $reference_field_value
	 * @return array
	 */
	function get_dynamic_field_options( $field_name, $reference_field_value = false ) {
		return [];
	}


	/**
	 *
	 */
	function check_requirements() {}


	function warning( $message ) {
		if ( ! is_admin() ) return;
?>
		<script type="text/javascript">
			alert('ERROR: <?php echo $message ?>');
		</script>
<?php
	}

}
