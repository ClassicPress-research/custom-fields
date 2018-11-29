<?php

/**
 * Class WP_Field_Help_Feature
 */
class WP_Field_Help_Feature extends WP_Field_Feature_Base {

	var $help_text;

	/**
	 * @return string
	 */
	function get_element_value() {

		return $this->help_text;

	}

}
