<?php

/**
 * Class WP_Option_Storage
 */
class WP_Option_Storage extends WP_Storage_Base {

	/**
	 *
	 */
	const STORAGE_TYPE = 'option';

	/**
	 *
	 */
//	const PREFIX = 'option';

	/**
	 * @var bool|string - Option type such as 'post', 'user' and 'comment' (in future, other.)
	 */
	var $option_type = false;

	/**
	 * @return mixed $value
	 */
	function get_value() {

		return get_option( $this->storage_key(), true );

	}

	/**
	 * Get Option Key
	 *
	 * @TODO This is all wrong. This logic should go into get_value() and update_value() using real array vs. simulation.
	 *
	 * @return string
	 */
	function storage_key() {

		$field       = $this->field;
		$object_type = $field->object_type;

		if ( $group = $object_type->subtype ) {
			$option_name = "_{Custom_Fields::$prefix}{$group}[{$field->field_name}]"; // @todo Variable not set
		} else {
			$option_name = "_{Custom_Fields::$prefix}{$field->field_name}"; // @todo Variable not set
		}

		return $option_name;

	}

	/**
	 * @param string $field_name
	 * @param null|mixed $value
	 */
	function update_value( $value = null ) {

		update_option( $this->storage_key(), esc_sql( $value ) );

	}

}
