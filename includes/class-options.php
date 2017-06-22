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
	protected $key = 'doubleclick_for_wordpress_doubleclick';

	/**
	 * Options page metabox ID.
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	protected $metabox_id = 'doubleclick_for_wordpress_doubleclick_metabox';

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
		$this->title = esc_attr__( 'DoubleClick for WordPress Doubleclick', 'doubleclick-for-wordpress' );
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
		$this->options_page = add_menu_page(
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
		</div>
		<?php
	}
}
