<?php

/**
 * Class WP_Html_Element
 *
 * Class used to generate an HTML element, including contained elements, if applicable.
 *
 * This class allows an associative array of $args containing attributes and values to be
 * passed down through functions that call other functions, some of which may set default
 * $arg values, where the $args are ultimately designed to generate HTML.
 *
 * As an example, a developer can call register_post_field() and pass a value of 10 for
 * the $field_args key of 'view:features[input]:element:size' and that allows the HTML
 * Element to receive an array with a key/value of 'size'=>10 in the $attributes array
 * passed to the HTML Element's __construct() method.
 *
 * This if fields use HTML Element objects to construct the HTML elements needed for output
 * no extra work needs to be done to support initialization of any property that is valid
 * for the element type identified by the $tag_name property.
 *
 * Class does not (currently?) contain child HTML Elements; its $value property
 * should contain the generated text of the child HTML Elements.  This may change
 * if we discover the need for containing children.
 *
 * Designed for HTML5.  Not designed to generate valid XHTML.
 *
 */
final class WP_Html_Element {

	/**
	 * Provide list of HTML element names that do not need a closing tag.
	 * Used to ensure proper generation of HTML element.
	 *
	 * @const string
	 */
	const _VOID_ELEMENTS = 'area|base|br|col|command|embed|hr|img|input|keygen|link|meta|param|source|track|wbr';

	/**
	 * The HTML Tag Name, such as 'div', 'input', 'label', or 'textarea'
	 *
	 * @var string
	 */
	var $tag_name;

	/**
	 * The value to use when generating HTML element.  If $tag_name contains a void tag then this
	 * $value is used for the 'value' attribute otherwise it's used as the inner text of the element.
	 *
	 * To generate an HTML element that contains other HTML elements, set this property with the text
	 * string that contains the HTML elements.
	 *
	 * Can be a callable that will generate the value.
	 *
	 * @var null|string|callable
	 */
	var $value;

	/**
	 * Contains names and values of the HTML element attributes.
	 *
	 * @var array
	 */
	protected $_attributes;

	/**
	 * Construct a new HTML Element object.
	 *
	 * @param string $tag_name The HTML Element tag name, i.e. 'div', 'input', 'label', or 'textarea'.
	 * @param array $attributes An associative array containing attribute names and their values
	 * @param null|callable|string $value The value of the innner text property or of the 'value' attribute.
	 */
	function __construct( $tag_name, $attributes = array(), $value = null ) {

		/**
		 * assign() initializes the properties of the HTML Element.
		 */
		$this->assign( $tag_name, $attributes, $value );

	}

	/**
	 * Define the parameters needs for the make_new() Factory menthod for this class.
	 * @return array
	 */
	static function CLASS_VALUES() {
		return array(
				'parameters' => array(
						'html_tag',
						'$value',
						null,
				)
		);
	}

	/**
	 * Factory method for WP_Html_Element
	 *
	 * @param string $tag_name
	 * @param array $attributes
	 * @param null|callable|string $value
	 *
	 * @return self
	 */
	static function make_new( $tag_name, $attributes = array(), $value = null ) {

		return new self( $tag_name, $attributes, $value );

	}

	/**
	 * Used to initialize an HTML Element with it's constructor parameters.
	 *
	 * Also useful to allow HTML Elements to be reused rather than having to create a new one
	 * where the HTML Element object is just being used for output, in a loop for example.
	 *
	 * @param string $tag_name
	 * @param array $attributes
	 * @param null|callable|string $value
	 */
	function assign( $tag_name, $attributes = array(), $value = null ) {

		$this->tag_name = $tag_name;
		if ( is_null( $attributes ) ) {
			$attributes = array();
		}
		$this->_attributes = wp_parse_args( $attributes );
		$this->value       = $value;

	}

	/**
	 * Generate the HTML string for this HTML Element, its attributes, and its contained value if applicable.
	 *
	 * @return string
	 */
	function get_html() {

		/**
		 * Sanitize this tag_name to ensure
		 */
		if ( ! ( $tag_name = Custom_Fields::sanitize_identifier( $this->tag_name ) ) ) {

			/*
			 * If tag name is empty after sanitization, it's not a valid tag name.
			 * Provide some type of indicator of value in the generated HTML.
			 *
			 * @todo Figure out how to sanitize it enough so we can debugging output.
			 *
			 */

			$html = "<!-- invalid WP_Html_Element->tag_name -->";

		} else {

			/*
			 * Build the HTML Element's opening tag.
			 */
			$html = "<{$tag_name} " . $this->get_attributes_html() . '>';

			if ( ! $this->is_void_element() ) {
				/*
				 * If not a void element and
				 */
				if ( is_callable( $this->value ) ) {

					/*
					 * Call the callable to generate the value.
					 * Pass in $this so it has some context.
					 */
					$value = call_user_func( $this->value, $this );

				} else {

					/*
					 * Just grab the value.
					 */
					$value = $this->value;

				}

				/*
				 * Append the inner text value and the closing tag.
				 */
				$html .= "{$value}</{$tag_name}>";
			}

		}
		return $html;

	}

	/**
	 * Generate the HTML that for the names and attributes inside an HTML element's opening tag.
	 *
	 * Only generates name/value pairs for attributes that are valid for the HTML element type
	 * as indicated by tag_name.
	 *
	 * @example output (sans single quotes):
	 *
	 *    'attr1="value1" attr2="value2" attr3="value3"';
	 *
	 * @return string
	 */
	function get_attributes_html() {

		/*
		 * Get the valid attributes for this HTML element by tag name.
		 */
		$valid_attributes = Custom_Fields::get_html_attributes( $this->tag_name );

		/*
		 * Remove any attributes empty attributes.
		 */
		$attributes = array_filter( $this->_attributes );

		$html = array();

		if ( isset( $valid_attributes['value'] ) && $this->is_void_element() ) {
			/*
			 * If this has a value set and it's a void element, be sure to sanitize it.
			 */
			$attributes['value'] = esc_attr( $this->value );
		}

		/*
		 * Loop through each of the attributes
		 */
		foreach ( $attributes as $name => $value ) {
			if ( false !== $value && ! is_null( $value ) && isset( $valid_attributes[ $name ] ) ) {
				/*
				 *  Include if the attribute has a value and is valid for this HTML Element type
				 */
				if ( $name = Custom_Fields::sanitize_identifier( $name ) ) {
					/**
					 * Is the name provided can be sanitized (because if not the sanitize_identifier() returns a null)
					 * add this name/value pair to the $html array.
					 */
					$value  = esc_attr( $value );
					$html[] = "{$name}=\"{$value}\"";
				}
			}
		}

		/*
		 * Merge array of formatted attribute name/value pairs into an HTML string.
		 */
		return implode( ' ', $html );

	}

	/**
	 * Acess the internal associative array containing the names and value of the attributes
	 * for this HTML Element.
	 *
	 * @return array
	 */
	function attributes() {

		return $this->_attributes;

	}

	/**
	 * Tests the current $tag_name to determine if it represents an HTML5 element that does
	 * not require a closing tag.
	 *
	 * @return bool
	 */
	function is_void_element() {

		return preg_match( '#^(' . self::_VOID_ELEMENTS . ')$#i', $this->tag_name ) ? true : false;

	}

	/**
	 * Shortcut to access the value for the 'id' attribute of an HTML Element.
	 *
	 * @return string
	 */
	function get_id() {

		return $this->get_attribute_value( 'id' );

	}

	/**
	 * Shortcut to access the value for the 'name' attribute of an HTML Element.
	 *
	 * @return string
	 */
	function get_name() {

		return $this->get_attribute_value( 'name' );

	}

	/**
	 * Shortcut to access the value for the 'class' attribute of an HTML Element.
	 *
	 * @return string
	 */
	function get_class() {

		return $this->get_attribute_value( 'class' );

	}

	/**
	 * Shortcut to access the  value for any attribute of an HTML Element.
	 *
	 * @param string $attribute_name
	 *
	 * @return mixed
	 */
	function get_attribute_value( $attribute_name ) {

		return ! empty( $this->_attributes[ $attribute_name ] )
			? trim( $this->_attributes[ $attribute_name ] )
			: false;

	}

	/**
	 * Shortcut to set a value for the HTML Element's 'id' attribute.
	 *
	 * @param $value
	 */
	function set_id( $value ) {

		$this->set_attribute_value( 'id', $value );

	}

	/**
	 * Shortcut to set a value for the HTML Element's 'name' attribute.
	 *
	 * @param $value
	 */
	function set_name( $value ) {

		$this->set_attribute_value( 'name', $value );

	}

	/**
	 * Shortcut to set a value for the HTML Element's 'class' attribute.
	 *
	 * @param $value
	 */
	function set_class( $value ) {

		$this->set_attribute_value( 'class', $value );

	}

	/**
	 * Shortcut to set a value for any HTML Element's attribute.
	 *
	 * @param string $attribute_name
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	function set_attribute_value( $attribute_name, $value ) {

		if ( false === $value || is_null( $value ) ) {

			unset( $this->_attributes[ $attribute_name ] );

		} else {

			$this->_attributes[ $attribute_name ] = $value;

		}

		return $value;

	}

	/**
	 * Shortcut to append a value to the HTML Element's existing value for its 'class' attribute.
	 *
	 * @param $value
	 */
	function append_class( $value ) {

		$this->append_attribute_value( 'class', $value );

	}

	/**
	 * Appends a value to the end of an attribute's existing value.
	 *
	 * Useful to add another selector to a 'class' attribute, see $this->append_class().
	 *
	 * @param string $attribute_name Name of a valid attribute for the element type as defined by $tag_name.
	 * @param mixed $value Value to append to the element.
	 * @param string $separator
	 *
	 */
	function append_attribute_value( $attribute_name, $value, $separator = ' ' ) {

		if ( false !== $value && ! is_null( $value ) ) {

			if ( isset( $this->_attributes[ $attribute_name ] ) ) {

				$this->_attributes[ $attribute_name ] = trim( "{$this->_attributes[$attribute_name]}{$separator}{$value}" );

			} else {

				$this->_attributes[ $attribute_name ] = $value;

			}

		}

	}

}
