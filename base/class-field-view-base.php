<?php

/**
 * Class WP_Field_View_Base
 *
 * @mixin WP_Field_Base
 * @property WP_Field_Input_Feature $input
 * @property WP_Field_Label_Feature $label
 * @property WP_Field_Help_Feature $help
 * @property WP_Field_Message_Feature $message
 * @property WP_Field_Infobox_Feature $infobox
 *
 */
abstract class WP_Field_View_Base extends WP_View_Base {

	/**
	 * @var array[]
	 */
	private static $_shortnames = array();
	/**
	 * @var string
	 */
	var $view_type;
	/**
	 * @var WP_Field_Base
	 */
	var $field;
	/**
	 * @var bool|array
	 */
	var $features = false;

	/**
	 * @param string $view_type
	 * @param WP_Field_Base|null $field
	 * @param array $view_args
	 */
	function __construct( $view_type, $field, $view_args = array() ) {

		$this->view_type = $view_type;

		if ( is_object( $field ) ) {

			$field->view = $this;

		}

		$this->field = $field;

		parent::__construct( $view_args );

		$this->owner = $field;

	}

	/**
	 * @return array
	 */
	static function CLASS_VALUES() {
		return array(
				'parameters' => array(
						'view_type',
						'$parent',
						'$value',
				)
		);
	}

	/**
	 * @return array
	 */
	static function PROPERTIES() {

		return array(
				'field'    => array( 'type' => 'WP_Field_Base', 'auto_create' => false ),
				'wrapper'  => array( 'type' => 'WP_Html_Element' ),
				'features' => array(
						'type'     => 'WP_Field_Feature_Base[]',
						'default'  => '$key_name',
						'registry' => 'field_feature_types',    // @todo Is $registry needed?
						'keys'     => array(
								'label',
								'input',
								'help',
								'message',
								'infobox',
						),
				)
		);

	}

	/**
	 * @param string $view_type
	 * @param WP_Field_Base|null $field
	 * @param array $view_args
	 *
	 * @return WP_Field_View_Base
	 *
	 */
	static function make_new( $view_type, $field, $view_args = array() ) {

		$view = false;

		if ( ! isset( $view_args['view_type'] ) ) {
			/*
			 * We have to do this normalization of the 'type' $arg prior to
			 * the Field classes __construct() because it drives the class used
			 * to instantiate the View. All other $args can be normalized
			 * in the Field class constructor.
			 */
			if ( ! isset( $view_args['type'] ) ) {

				$view_args['view_type'] = 'text';

			} else {

				$view_args['view_type'] = $view_args['type'];

				unset( $view_args['type'] );

			}

		}

		$view_type_args = Custom_Fields::get_field_view_type_args( $view_args['view_type'] );

		if ( is_string( $view_type_args ) && class_exists( $view_type_args ) ) {

			/**
			 * View Type is a Class name
			 */
			$view = new $view_type_args( $view_type, $field, $view_args );

		} else if ( is_array( $view_type_args ) ) {

			/**
			 * View Type passed to make_new() is a 'Prototype'
			 */
			$view_args = wp_parse_args( $view_args, $view_type_args );

			$view = self::make_new( $view_name, $object_type, $view_args );

		}

		if ( $view ) {

			if ( property_exists( $field, 'field' ) ) {

				$view->field = $field;

			}

		} else {

			$view = null;

		}

		return $view;

	}

	/**
	 * @param string $view_class
	 *
	 * @return string[]
	 */
	static function get_input_tag( $view_class ) {

		return Custom_Fields::get_view_input_tag( $view_class );

	}

	/**
	 * @param $args
	 */
	function initialize( $args ) {
		/**
		 * @var WP_Field_Label_Feature $label
		 */
		if ( ! empty( $this->features['label'] ) && is_object( $label = $this->features['label'] ) ) {
			$label->element->set_attribute_value( 'for', $this->features['input']->element->get_id() );
		}

	}

	function initial_element_id() {

		return $this->field->field_name . '-custom-field';

	}

	/**
	 * @return bool|string
	 */
	function initial_element_class() {

		return "custom-field";

	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	function get_shortnames( $args = array() ) {

		if ( ! isset( self::$_shortnames[ $class_name = get_class( $this ) ] ) ) {

			$shortnames = parent::get_shortnames();

			$properties = $this->get_class_vars( 'PROPERTIES' );

			if ( ! empty( $properties['features']['keys'] ) && is_array( $feature_keys = $properties['features']['keys'] ) ) {

				$features                           = implode( '|', $feature_keys );
				$shortnames["^({$features}):(.+)$"] = 'features[$1]:$2';

			}

			if ( $attributes = $this->get_view_input_attributes() ) {

				$attributes                                                    = implode( '|', $attributes );
				$shortnames["^features\\[([^]]+)\\]:({$attributes})$"]         = 'features[$1]:element:$2';
				$shortnames["^features\\[([^]]+)\\]:wrapper:({$attributes})$"] = 'features[$1]:wrapper:$2';

			}
			self::$_shortnames[ $class_name ] = $shortnames;

		}

		return self::$_shortnames[ $class_name ];

	}

	/**
	 * Delegate to $field explicitly since it is defined in base class.
	 *
	 * @return array
	 */
	function get_prefix() {
		/**
		 * @var Custom_Fields_Base $field
		 */
		$field = $this->field;

		if ( ! $field->has_property_annotations( $field->field_name ) ) {

			$prefix = false;

		} else {

			$prefix = $field->get_annotated_property( $field->field_name )->prefix;

		}

		return $prefix;
	}

	/**
	 * Convenience so users can use a more specific name than get_html().
	 *
	 * @return string
	 */
	function get_field_html() {

		return $this->get_html();

	}

	/**
	 * @return string
	 */
	function get_element_html() {

		return $this->get_features_html();

	}

	/**
	 * @return array
	 */
	function get_features_html() {

		$features_html = array();

		foreach ( $this->get_feature_types() as $feature_type ) {
			/**
			 * @var WP_Field_Feature_Base $feature
			 */
			$feature = $this->features[ $feature_type ];

			if ( 'input' == $feature_type ) {

				$features_html[ $feature_type ] = $this->get_input_html();

			} else {

				$features_html[ $feature_type ] = $feature->get_feature_html();

			}

		}

		return implode( "\n", $features_html );

	}

	/**
	 * Gets array of field feature type names
	 *
	 * @return array
	 */
	function get_feature_types() {

		$features = $this->get_annotated_property( 'features' );

		return is_array( $features->keys ) ? $features->keys : array();

	}

	/**
	 *  Allow Input HTML to be overridden in Field or Field View
	 *
	 *  To override in Field, implement get_input_html().
	 *  To override in Field View, implement get_input_html().
	 *
	 */
	function get_input_html() {

		if ( method_exists( $this->field, 'get_input_html' ) ) {

			$input_html = $this->field->get_input_html();

		} else {

			$input_html = $this->input_feature()->get_feature_html();

		}

		return $input_html;
	}

	/**
	 * @return WP_Field_Feature_Base
	 */
	function input_feature() {

		if ( ! isset( $this->features['input'] ) ) {

			// Do this to ensure the return value of input_feature() can be dereferenced. Should never be needed.
			$this->features['input'] = new WP_Field_Input_Feature( $this->field->view );

		}

		return $this->features['input'];

	}

	/**
	 * Delegate accesses for missing poperties to the $field property
	 *
	 * @param string $property_name
	 *
	 * @return mixed
	 */
	function __get( $property_name ) {

		return isset( $this->features[ $property_name ] ) ? $this->features[ $property_name ] : ( property_exists( $this->field,
				$property_name ) ? $this->field[ $property_name ] : null );

	}

	/**
	 * Delegate accesses for missing poperties to the $field property
	 *
	 * @param string $property_name
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	function __set( $property_name, $value ) {

		return isset( $this->features[ $property_name ] ) ? $this->features[ $property_name ] = $value : ( property_exists( $this->field,
				$property_name ) ? $this->field->$property_name = $value : null );

	}

	/**
	 * Delegate calls for missing methods to the $field property
	 *
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return mixed
	 */
	function __call( $method_name, $args = array() ) {

		return method_exists( $this->field, $method_name ) ? call_user_func_array( array(
				$this->field,
				$method_name
		), $args ) : parent::__call( $method_name, $args );

	}

	/**
	 * @param string $property_name
	 *
	 * @return bool
	 */
	function __isset( $property_name ) {

		return isset( $this->features[ $property_name ] );

	}

}
