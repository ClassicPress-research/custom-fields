<?php

/**
 * Class WP_Core_Storage
 */
class WP_Core_Storage extends WP_Storage_Base {

	/**
	 *
	 */
	const STORAGE_TYPE = 'core';

	/**
	 * @return mixed $value
	 */
	function get_value() {

		return $this->has_field() ? $this->object->{$this->field->field_name} : null;

	}

	/**
	 * @return bool
	 */
	function has_field() {

		$field_name = $this->field->field_name;

		return property_exists( $this->object, $field_name ) && parent::has_field( $field_name );

	}

	/**
	 * @param null|mixed $value
	 */
	function update_value( $value = null ) {

		if ( $this->has_field( $field_name = $this->field->field_name ) && 0 < intval( $this->object_id() ) ) {
			/**
			 * @var wpdb $wpdb
			 */
			global $wpdb;

			$wpdb->update( $wpdb->posts, array( $field_name => $value ), array(
					'ID' => $this->object_id()
			) );
		}

	}

}
