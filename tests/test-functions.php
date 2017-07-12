<?php
/**
 * DoubleClick for WordPress Functions Tests.
 *
 * @since   0.2.1
 * @package DoubleClick_For_WordPress
 */
class DCWP_Functions_Test extends WP_UnitTestCase {

	/**
	 * Test if our class exists.
	 *
	 * @since  0.2.1
	 */
	function test_class_exists() {
		$this->assertTrue( class_exists( 'DCWP_Functions' ) );
	}

	/**
	 * Test that we can access our class through our helper function.
	 *
	 * @since  0.2.1
	 */
	function test_class_access() {
		$this->assertInstanceOf( 'DCWP_Functions', doubleclick_for_wordpress()->functions );
	}

	/**
	 * Replace this with some actual testing code.
	 *
	 * @since  0.2.1
	 */
	function test_sample() {
		$this->assertTrue( true );
	}
}
