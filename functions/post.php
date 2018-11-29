<?php

/**
 * Registers a form for a post.
 *
 * @param string $form_name
 * @param bool|string $post_type
 * @param array $form_args
 */
function register_post_form( $form_name, $post_type = false, $form_args = array() ) {

	Custom_Fields::register_form( $form_name, Custom_Fields::get_post_object_type_literal( $post_type ), $form_args );

}

/**
 * Registers a field for a post.
 *
 * @param string $field_name
 * @param bool|string $post_type
 * @param array $field_args
 */
function register_post_field( $field_name, $post_type = false, $field_args = array() ) {

	Custom_Fields::register_field( $field_name, Custom_Fields::get_post_object_type_literal( $post_type ), $field_args );

}

/**
 * @param string $form_name
 * @param string $post_type
 * @param array $form_args
 *
 * @return WP_Form
 */
function get_post_form( $form_name, $post_type, $form_args = array() ) {

	return Custom_Fields::get_form( $form_name, Custom_Fields::get_post_object_type_literal( $post_type ), $form_args );

}

/**
 * @param string $post_type
 * @param bool|array $form_names
 *
 * @return array
 */
function get_post_forms( $post_type, $form_names = false ) {

	return Custom_Fields::get_forms( Custom_Fields::get_post_object_type_literal( $post_type ), $form_names );

}
