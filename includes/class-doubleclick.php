<?php
/**
 * DoubleClick for WordPress Functions.
 *
 * @since   0.2.1
 * @package DoubleClick_For_WordPress
 */

/**
 * DoubleClick for WordPress Functions.
 *
 * @since 0.2.1
 */
class DCWP_DoubleClick {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.2.1
	 *
	 * @var   DoubleClick_For_WordPress
	 */
	protected $plugin = null;

	/**
	 * Network code from DFP.
	 *
	 * @var int
	 */
	public $network_code;

	/**
	 * If true, plugin prints debug units instead of
	 * making a call to dfp.
	 *
	 * @var boolean
	 */
	public $debug = false;

	/**
	 * Array of defined breakpoints
	 *
	 * @var Array
	 */
	public $breakpoints = array();

	/**
	 * Array of placed ads.
	 *
	 * @var Array
	 */
	public $ad_slots = array();

	/**
	 * Whether we have hooked enqueue of the script
	 * to wp head.
	 *
	 * @var boolean
	 */
	private static $enqueued = false;

	/**
	 * Size mappings for ad units.
	 *
	 * @var Array
	 */
	private static $mapping = array();

	/**
	 * The number of ads on a page. Also appended to
	 * ad identifiers to create unique strings.
	 *
	 * @var int
	 */
	public static $count = 0;

	/**
	 * Create a new DoubleClick object
	 *
	 * @param string $network_code The code for your dfp instance.
	 * @TODO update parameters
	 */
	public function __construct( $plugin ) {

		$this->network_code = $this->network_code();

		// Script enqueue is static because we only ever want to print it once.
		if ( ! $this::$enqueued ) {
			add_action( 'wp_footer', array( $this, 'enqueue_scripts' ) );
			$this::$enqueued = true;
		}

		add_action( 'wp_print_footer_scripts', array( $this, 'footer_script' ) );

		$breakpoints = apply_filters( 'dfw_breakpoints', maybe_unserialize( get_option( 'dfw_breakpoints' ) ) );

		if ( ! empty( $breakpoints ) ) {
			foreach ( $breakpoints as $breakpoint ) {
				// if this is not set explicitly, it's coming from the dfw_breakpoints option.
				if ( ! isset( $breakpoint['option'] ) ) {
					$breakpoint['option'] = true;
				}
				$args = array(
					'min_width' => $breakpoint['min-width'],
					'max_width' => $breakpoint['max-width'],
					'_option'	=> $breakpoint['option'],
				);
				$this->register_breakpoint( $breakpoint['identifier'], $args );
			}
		}
	}

	/**
	 * Register Breakpoint
	 *
	 * @param string       $identifier the breakpoint to register.
	 * @param string|array $args additional args.
	 */
	public function register_breakpoint( $identifier, $args = null ) {
		$this->breakpoints[ $identifier ] = new DCWP_Breakpoint( $identifier, $args );
	}

	/**
	 * Register scripts
	 *
	 * @global WP_DEBUG used to determine whether or not
	 */
	public function enqueue_scripts() {
		$suffix = (WP_DEBUG)? '' : '.min';

		wp_register_script(
			'jquery.dfp.js',
			plugins_url( 'assets/js/jquery.dfp' . $suffix . '.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			DoubleClick_For_WordPress::VERSION,
			true
		);
		wp_register_script(
			'jquery.dfw.js',
			plugins_url( 'assets/js/jquery.dfw' . $suffix . '.js', dirname( __FILE__ ) ),
			array( 'jquery.dfp.js' ),
			DoubleClick_For_WordPress::VERSION,
			true
		);

		// Localize the script with other data
		// from the plugin.
		$mappings = array();
		foreach ( $this->ad_slots as $ad ) {
			if ( $ad->has_mapping() ) {
				$mappings[ "mapping{$ad->id}" ] = $ad->mapping();
			}
		}

		$data = array(
			'network_code' => $this->network_code,
			'mappings' => $mappings,
			'targeting' => $this->targeting(),
		);

		wp_localize_script( 'jquery.dfw.js', 'dfw', $data );
		wp_enqueue_script( 'jquery.dfw.js' );

		wp_enqueue_style(
			'dfp',
			plugins_url( 'assets/css/dfp.css', dirname( __FILE__ ) ),
			array(),
			DoubleClick_For_WordPress::VERSION,
			'all'
		);
	}

	/**
	 * If the network code is set by the theme, return that.
	 * Else, try to return the front end option.
	 *
	 * @return String network code.
	 */
	private function network_code() {
		$network_code = isset( $this->network_code ) ? $this->network_code : get_option( 'dfw_network_code','xxxxxx' );
		return apply_filters( 'dfw_network_code', $network_code );
	}

	/**
	 * Add DFP script to the footer
	 */
	public function footer_script() {
		if ( ! $this->debug ) {
			$mappings = array();
			foreach ( $this->ad_slots as $ad ) {
				if ( $ad->has_mapping() ) {
					$mappings[ "mapping{$ad->id}" ] = $ad->mapping();
				}
			} ?>
			<script type="text/javascript">
				jQuery('.dfw-unit:not(.dfw-lazy-load)').dfp({
					dfpID: <?php echo wp_json_encode( $this->network_code() ); ?>,
					collapseEmptyDivs: false,
					setTargeting: <?php echo wp_json_encode( $this->targeting() ); ?>,
					sizeMapping: <?php echo wp_json_encode( $mappings ); ?>
				});
			</script>
		<?php }
	}

	private function targeting() {
		/** @see http://codex.wordpress.org/Conditional_Tags */

		$targeting = array();
		$targeting['Page'] = array();

		// Homepage.
		if ( is_home() ) {
			$targeting['Page'][] = 'home';
		}

		if ( is_front_page() ) {
			$targeting['Page'][] = 'front-page';
		}

		// Admin.
		if ( is_admin() ) {
			$targeting['Page'][] = 'admin';
		}

		if ( is_admin_bar_showing()  ) {
			$targeting['Page'][] = 'admin-bar-showing';
		}

		// Templates.
		if ( is_single() ) {
			$targeting['Page'][] = 'single';
		}

		if ( is_post_type_archive() ) {
			$targeting['Page'][] = 'archive';
		}

		if ( is_author() ) {
			$targeting['Page'][] = 'author';
		}

		if ( is_date() ) {
			$targeting['Page'][] = 'date';
		}

		if ( is_search() ) {
			$targeting['Page'][] = 'search';
		}

		if ( is_single() ) {
			global $wp_query;
			$current_page_id = $wp_query->get_queried_object_id();
			$cats = get_the_category( $current_page_id );
			$targeting['Category'] = array();

			if ( $cats ) {
				foreach ( $cats as $cat ) {
					$targeting['Category'][] = $cat->slug;
				}
			}
		}

		if ( is_single() ) {
			global $wp_query;
			$current_page_id = $wp_query->get_queried_object_id();
			$tags = get_the_tags( $current_page_id );
			if ( is_array( $tags ) ) {
				$targeting['Tag'] = array();
				foreach ( $tags as $tag ) {
					$targeting['Tag'][] = $tag->slug;
				}
			}
		}

		// return the array of targeting criteria.
		return apply_filters( 'dfw_targeting_criteria', $targeting );
	}

	/**
	 * Place a DFP ad.
	 *
	 * @param string       $identifier A DFP identifier.
	 * @param string|array $sizes the dimensions the ad could be.
	 * @param string|array $args additional args.
	 */
	public function place_ad( $identifier, $sizes, $args = null ) {
		echo wp_kses(
			$this->get_ad_placement( $identifier, $sizes, $args ),
			array(
				'div' => array(
					'class' => array(),
					'data-adunit' => array(),
					'data-size-mapping' => array(),
					'data-dimensions' => array(),
				)
			)
		);
	}

	/**
	 * Build the ad code.
	 *
	 * @param string       $identifier A DFP identifier.
	 * @param string|array $sizes the dimensions the ad could be.
	 * @param string|array $args additional args.
	 */
	public function get_ad_placement( $identifier, $sizes, $args = null ) {
		global $post;

		if ( null === $args ) {
			$args = array();
		}

		$defaults = array(
			'lazyLoad' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$ad_object = new DCWP_AdSlot( $identifier, $sizes );
		$this->ad_slots[] = $ad_object;

		// Print the ad tag.
		$classes = 'dfw-unit';

		if ( $args['lazyLoad'] ) {
			$classes .= ' dfw-lazy-load';
		}

		$id = $ad_object->id;

		if ( $ad_object->has_mapping() ) {
			$ad = "<div
				class='$classes'
					data-adunit='$identifier'
					data-size-mapping='mapping{$id}'></div>";
		} else {
			$ad = "<div
				class='$classes'
					data-adunit='$identifier'
					data-dimensions='$sizes'></div>";
		}

		return $ad;
	}
}
