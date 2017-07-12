<?php
/**
 * DoubleClick for WordPress Doubleclick.
 *
 * @since   0.2.1
 * @package DoubleClick_For_WordPress
 */

/**
 * DoubleClick for WordPress Doubleclick class.
 *
 * @since 0.2.1
 */
class DCWP_Options {
	/**
	 * Parent plugin class.
	 *
	 * @var    DoubleClick_For_WordPress
	 * @since  0.2.1
	 */
	protected $plugin = null;

	/**
	 * Option key, and option page slug.
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	protected $key = 'doubleclick_for_wordpress';

	/**
	 * Options page metabox ID.
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	protected $metabox_id = 'doubleclick_for_wordpress_metabox';

	/**
	 * Options Page title.
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	protected $title = '';

	/**
	 * Options Page hook.
	 *
	 * @var string
	 */
	protected $options_page = '';

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

		// Set our title.
		$this->title = esc_attr__( 'DoubleClick', 'dfw' );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.2.1
	 */
	public function hooks() {

		// Hook in our actions to the admin.
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'admin_init', array( $this, 'register_options' ) );
	}

	public function add_settings_link( $links ) {
		$mylinks = array(
			'<a href="options-general.php?page=doubleclick_for_wordpress">' . __( 'Settings', 'dfw' ) . '</a>',
		);
		return array_merge( $links, $mylinks );
	}

	/**
	 * Register our setting to WP.
	 *
	 * @since  0.2.1
	 */
	public function admin_init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page.
	 *
	 * @since  0.2.1
	 */
	public function add_options_page() {
		$this->options_page = add_submenu_page(
			'options-general.php',
			$this->title,
			$this->title,
			'manage_options',
			$this->key,
			array( $this, 'admin_page_display' )
		);
	}

	/**
	 * Admin page markup. Mostly handled by CMB2.
	 *
	 * @since  0.2.1
	 */
	public function admin_page_display() {
		?>
		<div class="wrap options-page <?php echo esc_attr( $this->key ); ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'doubleclick-for-wordpress' ); ?>
				<?php do_settings_sections( 'doubleclick-for-wordpress' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Registers options for the plugin.
	 *
	 * @since v0.1
	 */
	public function register_options() {
		// Add a section for network option
		add_settings_section(
			'network_options',
			'Network Settings',
			array( $this, 'settings_section_intro' ),
			'doubleclick-for-wordpress'
		);

		// Add a section for network option
		add_settings_section(
			'breakpoint_options',
			'Breakpoints',
			array( $this, 'breakpoints_section_intro' ),
			'doubleclick-for-wordpress'
		);

		// Add a section for network option
		add_settings_section(
			'documentation_options',
			'Documentation',
			array( $this, 'documentation_section_intro' ),
			'doubleclick-for-wordpress'
		);

		// Network Code
		add_settings_field(
			'network_code',
			'DoubleClick Network Code',
			array( $this, 'network_code_input' ),
			'doubleclick-for-wordpress',
			'network_options'
		);

		// Breakpoints
		add_settings_field(
			'breakpoints',
			'Breakpoints',
			array( $this, 'breakpoints_input' ),
			'doubleclick-for-wordpress',
			'breakpoint_options'
		);

		register_setting( 'doubleclick-for-wordpress', 'network_code' );
		register_setting( 'doubleclick-for-wordpress', 'breakpoints', array( $this, 'breakpoints_save' ) );

	}

	public function settings_section_intro() {
		echo "Enter a DoubleClick for Publisher's Network Code ( <a href='https://developers.google.com/doubleclick-publishers/docs/start#signup' target='_blank'>?</a> )";
	}

	public function breakpoints_section_intro() {
		echo 'Enter breakpoints below<br />';
		echo '<em>Example: phone: 0 to 480, tablet 480 to 768, desktop 768 to 9999.</em>';
	}

	public function documentation_section_intro() {
		echo '<a href="https://github.com/INN/DoubleClick-for-WordPress/blob/master/docs/index.md">Available on GitHub</a>';
	}

	public function network_code_input() {
		global $doubleclick;

		if ( isset( $doubleclick->network_code ) ) {
			echo '<input value="' . esc_attr( $doubleclick->network_code ) . ' (set in theme)" type="text" class="regular-text" disabled/>';
		} else {
			echo '<input name="network_code" id="network_code" type="text" value="' . esc_attr( get_option( 'network_code' ) ) . '" class="regular-text" />';
		}
	}

	public function breakpoints_input() {
		global $doubleclick;

		if ( isset( $doubleclick->breakpoints ) ) {
			foreach ( $doubleclick->breakpoints as $breakpoint ) {
				if ( ! $breakpoint->option ) {
					echo '<input value="' . esc_attr( $breakpoint->identifier ) . '" type="text" class="medium-text" disabled />';
					echo '<label> min-width</label><input value="' . esc_attr( $breakpoint->min_width ) . '" type="number" class="small-text" disabled />';
					echo '<label> max-width</label><input value="' . esc_attr( $breakpoint->max_width ) . '" type="number" class="small-text" disabled /> (set in theme)<br/>';
				}
			}
		}

		$breakpoints = maybe_unserialize( get_option( 'breakpoints' ) );

		$i = 0;
		while ( $i < 5 ) {
			$identifier = ( isset( $breakpoints[ $i ]['identifier'] ) )? $breakpoints[ $i ]['identifier'] : '';
			$min_width = ( isset( $breakpoints[ $i ]['min-width'] ) )? $breakpoints[ $i ]['min-width'] : '';
			$max_width = ( isset( $breakpoints[ $i ]['max-width'] ) )? $breakpoints[ $i ]['max-width'] : ''; ?>
			<input value="<?php echo esc_attr( $identifier ); ?>"
					placeholder="Name"
					name="breakpoints[]"
					type="text"
					class="medium-text" />
			<label> min-width
				<input
					value="<?php echo esc_attr( $min_width ); ?>" placeholder="0"
					name="breakpoints[]"
					type="number"
					class="small-text" />
			</label>
			<label> max-width
				<input
					value="<?php echo esc_attr( $max_width ); ?>"
					placeholder="9999"
					name="breakpoints[]"
					type="number"
					class="small-text" />
			</label><br/><?php
			$i++;
		}

	}

	public function breakpoints_save( $value ) {
		$message = null;
		$type = null;
		$breakpoints = array();
		$groups = array_chunk( $value, 3 );

		foreach ( $groups as $group ) {
			if ( isset( $group[0] ) && $group[0] ) {

				// Make sure the min is, in fact, smaller than the max.
				if ( $group[1] > $group[2] ) {
					$message = __( 'The max value must be greater than the min.', 'dfw' );
					$type = 'error';
				}

				// Compare with previous item in the array and make sure breakpoints don't overlap.
				if ( $last = end( $breakpoints ) ) {
					if ( $group[2] <= $last['max-width'] ) {
						$message = __( 'Breakpoints cannot overlap.', 'dfw' );
						$type = 'error';
					}
				}

				if ( $message ) {
					continue; // don't add this one to the array to save.
				}

				// OK we're good, add it to the array to save.
				$breakpoints[] = array(
					'identifier' => $group[0],
					'min-width' => $group[1],
					'max-width' => $group[2],
				);
			}
		}

		if ( ! empty( $breakpoints ) && ! $message ) {
			$message = __( 'Breakpoints updated.', 'dfw' );
			$type = 'updated';
		}
		add_settings_error( 'breakpoint_save_notice', 'breakpoint_save_notice', $message, $type );

		return $breakpoints;
	}
}
