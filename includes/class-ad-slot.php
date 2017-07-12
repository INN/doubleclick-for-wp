<?php
/**
 * DoubleClick for WordPress Ad Slot.
 *
 * @since   0.2.1
 * @package DoubleClick_For_WordPress
 */

/**
 * DoubleClick for WordPress Ad Slot.
 *
 * @since 0.2.1
 */
class DCWP_Ad_Slot {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.2.1
	 *
	 * @var   DoubleClick_For_WordPress
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.2.1
	 *
	 * @param  DoubleClick_For_WordPress $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.2.1
	 */
	public function hooks() {

	}
}
