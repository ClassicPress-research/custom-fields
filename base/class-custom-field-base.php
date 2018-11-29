<?php

/**
 * Class Custom_Fields_Base
 *
 * This is the "base" class for all other classes in the Custom_Fields 'feature-as-plugin" except for the "core" classes
 * in the /core/ directory or classes whose base class ultimately extends this class.
 *
 * The primary benefits of the Custom_Fields_Base class are :
 *
 *  1. Provide support for Java-like annotations that we use to enable must greater control over instantiation.
 *  2. Provide a run-once class initializer
 *  3. Provide a framework for initializing object properties using $args passed in directly or indirectly.
 *
 * Although Custom_Fields_Base's name implies it is only designed for use by the #custom-fields feature-as-plugin this
 * class was in fact architected in hopes that it can be renamed WP_Base and used throughout WordPress anywhere
 * there would be a significant benefit to using an OOP-based object hiearchy.
 */
abstract class Custom_Fields_Base {

	/**
	 * @var array Array to capture args passed but not expected by the class.
	 *            Useful for allowing site builder to add values needed for
	 *            customized site logic without having to create a new class,
	 *            when applicable.
	 */
	var $custom_args = array();

	/**
	 * @var array Used to capture args just before assignment to properties.
	 *            Useful for debugging.
	 */
	private $_args = array();

	/**
	 * @var bool[] Array of flags with class names as keys indicating that the
	 *             class has been instantiated at least once and that the
	 *             method initialize_class() has been run for this class and
	 *             all it's ancestor classes.
	 */
	private $_initialized = array();


	/**
	 * Instantiate most any object of the Custom_Fields feature-as-plugin.
	 *
	 * This constructor extends PHP in signficant and powerful ways, support annotations to enable
	 * complex class containment hierarchies to be instantiated directly from the $args passed to
	 * the main object. This is really beneficial for creating flexible Fields and Forms.
	 *
	 * @param array $args
	 *
	 */
	function __construct( $args = array() ) {

		if ( ! isset( $this->_initialized[ $this_class = get_class( $this ) ] ) ) {
			/*
			 * Check to see if any instance of this class has ever been instantiated.
			 */
			$this->_initialized[ $this_class ] = true;

			/*
			 * Runs the $this->initialize_class() method once per each ancestor class, if
			 * declared, and then once for the class itself, if declared. This allows each
			 * declared class a chance to initialize itself.
			 *
			 * IMPORTANT: parent::initialize_class() should NEVER be called inside any
			 * initialize_class() method as it will result in running initializations
			 * multiple times that were designed to be run once.
			 */
			$this->do_class_action( 'initialize_class' );
		}

		if ( ! is_array( $args ) ) {
			/*
			 * If a value is passed in for $args and it's not an array, toss it and give me an array().
			 * Is this needed. Meebe, meebe-not. But it's here just in case.
			 */
			$args = array();
		}

		if ( $this->do_process_args( true, $args ) ) {
			/*
			 * Allow a child class to short-circuit the defaults/expand/collect/assign
			 * steps below. To short-circuit simply implement the do_process_args()
			 * method and return false.
			 */

			/*
			 * Add any default values declared in annotations and
			 * merge with $args to include defaults for and missing
			 * properties but not to overwrite $args values that
			 * exist as elements in the array.
			 */
			$args = $this->get_default_args( $args );

			/*
			 * Use the RegEx entries defined in the CLASS_VARs() 'shortcuts' element to
			 * expand arguments from shortcuts to fully qualified args.
			 *
			 * @example When passed to WP_Field_Base:
			 *
			 *    'label' expands to 'view:features[label]:label_text
			 *    'size'  expands to 'view:features[input]:eleement:size
			 *
			 */
			$args = $this->expand_args( $args );

			/*
			 * Scan the args for colons and convert those into subarrays by paring off
			 * the string to the left of the first colon and using it as an array element
			 * that contains an array of the values stripped.
			 *
			 * @example $args['view:view_type'] collects to $args['view']['view_type']
			 *
			 * In the case of an array then it collects but the array and keys.
			 *
			 * @example $args['features[input]:element:size'] collects to $args['features']['input']['element:size']
			 *
			 * Note that the architecture does not attempt to collect recursively which
			 * is why 'element:size' was not collected.It assumes that a contained class
			 * will handle that collection later.
			 */
			$args = $this->collect_args( $args );

			/*
			 * Capture these args before assignment for debugging, if needed.
			 */
			$this->_args = $args;

			/*
			 * Assign these $args to the properties of this object.
			 *
			 * In the case of properties that expect contained objects as per their annotations
			 * as returned by the PROPERTIES() method, use the ::make_new() factory method
			 * to instantiate and use the 'properties' element of the array returned by
			 * CLASS_VALUES() to build the parameter list for ::make_new().
			 */
			$this->assign_args( $args );

		}

		/*
		 * Call $this->pre_initialize() to filter $args after assignment but just prior
		 * to runing $this->initialize(), if needed.  There are some cases where this
		 * is imporant.
		 *
		 * @see WP_Field_Input_Feature->pre_initialize() for an example.
		 *
		 * Runs $this->pre_initialize() method only once per each ancestor class, if
		 * declared, and then once for the class itself, if declared. This allows
		 * each declared class a chance to initialize itself.
		 *
		 * IMPORTANT: parent::pre_initialize() should NEVER be called inside any
		 * initialize_class() method as it will result in running initializations
		 * multiple times that were designed to be run once.
		 */
		$args = $this->apply_class_filters( 'pre_initialize', $args );

		/*
		 * Call $this->initialize() to initialize any property values that cannot
		 * be initialized via generic methods, such as setting the @for attribute
		 * of the <label> feature to equal the @id attribute of the <input>
		 * feature.
		 *
		 * @see WP_Field_View_Base->initialize() for an example.
		 *
		 * Runs $this->initialize() method only once per each ancestor class, if
		 * declared, and then once for the class itself, if declared. This allows
		 * each declared class a chance to initialize itself.
		 *
		 * IMPORTANT: parent::initialize() should NEVER be called inside any
		 * initialize() method as it will result in running initializations
		 * multiple times that were designed to be run once.
		 */
		$this->do_class_action( 'initialize', $args );


		if ( ! WP_DEBUG ) {
			/**
			 * If we are not running with WP_DEBUG == true then clear out the
			 * memory used by $_args since we primarily only captured for
			 * debugging purposes anyway.
			 */
			$this->_args = null;

		}

	}

	/**
	 * CLASS_VALUES() is designed to be subclassed for class-level annotations.
	 *
	 * This ia a special purpose function (hence the UPPER_CASE() naming
	 * format) which is designed for providing annotation values at a class
	 * level.
	 *
	 * @important Return values SHOULD BE CACHABLE ACROSS PAGE LOADS from child
	 *            class implementations.
	 *
	 * @important Child class implementations SHOULD NOT call parent::CLASS_VALUES().
	 *
	 * The current recognized keys are the following (although plugins are free
	 * to add their own while following the best practice of prefixing[1]) and
	 * there is no set structure for the values:
	 *
	 *  - 'defaults'    - An associative array of defaults for $args. Useful for
	 *                    setting default $args for contained objects that will
	 *                    be instantiated automatically inside $this->assign_args().
	 *
	 *  - 'shortnames' -  An associative array with Regex as keys and Match Patterns
	 *                    as values used to transform short $arg names into fully
	 *                    qualified $arg names.
	 *
	 *  - 'parameters' -  A simple array whose values defines the parameter(s)
	 *                    needed for the make_new() method.
	 *
	 * @return array The full associative array of CLASS_VALUES for this class.
	 *
	 * @see [1] http://nacin.com/2010/05/11/in-wordpress-prefix-everything/
	 */
	static function CLASS_VALUES() {

		return array(
				/*
				 * Although the base class does not define its own make_new() method,
				 * the base pattern for make_new() is to expect one parameter being
				 * an associative array of "$args."
				 *
				 * So set this here for simple classes so those classes do not have to.
				 */
				'parameters' => array( '$args' ),
		);

	}

	/**
	 * PROPERTIES() is designed to be subclassed for class-level annotations.
	 *
	 * This ia a special purpose function (hence the UPPER_CASE() naming
	 * format) which is designed for providing annotations at a class property
	 * level.
	 *
	 * @important Return values SHOULD BE CACHABLE ACROSS PAGE LOADS from child
	 *            class implementations.
	 *
	 * @important Child class implementations SHOULD NOT call parent::CLASS_VALUES().
	 *
	 * The current recognized keys match the property names for the proerties of
	 * the class they annotate. Their values should be an associative array
	 * that are valid $arg values for instantiating a WP_Annotated_Property
	 * however 'type' is used as a shorthand for $property_type.
	 *
	 * However a sitebuilder, or a plugin or theme developer can add add their kyes
	 * assumubg they follow the best practice of prefixing[1]):
	 *
	 * Commonly used keys:
	 *
	 *  - 'type'        - Often a class name but may alternately designate an array of
	 *                    same class name where the class name is a base class that
	 *                    has a make_new() factory static method. The syntax for
	 *                    an array of classes is 'Class_Name[]' where the empty
	 *                    square brackets denote the array. The type may also be
	 *                    scalar (i.e. 'int') or an array of scalar (i.e. 'string[]').
	 *                    This gets stored in the $property_type property of a
	 *                    WP_Annotated_Property object.
	 *
	 *  - 'default'     - The default value for this property if a value is not
	 *                    provided in the $args array passed to __construct().
	 *
	 *  - 'auto_create' - Defaults to true, setting to false tells this class not
	 *                    to automatically create instances of the contained object
	 *                    for this property. The most common use for this would be
	 *                    in the case of a 'parent or 'owner' object assigned to a
	 *                    property of a child or contained object.
	 *
	 *  - 'registry'    - The WP_Registry Type which should also have either been
	 *                    hardcoded in Custom_Fields::$_registries or registered using
	 *                    Custom_Fields::register_registry(). This registry is used to
	 *                    look up class names by key when 'type' is an array of objects,
	 *                    i.e. 'WP_Field_Feature_Base[]'.
	 *
	 *  - 'keys'        - The array of array key names (string) for when 'type' is
	 *                    an array of objects, i.e. for when 'type' is the array
	 *                    'WP_Field_Feature_Base[]' then the array of key names
	 *                    is: array('label','input','help','message','infobox')
	 *
	 *  - 'prefix'      - The qualifing prefix for this property. This can be specified
	 *                    if you need to change the name of the $args prefix to be
	 *                    different than the $property_name. For example, the prefix
	 *                    for the view of a field is 'view' this this is a valid $arg
	 *                    key for instantiating a field: 'view:view_type' although
	 *                    for that specific case 'view_type' can be used as a shorthand.
	 *
	 * @return array The full associative array of PROPERTIES for this class.
	 *
	 * @see [1] http://nacin.com/2010/05/11/in-wordpress-prefix-everything/
	 */
	static function PROPERTIES() {

		/*
		 * No properties are declared in this base class.
		 */
		return array();

	}

	/**
	 * Do an action specific to this class and its ancestor classes.
	 *
	 * This function starts by calling the specified field on the most distant ancestor class which defines the filter
	 * and then calls its next child that defines the filter, and so on.
	 *
	 * @example Calling $this->do_action( 'do_something' ) on an instance of 'WP_Text_Field' which extends
	 *          'WP_Field_Base' which extends 'Custom_Fields_Base' would be the same as calling the following code
	 *          assuming that each class defined a do_something() method and that PHP could cast objects to their
	 *          subclasses and in this order:
	 *
	 *          <code>
	 *          $field = (Custom_Fields_Base)$field;
	 *          $field->do_something();
	 *
	 *          $field = (WP_Field_Base)$field;
	 *          $field->do_something();
	 *
	 *          $field = (WP_Text_Field)$field;
	 *          $field->do_something();
	 *          </code>
	 *
	 *
	 * @important Implementations SHOULD NOT call parent::{$$action_method}() as
	 *            Custom_Fields::do_class_action() will do that.
	 *
	 * @important This is NOT a replacement for do_action(); instead it is used for different use-cases.
	 *
	 * This function is used instead of do_action() for when the scope and visibility should rightly stay
	 * within the context of the class, and should NOT REQUIRE the implementor of the child class to
	 * ensure that context is maintained.
	 *
	 * Using this function instead of do_action() *where it applies* should result in more robust code.
	 *
	 * There should still be do_action()s placed in code for when hooking is important in non-subclassing
	 * use-cases.
	 *
	 * @uses Custom_Fields::do_class_action() Called after this function packages its parameters.
	 *
	 * @param string $action_method The name of the method to call on the object cast as instances of ancestor classes
	 *                              and then on itself, assuming the method is declared for a given class.
	 *
	 */
	function do_class_action( $action_method ) {

		/*
		 * Insert a reference to $this and this object's class name ahead of
		 * the parameters passed to this function.
		 */
		$args = array_merge( array( $this, get_class( $this ) ), func_get_args() );

		/*
		 * Call Custom_Fields::do_class_action() with the $args $this, class name and then
		 * whatever $args where passed to this function.
		 */
		call_user_func_array( array( 'Custom_Fields', 'do_class_action' ), $args );

	}

	/**
	 * Do an action specific to this class and its ancestor classes.
	 *
	 * This function starts by calling the specified field on the most distant ancestor class which defines the filter
	 * and then calls its next child that defines the filter, and so on.
	 *
	 * This function has a responsibility to return it's first parameter 'filtered' in whatever way that 'filtered'
	 * means for this function (which could mean no change if the function simply wants to be called to do something
	 * else at that point, although that usage would generally be frowned on unless there is no other way to acheive
	 * the end goal.)
	 *
	 * @example Calling $this->apply_class_filters( 'filter_args' ) on an instance of 'WP_Text_Field' which extends
	 *          'WP_Field_Base' which extends 'Custom_Fields_Base' would be the same as calling the following code
	 *          assuming that each class defined a filter_args() method and that PHP could cast objects to their
	 *          subclasses and in this order:
	 *
	 *          <code>
	 *          $field = (Custom_Fields_Base)$field;
	 *          $args = $field->filter_args( $args );
	 *
	 *          $field = (WP_Field_Base)$field;
	 *          $args = $field->filter_args( $args );
	 *
	 *          $field = (WP_Text_Field)$field;
	 *          $args = $field->filter_args( $args );
	 *          </code>
	 *
	 *
	 * @important Implementations SHOULD NOT call parent::{$filter_method}() as
	 *            Custom_Fields::apply_class_filters() will do that.
	 *
	 * @important This is NOT a replacement for apply_filters(); instead it is used for different use-cases.
	 *
	 * This function is used instead of apply_filters() for when the scope and visibility should rightly
	 * stay within the context of the class, and should NOT REQUIRE the implementor of the child class to
	 * ensure that context is maintained.
	 *
	 * Using this function instead of apply_filters() *where it applies* should result in more robust code.
	 *
	 * There should still be apply_filters()s placed in code for when hooking is important in non-subclassing
	 * use-cases.
	 *
	 * @uses Custom_Fields::apply_class_filters() Called after this function packages its parameters.
	 *
	 * @param string $filter_method The name of the method to call on the object cast as instances of ancestor classes
	 *                              and then on itself, assuming the method is declared for a given class.
	 * @param mixed $value The value to filter.
	 *
	 * @return mixed
	 */
	function apply_class_filters( $filter_method, $value ) {

		if ( is_null( $args = func_get_args() ) ) {

			$args = array( $this );

		} else {

			array_unshift( $args, $this );

		}

		return call_user_func_array( array( 'Custom_Fields', 'apply_class_filters' ), $args );

	}

	/**
	 * Return true if _construct() should call defaults/expand/collect/assign for the $args passed.
	 *
	 * do_process_args() is designed to be subclassed for class-level annotations to allow subclasses
	 * to override and/or replace the defaults/expand/collect/assign processing, if needed.
	 *
	 * @param bool $continue The value of continue passed in by either this class or the child class.
	 *
	 * @param array $args The $args passed to __construct()
	 *
	 * @return bool If false returned then the defaults/expand/collect/assign processing of $args is bypassed.
	 *
	 * @important Assuming $continue is passed in as false then this subclassed function's responsibility
	 *            is to return false as well unless it knowingly is able to override the reason that false
	 *            is passed in (which is unlikely given it's defined in a parent class.)
	 */
	function do_process_args( $continue, $args = array() ) {

		/*
		 * Return what was passed since sole reason for being here is to allow subclasses to override this function.
		 */
		return $continue;

	}

	/**
	 * Get the default $arg names and values declared for the class of $this instance and merge in those passed.
	 *
	 * The $args passed in take precedent over default $args.
	 *
	 * This function does the work to collect up the default args the first time it is called after which it
	 * retrieves the value from cache.
	 *
	 *
	 * @param $args array
	 *
	 * @return array
	 */
	function get_default_args( $args = array() ) {

		/*
		 * Check the object cache for "{$class_name}::default_args"
		 */
		if ( !( $default_args = wp_cache_get( $cache_key = get_class( $this ) . '::default_args', 'custom-fields' ) ) ) {
			/*
			 * If this is the first call of this method for the this class and thus the cache has yet to be set...
			 */

			/*
			 * First get the annotations that are available
			 */
			$annotations = $this->get_property_annotations();

			/*
			 * Create a variable to hold the default $args and then initialize it
			 * with any class default $argsdefined in the 'defaults' argument of
			 * the CLASS_ARGS() method.
			 */
			$default_args = self::get_class_defaults();

			foreach ( $annotations as $annotation_name => $annotation ) {
				/*
				 * For each of the available annotations
				 */

				if ( ! is_null( $annotation->default )  ) {
					/*
					 * Capture the property's default if a default has been set.
					 * Overwrite any defaults that were defined
					 */
					$default_args[ $annotation_name ] = $annotation->default;

				}

			}

			/*
			 * Now store all that work in the object cache!
			 */
			wp_cache_set( $cache_key, $default_args, 'custom-fields' );

		}

		/*
		 * Finally, merge the $args passed in over top of the default $args, if there were any.
		 */
		return count( $args ) ? array_merge( $default_args, $args ) : $default_args;
	}

	/**
	 *
	 * @return WP_Annotated_Property[]
	 */
	function get_property_annotations() {

		$class_name = get_class( $this );

		$cache_key = "{$class_name}::property_annotations";

		if ( !( $property_annotations = wp_cache_get( $cache_key, 'custom-fields' ) ) ) {

			$property_annotations = Custom_Fields::get_class_vars( $class_name, 'PROPERTIES' );

			/**
			 * Finally, convert all annotated properties to a WP_Annotated_Property class.
			 */
			foreach ( $property_annotations as $property_name => $property_args ) {

				$property_annotations[ $property_name ] = new WP_Annotated_Property( $property_name, $property_args );

			}

			wp_cache_set( $cache_key, $property_annotations, 'custom-fields' );

		}

		return $property_annotations;

	}

	/**
	 * @return array
	 */
	function get_class_defaults() {

		$defaults = Custom_Fields::get_class_value( get_class( $this ), 'defaults' );

		return is_array( $defaults ) ? $defaults : array();

	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	function expand_args( $args ) {

		if ( count( $shortnames = $this->get_shortnames( $args ) ) ) {

			foreach ( $shortnames as $regex => $result ) {
				foreach ( $args as $name => $value ) {
					if ( preg_match( "#{$regex}#", $name, $matches ) ) {

						$args['_expanded_args'][ $name ] = $value;

						unset( $args[ $name ] );

						$new_name = $result;
						if ( 1 <= ( $top_index = count( $matches ) - 1 ) ) {
							for ( $i = 1; $i <= $top_index; $i ++ ) {
								$new_name = str_replace( '$' . $i, $matches[ $i ], $new_name );
							}
						}
						$args[ $new_name ] = $value;
					}
				}
			}

		}

		return $args;

	}

	/**
	 * Returns an array of shortname regexes as array key and expansion as key value.
	 *
	 * Subclasses should define 'shortnames' element in CLASS_VALUES() function array return value:
	 *
	 *    return array(
	 *      $regex1 => $shortname1,
	 *      $regex2 => $shortname2,
	 *      ...,
	 *      $regexN => $shortnameN,
	 *    );
	 *
	 * @example:
	 *
	 *  return array(
	 *    'shortnames'  =>  array(
	 *      '^label$'                     => 'view:label:label_text',
	 *      '^label:([^_]+)$'             => 'view:label:$1',
	 *      '^(input|element):([^_]+)$'   => 'view:input:element:$2',
	 *      '^(input:)?wrapper:([^_]+)$'  => 'view:input:wrapper:$2',
	 *      '^view_type$'                 => 'view:view_type',
	 *     ),
	 *  );
	 *
	 * @note   Multiple shortnames can be applied so order is important.
	 *
	 * @return array
	 */
	function get_shortnames( $args = array() ) {

		$class_vars = $this->get_class_vars( 'CLASS_VALUES' );

		$shortnames = ! empty( $class_vars['shortnames'] ) && is_array( $class_vars['shortnames'] )
				? $class_vars['shortnames']
				: array();

		return $shortnames;

	}

	/**
	 * @param string $class_var
	 *
	 * @return array
	 */
	function get_class_vars( $class_var ) {

		return Custom_Fields::get_class_vars( get_class( $this ), $class_var );

	}

	/**
	 * collect $args from delegate properties. Also store in $this->delegated_args array.
	 *
	 * @example
	 *
	 *  $input = array(
	 *    'field_name' => 'Foo',
	 *    'html:size' => 50,     // Will be split and "collect" like
	 *    'wrapper:size' => 25,
	 *  );
	 *  print_r( self::collect_args( $input ) );
	 *  // Outputs:
	 *  array(
	 *    'field_name' => 'Foo',
	 *    'html' => array( 'size' => 50 ),
	 *    'wrapper' => array( 'html:size' => 25 ),
	 *  );
	 *
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function collect_args( $args ) {

		$args = Custom_Fields::collect_args( $args, $this->get_property_prefixes() );

		return $args;

	}

	/**
	 * @return string[]
	 */
	function get_property_prefixes() {

		$cache_key = ( $class_name = get_class( $this ) ) . '::property_prefixes';

		if ( !( $property_prefixes = wp_cache_get( $cache_key, 'custom-fields' ) ) ) {

			$property_prefixes = array();

			$annotated_properties = $this->get_property_annotations( $class_name );

			foreach ( $annotated_properties as $field_name => $annotated_property ) {

				if ( $annotated_property->is_class() || $annotated_property->is_array() && ! empty( $annotated_property->prefix ) ) {

					$property_prefixes[ $field_name ] = $annotated_property->prefix;

				}

			}

			wp_cache_set( $cache_key, $property_prefixes, 'custom-fields' );

		}

		return $property_prefixes;

	}
	/**
	 * Assign the element values in the $args array to the properties of this object.
	 *
	 * @param array $args An array of name/value pairs that can be used to initialize an object's properties.
	 */
	function assign_args( $args ) {

		$class_name = get_class( $this );

		$args = array_merge( $this->get_defaulted_property_values(), $args );

		$args = $this->_sort_args_scaler_types_first( $args );

		/*
		 * Assign the arg values to properties, if they exist.
		 * If no property exists capture value in the $this->custom[] array.
		 */
		foreach ( $args as $name => $value ) {

			$property = $property_name = false;

			/**
			 * @var WP_Annotated_Property $property
			 */
			if ( '$' == $name[0] || preg_match( '#^_(expanded|collected)_args$#', $name ) ) {

				continue;

			} else if ( method_exists( $this, $method_name = "set_{$name}" ) ) {

				call_user_func( array( $this, $method_name ), $value );

			} else if ( $this->has_property_annotations( $name ) ) {

				$annotated_property = $this->get_annotated_property( $property_name = $name );

				if ( $annotated_property->auto_create ) {

					if ( $annotated_property->is_class() ) {

						$object_args = $this->extract_prefixed_args( $annotated_property->prefix, $args );

						$object_args['$value']    = $value;
						$object_args['$parent']   = $this;
						$object_args['$property'] = $annotated_property;

						$value = $annotated_property->make_object( $object_args );

					} else if ( $annotated_property->is_array() ) {

						if ( ! empty( $value ) ) {

							$parent_class_name = $annotated_property->array_of;

							if ( is_array( $annotated_property->keys )
							     && ! empty( $annotated_property->registry )
							     && Custom_Fields::registry_exists( $annotated_property->registry )
							) {

								foreach ( $annotated_property->keys as $key_name ) {

									$object_args = isset( $value[ $key_name ] ) ? $value[ $key_name ] : array();

									$object_args['$value']    = $key_name;
									$object_args['$parent']   = $this;
									$object_args['$property'] = $annotated_property;

									$class_name = Custom_Fields::get_registry_item( $annotated_property->registry, $key_name );

									if ( ! is_subclass_of( $class_name, $parent_class_name ) ) {

										$error_msg = __( 'ERROR: No registered class %s in registry %s.', 'custom-fields' );
										trigger_error( sprintf( $error_msg, $key_name, $annotated_property->registry ) );

									} else {

										$parameters = Custom_Fields::build_property_parameters( $class_name, $object_args );

										$value[ $key_name ] = call_user_func_array( array( $class_name, 'make_new' ), $parameters );

									}

								}

							}

						}

					}

				}

			} else if ( property_exists( $this, $name ) ) {

				$property_name = $name;

			} else if ( property_exists( $this, $non_public_name = "_{$name}" ) ) {

				$property_name = $non_public_name;

			} else {

				$this->custom_args[ $name ] = $value;

			}

			if ( $property_name ) {

				$this->{$property_name} = $value;

			}

		}

	}

	/**
	 * Return an array of annotated property names and their default values for the current class.
	 *
	 * @return array
	 */
	function get_defaulted_property_values() {

		$cache_key = get_class( $this ) . '::defaulted_property_values';

		if ( !( $property_values = wp_cache_get( $cache_key, 'custom-fields' ) ) ) {

			$property_values = array();

			foreach ( $this->get_property_annotations() as $class_name => $property ) {

				if ( ! $property->auto_create ) {
					continue;
				}

				$property_name = $property->property_name;

				if ( is_null( $property->default ) && isset( $this->{$property_name} ) ) {

					$default_value = $this->{$property_name};

				} else {

					if ( 'array' == $property->property_type && isset( $property->keys ) ) {

						$default_value = array_fill_keys( $property->keys, $property->default );

					} else {

						$default_value = $property->default;

					}

				}

				$property_values[ $property_name ] = $default_value;

			}

			wp_cache_set( $cache_key, $property_values, 'custom-fields' );

		}

		return $property_values;
	}


	/**
	 * @param string $property_name
	 *
	 * @return bool
	 */
	function has_property_annotations( $property_name ) {

		$properties = $this->get_property_annotations();

		return isset( $properties[ $property_name ] );

	}

	/**
	 * Gets array of properties field names that should not get a prefix.
	 *
	 * @param string $property_name
	 *
	 * @return WP_Annotated_Property|bool
	 */
	function get_annotated_property( $property_name ) {

		$annotated_properties = $this->get_property_annotations();

		return isset( $annotated_properties[ $property_name ] ) ? $annotated_properties[ $property_name ] : null;

	}

	/**
	 * @param string $prefix
	 * @param array $args
	 *
	 * @return mixed|array;
	 */
	function extract_prefixed_args( $prefix, $args ) {

		if ( ! $prefix || empty( $args[ $prefix ] ) || ! is_array( $prefixed_args = $args[ $prefix ] ) ) {

			$prefixed_args = array();

		}

		return $prefixed_args;

	}

	/**
	 *
	 */
	function initialize_class() {
		/**
		 * Initialize Class Property Annotations for the class of '$this.'
		 */
		$this->get_property_annotations();

	}

	/**
	 * @param string $const_name
	 * @param bool|string $class_name
	 *
	 * @return mixed
	 */
	function constant( $const_name, $class_name = false ) {

		if ( ! $class_name ) {

			$class_name = get_class( $this );

		}

		return Custom_Fields::constant( $class_name, $const_name );

	}


	/**
	 * @return array
	 */
	function get_properties() {

		return array_merge( $this->get_property_annotations(), get_object_vars( $this ) );

	}

	/**
	 * @param string $annotation_name
	 * @param string $property_name
	 *
	 * @return mixed
	 */
	function get_annotation_value( $annotation_name, $property_name ) {

		if ( $property = $this->get_annotated_property( $property_name ) ) {

			$value = $property->get_annotation_value( $annotation_name );

		} else {

			$value = null;

		}

		return $value;
	}

	/**
	 * @param string $annotation_name
	 * @param string $property_name
	 *
	 * @return mixed
	 */
	function get_annotation_custom( $annotation_name, $property_name ) {

		if ( $property = $this->get_annotated_property( $property_name ) ) {

			$value = $property->get_annotation_custom( $annotation_name );

		} else {

			$value = null;

		}

		return $value;
	}


	/**
	 * @param array $args
	 *
	 * @return array
	 */
	private function _sort_args_scaler_types_first( $args ) {

		uksort( $args, array( $this, '_scaler_types_first' ) );

		return $args;

	}

	/**
	 * @param string $field1
	 * @param string $field2
	 *
	 * @return int
	 */
	private function _scaler_types_first( $field1, $field2 ) {

		$sort       = 0;
		$has_field1 = $this->has_property_annotations( $field1 );
		$has_field2 = $this->has_property_annotations( $field2 );

		if ( $has_field1 && $has_field2 ) {

			$field1 = $this->get_annotated_property( $field1 );
			$field2 = $this->get_annotated_property( $field2 );

			if ( $field1->is_array() && $field2->is_class() ) {
				$sort = - 1;

			} else if ( $field1->is_class() && $field2->is_array() ) {
				$sort = + 1;

			}

		} else if ( $has_field1 ) {
			$sort = + 1;

		} else if ( $has_field2 ) {
			$sort = - 1;

		}

		return $sort;

	}

	/**
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return mixed
	 */
	function __call( $method_name, $args = array() ) {

		$result = false;

		/**
		 * @todo Move to the WP_View_Base class.
		 */
		if ( preg_match( '#^the_(.*)$#', $method_name, $match ) ) {

			$method_exists = false;

			if ( method_exists( $this, $method_name = $match[1] ) ) {

				$method_exists = true;

			} elseif ( method_exists( $this, $method_name = "{$method_name}_html" ) ) {

				$method_exists = true;

			} elseif ( method_exists( $this, $method_name = "get_{$method_name}" ) ) {

				$method_exists = true;

			} elseif ( method_exists( $this, $method_name = "get_{$match[1]}" ) ) {

				$method_exists = true;

			}

			if ( $method_exists ) {

				echo call_user_func_array( array( $this, $method_name ), $args );

				$result = true;

			}
		}

		return $result;

	}
}
