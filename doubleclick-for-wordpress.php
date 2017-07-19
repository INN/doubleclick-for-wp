<?php
/**
 * Plugin Name: DoubleClick for WordPress
 * Plugin URI:  https://labs.inn.org
 * Description: Serve DoubleClick ads natively in WordPress. Built to make serving and targeting responsive ads easy.
 * Version:     0.2.1
 * Author:      innlabs, willhaynes24
 * Author URI:  https://labs.inn.org
 * Donate link: https://labs.inn.org
 * License:     GPLv2
 * Text Domain: dfw
 * Domain Path: /languages
 *
 * @link    https://labs.inn.org
 *
 * @package DoubleClick_For_WordPress
 * @version 0.2.1
 *
 * Built using generator-plugin-wp (https://github.com/WebDevStudios/generator-plugin-wp)
 */

/**
 * Copyright (c) 2017 innlabs, willhaynes24 (email : labs@inn.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Main initiation class.
 *
 * @since  0.2.1
 */
final class DoubleClick_For_WordPress {

	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	const VERSION = '0.3.0';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	protected $basename = '';

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  0.2.1
	 */
	protected $activation_errors = array();

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    DoubleClick_For_WordPress
	 * @since  0.2.1
	 */
	protected static $single_instance = null;

	/**
	 * Instance of DCWP_Options
	 *
	 * @since0.2.1
	 * @var DCWP_Options
	 */
	protected $options;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.2.1
	 * @return  DoubleClick_For_WordPress A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.2.1
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.2.1
	 */
	public function plugin_classes() {
		global $doubleclick;
		$this->options = new DCWP_Options( $this );
		$this->widget = new DCWP_Widget( $this );
		$doubleclick = new DCWP_DoubleClick( $this );
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  0.2.1
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
		do_action( 'dfw_setup' );
	}

	/**
	 * Activate the plugin.
	 *
	 * @since  0.2.1
	 */
	public function _activate() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  0.2.1
	 */
	public function _deactivate() {
		// Add deactivation cleanup functionality here.
	}

	/**
	 * Init hooks
	 *
	 * @since  0.2.1
	 */
	public function init() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Load translated strings for plugin.
		load_plugin_textdomain( 'dfw', false, dirname( $this->basename ) . '/languages/' );

		// Run the updates if needed.
		$this->update_options();

		// Load the includes.
		$this->load_files();

		// Initialize plugin classes.
		$this->plugin_classes();

		// Add a settings link to the plugins page.
		add_filter( 'plugin_action_links_' . $this->basename, array( 'DCWP_Options', 'add_settings_link' ) );

	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.2.1
	 *
	 * @return boolean True if requirements met, false if not.
	 */
	public function check_requirements() {

		// Bail early if plugin meets requirements.
		if ( $this->meets_requirements() ) {
			return true;
		}

		// Add a dashboard notice.
		add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

		// Deactivate our plugin.
		add_action( 'admin_init', array( $this, 'deactivate_me' ) );

		// Didn't meet the requirements.
		return false;
	}

	/**
	 * Before v0.3 the options were not prefixed.
	 * Here, we update the option keys if needed.
	 *
	 * @since  0.3
	 */
	public function update_options() {
		$prefix = 'dfw_';
		$options = array( 'network_code', 'breakpoints' );
		foreach ( $options as $old_option ) {
			$new_option = $prefix . $old_option;
			if ( ! get_option( $new_option ) && $old_option_value = get_option( $old_option ) ) {
				update_option( $new_option, $old_option_value );
				delete_option( $old_option );
			}
		}
	}

	/**
	 * Load all of the stuff in the includes folder.
	 *
	 * @since  0.3
	 */
	public function load_files() {
		$includes = array(
			'includes/class-breakpoint.php',
			'includes/class-ad-slot.php',
			'includes/class-options.php',
			'includes/class-widget.php',
			'includes/class-doubleclick.php',
		);

		foreach ( $includes as $include ) {
			if ( 0 === validate_file( $this->path . $include ) ) {
				require_once( $this->path . $include );
			}
		}
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  0.2.1
	 */
	public function deactivate_me() {

		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @since  0.2.1
	 *
	 * @return boolean True if requirements are met.
	 */
	public function meets_requirements() {

		// Do checks for required classes / functions or similar.
		// Add detailed messages to $this->activation_errors array.
		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since  0.2.1
	 */
	public function requirements_not_met_notice() {

		// Compile default message.
		$default_message = sprintf( __( 'DoubleClick for WordPress is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'doubleclick-for-wordpress' ), admin_url( 'plugins.php' ) );

		// Default details to null.
		$details = null;

		// Add details if any exist.
		if ( $this->activation_errors && is_array( $this->activation_errors ) ) {
			$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
		}

		// Output errors.
		?>
		<div id="message" class="error">
			<p><?php echo wp_kses_post( $default_message ); ?></p>
			<?php echo wp_kses_post( $details ); ?>
		</div>
		<?php
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.2.1
	 *
	 * @param  string $field Field to get.
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'doubleclick':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}
}

/**
 * Grab the DoubleClick_For_WordPress object and return it.
 * Wrapper for DoubleClick_For_WordPress::get_instance().
 *
 * @since  0.2.1
 * @return DoubleClick_For_WordPress  Singleton instance of plugin class.
 */
function doubleclick_for_wordpress() {
	return DoubleClick_For_WordPress::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( doubleclick_for_wordpress(), 'hooks' ) );

// Activation and deactivation.
register_activation_hook( __FILE__, array( doubleclick_for_wordpress(), '_activate' ) );
register_deactivation_hook( __FILE__, array( doubleclick_for_wordpress(), '_deactivate' ) );
