<?php

/**
 * Class WP_View_Base
 *
 * @TODO Refactor so this class can handle an arbitrary list of properties, not just 'wrapper' and 'element'
 *
 */
class WP_View_Base extends Custom_Fields_Base {

	/**
	 * @var array
	 */
	private static $_shortnames = array();
	/**
	 * @var WP_Html_Element
	 */
	var $wrapper = null;
	/**
	 * @var WP_Html_Element
	 */
	var $element = null;
	/**
	 * @var Custom_Fields_Base
	 */
	var $owner;

	/**
	 * @return array
	 */
	static function PROPERTIES() {

		return array(
				'wrapper' => array( 'type' => 'WP_Html_Element' ),
				'element' => array( 'type' => 'WP_Html_Element' ),
		);

	}

	/**
	 * @return string
	 */
	function get_html() {

		if ( is_object( $wrapper = $this->wrapper ) && method_exists( $wrapper, 'get_html' ) ) {

			$wrapper->value = $this->get_element_html();

			$html = $wrapper->get_html();

		} else {

			$html = null;

		}

		return $html;

	}

	/**
	 * @return string
	 */
	function get_element_html() {

		$this->element->value = $this->get_element_value();

		return $this->element->get_html();

	}

	/**
	 * Get the value of the element.
	 *
	 * @note: Typically this will be overridden in child classes.
	 *
	 * @return bool
	 */
	function get_element_value() {
		return false;
	}

	/**
	 * Return the HTML tag to be used by this class.
	 *
	 * @return array
	 */
	function get_wrapper_tag() {

		if ( ! ( $html_tag = $this->get_annotation_custom( 'html_tag', 'wrapper' ) ) ) {

			$html_tag = 'div';

		}

		return $html_tag;

	}

	/**
	 * @param array $feature_args
	 *
	 * @return array
	 */
	function initialize( $feature_args ) {

		$this->element->append_class( $this->initialize_attribute( 'class', 'element' ) );
		$this->element->set_id( $this->initialize_attribute( 'id', 'element' ) );

		$this->wrapper->append_class( $this->initialize_attribute( 'class', 'wrapper' ) );
		$this->wrapper->set_id( $this->initialize_attribute( 'id', 'wrapper' ) );

		return '';
	}


	/**
	 * @param string $attribute_name
	 * @param string $html_element_name
	 *
	 * @return mixed
	 */
	function initialize_attribute( $attribute_name, $html_element_name ) {
		$value = null;
		switch ( $element_attribute = "{$html_element_name}_{$attribute_name}" ) {

			case 'element_name':
			case 'element_id':
			case 'element_class':

				if ( method_exists( $this, $method_name = "initial_{$element_attribute}" ) ) {

					$value = $this->{$method_name}();

				} else {

					switch ( $element_attribute ) {
						case 'element_name':
							$value = 'element_name_not_set_in_child_class';
							break;

						case 'element_id':
							$value = str_replace( '_', '-', $this->element->get_name() );
							break;

						case 'element_class':
							$value = '';
							break;
					}

				}
				break;

			case 'wrapper_id':

				$value = $this->element->get_id() . '-wrapper';
				break;

			case 'wrapper_class':

				if ( $classes = $this->element->get_class() ) {

					$classes = explode( ' ', $classes );

					foreach ( $classes as &$class ) {

						$class = trim( $class ) . '-wrapper';

					}

					$value = implode( ' ', $classes );

				}

				break;

		}

		return $value;
	}

}



