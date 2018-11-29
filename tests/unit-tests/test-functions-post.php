<?php
namespace Custom_Fields\Unit_Tests;

require(CODEBASE . '/functions/post.php');

/**
 * Test post functions
 *
 * @package    Custom_Fields\UnitTests
 * @version    version
 * @filesource tests/unit-tests/test-post-functions.php
 * @since      version
 */
class testFunctionPost extends WPMetadata_UnitTestCase {
	/**
	 * The test set up
	 */
	public function setUp() {
		//This is run at the beginning of each individual test
	}

	/**
	 * The test tear down
	 */
	public function tearDown() {
		//This is run at the end of each individual test
	}

	/**
	 * Test the register_post_field function
	 *
	 */
	public function testRegisterPostField() {
		$this->assertTrue(function_exists('register_post_field'), 'Function register_post_field does not exist');
	}
}
