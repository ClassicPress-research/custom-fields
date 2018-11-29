<?php

/**
 * Class WP_Hidden_Field_View
 *
 * @property WP_Hidden_Field $field
 */
class WP_Hidden_Field_View extends WP_Field_View_Base {

//	/**
//	 * @return bool|string
//	 */
//	function initial_element_id() {
//
//		if ( $this->field->shared_name ) {
//
//			/*
//			 * If $shared_name is true there will be '[]' contained in the html ID from $this->element_name().
//			 * Remove it.
//			 */
//			return preg_replace( '#\[\]#', '', parent::initial_element_id() );
//
//		}
//
//		return parent::initial_element_id();
//	}
//
//	/**
//	 * @return bool|string
//	 */
//	function element_name() {
//
//		if ( $this->field->shared_name ) {
//
//			return parent::initial_element_name() . '[]';
//
//		}
//
//		return parent::initial_element_name();
//
//	}
//
//	/**
//	 * Return just the <input> HTML.
//	 *
//	 * Hidden fields don't need wrappers.
//	 *
//	 * @return string
//	 */
//	function get_field_html() {
//
//		/**
//		 * @var WP_Field_Feature_Base $feature
//		 */
//		$feature = $this->features[ 'input' ];
//
//		return $feature->element->get_html();
//
//	}
}
