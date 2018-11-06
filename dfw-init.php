<?php
/**
 * Initialize Super Cool Ad Manager Plugin
 */

/**
 * Global instances for DoubleClick object.
 *
 * This instantiates the plugin.
 */
$doubleclick = new DoubleClick();


/**
 * A wrapper for wp_loaded
 *
 * This means that if the plugin is not installed,
 * the setup function will not run and throw an error.
 */
function dfw_add_action() {

	/**
	 * Use this action to setup
	 * breakpoints and network tracking code
	 * in your theme's functions.php.
	 *
	 * @since v0.1
	 */
	do_action( 'dfw_setup' );
}
add_action( 'wp_loaded', 'dfw_add_action' );
