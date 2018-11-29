<?php

/**
 * Class WP_Taxonomy_Storage
 *
 * @todo Implement get_value() and update_value()
 */
class WP_Taxonomy_Storage extends WP_Storage_Base {

	/**
	 *
	 */
	const STORAGE_TYPE = 'taxonomy';

	/**
	 *
	 */
//	const PREFIX = 'taxonomy';

	/**
	 * @return mixed $value
	 */
	function get_value() {

		return null;

	}

	/**
	 * @param null|mixed $value
	 */
	function update_value( $value = null ) {

	}

	/**
	 * Taxonomy Terms are the Field names.
	 *
	 * @return string
	 */
	function storage_key() {

		return null;

	}

}
