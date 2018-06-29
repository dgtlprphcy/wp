<?php

namespace AutomateWoo;

/**
 * Process variables into values. Is used on workflows and action options.
 *
 * @class Variable_Processor
 * @since 2.0.2
 */
class Variables_Processor {

	/** @var Workflow */
	public $workflow;


	/**
	 * @param $workflow
	 */
	function __construct( $workflow ) {
		$this->workflow = $workflow;
	}


	/**
	 * @param $text string
	 * @param bool $allow_html
	 * @return string
	 */
	function process_field( $text, $allow_html = false ) {

		$replacer = new Replace_Helper( $text, [ $this, '_callback_process_field' ], 'variables' );
		$value = $replacer->process();

		if ( ! $allow_html ) {
			$value = html_entity_decode( strip_tags( $value ) );
		}

		return $value;
	}


	/**
	 * @param $string
	 * @return bool|mixed
	 */
	function _callback_process_field( $string ) {

		$string = $this->sanitize( $string );

		$data_type_and_field = $this->parse_data_type_and_field( $string );

		if ( ! $data_type_and_field ) {
			return false;
		}

		list( $data_type, $data_value ) = $data_type_and_field;

		$parameters = $this->parse_parameters( $string );

		$value = $this->get_variable_value( $data_type, $data_value, $parameters );

		$value = apply_filters( 'automatewoo/variables/after_get_value', $value, $data_type, $data_value, $parameters, $this->workflow );

		if ( ! $value ) {
			// backwards compatibility
			if ( isset( $parameters['default'] ) )
				$parameters['fallback'] = $parameters['default'];

			// show default if set and no real value
			if ( isset( $parameters['fallback'] ) )
				$value = $parameters['fallback'];
		}

		return $value;
	}


	/**
	 * @param $string
	 * @return false|string
	 */
	static function parse_data_type_and_field( $string ) {

		if ( ! strstr( $string, '.' ) )
			return false;

		// Only explode once so a full stop can be used in params
		$data = explode( '.', $string, 2 );

		// remove params
		if ( $pos = strpos( $data[1], '|' ) ) {
			$data[1] = substr( $data[1], 0, $pos );
		}

		$data = array_map( 'sanitize_title', $data );
		return $data;
	}


	/**
	 * @param $string
	 * @return array
	 */
	static function parse_parameters( $string ) {

		$return = [];

		if ( ! $splitter_pos = strpos( $string, '|' ) ) {
			return $return;
		}

		$string = substr( $string, $splitter_pos + 1 );

		// split by commas ignoring commas that are inside single quotes
		$parameters = preg_split('/(,)(?=(?:[^\']|\'[^\']*\')*$)/', $string );

		foreach ( $parameters as $parameter ) {

			if ( ! strstr( $parameter, ':' ) ) {
				continue;
			}

			list( $key, $value ) = explode( ':', $parameter, 2 );

			$key = sanitize_title($key);
			$value = sanitize_text_field( self::unquote( $value ) );

			$return[ $key ] = $value;
		}

		return $return;
	}


	/**
	 * Remove single quotes from start and end of a string
	 * @param $string
	 * @return string
	 */
	static function unquote( $string ) {
		return trim( trim( $string ), "'" );
	}


	/**
	 * @param $data_type
	 * @param $data_field
	 * @param $parameters
	 * @return mixed
	 */
	function get_variable_value( $data_type, $data_field, $parameters = [] ) {

		// Short circuit filter
		if ( $filtered = apply_filters( 'automatewoo_text_variable_value', false, $data_type, $data_field ) )
			return $filtered;

		$this->_compatibility( $data_type, $data_field, $parameters );

		$variable = "$data_type.$data_field";
		$variable_obj = Variables::get_variable( $variable );

		if ( method_exists( $variable_obj, 'get_value' ) ) {

			$empty_data_types = [ 'shop' ];

			if ( in_array( $data_type, $empty_data_types ) ) {
				return $variable_obj->get_value( $parameters, $this->workflow );
			}
			else {

				if ( ! $data_item = $this->workflow->get_data_item( $variable_obj->get_data_type() ) ) {
					return false;
				}

				return $variable_obj->get_value( $data_item, $parameters, $this->workflow );
			}
		}
	}


	/**
	 * Based on sanitize_title()
	 *
	 * @param $string
	 * @return mixed|string
	 */
	static function sanitize( $string ) {

		// remove style and script tags
		$string = wp_strip_all_tags( $string, true );
		$string = remove_accents( $string );

		// remove unicode white spaces
		$string = preg_replace( "#\x{00a0}#siu", ' ', $string );

		$string = trim($string);

		return $string;
	}


	/**
	 * Backwards compatibility
	 */
	private function _compatibility( &$data_type, &$value, &$parameters ) {

		if ( $data_type == 'site' ) {
			$data_type = 'shop';
		}

		if ( $data_type == 'shop' ) {
			if ( $value == 'products_on_sale' ) {
				$value = 'products';
				$parameters['type'] = 'sale';
			}

			if ( $value == 'products_recent' ) {
				$value = 'products';
				$parameters['type'] = 'recent';
			}

			if ( $value == 'products_featured' ) {
				$value = 'products';
				$parameters['type'] = 'featured';
			}
		}

		switch ( $data_type ) {
			case 'site':

				$data_type = 'shop';

				break;
		}
	}

}

