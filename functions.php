<?php
/**
 * Ensure that an $args array has an 'object_type' property of class WP_Object_Type
 *
 * Defaults to "post:{$post->post_type}"
 *
 * @param array $args
 *
 * @return array
 */
function wp_ensure_object_type( $args ) {

	$args = wp_parse_args( $args );

	if ( empty( $args['object_type'] ) ) {
		global $post;

		$args['object_type'] = isset( $post->post_type ) ? $post->post_type : false;
	}

	if ( ! $args['object_type'] instanceof WP_Object_Type ) {
		$args['object_type'] = new WP_Object_Type( $args['object_type'] );
	}

	return $args;

}

/**
 * Register a new Object Type $class.
 *
 * Allows a plugin or theme to register it' own $class values for Object Types.
 *
 * An example might be for a plugin we call 'Awesome Event Calendar', it might
 * register a new Object Type $class of 'aec_event' where 'aec_' is the plugin's
 * prefix:
 *
 *    register_object_type_class( 'aec_event' );
 *
 * This would allow developers to register fields for an 'aec_event'.
 * HOWEVER, an event would probably best be a custom post type so this functionality
 * may be rarely used, if ever.  Still, it's here if it is needed.
 *
 * The $args array is currently unused but here for future needs.
 *
 * $class values cannot be registered twice
 *
 * @param string $class The new Object Type $class to register.
 * @param array $class_args The $args for the registered $class. Currently unused.
 *
 * @return bool Whether the object type $class was registered
 */
function register_object_type_class( $class, $class_args = array() ) {

	return WP_Object_Type::register_class( $class, $class_args );

}

/**
 * Register field type
 *
 * @param string $type_name
 * @param string|array $type_def - Classname, or array of $args
 *
 * @return bool Whether the object type $type_name was registered
 */
function register_field_type( $type_name, $type_def = array() ) {

	return Custom_Fields::register_field_type( $type_name, $type_def );

}

/**
 * @param string $class_name
 * @param string $class_filepath
 *
 * @return bool Return true if it was registered, false if not.
 */
function register_autoload_class( $class_name, $class_filepath ) {

	return Custom_Fields::register_autoload_class( $class_name, $class_filepath );

}



