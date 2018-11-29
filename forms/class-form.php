<?php

/**
 * Class WP_Form
 *
 * @method void the_form()
 * @method void the_form_fields()
 */
class WP_Form extends Custom_Fields_Base {

	/**
	 *
	 */
//	const PREFIX = 'form';

	/**
	 * @var string
	 */
	var $form_name;

	/**
	 * @var string|WP_Object_Type
	 */
	var $object_type;

	/**
	 * @var WP_Field_Base[]
	 */
	var $fields = array();

	/**
	 * @var int
	 */
	var $form_index;

	/**
	 * @var WP_Form_View_Base
	 */
	var $view;

	/**
	 * @var WP_Storage_Base
	 */
	var $storage;

	/**
	 *
	 */
	private $_initialized = false;

	/**
	 * @param string $form_name
	 * @param string|WP_Object_Type $object_type
	 * @param array $form_args
	 */
	function __construct( $form_name, $object_type, $form_args ) {

		$form_args['form_name']   = $form_name;
		$form_args['object_type'] = new WP_Object_Type( $object_type );

		parent::__construct( $form_args );

	}

	/**
	 * @return array
	 */
	static function CLASS_VALUES() {
		return array(
				'parameters' => array(
						'$value',
						'object_type',
						'$args',
				)
		);
	}

	/**
	 * @return array
	 */
	static function PROPERTIES() {

		return array(
				'view'    => array( 'type' => 'WP_Form_View', 'default' => 'default' ),
				'storage' => array( 'type' => 'WP_Storage_Base', 'default' => 'meta' ),
				'fields'  => array( 'type' => 'WP_Field_Base[]' ),
		);

	}

	/**
	 * @param string $form_name
	 * @param string|WP_Object_Type $object_type
	 * @param array $form_args
	 *
	 * @return WP_Form
	 *
	 * @todo Support more than one type of form. Maybe. If needed.
	 *
	 */
	static function make_new( $form_name, $object_type, $form_args = array() ) {

		$form = new WP_Form( $form_name, $object_type, $form_args );

		return $form;

	}

	/**
	 *
	 */
	function initialize_class() {

		$this->register_view( 'default', 'WP_Form_View' );

	}

	/**
	 * Register a class to be used as a form_view for the current class.
	 *
	 * $wp_form->register_view( 'post_admin', 'WP_Post_Adminview' );
	 *
	 * @param string $view_type  The name of the view that is unique for this class.
	 * @param string $class_name The class name for the View object.
	 */
	function register_view( $view_type, $class_name ) {

		Custom_Fields::register_view( 'form', $view_type, $class_name, get_class( $this ) );

	}

	/**
	 * @param array $form_args
	 */
	function initialize( $form_args ) {

		if ( ! is_object( $this->view ) ) {

			$this->set_form_view( 'default' );

		}

		$this->initialize_form_fields( $form_args['object_type'] );

	}

	/**
	 * @param string $view_type
	 */
	function set_form_view( $view_type ) {

		if ( ! $this->form_view_exists( $view_type ) ) {
			$this->view = false;
		} else {
			$form_view_class = $this->get_view_class( $view_type );

			$this->view = new $form_view_class( $view_type, $this );
		}

	}

	/**
	 * Does the named form view exist
	 *
	 * @param string $view_type The name of the view that is unique for this class.
	 *
	 * @return bool
	 */
	function form_view_exists( $view_type ) {

		return Custom_Fields::view_exists( 'form', $view_type, get_class( $this ) );

	}

	/**
	 * Retrieve the class name for a named view.
	 *
	 * @param string $view_type The name of the view that is unique for this class.
	 *
	 * @return string
	 */
	function get_view_class( $view_type ) {

		return Custom_Fields::get_view_class( 'form', $view_type, get_class( $this ) );

	}

	/**
	 * @param string $object_type
	 * @param bool|array $field_names
	 */
	function initialize_form_fields( $object_type, $field_names = false ) {

		$this->fields = array();

		if ( ! $field_names ) {
			$field_names = Custom_Fields::get_field_names( $object_type );
		}

		foreach ( $field_names as $field_name ) {
			$field = Custom_Fields::get_field( $field_name, $object_type, array(
					'form' => $this,
			) );

			if ( is_object( $field ) ) {
				$this->add_field( $field );
			}
		}

	}

	/**
	 * @param WP_Field_Base $field
	 */
	function add_field( $field ) {

		$field->form                        = $this;
		$this->fields[ $field->field_name ] = $field;

	}

	/**
	 * @param WP_Post|object $object
	 */
	function set_storage_object( $object ) {

		/**
		 * @var WP_Field_Base $field
		 */
		foreach ( $this->fields as $field ) {
			if ( ! is_object( $field->storage->object ) ) {
				$field->storage->object = $object;
			}
		}

	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	function get_default_args( $args = array() ) {
		$args                 = parent::get_default_args( $args );
		$args['element_name'] = str_replace( '-', '_', $this->form_name );

		return $args;

	}

	/**
	 *
	 */
	function set_object( $object ) {
		/**
		 * @var WP_Field_Base $field
		 */
		foreach ( $this->fields as $field ) {
			$field->set_object( $object );
		}
	}

	/**
	 *
	 */
	function update_values( $values = false ) {
		if ( false === $values ) {
			/**
			 * @var WP_Field_Base $field
			 */
			foreach ( $this->fields as $field ) {
				$field->update_value();
			}
		} else if ( is_array( $values ) ) {
			/**
			 * @var WP_Field_Base $field
			 */
			foreach ( $this->fields as $field_name => $field ) {
				if ( isset( $values[ $field_name ] ) ) {
					if ( is_null( $values[ $field_name ] ) ) {
						/*
						 * $field->update_value( null ) updates using existing $field->value().
						 */
						$values[ $field_name ] = false;
					}
					$field->update_value( $values[ $field_name ] );
				}
			}
		}
	}

	/**
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return mixed
	 */
	function __call( $method_name, $args = array() ) {

		/*
		 * If method was the_*() method, parent __call() will fall through and return false.
		 */
		if ( ! ( $result = parent::__call( $method_name, $args ) ) ) {
			/*
			 * Delegate call to view and return it's result to caller.
			 */
			$result = $this->view->{$method_name}( $args );
		}

		return $result;

	}

}
