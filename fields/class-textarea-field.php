<?php

/**
 * Class WP_Textarea_Field
 */
class WP_Textarea_Field extends WP_Field_Base {

	/**
	 *
	 */
	const FIELD_TYPE = 'textarea';

	/**
	 * @return array
	 */
	static function CLASS_VALUES() {

		return array(
				'defaults' => array( 'view:view_type' => 'textarea' ),
		);

	}

}
