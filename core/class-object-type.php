<?php

/**
 * Class WP_Object_Type
 *
 * Object Type is an infrastructure class used to classify fields and forms with
 * other potential classification use cases in the future.
 *
 * Registered fields are often intended to be for specific post types and as such
 * post types need to be specified. However, Fields should not be specific to
 * post types as fields would be beneficial for users, comments, options, and more.
 *
 * So the Object Type was designed to capture and allow developers to specify both
 * the $class of object (i.e. 'post', 'user', 'comment', etc.) as well as the $subtype
 * specific to the class, (i.e. 'post', 'page', 'attachment', etc. for Object Types
 * of $class 'post.')
 *
 * Object Types literals are specified in string form with a colon separating $class
 * from $subtype, which looks like this:
 *
 *    'post:post'
 *    'post:page'
 *    'post:attachment'
 *    'post:my_post_type'
 *
 * Object can be comparied with $object_type where is_object($object_type) is
 * true (because of the Object Type's __toString() method.):
 *
 *    $object_type = new WP_Object_Type( 'post:my_post_type' );
 *
 *    if ( 'post:my_post_type' == $object_type ) {
 *       echo 'They *are* equal!'
 *    }
 *
 * The 'any' subtype will match any item of the specified $class, and if a trailing
 * colon is used and subtype is ommitted then it implies 'any'. Both of these are
 * equivalent:
 *
 *    'post:any'
 *    'post:'
 *
 * If the colon is ommitted from an Object Type string literal then the Object Type's
 * $class is assumed to be 'post'
 *
 *    $object_type = new WP_Object_Type( 'my_post_type' );
 *
 *    if ( 'post:my_post_type' == $object_type ) {
 *       echo 'This is equal too.'
 *    }
 *
 * To specify any other $subtype besides 'post' requires a colon. If there is no
 * $subtype then it requires a trailing colon:
 *
 *    'user:'
 *    'comment:'
 *    'option:'
 *    'site_option:'
 *
 * Object Types can be reused by using the assign() method:
 *
 *    $post_types = get_post_types( array( '_builtin' => false ) );
 *    $object_type = new WP_Object_Type()
 *    foreach( $post_types as $post_type ) {
 *        $object_type->assign( $post_type );
 *        // Do something with $object_type
 *    }
 *
 * It's also possible to instantiate an Object Type with an associative array:
 *
 *    $object_type = new WP_Object_Type( array(
 *      'class' => 'post',
 *      'subtype' => 'my_post_type',
 *    );
 *
 */
final class WP_Object_Type {

	/**
	 * List of Object Type $class values recognized by WordPress core
	 *
	 * The array keys are the type revelent to the Object Type's $class, and t
	 * the values array() is designed to contain are a list of $args that are
	 * relevant to the Object Type $class.
	 *
	 * At this time the array() $args is not used, but reserved for future use.
	 *
	 * @todo Complete this list.
	 *
	 * @var array
	 */
	protected static $_object_type_classes = array(
			'post'    => array(),
			'user'    => array(),
			'comment' => array(),
		  'option' => array(),
		  'site_option' => array(),
		  // @todo And more
	);

	/**
	 * The $class property is used to contain the class of object such as 'post',
	 * 'user', 'comment', 'option', etc.
	 *
	 * @var null|string
	 */
	var $class = null;

	/**
	 * The $subtype property is to contain the 'type' relevant to the Object Type's
	 * $class, i.e. for 'post' there is 'post', 'page', 'attachment' and whatever
	 * custom post types have been defined.
	 *
	 * For $class values of 'user' we are currently assuming role will used for $subtype.
	 *
	 * For all other $class values the value of $subtype is TBD.
	 *
	 * @var null|string
	 */
	var $subtype = null;

	/**
	 * Initialize an Object Type object with an optional Object Type literal string passed
	 * in to represent the object type:
	 *
	 * @example
	 *
	 *    $object_type = new WP_Object_Type( 'my_post_type' );
	 *
	 * An Object Type can effectively be cloned by passing an Object Type object instead
	 * of a literal, i.e.
	 *
	 *    $object_type = new WP_Object_Type( 'my_post_type' );
	 *    $2nd_object_type = new WP_Object_Type( $object_type );
	 *
	 *    if ( (string)$object_type == (string)$2nd_object_type ) {
	 *       echo 'This is equal.'
	 *    }
	 *    if ( 'post:my_post_type' == $object_type ) {
	 *       echo 'And this is equal.'
	 *    }
	 *    if ( 'post:my_post_type' == $2nd_object_type ) {
	 *       echo 'And this is also equal.'
	 *    }
	 *    if ( $object_type === $2nd_object_type ) {
	 *       echo 'But this is NOT equal.'
	 *    }
	 *
	 * Passing in an object is useful in functions that might be called directly by
	 * a developer with an object literal but might be also called indirectly where
	 * the Object Type literal string had already been replaced with its object
	 * equivalent.
	 *
	 * It's also possible to instantiate an Object Type with an associative array:
	 *
	 *    $object_type = new WP_Object_Type( array(
	 *      'class' => 'post',
	 *      'subtype' => 'my_post_type',
	 *    );
	 *
	 * @param bool|string|array|object $object_type
	 */
	function __construct( $object_type = false ) {

		if ( $object_type ) {

			$this->assign( $object_type );

		}

	}

	/**
	 * Validates and assigns a value to this Object Type
	 *
	 * @example:
	 *
	 *    $object_type = new WP_Object_Type();
	 *    $object_type->assign( 'my_post_type' )
	 *
	 * Which is equivalent to:
	 *
	 *    $object_type = new WP_Object_Type( 'my_post_type' )
	 *
	 * @param bool|string|array|WP_Object_Type $object_type
	 */
	function assign( $object_type = false ) {

		if ( empty( $object_type ) ) {
			/**
			 * If an empty Object Type is passed assume it's of $class == 'post' and
			 * $subtype == $post->post_type.
			 */
			global $post;

			$object_type = self::get_post_object_type_literal( $post->post_type );

		}

		if ( is_a( $object_type, __CLASS__ ) ) {
			/**
			 * If a WP_Object_Type object was passed in then copy it's values.
			 *
			 * @see The PHPDoc for __construct() to understand why accepting an
			 *      object in addition to a string literal is useful.
			 */
			$this->class   = $object_type->class;
			$this->subtype = $class->subtype;

		} else {

			if ( is_string( $object_type ) ) {
				/**
				 * When an Object Type string literal is passed...
				 */
				if ( false === strpos( $object_type, ':' ) ) {

					/**
					 * And the literal contains no colon, assume the $class is 'post'
					 * and the string literal passed in is the $subtype (often used
					 * with custom post types.)
					 */
					$this->class   = 'post';
					$this->subtype = $object_type;

				} else {

					/**
					 * Otherwise split the Object Type literal on a the colon and assign
					 * to $class and $subtype, respectively.
					 */
					list( $this->class, $this->subtype ) = explode( ':', $object_type );

				}

			} else if ( is_array( $object_type) && 2 == count( $object_type ) && isset( $object_type[0] ) && isset( $object_type[1] ) ) {

				/**
				 * A 2 element numerically indexed array where the first element is $class and the 2nd is $subtype.
				 * So assign it.
				 */

				list( $this->class, $this->subtype ) = $object_type;

				/*
				 * Initialize the class property, defaulting to 'post' if empty.
				 */
				if ( empty( $this->class ) ) {

					$this->class = 'post';

				}

			} else {

				/*
				 * Assumes the $object_type passed in is either an associative array with 'class'
				 * and 'subtype' properties or an object that is not of class WP_Object_Type with
				 * $class and $subtype properties
				 *
				 * Not sure why an object not of type WP_Object_Type would ever be needed, but if
				 * someone finds a need for it this this method will support initialized from its
				 * property values.
				 */
				if ( ! is_array( $object_type ) ) {
					/*
					 * Convert the array to object so the same code can initialize if an array or an
					 * object is passed in.
					 */
					$object_type = (object)$object_type;
				}

				/*
				 * Initialize the class property, defaulting to 'post' if empty.
				 */
				$this->class = empty( $object_type->class ) ? $object_type->class : 'post';

				/*
				 * Initialize the subtype property, defaulting to false if empty.
				 */
				$this->subtype = empty( $object_type->subtype ) ? $object_type->subtype : false;
			}

			/**
			 *  Ensure $class is sanitized to be a valid identifier
			 */
			$this->class = Custom_Fields::sanitize_identifier( $this->class );

			if ( $this->subtype ) {
				/**
				 *  Ensure $subtype is sanitized to be a valid identifier too, but only need to do if not empty.
				 */
				$this->subtype = Custom_Fields::sanitize_identifier( $this->subtype );

			}
		}

		if ( empty( $this->subtype ) ) {
			/**
			 * Lastly, if $subtype is still empty, set to 'any'.
			 */
			$this->subtype = 'any';

		}

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
	 * @return bool Whether the object type $class was registered. \
	 */
	public static function register_class( $class, $class_args = array() ) {

		if ( ! isset( self::$_object_type_classes[ $class ] ) ) {

			self::$_object_type_classes[ $class ] = $class_args;

			return true;

		}

		return false;

	}

	/**
	 * Get an unqualified type string for generating simplified output when context is not needed.
	 *
	 * Gets the $subtype unless $class is 'any' or empty.
	 *
	 * @return string
	 */
	function unqualified_type() {

		return empty( $this->subtype ) || 'any' == $this->subtype ? $this->class : $this->subtype;

	}

	/**
	 * Check if the current Object Type is valid.
	 *
	 * Validity is determined by having a non-empty $class value.
	 *
	 * @return bool Is the Object Type valid?
	 */
	public function is_valid() {

		return ! empty( $this->class );

	}

	/**
	 * Check if the current Object Type is equivalent to the one passed in.
	 *
	 * Equivalency is true if both objects have the same values for their $class and $subtype properties.
	 *
	 * If not parameter is passed then this method assume an object type based on the global $post object.
	 *
	 * @param false|WP_Object_Type|string $object_type The Object Type to compare with $this.
	 *
	 * @return bool If $object_type is equivalent to $this.
	 */
	public function is_equivalent( $object_type = false ) {

		if ( ! is_a( $object_type, __CLASS__ ) ) {
			/*
			 * First check to see if the passed in parameter is a WP_Object_Type object.
			 * If not, instantiate a new object with the passed $arg.
			 */
			$object_type = new self( $object_type );

		}

		/**
		 * Check for object equivalency
		 * Yes this is correct (if you thought it was not, like I did at first.)
		 */
		return $this == $object_type;

	}

	/**
	 * Returns an Object Type literal string given a post type
	 *
	 * @param string $post_type Post type to generate an Object Type literal string.
	 *
	 * @return string An Object Type literal.
	 *
	 * @todo Should $post_type be validated to exist?
	 *
	 */
	static function get_post_object_type_literal( $post_type ) {

		return $post_type ? "post:{$post_type}" : 'post:any';

	}

	/**
	 * Returns an Object Type given a post type
	 *
	 * @param string $post_type Post type to generate an Object Type.
	 *
	 * @return string An Object Type.
	 *
	 * @todo Should $post_type be validated to exist?
	 *
	 */
	static function get_post_object_type( $post_type ) {

		return new self( self::get_post_object_type_literal( $post_type ) );

	}

	/**
	 * Magic method to convert the Object Type into it's string literal form.
	 *
	 * @return string  An Object Type literal representing $this, the current Object Type.
	 */
	function __toString() {

		return "{$this->class}:{$this->subtype}";

	}

}
