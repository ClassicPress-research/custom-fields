<?php

/**
 * Class WP_Storage_Base
 */
abstract class WP_Storage_Base extends Custom_Fields_Base {

	/**
	 *
	 */
	const STORAGE_TYPE = 'unspecified';

	/**
	 *
	 */
	const OBJECT_ID = 'ID';

	/**
	 * @var WP_Post|WP_User|object
	 */
	var $object;

	/**
	 * @var WP_Field_Base
	 */
	var $owner;

	/**
	 * @param object $owner
	 * @param array $storage_args
	 */
	function __construct( $owner, $storage_args = array() ) {

		$this->owner = $owner;

		parent::__construct( $storage_args );

	}

	/**
	 * @return array
	 */
	static function CLASS_VALUES() {
		return array(
				'parameters' => array(
						'$value',
						'$parent',
						'$args',
				)
		);
	}

	/**
	 * $storage_arg names that should not get a prefix.
	 *
	 * Intended to be used by subclasses.
	 *
	 * @return array
	 */
	static function PROPERTIES() {

		return array(
				'owner'  => array( 'auto_create' => false ),
				'object' => array( 'auto_create' => false ),
		);

	}

	/**
	 * Returns a new instance of a storage object.
	 *
	 * Defaults to WP_Meta_Storage if $storage_type not passed (i.e. $storage_type => 'meta').
	 *
	 * @param string $storage_type
	 * @param object $owner
	 * @param array $storage_args
	 *
	 * @return null|WP_Storage_Base
	 */
	static function make_new( $storage_type, $owner = null, $storage_args = array() ) {

		if ( ! $storage_type ) {

			$storage_type = 'meta';

		}

		if ( $storage_class = Custom_Fields::get_storage_type_class( $storage_type ) ) {

			$storage = new $storage_class( $owner, $storage_args );

			if ( property_exists( $owner, 'storage' ) ) {

				$owner->storage = $storage;

			}

		} else {

			$storage = null;

		}

		return $storage;

	}

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
	 * Name used for owner key.
	 *
	 * Most common example of a owner key would be a meta key.
	 *
	 * @return string
	 */
	function storage_key() {

		return '_' . $this->owner->storage_key();

	}

	/**
	 * @return int
	 */
	function object_id() {

		if ( is_object( $this->object ) && property_exists( $this->object, self::OBJECT_ID ) ) {
			return $this->object->{self::OBJECT_ID};
		} else {
			return null;
		}

	}

	/**
	 * @param $object_id
	 */
	function set_object_id( $object_id ) {

		if ( is_object( $this->object ) && property_exists( $this->object, self::OBJECT_ID ) ) {
			$this->object->{self::OBJECT_ID} = $object_id;
		}

	}

	/**
	 *
	 * @param $field_name
	 *
	 * @return bool
	 */
	function has_field( $field_name ) {

		return true;

	}

}
