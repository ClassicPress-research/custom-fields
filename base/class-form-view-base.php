<?php

/**
 * Class WP_Form_View_Base
 */
abstract class WP_Form_View_Base extends WP_View_Base {

	/**
	 * @var string
	 */
	var $view_type;

	/**
	 * @var WP_Form
	 */
	var $form;

	/**
	 * @var WP_Html_Element
	 */
	var $wrapper;

	/**
	 * @var WP_Html_Element
	 */
	var $element;

	/**
	 * @param string $view_type
	 * @param string $form
	 * @param array $view_args
	 *
	 */
	function __construct( $view_type, $form, $view_args = array() ) {

		$view_args['view_type'] = $view_type;

		$this->form = $form;

		parent::__construct( $view_args );

		$this->owner = $form;

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
				'form' => array( 'type' => 'WP_Form', 'auto_create' => false ),
		);

	}

	/**
	 * @param string $view_type
	 * @param string $form
	 * @param array $view_args
	 *
	 * @return WP_Form_View
	 *
	 */
	static function make_new( $view_type, $form, $view_args = array() ) {

		$form_view = new WP_Form_View( $view_type, $form, $view_args );

		return $form_view;

	}

	/**
	 * Convenience so users can use a more specific name than get_html().
	 *
	 * @return string
	 */
	function get_form_html() {

		return $this->get_html();

	}

	/**
	 * @return string
	 */
	function get_element_html() {

		return $this->get_form_fields_html();
	}

	/**
	 * @return string
	 */
	function get_form_fields_html() {

		$fields_html = array();

		/**
		 * @var WP_Field_Base $field
		 */
		foreach ( $this->form->fields as $field_name => $field ) {

			$fields_html[] = $field->view->get_field_html();

		}

//		$form_field = new WP_Hidden_Field( "cp_custom_fields_forms", array(
//			'value' => $this->form->form_name,
//			'storage' => 'memory',
//			'view' => 'hidden',
//			'shared_name' => true,
//			'form' => $this->form,
//		));
//
//		$fields_html[] = $form_field->get_field_html();

		return implode( "\n", $fields_html );

	}

	/**
	 * @return bool|string
	 */
	function initial_element_id() {

		return str_replace( '_', '-', "{$this->form->form_name}-custom-form" );

	}

	/**
	 * @return bool|string
	 */
	function initial_element_class() {

		return "custom-form";

	}

//	/**
//	 * @return bool|string
//	 */
//	function initial_element_id() {
//
//		return str_replace( '_', '-', $this->element->get_name() ) . '-' . $this->element->get_class();
//
//	}

}
