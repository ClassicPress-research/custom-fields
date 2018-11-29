<?php
namespace Custom_Fields\Unit_Tests;

class WPMetadata_UnitTestCase extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function clean_up_global_scope() {
		parent::clean_up_global_scope();
	}

	public function assertPreConditions() {
		parent::assertPreConditions();
	}

	public function go_to( $url ) {
		$GLOBALS['_SERVER']['REQUEST_URI'] = $url = str_replace( network_home_url(), '', $url );

		$_GET = $_POST = array();

		foreach ( array( 'query_string', 'id', 'postdata', 'authordata', 'day', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages', 'pagenow' ) as $v ) {
			if ( isset( $GLOBALS[ $v ] ) ) unset( $GLOBALS[ $v ] );
		}

		$parts = parse_url($url);

		if ( isset( $parts['scheme'] ) ) {
			$req = $parts['path'];
			if ( isset( $parts['query'] ) ) {
				$req .= '?' . $parts['query'];
				parse_str( $parts['query'], $_GET );
			}
		} else {
			$req = $url;
		}

		if ( ! isset( $parts['query'] ) ) {
			$parts['query'] = '';
		}

		// Scheme
		if ( 0 === strpos( $req, '/wp-admin' ) && force_ssl_admin() ) {
			$_SERVER['HTTPS'] = 'on';
		} else {
			unset( $_SERVER['HTTPS'] );
		}

		$_SERVER['REQUEST_URI'] = $req;
		unset($_SERVER['PATH_INFO']);

		$this->flush_cache();

		unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);

		$GLOBALS['wp_the_query'] = new WP_Query();
		$GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];
		$GLOBALS['wp'] = new WP();

		foreach ( $GLOBALS['wp']->public_query_vars as $v ) {
			unset( $GLOBALS[ $v ] );
		}
		foreach ( $GLOBALS['wp']->private_query_vars as $v ) {
			unset( $GLOBALS[ $v ] );
		}

		$GLOBALS['wp']->main( $parts['query'] );
	}

	public function set_current_user( $user_id ) {
		wp_set_current_user( $user_id );
	}

	public function getReflectionPropertyValue( $class, $property )	{
		$reflection = new \ReflectionProperty( $class, $property );
		$reflection->setAccessible( true );
		return $reflection->getValue( $class );
	}

	public function setReflectionPropertyValue( $class, $property, $value )	{
		$reflection = new \ReflectionProperty( $class, $property );
		$reflection->setAccessible( true );
		return $reflection->setValue( $class, $value );
	}

	public function reflectionMethodInvoke( $class, $method ) {
		$reflection = new \ReflectionMethod( $class, $method );
		$reflection->setAccessible( true );
		return $reflection->invoke( $class );
	}

	public function reflectionMethodInvokeArgs( $class, $method, $args ) {
		$reflection = new \ReflectionMethod( $class, $method );
		$reflection->setAccessible( true );
		return $reflection->invokeArgs( $class, $args );
	}
}