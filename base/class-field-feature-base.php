<?php

/**
 * Class WP_Field_Feature_Base
 */
abstract class WP_Field_Feature_Base extends WP_View_Base {

	/**
	 * @var string
	 */
	var $feature_type;

	/**
	 * @var WP_Field_Base
	 */
	var $field;

	/**
	 * @var WP_Field_View_Base
	 */
	var $view;

	/**
	 * @param WP_Field_View_Base $view
	 * @param array $feature_args
	 */
	function __construct( $view, $feature_args = array() ) {

		$this->field = $view->field;
		$this->view  = $view;

		parent::__construct( $feature_args );

		$this->owner = $this->field;

	}

	/**
	 * @return array
	 */
	static function CLASS_VALUES() {
		return array(
				'parameters' => array(
						'$value',
						'$parent',
						'$args',
				)
		);
	}

	/**
	 * @return array
	 */
	static function PROPERTIES() {

		return array(
				'field' => array( 'type' => 'WP_Field_Base', 'auto_create' => false ),
		);

	}

	/**
	 * Returns a new instance of a Field Feature object.
	 *
	 * @param string $feature_type
	 * @param WP_Field_View_Base $view
	 * @param array $feature_args
	 *
	 * @return null|WP_Field_Feature_Base
	 */
	static function make_new( $feature_type, $view, $feature_args = array() ) {

		if ( $feature_type_class = Custom_Fields::get_feature_type_class( $feature_type ) ) {

			$feature_args['feature_type'] = $feature_type;

			$feature = new $feature_type_class( $view, $feature_args );

		} else {

			$feature = null;

		}

		return $feature;

	}

	/**
	 * @return bool|string
	 */
	function initial_element_id() {

		return str_replace( '_', '-', $this->field_name() ) . "-field-{$this->feature_type}";

	}

	/**
	 *  Used in initial_*() functions above.
	 */
	function field_name() {

		return $this->field->field_name;

	}

	/**
	 * @return bool|string
	 */
	function initial_element_class() {

		return "field-feature field-{$this->feature_type}";

	}

	/**
	 * @return bool|string
	 */
	function initial_element_name() {

		return 'cp_custom_fields_forms[' . $this->field->form_element_name() . '][' . $this->field_name() . ']';

	}

	/**
	 * @return string
	 */
	function get_feature_html() {

		return $this->get_html();

	}

	/**
	 * @param string $property_name
	 *
	 * @return mixed
	 */
	function __get( $property_name ) {

		$view = $this->view;

		if ( isset( $view->features[ $property_name ] ) ) {

			$value = $view->features[ $property_name ];

		} else {

			$value = parent::__get( $property_name );

		}

		return $value;

	}

	/**
	 * @param string $property_name
	 * @param mixed $value
	 */
	function __set( $property_name, $value ) {

		$this->view->features[ $property_name ] = $value;

	}
}
