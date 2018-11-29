<?php

/**
 * Class WP_Field_Message_Feature
 */
class WP_Field_Message_Feature extends WP_Field_Feature_Base {

	var $message_text;

	/**
	 * @return string
	 */
	function get_element_value() {

		return $this->message_text;

	}

}
