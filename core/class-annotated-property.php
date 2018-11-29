<?php

/**
 * Class WP_Annotated_Property
 *
 * Class that enabledsuses of 'annotations' of class properties similar to Annotations in Java
 *
 * @see: http://tutorials.jenkov.com/java/annotations.html
 *
 * Annotations are useful because they allow programmers to develop generic logic that can
 * process a collection of classes using the data in the annotation with one set of generic
 * code instead of having to hardcode logic for each unique type of class. In the example shown
 * above you can see 'type' which allows WP_Field_Base's parent::_construct() method to auto
 * create instances for the properties that have been annotated with a class for 'type', assuming
 * that the 'auto_create' annotation has not been set to false.
 *
 * This class is used to represent the 'annotations' for a single property on another class.
 * Any PHP class can define annotations for itself by declaring a PROPERTIES() static function
 * that returns an array of arrays where the keys of the array are the names of the properties as
 * declared in the class.
 *
 * @example From the WP_Field_Base class:
 *
 *		static function PROPERTIES() {
 *
 *			return array(
 *				'value'   => array( 'type' => 'mixed' ),
 *				'form'    => array( 'type' => 'WP_Form', 'auto_create' => false ),
 *				'storage' => array( 'type' => 'WP_Storage_Base', 'default' => 'meta' ),
 *				'view'    => array( 'type' => 'WP_Field_View_Base' ),
 *			);
 *
 *		}
 *
 * Properties in a class are not required to be annotated but if they are then the literal
 * form of an annotation typed by a developer will an $args array similar in format to the $args
 * array passed to register_post_type().
 *
 * The $args array will be passed the 2nd parameter of WP_Annotated_Property->__contruct(), the
 * first parameter being the name of the property as defined in the class (although you rarely
 * if ever need to instantiate yourself; it'll be done automatically fot you):
 *
 *     $name = 'form';
 *     $args = array( 'type' => 'WP_Form', 'auto_create' => false ),
 *     $property = new WP_Annotated_Property( $name, $form );
 *
 * The main use of this class within Custom_Fields is in ::get_property_annotations( $class_name )
 * where each sub-array of 'annotation' arrays is passed into an object of type
 * WP_Annotated_Property. The array if 'annotation' arrays is returned by the PROPERTIES() method.
 *
 */
final class WP_Annotated_Property {

	/**
	 * @var array List of default annotations by class.
	 *
	 * For example a default annotation of WP_Html_Class->html_tag is 'div' and is specified like so:
	 *
	 * 		Custom_Fields::register_default_annotations( 'WP_Html_Element', array(
	 *			'html_tag' => 'div'
	 *    ) );
	 *
	 * Modified by self::register_default_annotations()
	 * Accessed by self::get_default_annotations()
	 *
	 */
	static $_default_annotations = array();

	/**
	 * @var string The name of the property for the associated class for which the annotations apply.
	 */
	var $property_name;

	/**
	 * @var string The "Type" of the property for the associated class for which the annotations apply.
	 *
	 * Types are class names, arrays of class names (denoted as "Class_Name[]"), scalar types (i.e. 'int',
	 * 'string', etc.) or arrays of scalar types, i.e 'int[]', 'string[]', etc.
	 * @example 'Class_Name', 'Class_Name[]', 'int', 'string[]', etc.
	 */
	var $property_type;

	/**
	 * @var string Value to capture the "Type" of array eleemnts when a 'type' value passed in as an
	 *             $arg to __construct()contains trailing open/close square brackets ('[]').
	 *
	 * If 'WP_Html_Element[]' is passed to __construct() then $array_of will get' WP_Html_Element'
	 * and $property_type will get set to 'array'.
	 *
	 * @example 'Class_Name', 'int', etc.
	 */
	var $array_of = null;

	/**
	 * @var mixed Value to assign the property when no value is passed to __construct() for the property.
	 */
	var $default;

	/**
	 * @var array Allows for overriding the 'parameters' in CLASS_VALUES() in the case it is needed.
	 *
	 * @todo Need to find a use-case before implementing this.
	 */
	var $parameters = array();

	/**
	 * @var string The prefix used by the declaring class for properties of a contained class.
	 *
	 * For example, the WP_Field_Input_Feature class has an $element property designed to contain an
	 * WP_Html_Element object thus its prefix is 'element' and that allows the Input Feature class
	 * to be instantiated with an $arg like 'element:size' and thus pass the value of 'size' to the
	 * __construct() method of WP_Html_Element when instantiating.
	 *
	 * @example
	 *
	 *    'form', 'field', 'storage', etc.
	 */
	var $prefix;

	/**
	 * @var bool When true the __construct() method for the Custom_Fields_Base class -- which this
	 *           class extends from -- will attempt to instantiate and assign any property defined
	 *           have it's type be a class or array of classes.  If false, the assumption is made
	 *           the the property will be assigned manually in code somewhere.
	 */
	var $auto_create = true;

	/**
	 * @var array An array to contain any 'custom' values passed in that might be needed beyond those
	 *            that are predefined in this class.
	 *
	 * To limit complexity we decided this class would declared 'final' and thus mostly just be used
	 *            as as a repository of data and not be able to be subclassed. This $custom is a
	 *            properly that allows some extensibility by allow any data to be added to properties
	 *            but in a way that won't impeded future enhancement to this class.
	 */
	var $custom = array();

	/**
	 * @var string Contains the $name of a registry that is passed to the __construct() method for
	 *             WP_Registry. It will refer to a registry that contains the keys for an array
	 *             property.
	 *
	 * For example, the 'field_feature_type' registry contains the applicable keys for the $features
	 *             array of the WP_Field_View_Base class. This allows Custom_Fields_Base to know
	 *             how to initialize those properties.
	 *
	 * @example
	 *
	 *    'field_type', 'storage_type', 'field_feature_type', etc.
	 */
	var $registry;

	/**
	 * @var array Contains the list of valid keys for an parperty defined as an array.
	 *
	 * For example, the WP_Field_View_Base class declares the keys 'label', 'input', 'help', 'message'
	 * and 'infobox' for the property $features.
	 *
	 */
	var $keys = array();

	/**
	 * Instantiate an Annotated Property object.
	 *
	 *
	 *
	 * @param string $property_name Name of the associated property of the contained class
	 * @param mixed[] $annotations List of name/value pair annotations for the property.
	 */
	function __construct( $property_name, $annotations ) {

		/**
		 * Capture the property name first.
		 */
		$this->property_name = $property_name;

		/**
		 * Now determine the Type of the property
		 */
		if ( empty( $annotations['type'] ) ) {
			/*
			 * Default to 'mixed' if not declared.
			 */
			$this->property_type = 'mixed';
		} else if ( preg_match( '#(.+)\[\]$#', $annotations['type'], $match ) ) {
			/*
			 * In case of array, designed by trailing brackets e.g. '[]',
			 * set type = 'array' and 'array_of' to the type specified.
			 */
			$this->property_type = 'array';
			$this->array_of      = $match[1];
		} else {
			/*
			 * Otherwuise set $property_type to what was specified in the 'type' annotation.
			 */
			$this->property_type = $annotations['type'];
		}
		/**
		 * Now unset this because we no longer need it and don't want to add to the $custom array.
		 */
		unset( $annotations['type'] );

		if ( class_exists( $this->property_type ) || ( $this->is_array() && class_exists( $this->array_of ) ) ) {
			/*
			 * If a class was specified either singularly or as an array of...
			 */
			if ( ! isset( $annotations['prefix'] ) ) {

				/*
				 *  Default 'prefix' to property name, for convenience.
				 */
				$this->prefix = $property_name;
			}

			/*
			 *  Now merge in the default annotations that were registered for the type
			 *  using register_default_annotations() method of this class.
			 *
			 * For example WH_Html_Element has a defailt annotation for 'html_tag' set to 'div':
			 *
			 *			self::register_default_annotations( 'WP_Html_Element', array(
			 *					'html_tag' => 'div'
			 *			));
			 *
			 */
			$annotations = array_merge(
					self::get_default_annotations( $this->property_type ),
					$annotations
			);

		}

		foreach ( $annotations as $arg_name => $arg_value ) {
			/*
			 * Now assign the remaining annotations to either...
			 */
			if ( property_exists( $this, $arg_name ) && 'custom' != $arg_name ) {

				/*
				 * ... the named property (unless it is 'custom'), or...
				 */
				$this->$arg_name = $arg_value;

			} else {

				/*
				 *  ... an array element of the $custom property.
				 */
				$this->custom[ $arg_name ] = $arg_value;

			}
		}

	}

	/**
	 * Access the value for a known annotation contained in $this WP_Annotated_Property object.
	 *
	 * First checks to see if $annotation_name is a property and if so, returns the property's value.
	 * Next checks to see if $annotation_name prefixed with 'property_' is a property and if so, returns the property's value.
	 * Next checks to see if removing a 'property_' prefix, if one exists, is a property and if so, returns the property's value.
	 * If none of these check out to be true, the function returns null.
	 *
	 * @param string $annotation_name Name of the annotation for which to return it's value.
	 *
	 * @return mixed|null The value of the annotation named as $annotation_name.
	 */
	function get_annotation_value( $annotation_name ) {

		if ( property_exists( $this, $annotation_name ) ) {
			/*
			 * Check to see if $annotation_name is a property and if so, returns the property's value.
			 */
			$annotation = $this->{$annotation_name};

		} else if ( property_exists( $this, $long_name = "property_{$annotation_name}" ) ) {
			/*
			 * Check to see if $annotation_name prefixed with 'property_' is a property and if so, returns the property's value.
			 */
			$annotation = $this->{$long_name};

		} else if ( property_exists( $this, $short_name = preg_replace( '#^property_(.+)$#', '$1', $annotation_name ) ) ) {
			/*
			 * Check to see if removing a 'property_' prefix, if one exists, is a property and if so, returns the property's value.
			 */
			$annotation = $this->{$short_name};

		} else {
			/*
			 * If none of these check out to be true, the function returns null.
			 */
			$annotation = null;

		}

		return $annotation;

	}

	/**
	 * Access the value for a custom annotation contained in $this WP_Annotated_Property object.
	 *
	 * Checks to see if such a custom property exists and if not returns null
	 *
	 * @param string $annotation_name Name of the annotation for which to return it's value.
	 *
	 * @return mixed|null The value of the custom annotation named as $annotation_name.
	 */
	function get_annotation_custom( $annotation_name ) {

		if ( isset( $this->custom[ $annotation_name ] ) ) {
			/**
			 * Check to see if such a custom property exists.
			 * If yes, return its value.
			 */
			$annotation = $this->custom[ $annotation_name ];

		} else {
			/**
			 * If no, return null.
			 */
			$annotation = null;

		}

		return $annotation;

	}

	/**
	 * Generic factory method to instantiate a new object as defined by the class name in $property_type.
	 *
	 * Calls the make_new() static factory method declared for the class in $property_type, if it exists.
	 *
	 * Assuming the make_new() method expects parameters the $class named in $property_type should have a
	 * CLASS_VALUES() method that returns an array with an element 'parameters' that itself is a simple
	 * array who values specify indicators for the args required, list in the same order that make_new()
	 * expects them.
	 *
	 * For example, the static factory method for WP_Field_Base has the following signature
	 *
	 *    static function make_new( $field_name, $object_type, $field_args = array() );
	 *
	 * And the class annotations for WP_Field_Base::CLASS_VALUES() contain a 'parameters' element like this:
	 *
	 *   	static function CLASS_VALUES() {
	 *
	 *		  return array(
	 *			  	'...' => array( ... ),
	 *				  'parameters' => array(
	 *					  	'$value',
	 *						  'object_type',
	 *  						'$args',
	 *	  			),
	 *		  	'...' => array( ... ),
	 * 		  );
	 *   }
	 *
	 * Thus:
	 *
	 *      - $field_name  gets the value of $args[$this->property_name] passed in from register_post_field() per '$value'
	 *      - $object_type gets the property value for $this->object_type per 'object_type'.
	 *      - $field_args  gets the $args array passed in from register_post_field() per '$args'.
	 *
	 * @see WP_Annotated_Property::build_parameters()
	 *
	 * @param array $object_args The context and other values needed to pass to the make_new() method.
	 *
	 * @return object|null The object instantiated by this factory, or null if object could not be instantiated.
	 *
	 */
	function make_object( $object_args ) {

		if ( $this->is_class() && method_exists( $this->property_type, 'make_new' ) ) {

			/*
			 * If this property represents an object class with a static method named make_new()
			 * then build the parameters needed to pass to make_new().
			 */
			$parameters = self::build_parameters( $this->property_type, $object_args );

			/*
			 * Call make_new() with the built parameters to generate an instance of the class named in $property_type.
			 */
			$object = call_user_func_array( array( $this->property_type, 'make_new' ), $parameters );

		} else {

			/*
			 * Property does not declare a class or the class has no make_new() method.
			 */
			$object = null;

		}

		return $object;

	}

	/**
	 * Test to see if the value in $property_type is a valid class name.
	 *
	 * @return bool
	 */
	function is_class() {

		return class_exists( $this->property_type );

	}

	/**
	 * Test to see if the value in $property_type represents an array.
	 *
	 * @return bool
	 */
	function is_array() {

		return 'array' == $this->property_type;

	}

	/**
	 * Register the default annotations for a 'type'.
	 *
	 * In the case of type=class_name, the registered default annotations apply to it's child classes.
	 *
	 * @param string $type Class name or other data type for which to register default annotations.
	 *
	 * @param array $default_values The array of default annotations to register.
	 */
	static function register_default_annotations( $type, $default_values ) {

		/*
		 * Store in an array by 'type', and provide logic to determine if the parent class
		 * annotations have been merged in yet or not via the 'cached' property.
		 */
		self::$_default_annotations[ $type ] = (object) array(
				'cached' => false,
				'values' => $default_values,
		);

	}

	/**
	 * Get default annotations for a 'type'
	 *
	 * If the first time accessed for a type, this function accesses annotations for all parent classes
	 * too and merges them in, finally caching them so the initial access need only be done one.
	 *
	 * @param string $type  Class name or other data type for which to get default annotations.
	 *
	 * @return array List of default annotations for the 'type' specified.
	 */
	static function get_default_annotations( $type ) {

		if ( ! isset( self::$_default_annotations[ $type ] ) ) {

			/*
			 * If the first time for this class, initialize a structure to contain the default values.
			 */
			self::$_default_annotations[ $type ] = (object) array(
					'cached' => false,
					'values' => array(),
			);

		}

		if ( ! self::$_default_annotations[ $type ]->cached ) {
			/*
			 * If default annotations not previouslty cached for this type,
			 * look at ancestor classes and merge in their default annotations
			 * too.
			 */

			if ( $parent = get_parent_class( $type ) ) {
				/**
				 * Annotation values for the same name from child classes override
				 * the value defined in a parent class.
				 */
				self::$_default_annotations[ $type ]->values = array_merge(
						self::get_default_annotations( $parent ),
						self::$_default_annotations[ $type ]->values
				);

			}

			/**
			 * Set 'cached' to indicate we don't need to do this again.
			 */
			self::$_default_annotations[ $type ]->cached = true;

		}

		/**
		 * Finally return the newly or previously cached defaul annotation values.
		 */
		return self::$_default_annotations[ $type ]->values;

	}

	/**
	 * Build the parameters needed for a classes' static make_new() object factory.
	 *
	 * @param string $class_name The name of the class to instantiate
	 * @param array $object_args The context values and $args passed (indirectly) to instantiate objects.
	 *
	 * @return array The array of parameters to pass to the class's static make_new() method.
	 */
	static function build_parameters( $class_name, $object_args ) {

		/*
		 * Start with an empty set of arguments.
		 */
		$parameters = array();

		/*
		 * Initialize a variable to keep track of the $args position in the array.
		 */
		$args_index = false;

		/*
		 * @todo Change all these to be stored in a '$context' array instead of stored inline.
		 *
		 * Get the 'make_new' parameter template for the named $class_name. Will return an array that may
		 * contain any of the following:
		 *
		 *  '$value' -    If included will refer to $object_args['$value'] set to be the value passed
		 *                in for the property. Typically this will be a registered name used to look
		 *                up more information in the registry.
		 *
		 *  '$parent' -   If included will refer to the parent object of the object that is calling make_new()
		 *                and its meaning is defined by whichever class uses it.
		 *
		 *  '$args' -     If included will refer to the entire list of $object_args elements minus any
		 *                ${context} args.
		 *
		 *  '$property' - If included will refer to the instance of this WP_Annotated_Property class that
		 *                will provide access to the custom
		 *
		 *  '{name}' -    If included then 'name' will refer to the value of $object_args['name'], if such an
		 *                element exists. Examples include 'object_type', 'view_type', etc.
		 */
		$make_new_parameters = Custom_Fields::get_make_new_parameters( $class_name );


		/*
		 * For each of the make_new() parameters needed...
		 */
		foreach ( $make_new_parameters as $parameter_name ) {

			if ( preg_match( '#^(\$value|\$parent)$#', $parameter_name ) ) {
				/*
				 * If $parameter_name is either '$value' or '$parent',
				 * just add one's value to $parameters array.
				 */
				$parameters[] = $object_args[ $parameter_name ];

			} else if ( '$args' == $parameter_name ) {
				/*
				 * If $parameter_name is '$args' then capture it's index in
				 * the $parameters array and then pass in all the $args.
				 */
				$args_index   = count( $parameters );
				$parameters[] = $object_args;

			} else if ( is_null( $parameter_name ) || is_bool( $parameter_name ) ) {
				/*
				 * If $parameter_name is null, false or true the class wants to skip
				 * that parameter, so just set as is.
				 */
				$parameters[] = $parameter_name;

			} else if ( isset( $object_args[ $parameter_name ] ) ) {
				/*
				 * If $parameter_name is a key for an element of the $object_args then
				 * use that element's value for the parameter.
				 */
				$parameters[] = $object_args[ $parameter_name ];

			} else if ( isset( $object_args['$property'] ) ) {

				/*
				 * Assume that there will be a default value for this $property_name
				 * in the 'custom' collection of the Annotated Property for the property
				 * that will get assigned theyet-to-be-built instance we are building
				 * the parameters for.
				 */
				$property_args = $object_args['$property']->custom;

				if ( isset( $property_args[ $parameter_name ] ) ) {
					/*
					 * If we assumed correctly and their was a matching 'custom' name
					 * in the Annotated Property then use it's value for the parameter
					 */

					$parameters[] = $property_args[ $parameter_name ];

				} else {

					/**
					 * Otherwise generate an error message to aid in debugging.
					 */
					$message = __( 'Unknown parameter %s for %s::make_new().', 'custom-fields' );
					trigger_error( sprintf( $message, $parameter_name, $class_name ) );

				}

			}

		}

		if ( $args_index ) {
			/*
			 * If $args are used in the $parmeters array
			 * Then remove every one of the ${context} variables.
			 */
			foreach ( array_keys( $parameters[ $args_index ] ) as $key_name ) {
				if ( '$' == $key_name[0] ) {
					unset( $parameters[ $args_index ][ $key_name ] );
				}
			}

		}

		/**
		 * return the built set of parameters.
		 */
		return $parameters;

	}

}
