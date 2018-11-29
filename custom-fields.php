<?php
/**
 * Plugin Name: ClassicPress Custom Fields
 * Description: Feature-as-a-plugin offering Forms & Fields for ClassicPress, initially forms for post admin edit but later for users, comments, taxonomy terms, options, etc.
 */

require( dirname( __FILE__ ) . '/functions.php' );
require( dirname( __FILE__ ) . '/functions/post.php' );
require( dirname( __FILE__ ) . '/functions/user.php' );
require( dirname( __FILE__ ) . '/functions/comment.php' );
require( dirname( __FILE__ ) . '/functions/option.php' );

/**
 * Class Custom_Fields
 */
class Custom_Fields {

	/**
	 * @var string
	 */
	static $prefix = false;

	/**
	 * @var array
	 */
	private static $_form_args = array();

	/**
	 * @var array
	 */
	private static $_field_args = array();

	/**
	 * @var array
	 */
	private static $_object_type_fields = array();

	/**
	 * @var array
	 */
	private static $_object_type_forms = array();

	/**
	 * @var array
	 */
	private static $_element_attributes = array();

	/**
	 * @var array
	 */
	private static $_views = array();

	/**
	 * @var WP_Annotated_Property[]
	 */
	private static $_class_annotations;

	/**
	 * @var WP_Registry[]
	 */
	private static $_registries = array(
			'storage_types'       => null,
			'field_types'         => null,
			'field_feature_types' => null,
	);

	/**
	 * @var array
	 */
	private static $_autoload_classes = array(
		'Custom_Fields_Base'       => 'base/class-custom-field-base.php',
		'WP_Object_Type'           => 'core/class-object-type.php',
		'WP_Html_Element'          => 'core/class-html-element.php',
		'WP_Registry'              => 'core/class-registry.php',
		'WP_Annotated_Property'    => 'core/class-annotated-property.php',
		'WP_Storage_Base'          => 'base/class-storage-base.php',
		'WP_Field_Base'            => 'base/class-field-base.php',
		'WP_Field_Feature_Base'    => 'base/class-field-feature-base.php',
		'WP_View_Base'             => 'base/class-view-base.php',
		'WP_Form_View_Base'        => 'base/class-form-view-base.php',
		'WP_Field_View_Base'       => 'base/class-field-view-base.php',
		'WP_Core_Storage'          => 'storage/class-core-storage.php',
		'WP_Meta_Storage'          => 'storage/class-meta-storage.php',
		'WP_Option_Storage'        => 'storage/class-option-storage.php',
		'WP_Memory_Storage'        => 'storage/class-memory-storage.php',
		'WP_Form'                  => 'forms/class-form.php',
		'WP_Text_Field'            => 'fields/class-text-field.php',
		'WP_Textarea_Field'        => 'fields/class-textarea-field.php',
		'WP_Url_Field'             => 'fields/class-url-field.php',
		'WP_Date_Field'            => 'fields/class-date-field.php',
		'WP_Hidden_Field'          => 'fields/class-hidden-field.php',
		'WP_Field_Input_Feature'   => 'features/class-field-input-feature.php',
		'WP_Field_Label_Feature'   => 'features/class-field-label-feature.php',
		'WP_Field_Help_Feature'    => 'features/class-field-help-feature.php',
		'WP_Field_Message_Feature' => 'features/class-field-message-feature.php',
		'WP_Field_Infobox_Feature' => 'features/class-field-infobox-feature.php',
		'WP_Form_View'             => 'views/class-form-view.php',
		'WP_Text_Field_View'       => 'views/class-text-field-view.php',
		'WP_Textarea_Field_View'   => 'views/class-textarea-field-view.php',
		'WP_Select_Field_View'     => 'views/class-select-view.php',
		'WP_Hidden_Field_View'     => 'views/class-hidden-field-view.php',
	);


	/**
	 *
	 */
	static function on_load() {

		spl_autoload_register( array( __CLASS__, '_autoloader' ) );

		self::initialize_registries();

		self::register_default_annotations( 'WP_Html_Element', array(
				'html_tag' => 'div'
		) );

		/*
		 * Register field classes
		 */
		//self::$_field_type_registry = new WP_Registry();
		self::register_field_type( 'text', 'WP_Text_Field' );
		self::register_field_type( 'textarea', 'WP_Textarea_Field' );
		self::register_field_type( 'url', 'WP_Url_Field' );
		self::register_field_type( 'date', 'WP_Date_Field' );
		self::register_field_type( 'hidden', 'WP_Hidden_Field' );

		self::register_field_view( 'text', 'WP_Text_Field_View' );
		self::register_field_view( 'textarea', 'WP_Textarea_Field_View' );
		self::register_field_view( 'select', 'WP_Select_Field_View' );
		self::register_field_view( 'hidden', 'WP_Hidden_Field_View' );

		self::register_feature_type( 'input', 'WP_Field_Input_Feature' );
		self::register_feature_type( 'label', 'WP_Field_Label_Feature' );
		self::register_feature_type( 'message', 'WP_Field_Message_Feature' );
		self::register_feature_type( 'help', 'WP_Field_Help_Feature' );
		self::register_feature_type( 'infobox', 'WP_Field_Infobox_Feature' );

		/*
		 * Register "storage" classes
		 */
		self::register_storage_type( 'meta', 'WP_Meta_Storage' );
		self::register_storage_type( 'core', 'WP_Core_Storage' );
		self::register_storage_type( 'option', 'WP_Option_Storage' );
		self::register_storage_type( 'taxonomy', 'WP_Taxonomy_Storage' );
		self::register_storage_type( 'memory', 'WP_Memory_Storage' );

		//    /**
		//     * Hook a different hook differently based on how the page is loaded to initialize the fields.
		//     */
		//    if ( defined( 'DOING_AJAX' ) ) {
		//      add_action( 'admin_init', array( __CLASS__, '_wp_loaded' ) );
		//    } else if ( is_admin() ) {
		//      add_action( 'admin_menu', array( __CLASS__, '_wp_loaded' ) );
		//    } else {
		//      add_action( 'wp_loaded', array( __CLASS__, '_wp_loaded' ) );
		//    }

		add_action( 'registered_post_type', array( __CLASS__, '_registered_post_type' ), 10, 2 );

		if ( is_admin() ) {
			add_action( 'admin_init', array( __CLASS__, '_admin_init' ) );
		}

	}

	/**
	 *
	 * @param string $class_name
	 * @param array $default_values
	 */
	static function register_default_annotations( $class_name, $default_values ) {

		WP_Annotated_Property::register_default_annotations( $class_name, $default_values );

	}

	/**
	 * @param string $type_name      - Name of type
	 * @param string|array $type_def - Classname, or array of $args
	 *
	 * @return bool Whether the object type $type_name was registered
	 */
	static function register_field_type( $type_name, $type_def = array() ) {

		if ( ! isset( self::$_registries['field_types']->$type_name ) ) {

			self::$_registries['field_types']->$type_name = $type_def;

			return true;

		}

		return false;
	}

	/**
	 * Register a class or array of View Type args.
	 *
	 * @example
	 *
	 *      Custom_Fields::register_field_view( 'text', 'WP_Text_Field_View' );
	 *      Custom_Fields::register_field_view( 'hidden', 'WP_Hidden_Field_View' );
	 *
	 * @param string $field_view
	 * @param string|array $field_view_args
	 */
	static function register_field_view( $field_view, $field_view_args ) {

		self::register_view( 'field_views', $field_view, $field_view_args );

	}

	/**
	 * Register a class to be used as a view for the current class.
	 *
	 * @example
	 *
	 *      Custom_Fields::register_view( 'field', 'text', 'WP_Text_Field_View' );
	 *      Custom_Fields::register_view( 'field', 'hidden', 'WP_Hidden_Field_View' );
	 *
	 * @param string $view_group Grouping of Views
	 * @param string $view_type  The name of the view that is unique for this class.
	 * @param string $class_name The class name for the View object.
	 */
	static function register_view( $view_group, $view_type, $class_name ) {

		if ( ! self::view_exists( $view_type, $view_group ) ) {
			self::$_views[ $view_group ][ $view_type ] = $class_name;
		}

	}

	/**
	 * Does the named field view exist
	 *
	 * @param string $view_type  The name of the view that is unique for this class.
	 * @param string $view_group Grouping of Views
	 *
	 * @return bool
	 */
	static function view_exists( $view_group, $view_type ) {

		return isset( self::$_views[ $view_group ][ $view_type ] );

	}

	/**
	 * @param string $feature_type  Name of Feature
	 * @param string $feature_class Classname
	 */
	static function register_feature_type( $feature_type, $feature_class ) {

		self::$_registries['field_feature_types']->register_entry( $feature_type, $feature_class );

	}

	/**
	 * @param string $storage_type_name       - Name of storage
	 * @param bool|string $storage_type_class - Classname
	 */
	static function register_storage_type( $storage_type_name, $storage_type_class = false ) {

		self::$_registries['storage_types']->register_entry( $storage_type_name, $storage_type_class );

	}

	/**
	 * @param string $class_name
	 * @param string $class_filepath
	 *
	 * @return bool Return true if it was registered, false if not.
	 */
	static function register_autoload_class( $class_name, $class_filepath ) {

		if ( ! isset( self::$_autoload_classes[ $class_name ] ) ) {

			self::$_autoload_classes[ $class_name ] = $class_filepath;

			return true;

		}

		return false;

	}

	/**
	 * @param string $class_name
	 */
	static function _autoloader( $class_name ) {

		if ( isset( self::$_autoload_classes[ $class_name ] ) ) {

			$filepath = self::$_autoload_classes[ $class_name ];

			/**
			 * @todo This needs to be made to work for Windows...
			 */
			if ( '/' == $filepath[0] ) {

				require_once( $filepath );

			} else {

				require_once( dirname( __FILE__ ) . "/{$filepath}" );

			}

		}

	}

	/**
	 *
	 */
	static function _admin_init() {
		if ( Custom_Fields::is_post_edit_screen() ) {
			add_action( 'edit_form_top', array( __CLASS__, '_edit_post_form' ) );
			add_action( 'edit_form_after_title', array( __CLASS__, '_edit_post_form' ) );
			add_action( 'edit_form_after_editor', array( __CLASS__, '_edit_post_form' ) );
			add_action( 'edit_form_advanced', array( __CLASS__, '_edit_post_form' ) );

//			add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );

			add_action( 'save_post_' . self::get_current_screen()->post_type, array( __CLASS__, '_save_post' ), 10, 3 );

			// Add global styles for custom fields api.
			add_action( 'admin_enqueue_scripts', array( __CLASS__, '_enqueue_admin_styles' ) );
		}
	}

	/**
	 * @return bool
	 *
	 * @todo For Core dev review. Better way?
	 */
	static function is_post_edit_screen() {

		global $pagenow;

		return 'post.php' == $pagenow || 'post-new.php' == $pagenow;

	}

	/**
	 * Grabs the current or a new WP_screen object.
	 *
	 * Tries to get the current one but if it's not available then it hacks it's way to recreate one
	 * because WordPress does not consistently set it, and it's not our place to change it's state.
	 * We just want what we want.
	 *
	 * @return WP_Screen
	 *
	 * @todo For Core dev review. Better way?
	 */
	static function get_current_screen() {

		$screen = get_current_screen();
		if ( empty( $screen ) ) {
			global $hook_suffix, $page_hook, $plugin_page, $pagenow, $current_screen;
			if ( empty( $hook_suffix ) ) {
				$save_hook_suffix    = $hook_suffix;
				$save_current_screen = $current_screen;
				if ( isset( $page_hook ) ) {
					$hook_suffix = $page_hook;
				} else if ( isset( $plugin_page ) ) {
					$hook_suffix = $plugin_page;
				} else if ( isset( $pagenow ) ) {
					$hook_suffix = $pagenow;
				}
				set_current_screen();
				$screen         = get_current_screen();
				$hook_suffix    = $save_hook_suffix;
				$current_screen = $save_current_screen;
			}
		}

		return $screen;
	}

	/**
	 * Load css required for the custom fields api.
	 *
	 */
	static function _enqueue_admin_styles( $hook ) {

		wp_enqueue_style( 'custom-fields', plugin_dir_url( __FILE__ ) . 'css/custom-fields.css', array() );

	}

	/**
	 * @param string|WP_Object_Type $object_type
	 * @param bool|array $form_names
	 *
	 * @return array
	 */
	static function get_forms( $object_type, $form_names = false ) {
		$forms = array();

		if ( isset( self::$_object_type_forms[ $object_type ] ) ) {
			$forms = self::$_object_type_forms[ $object_type ];
		}

		if ( $form_names ) {
			if ( is_array( $form_names ) ) {
				$form_names = array_flip( $form_names );
			} else {
				$form_names = array( $form_names => 0 );
			}
			$forms = array_intersect_key( $forms, $form_names );
		}

		foreach ( $forms as $form_name => $form_args ) {
			$forms[ $form_name ] = self::make_form( $form_name, $object_type, $form_args );
		}

		return $forms;
	}

	/**
	 * @param string $form_name
	 * @param string|WP_Object_Type $object_type
	 * @param array $form_args
	 *
	 * @return WP_Form
	 */
	static function make_form( $form_name, $object_type, $form_args = array() ) {

		return WP_Form::make_new( $form_name, $object_type, $form_args );

	}

	/**
	 * @param int $post_id
	 * @param WP_Post $post
	 * @param bool $update
	 */
	static function _save_post( $post_id, $post, $update ) {
		if ( count( $forms = self::get_forms_from_POST( $post->post_type ) ) ) {
			/**
			 * @var WP_Form $form
			 */
			foreach ( $forms as $form_name => $form ) {
				$form->set_object( $post );
				$form->update_values();
			}
		}
	}

	/**
	 * @param string $post_type
	 *
	 * @return array
	 */
	static function get_forms_from_POST( $post_type ) {
		$forms = array();
		if ( ! isset( $_POST[ 'cp_custom_fields_forms' ] ) || ! is_array( $_POST[ 'cp_custom_fields_forms' ] ) ) {
			$forms = array();
		} else {
			$forms = $_POST[ 'cp_custom_fields_forms' ];
			foreach ( $forms as $form_name => $form_data ) {
				$form = self::make_form( $form_name, Custom_Fields::get_post_object_type_literal( $post_type ), array( 'view' => false ) );
				/**
				 * @var WP_Field_Base $field
				 */
				foreach ( $form->fields as $field_name => $field ) {
					if ( isset( $form_data[ $field_name ] ) ) {
						$field->set_value( $form_data[ $field_name ] );
					}
				}
				$forms[ $form_name ] = $form;
			}
		}

		return $forms;
	}

	/**
	 * @param string $post_type
	 * @param array $args
	 */
	static function _registered_post_type( $post_type, $args ) {

		global $wp_post_types;

		if ( empty( $wp_post_types[ $post_type ] ) ) {
			return;
		}

		$wp_post_types[ $post_type ]->default_form = ! empty( $args->default_form ) ? $args->default_form : 'after_title';

	}

	/**
	 * Hook handler for 'edit_form_top', 'edit_form_after_title'. 'edit_form_after_editor' and 'edit_form_advanced'.
	 *
	 * Displayed the post_type's default form based on the value of post_type_object->default_form that can be set
	 * as an argument to register_post_type. Valid values for default form include:
	 *
	 *    'top', 'after_title', 'after_editor', 'advanced', or 'custom_fields'
	 *
	 * @todo Explain how to handle custom metaboxes once we figure out how we'll handle them.
	 *
	 * @param WP_Post $post
	 *
	 * @internal
	 *
	 */
	static function _edit_post_form( $post ) {

		$post_type    = $post->post_type;
		$object_type  = Custom_Fields::get_post_object_type_literal( $post_type );
		$current_form = preg_replace( '#^edit_form_(.*)$#', '$1', current_action() );

		if ( $current_form == get_post_type_object( $post_type )->default_form ) {
			if ( ! self::form_registered( $current_form, $object_type ) ) {
				self::register_form( $current_form, $object_type );
			}

			$form = self::get_form( $current_form, $object_type );

			$form->set_storage_object( $post );
			$form->the_form();
		}

	}

	/**
	 * @param string $form_name
	 * @param string|WP_Object_Type $object_type
	 *
	 * @return bool
	 */
	static function form_registered( $form_name, $object_type ) {

		return false !== self::get_form_index( $form_name, $object_type );

	}

	/**
	 * Retrieve a form
	 *
	 * @param string $form_name
	 * @param string|WP_Object_Type $object_type
	 *
	 * @return int
	 */
	static function get_form_index( $form_name, $object_type ) {

		return isset( self::$_object_type_forms[ $object_type ][ $form_name ] )
			? self::$_object_type_forms[ $object_type ][ $form_name ]
			: false;

	}

	/**
	 * @param string $form_name
	 * @param string|WP_Object_Type $object_type
	 * @param array $form_args
	 *
	 * @return int Form Index
	 */
	static function register_form( $form_name, $object_type, $form_args = array() ) {

		$form_args['form_name']   = $form_name;
		$form_args['object_type'] = $object_type;
		$form_args['form_index']  = count( self::$_form_args );

		self::$_object_type_forms[ $object_type ][ $form_name ] = $form_args['form_index'];
		self::$_form_args[]                                     = $form_args;

		return $form_args['form_index'];

	}

	/**
	 * Retrieve a form
	 *
	 * @param string $form_name
	 * @param string|WP_Object_Type $object_type
	 * @param array $form_args
	 *
	 * @return WP_Form
	 */
	static function get_form( $form_name, $object_type, $form_args = array() ) {

		$form_index = self::get_form_index( $form_name, $object_type );
		$form_args  = wp_parse_args( $form_args, self::get_form_args( $form_index ) );
		$form       = self::make_form( $form_name, $object_type, $form_args );

		return $form;

	}

	/**
	 * @param int $form_index
	 *
	 * @return bool|array
	 */
	static function get_form_args( $form_index ) {

		return isset( self::$_form_args[ $form_index ] ) ? self::$_form_args[ $form_index ] : false;

	}

	/**
	 * @param string|WP_Object_Type $object_type
	 *
	 * @return array
	 */
	static function get_field_names( $object_type ) {

		$object_type = (string) $object_type;

		return isset( self::$_object_type_fields[ $object_type ] )
			? array_keys( self::$_object_type_fields[ $object_type ] )
			: array();

	}

	/**
	 * Retrieve a field
	 *
	 * @param string $field_name
	 * @param string|WP_Object_Type $object_type
	 * @param array $field_args
	 *
	 * @return WP_Field_Base
	 */
	static function get_field( $field_name, $object_type, $field_args = array() ) {

		$field_index = self::get_field_index( $field_name, $object_type );
		$field_args  = wp_parse_args( $field_args, self::get_field_args( $field_index ) );
		$field       = self::make_field( $field_name, $object_type, $field_args );

		return $field;

	}

//
//	/**
//	 * @param string $tag_name
//	 * @param array $attributes
//	 * @param mixed $value
//	 *
//	 * @return WP_Html_Element
//	 */
//	static function get_element_html( $tag_name, $attributes, $value ) {
//
//		$html_element = self::get_html_element( $tag_name, $attributes, $value, true );
//
//		return $html_element->get_element_html();
//
//	}

	/**
	 * Retrieve a field
	 *
	 * @param string $field_name
	 * @param string|WP_Object_Type $object_type
	 *
	 * @return int
	 */
	static function get_field_index( $field_name, $object_type ) {

		$object_type = (string) $object_type;

		return isset( self::$_object_type_fields[ $object_type ][ $field_name ] ) ? self::$_object_type_fields[ $object_type ][ $field_name ] : false;

	}

	/**
	 * @param int $field_index
	 *
	 * @return bool|array
	 */
	static function get_field_args( $field_index ) {

		return isset( self::$_field_args[ $field_index ] ) ? self::$_field_args[ $field_index ] : false;

	}

	/**
	 * Make a New Field object
	 *
	 * @param string $field_name
	 * @param string|WP_Object_Type $object_type
	 * @param array $field_args
	 *
	 * @return WP_Field_Base
	 *
	 */
	static function make_field( $field_name, $object_type, $field_args = array() ) {

		return WP_Field_Base::make_new( $field_name, $object_type, $field_args );

	}

	/**
	 * @param string $field_name
	 * @param string|WP_Object_Type $object_type
	 * @param array $field_args
	 *
	 * @return int Field Index
	 */
	static function register_field( $field_name, $object_type, $field_args = array() ) {

		$field_args['field_name']  = $field_name;
		$field_args['object_type'] = $object_type;
		$field_args['field_index'] = count( self::$_field_args );

		self::$_object_type_fields[ $object_type ][ $field_name ] = $field_args['field_index'];
		self::$_field_args[]                                      = $field_args;

		return $field_args['field_index'];

	}

	/**
	 * @param string $field_type
	 *
	 * @return string|array|object
	 */
	static function get_field_type( $field_type ) {

		return self::$_registries['field_types']->$field_type;

	}

	/**
	 *
	 */

	/**
	 * Retrieve the $args for a named Field Type.
	 *
	 * Could be either an array of $args to create a field with make_new(), or a class name to instantiate it.
	 *
	 * @param string $field_type The name of the Field
	 *
	 * @return array
	 */
	static function get_field_type_args( $field_type ) {

		$field_type_args = self::$_registries['field_types']->$field_type;

		return $field_type_args;

	}

	/**
	 * @param string $tag_name
	 * @param array $attributes
	 * @param null ,mixed $value
	 * @param bool $reuse
	 *
	 * @return WP_Html_Element
	 */
	static function get_html_element( $tag_name, $attributes = array(), $value = null, $reuse = false ) {

		if ( ! $reuse ) {
			$element = new WP_Html_Element( $tag_name, $attributes, $value );
		} else {
			/**
			 * @var WP_Html_Element $reusable_element
			 */
			static $reusable_element = false;
			if ( ! $reusable_element ) {
				$reusable_element = new WP_Html_Element( $tag_name, $attributes, $value );
			} else {
				$reusable_element->assign( $tag_name, $attributes, $value );
			}
			$element = $reusable_element;
		}

		return $element;

	}

	/**
	 * Retrieve the class name for a named View.
	 *
	 * @param string $view_group Grouping of Views
	 * @param string $view_type  The name of the view that is unique for this class.
	 *
	 * @return string
	 */
	static function get_view_type_args( $view_group, $view_type ) {

		return self::view_exists( $view_group, $view_type ) ? self::$_views[ $view_group ][ $view_type ] : false;

	}

	/*********************************************/
	/***  Field Feature Type Registry Methods  ***/
	/*********************************************/

	/**
	 * Retrieve the class name for a named field view.
	 *
	 * @param string $field_view_type The name of the field_view that is unique for this class.
	 *
	 * @return string
	 */
	static function get_field_view_type_args( $field_view_type ) {

		return self::field_view_exists( $field_view_type )
				? self::$_views['field_views'][ $field_view_type ]
				: false;

	}

	/**
	 * Does the named field view exist?
	 *
	 * @param string $view_type The name of the view that is unique for this class.
	 *
	 * @return bool
	 */
	static function field_view_exists( $view_type ) {

		return self::view_exists( 'field_views', $view_type );

	}

	/**
	 * Does the named feature type exist?
	 *
	 * @param string $feature_type_name The name of the view that is unique for this class.
	 *
	 * @return bool
	 */
	static function feature_type_exists( $feature_type_name ) {

		return self::$_registries['field_feature_types']->entry_exists( $feature_type_name );

	}

	/*********************************************/
	/***  Field Storage Type Registry Methods  ***/
	/*********************************************/

	/**
	 * @param string $storage_type
	 *
	 * @return string
	 */
	static function get_storage_type_class( $storage_type ) {

		return self::$_registries['storage_types']->get_entry( $storage_type );

	}

	/**
	 * Does the named storage type exist?
	 *
	 * @param string $storage_type_name The name of the view that is unique for this class.
	 *
	 * @return bool
	 */
	static function storage_type_exists( $storage_type_name ) {

		return self::$_registries['storage_types']->entry_exists( $storage_type_name );

	}

	/**
	 * @param string $registry_type  - Name of Registry
	 * @param string $item_name      - Name of item in registry
	 * @param null|mixed $item_value - Value of item in registry
	 */
	static function register_registry_item( $registry_type, $item_name, $item_value = null ) {

		self::$_registries[ $registry_type ]->register_entry( $item_name, $item_value );

	}

	/*********************************************/
	/***    Generic Registry Item Methods      ***/
	/*********************************************/

	/**
	 * Initialize the generic registries.
	 *
	 */
	static function initialize_registries() {

		foreach ( array_keys( self::$_registries ) as $registry_type ) {

			self::register_registry( $registry_type );

		}

	}

	/**
	 * Registers a new type of Registry.
	 *
	 * @param string $registry_type  - Name of Registry
	 */
	static function register_registry( $registry_type ) {

		self::$_registries[ $registry_type ] = new WP_Registry( $registry_type );

	}

	/**
	 * @param string $registry_type - Name of Registry
	 * @param string $item_name     - Name of item in registry
	 *
	 * @return null|mixed
	 */
	static function get_registry_item( $registry_type, $item_name ) {

		return self::$_registries[ $registry_type ]->get_entry( $item_name );

	}

	/**
	 * Does the named registry item exist?
	 *
	 * @param string $registry_type - Name of Registry
	 * @param string $item_name     - Name of item in registry
	 *
	 * @return bool
	 */
	static function registry_item_exists( $registry_type, $item_name ) {

		return self::$_registries[ $registry_type ]->entry_exists( $item_name );

	}

	/**
	 * Does the named registry exist?
	 *
	 * @param string $registry_type - Name of Registry
	 *
	 * @return bool
	 */
	static function registry_exists( $registry_type ) {

		return isset( self::$_registries[ $registry_type ] );

	}

	/**
	 * Collect $args from list of keys and values into a tree of keys and values.
	 *
	 * Look for $args based on their prefixes (i.e. 'view:').
	 * If found capture the non-prefixed key and value into $collected_args for return.
	 * (Stripping the prefix allows for nested values, i.e. 'view:features[label]:wrapper:class')
	 *
	 * @note ONLY collect the first level, so $args['label:html:class'] would become $args['label']['html:class'].
	 *       Subnames will get split later thanks to calls that drill down recursively.
	 *
	 * @param array $prefixed_args
	 * @param array $prefixes
	 *
	 * @return array
	 */
	static function collect_args( $prefixed_args, $prefixes ) {
		$collected_args = array();

		$arg_count = count( $prefixed_args ) - 1;
		foreach ( array_keys( $prefixed_args ) as $key ) {
			/*
	 * Move the complex keys (ones with colons ) to after the simple keys
	 */
			if ( false !== strpos( $key, ':' ) ) {
				$value = $prefixed_args[ $key ];
				unset( $prefixed_args[ $key ] );
				$prefixed_args[ $key ] = $value;
			}
		}

		foreach ( $prefixed_args as $arg_name => $arg_value ) {
			if ( false === strpos( $arg_name, ':' ) ) {
				$collected_args[ $arg_name ] = $arg_value;
			} else {
				$index = false;
				list( $new_name, $sub_name ) = preg_split( '#:#', $arg_name, 2 );
				if ( preg_match( '#^(.+)\[([^]]+)\]$#', $new_name, $matches ) ) {
					list( $null, $new_name, $index ) = $matches;
					if ( ! isset( $collected_args[ $new_name ] ) || ! is_array( $collected_args[ $new_name ] ) ) {
						$collected_args[ $new_name ] = array();
					}
				}
				if ( isset( $prefixes[ $new_name ] ) ) {
					$collected_args['_collected_args'][ $arg_name ] = $arg_value;
					if ( false === $index ) {
						$collected_args[ $new_name ][ $sub_name ] = $arg_value;
					} else {
						$collected_args[ $new_name ][ $index ][ $sub_name ] = $arg_value;
					}
					unset( $collected_args[ $arg_name ] );
				}
			}
		}

		return $collected_args;

	}

	/*********************************************/
	/*** Prefix related methods                ***/
	/*********************************************/

	/**
	 * @param string $class_name
	 * @param string $property_name
	 *
	 * @note UNTESTED
	 *
	 * @return bool
	 */
	static function has_own_static_property( $class_name, $property_name ) {

		$has_own_static_property = false;

		if ( property_exists( $class_name, $property_name ) ) {
			$reflected_property      = new ReflectionProperty( $class_name, $property_name );
			$has_own_static_property = $reflected_property->isStatic();
		}

		return $has_own_static_property;

	}

	/**
	 * Allow invoking of instance methods that are overridden by methods in a child class.
	 *
	 * This allows for methods as filters and actions without requiring them to call parent::method().
	 *
	 * @note UNTESTED
	 *
	 * @param string $class_name
	 * @param object $object
	 * @param string $method_name
	 *
	 * @return mixed
	 */
	static function get_static_method_name( $class_name, $object, $method_name ) {

		$reflected_method = new ReflectionProperty( $class_name, $method_name );

		return $reflected_method->getValue( $object );

	}

	/**
	 * @param string $class_name
	 * @param string $property_name
	 *
	 * @return bool
	 */
	static function non_public_property_exists( $class_name, $property_name ) {

		$reflection = new ReflectionClass( $class_name );

		if ( ! $reflection->hasProperty( $property_name ) ) {
			$exists = false;
		} else {
			$property_name = $reflection->getProperty( $property_name );
			$exists        = $property_name->isProtected() || $property_name->isPrivate();
		}

		return $exists;

	}


	/**
	 * Get an array of class name parent
	 *
	 * Returns an array of all parent class names with most distant ancenstor first down to parent class,
	 * or the named class (if inclusive.)
	 *
	 * @example array( 'WP_Base', 'WP_Field_Base', 'WP_Text_Field' )
	 *
	 * @param string $class_name
	 * @param bool $inclusive
	 *
	 * @return array
	 */
	static function get_class_parents( $class_name, $inclusive = true ) {

		if ( ! ( $parents = wp_cache_get( $cache_key = "class_lineage[{$class_name}]" ) ) ) {
			$parents = $inclusive ? array( $class_name ) : array();

			if ( $class_name = get_parent_class( $class_name ) ) {
				$parents = array_merge( self::get_class_parents( $class_name, true ), $parents );
			}

			wp_cache_set( $cache_key, $parents );
		}

		return $parents;

	}

	/**
	 * @param string $class_name
	 * @param string $const_name
	 *
	 * @return mixed
	 *
	 */
	static function constant( $class_name, $const_name ) {

		return defined( $const_ref = "{$class_name}::{$const_name}" ) ? constant( $const_ref ) : null;

	}

	/**
	 * @param string $class_name
	 * @param string $method_name
	 *
	 * @return bool
	 */
	static function has_own_method( $class_name, $method_name ) {

		$has_own_method = false;

		if ( method_exists( $class_name, $method_name ) ) {
			$reflector      = new ReflectionMethod( $class_name, $method_name );
			$has_own_method = $class_name == $reflector->getDeclaringClass()->name;
		}

		return $has_own_method;

	}

	/**
	 * Allow invoking of instance methods that are overridden by methods in a child class.
	 *
	 * This allows for methods as filters and actions without requiring them to call parent::method().
	 *
	 * @param object|null $instance
	 * @param string $class_name
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return mixed
	 */
	static function invoke_specific_class_method( $instance, $class_name, $method_name, $args ) {

		$reflected_class  = new ReflectionClass( $class_name );
		$reflected_method = $reflected_class->getMethod( $method_name );

		if ( is_bool( $args ) ) {
		 	echo '';
		}
		return $reflected_method->invokeArgs( $instance, $args );

	}

	/**
	 * Return the HTML tag to be used by a view class.
	 *
	 * @param string $view_class
	 *
	 * @return string
	 */
	static function get_view_input_tag( $view_class ) {

		$input_tag = false;

		$defaults = Custom_Fields::get_class_defaults( $view_class );

		if ( isset( $defaults[ $input_key = 'features[input]:element:html_tag' ] ) ) {

			$input_tag = $defaults[ $input_key ];

		}

		return $input_tag ? $input_tag : 'input';

	}

	/**
	 * @param string|object $class_name_or_object
	 *
	 * @return string[];
	 *
	 */
	static function get_class_defaults( $class_name_or_object ) {

		$defaults = self::get_class_value( $class_name_or_object, 'defaults' );

		return $defaults ? $defaults : array();

	}

	/**
	 * Collect an array of class vars defined as either class constant or static method.
	 * Start with the most distant anscestor down to the current class and merge $class_vars.
	 *
	 * @param string|object $class_name_or_object
	 *
	 * @return string[];
	 *
	 */
	static function get_class_values( $class_name_or_object ) {

		return self::get_class_vars( $class_name_or_object, 'CLASS_VALUES' );

	}

	static function get_annotations() {
	}

	/**
	 * Collect an array of class vars defined as either class constant or static method.
	 * Start with the most distant anscestor down to the current class and merge $class_vars.
	 *
	 * @param string|object $class_name_or_object
	 * @param string $class_var
	 *
	 * @return string[];
	 *
	 */
	static function get_class_vars( $class_name_or_object, $class_var ) {

		$class_name = is_object( $class_name_or_object ) ? get_class( $class_name_or_object ) : $class_name_or_object;

		if ( !( $class_vars = wp_cache_get( $cache_key = "{$class_name}::class_vars[{$class_var}]", 'custom-fields' ) ) ) {

			$parents = self::get_class_parents( $class_name, true );

			$class_vars = array();

			foreach ( $parents as $parent ) {

				if ( defined( $const_ref = "{$parent}::{$class_var}" ) ) {

					$class_class_vars = array( self::constant( $class_var, $parent ) );

				} else if ( self::has_own_method( $parent, $class_var ) ) {

					$class_class_vars = Custom_Fields::invoke_specific_class_method( null, $parent, $class_var, $class_vars );

				} else {

					$class_class_vars = array();

				}

				foreach ( $class_class_vars as $field_name => $class_class_var ) {

					if ( isset( $class_vars[ $field_name ] ) && is_array( $class_vars[ $field_name ] ) ) {

						$class_vars[ $field_name ] = array_merge( $class_vars[ $field_name ], $class_class_var );

					} else {

						$class_vars[ $field_name ] = $class_class_var;

					}

				}

			}

			/*
		   * Remove any class_vars values that are null.
			 */
			$class_vars = array_filter( $class_vars, array( __CLASS__, '_strip_null_elements' ) );

			/*
			 * Now store all that work in the object cache!
			 */
			wp_cache_set( $cache_key, $class_vars, 'custom-fields' );

		}

		return $class_vars;

	}

	/**
	 * @param string $html_tag
	 *
	 * @return array
	 */
	static function get_view_element_attributes( $html_tag ) {

		return array_keys( self::get_html_attributes( $html_tag ) );

	}

	/**
	 * @param $html_element
	 *
	 * @return array
	 */
	static function get_html_attributes( $html_element ) {

		if ( ! isset( self::$_element_attributes[ $html_element ] ) ) {

			/**
			 * @see http://www.w3.org/TR/html5/dom.html#global-attributes
			 */
			$attributes = array(
					'accesskey',
					'class',
					'contenteditable',
					'dir',
					'draggable',
					'dropzone',
					'hidden',
					'id',
					'lang',
					'spellcheck',
					'style',
					'tabindex',
					'title',
					'translate'
			);

			switch ( $html_element ) {

				case 'input':
					$more_attributes = array(
							'accept',
							'alt',
							'autocomplete',
							'autofocus',
							'autosave',
							'checked',
							'dirname',
							'disabled',
							'form',
							'formaction',
							'formenctype',
							'formmethod',
							'formnovalidate',
							'formtarget',
							'height',
							'inputmode',
							'list',
							'max',
							'maxlength',
							'min',
							'minlength',
							'multiple',
							'name',
							'pattern',
							'placeholder',
							'readonly',
							'required',
							'selectionDirection',
							'size',
							'src',
							'step',
							'type',
							'value',
							'width'
					);
					break;

				case 'textarea':
					$more_attributes = array( 'cols', 'name', 'rows', 'tabindex', 'wrap' );
					break;

				case 'label':
					$more_attributes = array( 'for', 'form' );
					break;

				case 'ul':
					$more_attributes = array( 'compact', 'type' );
					break;

				case 'ol':
					$more_attributes = array( 'compact', 'reversed', 'start', 'type' );
					break;

				case 'li':
					$more_attributes = array( 'type', 'value' );
					break;

				case 'a':
					$more_attributes = array(
							'charset',
							'coords',
							'download',
							'href',
							'hreflang',
							'media',
							'rel',
							'target',
							'type'
					);
					break;

				case 'section':
				case 'div':
				case 'span':
				default:
					$more_attributes = false;
					break;
			}

			if ( $more_attributes ) {
				$attributes = array_merge( $attributes, $more_attributes );
			}

			self::$_element_attributes[ $html_element ] = array_fill_keys( $attributes, false );

		}

		return self::$_element_attributes[ $html_element ];

	}

	/**
	 * Call a named method starting with the most distant anscestor down to the current class filtering $value.
	 *
	 * @param object $object
	 * @param string $method_name
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	static function apply_class_filters( $object, $method_name, $value ) {

		$args    = func_get_args() ? array_slice( func_get_args(), 2 ) : array( null );
		$parents = self::get_class_parents( get_class( $object ), true );

		foreach ( $parents as $parent ) {
			if ( self::has_own_method( $parent, $method_name ) ) {
				$args[0] = $value;
				$value   = self::invoke_specific_class_method( $object, $parent, $method_name, $args );
			}
		}

		return $value;

	}

	/**
	 * Call a named method starting with the most distant anscestor down to the current class with no return value.
	 *
	 * @param object $object
	 * @param string $class_name
	 * @param string $method_name
	 */
	static function do_class_action( $object, $class_name, $method_name ) {

		$args = func_get_args();

		$invoke_args = $args ? array_slice( $args, 3 ) : array();

		$parents = self::get_class_parents( $class_name, true );

		foreach ( $parents as $parent ) {
			if ( self::has_own_method( $parent, $method_name ) ) {
				self::invoke_specific_class_method( $object, $parent, $method_name, $invoke_args );
			}
		}

	}

	/**
	 * @param mixed $element
	 *
	 * @return bool
	 */
	static function _strip_null_elements( $element ) {

		return ! is_null( $element );

	}

	/**
	 * Returns an instance of a storage object.
	 *
	 * @param string $storage_type
	 * @param object $owner
	 * @param array $storage_args
	 *
	 * @return null|WP_Storage_Base
	 */
	static function make_storage( $storage_type, $owner = null, $storage_args = array() ) {

		return WP_Storage_Base::make_new( $storage_type, $owner, $storage_args );

	}

	/**
	 * @param string $view_type
	 * @param WP_Field_Base $field
	 * @param array $view_args
	 *
	 * @return WP_Field_View_Base
	 */
	static function make_field_view( $view_type, $field, $view_args = array() ) {

		return WP_Field_View_Base::make_new( $view_type, $field, $view_args );

	}

	/**
	 * @param WP_Field_Base $field
	 * @param array[] $features_args
	 *
	 * @return null|WP_Field_Feature_Base
	 */
	static function make_field_features( $field, $features_args ) {

		$features = array();

		foreach ( $features_args as $feature_type => $feature_args ) {

			$features[ $feature_type ] = self::make_field_feature( $field, $feature_type, $feature_args );

		}

		return $features;

	}

	/**
	 * @param WP_Field_Base $field
	 * @param string $feature_type
	 * @param array $feature_args
	 *
	 * @return null|WP_Field_Feature_Base
	 */
	static function make_field_feature( $field, $feature_type, $feature_args ) {

		if ( $feature_class = self::get_feature_type_class( $feature_type ) ) {

			$feature = new $feature_class( $field, $feature_args );

			if ( property_exists( $field, 'field' ) ) {

				$view->field = $field;

			}

		} else {

			$feature = null;

		}

		return $feature;

	}

	/**
	 * @param $feature_type
	 *
	 * @return string
	 */
	static function get_feature_type_class( $feature_type ) {

		return self::$_registries['field_feature_types']->get_entry( $feature_type );

	}

	/**
	 * @param string|object $class_name_or_object
	 * @param string $value_name
	 *
	 * @return string[];
	 *
	 */
	static function get_class_value( $class_name_or_object, $value_name ) {

		$class_values = self::get_class_values( $class_name_or_object );

		return isset( $class_values[ $value_name ] ) ? $class_values[ $value_name ] : null;

	}

	/**
	 * @param string $class_name
	 *
	 * @return string[];
	 *
	 */
	static function get_make_new_parameters( $class_name ) {

		$parameters = array();

		$parents = self::get_class_parents( $class_name );

		foreach( $parents as $this_class ) {

			$class_vars = call_user_func( array( $this_class, 'CLASS_VALUES' ) );

			if ( ! empty( $class_vars['parameters'] ) && is_array( $class_vars['parameters'] ) ) {

				$parameters = $class_vars['parameters'];

			}

		}

		return $parameters;

	}

	/**
	 * Build Property Parameters for Object Constructor
	 *
	 * @param string $class_name
	 * @param array $object_args
	 *
	 * @return array
	 */
	static function build_property_parameters( $class_name, $object_args = array() ) {

		return WP_Annotated_Property::build_parameters( $class_name, $object_args );

	}

	/**
	 * Returns an Object Type literal given a post type
	 *
	 * @param string $post_type
	 *
	 * @return string
	 */
	static function get_post_object_type_literal( $post_type ) {

		return WP_Object_Type::get_post_object_type_literal( $post_type );

	}

	/**
	 * Sanitizes an identifier
	 *
	 * An identifier is defined as a string that must start with a letter and can contain letters numbers or underscrores.
	 *
	 * Identifiers are converted to lower case but will return null if the identifier is not valid.
	 *
	 * Dashes are allowed if a single '-' is passed at the 2nd parameter.
	 *
	 * @param string $identifier String to sanitize following the rules of an identifier.
	 *
	 * @param string $allow Typically used to allow a dash in the idenitifier; If needed, pass in a literal string '-'.
	 *
	 * @return null|string
	 */
	static function sanitize_identifier( $identifier, $allow = '' ) {

		$identifier = strtolower( $identifier );

		if ( ! preg_match( '#^[a-z_]#', $identifier ) || preg_replace( "#[^a-z0-9_{$allow}]#", '', $identifier ) != $identifier ) {

			$identifier = null;

		}

		return $identifier;

	}
}

Custom_Fields::on_load();
