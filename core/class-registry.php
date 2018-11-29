<?php

/**
 * Class WP_Registry
 *
 * Simple class to implement a registry for values like 'storage_types', 'field_types' and 'field_feature_types'.
 *
 */
final class WP_Registry {

	/**
	 * @var string
	 */
	var $registry_name;

	/**
	 * @var array
	 */
	private $_entries = array();

	/**
	 * Instantiate a new registry.
	 *
	 * @param string $registry_name Name of the registry, for reference.
	 */
	function __construct( $registry_name ) {

		$this->registry_name = $registry_name;

	}

	/**
	 *
	 * Register an named entry and it's $data to this
	 *
	 * @param string $name Name of the Entry to Register
	 * @param mixed $data Arguments to register. This can be an array or even a string, such as a class name.
	 *
	 * @return int The index of the entry in the registry.
	 */
	function register_entry( $name, $data ) {

		$index = count( $this->_entries );

		$this->_entries[ $name ] = $data;

		return $index;

	}

	/**
	 * Get the $data for a named Entry from $this Registry.
	 *
	 * @param string $name Name of the Entry for which to get its $data from the Registry.
	 *
	 * @return mixed The $data for this Registry's named Entry, or null if no such named Entry found.
	 */
	function get_entry( $name ) {

		return isset( $this->_entries[ $name ] ) ? $this->_entries[ $name ] : null;

	}

	/**
	 * Test to see if $this Registry has the specified named Entry
	 *
	 * @param string $name Name of the Entry for which to test to see if it exists in the Registry.
	 *
	 * @return bool True if the named Entry exists in the Registry, false if not.
	 */
	function entry_exists( $name ) {

		return $name && is_string( $name ) && isset( $this->_entries[ $name ] );

	}

}
