<?php

/**
 * Class WP_Field_Label_Feature
 */
class WP_Field_Label_Feature extends WP_Field_Feature_Base {

	/**
	 * @var string
	 */
	var $label_text;

	/**
	 * @return array
	 */
	static function PROPERTIES() {

		return array(

				'element' => array( 'html_tag' => 'label' ),

		);

	}

	/**
	 * @return mixed|string
	 */
	function get_element_value() {

		return $this->label_text;

	}

}
