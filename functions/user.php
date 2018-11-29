<?php
/**
 * Returns an object type given a user role
 *
 * @param string $user_role
 *
 * @return string
 */
function wp_get_user_object_type( $user_role ) {

	return $user_role ? "user:{$user_role}" : 'user:any';

}

/**
 * Registers a form for a user.
 *
 * @param string $form_name
 * @param bool|string $user_role
 * @param array $field_args
 */
function register_user_form( $form_name, $user_role = false, $form_args = array() ) {

	Custom_Fields::register_form( $form_name, wp_get_user_object_type( $user_role ), $form_args );

}

/**
 * Registers a field for a user.
 *
 * @param string $field_name
 * @param bool|string $user_role
 * @param array $field_args
 */
function register_user_field( $field_name, $user_role = false, $field_args = array() ) {

	Custom_Fields::register_field( $field_name, wp_get_user_object_type( $user_role ), $field_args );

}

/**
 * @param string $form_name
 * @param string $user_role
 * @param array $form_args
 *
 * @return WP_Form
 */
function get_user_form( $form_name, $user_role, $form_args = array() ) {

	return Custom_Fields::get_form( $form_name, wp_get_user_object_type( $user_role ), $form_args );

}
