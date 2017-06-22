<?php
/**
 * DoubleClick_For_WordPress.
 *
 * @since   0.2.1
 * @package DoubleClick_For_WordPress
 */
class DoubleClick_For_WordPress_Test extends WP_UnitTestCase {

	/**
	 * Test if our class exists.
	 *
	 * @since  0.2.1
	 */
	function test_class_exists() {
		$this->assertTrue( class_exists( 'DoubleClick_For_WordPress') );
	}

	/**
	 * Test that our main helper function is an instance of our class.
	 *
	 * @since  0.2.1
	 */
	function test_get_instance() {
		$this->assertInstanceOf(  'DoubleClick_For_WordPress', doubleclick_for_wordpress() );
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
