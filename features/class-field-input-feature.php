<?php

/**
 * Class WP_Field_Input_Feature
 */
class WP_Field_Input_Feature extends WP_Field_Feature_Base {

	/**
	 * @return array
	 */
	static function PROPERTIES() {

		return array(
				'element' => array( 'html_tag' => 'input' ),
		);

	}

	/**
	 * @return mixed
	 */
	function get_element_value() {

		return $this->field->value();

	}

	/**
	 * @note This is done in pre_initialize because element_id() might be calculated based on element_name()
	 *       and element_id() is calculated in WP_View_Base::initialize_attribute().
	 *
	 * @param array $input_args
	 *
	 * @return array
	 */
	function pre_initialize( $input_args ) {

		$this->element->set_name( $this->initialize_attribute( 'name', 'element' ) );

		return $input_args;

	}

}
