<?php

/**
 * Class WP_Memory_Storage
 *
 * Provides "storage" for values that can't be stored, such as for some hidden fields.
 *
 * @todo Decide on a better name.
 */
class WP_Memory_Storage extends WP_Storage_Base {

	/**
	 *
	 */
	const STORAGE_TYPE = 'memory';

	/**
	 *
	 */
//	const PREFIX = 'memory';

	/**
	 * @var mixed
	 */
	private $_value = null;

	/**
	 * @return mixed $value
	 */
	function get_value() {
		return $this->_value;

	}

	/**
	 * @param null|mixed $value
	 */
	function update_value( $value = null ) {

		$this->_value = $value;

	}

}
