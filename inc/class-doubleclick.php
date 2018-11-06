<?php
/**
 * The main plugin class
 *
 * generally accessible as the global $doubleclick thanks to dfw-init.php
 * @since 0.1
 */
class DoubleClick {

	/**
	 * Network code from GAM.
	 *
	 * @var int
	 */
	public $network_code;

	/**
	 * If true, plugin prints debug units instead of
	 * making a call to GAM.
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
	 * The plugin file for this plugin
	 *
	 * For feeding to https://codex.wordpress.org/Function_Reference/plugins_url
	 *
	 * @var string
	 */
	private $plugin_file = '';

	/**
	 * Create a new DoubleClick object
	 *
	 * @param string $network_code The code for your dfp instance.
	 */
	public function __construct( $network_code = null ) {

		$this->network_code = $network_code;

		// Script enqueue is static because we only ever want to print it once.
		if ( ! $this::$enqueued ) {
			add_action( 'wp_footer', array( $this, 'enqueue_scripts' ) );
			$this::$enqueued = true;
		}

		add_action( 'wp_print_footer_scripts', array( $this, 'footer_script' ) );

		$breakpoints = maybe_unserialize( get_option( 'dfw_breakpoints' ) );

		if ( ! empty( $breakpoints ) ) :
			foreach ( $breakpoints as $breakpoint ) {
				$args = array(
					'min_width' => $breakpoint['min-width'],
					'max_width' => $breakpoint['max-width'],
					'_option'	=> true,// this breakpoint is set in WordPress options.
					);
				$this->register_breakpoint( $breakpoint['identifier'], $args );
			}
		endif;

		$this->plugin_file = dirname( __DIR__ ) . '/dfw.php';

	}

	/**
	 * Register Breakpoint
	 *
	 * @param string       $identifier the breakpoint to register.
	 * @param string|array $args additional args.
	 * @return Boolean     Whether or not a breakpoint was registered.
	 */
	public function register_breakpoint( $identifier, $args = null ) {
		if ( is_string( $identifier) ) {
			$this->breakpoints[ $identifier ] = new DoubleClickBreakpoint( $identifier, $args );
			return true;
		} else if ( is_array( $identifier ) ) {
			$this->breakpoints[ $identifier['identifier'] ] = new DoubleClickBreakpoint( $identifier['identifier'], $args );
			return true;
		} else {
			if ( WP_DEBUG ) {
				error_log( 'DoubleClick->register_breakpoint() is being called with the wrong arguments somewhere.' );
			}
			return false;
		}
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
			plugins_url( 'js/vendor/jquery.dfp.js/jquery.dfp' . $suffix . '.js', $this->plugin_file  ),
			array( 'jquery' ),
			DFP_VERSION,
			true
		);
		wp_register_script(
			'jquery.dfw.js',
			plugins_url( 'js/jquery.dfw.js', $this->plugin_file ),
			array( 'jquery.dfp.js' ),
			DFP_VERSION,
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
			'dfpID' => $this->network_code(),
			'network_code' => $this->network_code(),
			'mappings' => $mappings,
			'targeting' => $this->targeting(),
		);

		/**
		 * Allow sites to filter the DFP settings passed to jquery.dfp.js
		 *
		 * @since 0.3
		 * @link https://github.com/INN/doubleclick-for-wp/issues/63#issuecomment-393342611
		 * @param Array $data An associative array of things. The default is:
		 *    array(
		 *        'network_code' => the option from the plugin settings
		 *        'mappings' => an array of ad mappings; not sure exactly what this is supposed to be
		 *        'targeting' => the ad targeting data appropriate to this page
		 *    )
		 */
		$data = apply_filters( 'dfw_js_data', $data );

		wp_localize_script( 'jquery.dfw.js', 'dfw', $data );
		wp_enqueue_script( 'jquery.dfw.js' );

		wp_enqueue_style(
			'dfp',
			plugins_url( 'css/dfp.css', $this->plugin_file ),
			array(),
			DFP_VERSION,
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
		return ( isset( $this->network_code ) && ! empty( $this->network_code ) ) ? $this->network_code : get_option( 'dfw_network_code','xxxxxx' );
	}

	public function footer_script() {
		if ( ! $this->debug ) {
			$mappings = array();
			foreach ( $this->ad_slots as $ad ) {
				if ( $ad->has_mapping() ) {
					$mappings[ "mapping{$ad->id}" ] = $ad->mapping();
				}
			} ?>
			<script type="text/javascript">
				jQuery('.dfw-unit:not(.dfw-lazy-load)').dfp( window.dfw );
			</script>
		<?php }
	}

	private function targeting() {
		global $wp_query;
		/** @see http://codex.wordpress.org/Conditional_Tags */

		$targeting = array();
		$targeting['Page'] = array();

		// Homepage
		if ( is_home() ) {
			$targeting['Page'][] = 'home';
		}

		if ( is_front_page() ) {
			$targeting['Page'][] = 'front-page';
		}

		// Admin
		if ( is_admin() ) {
			$targeting['Page'][] = 'admin';
		}

		if ( is_admin_bar_showing()  ) {
			$targeting['Page'][] = 'admin-bar-showing';
		}

		/*
		 * Templates
		 */
		if ( is_singular() && ( ! is_post_type_archive() && ! is_front_page() ) ) {
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

		if ( is_singular() && ( ! is_post_type_archive() && ! is_front_page() ) ) {
			// https://wordpress.org/support/topic/categories-tags-bug-and-solution/
			$cat_post_id = $wp_query->get_queried_object_id();
			if ( 0 === $cat_post_id ) {
				$cat_post_id = null; // get_queried_object_id returns 0 for no post found; get_the_category expects null;
			}
			$cats = get_the_category( $cat_post_id );
			$targeting['Category'] = array();

			if ( ! empty( $cats ) ) {
				foreach ( $cats as $c ) {
					$targeting['Category'][] = $c->slug;
				}
			}
		}

		if ( is_category() ) {
			$queried_object = get_queried_object();
			if ( ! isset( $targeting['Category'] ) ) {
				$targeting['Category'] = array();
			}
			$targeting['Category'][] = $queried_object->slug;
		}

		if ( is_single() ) {
			// https://wordpress.org/support/topic/categories-tags-bug-and-solution/
			$tag_post_id = $wp_query->get_queried_object_id();
			if ( 0 === $tag_post_id ) {
				$tag_post_id = null; // get_queried_object_id returns 0 for no post found; get_the_tags expects null;
			}
			$tags = get_the_tags( $tag_post_id );

			if ( $tags ) {
				$targeting['Tag'] = array();
				foreach ( $tags as $t ) {
					$targeting['Tag'][] = $t->slug;
				}
			}
		}

		if ( is_tag() ) {
			$queried_object = get_queried_object();
			if ( ! isset( $targeting['Tag'] ) ) {
				$targeting['Tag'] = array();
			}
			$targeting['Tag'][] = $queried_object->slug;
		}


		// return the array of targeting criteria.
		return apply_filters( 'dfw_targeting_criteria', $targeting );
	}

	/**
	 * Place a Google Ad Manager ad.
	 *
	 * @param string       $identifier A Google Ad Manager ad unit identifier.
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
				),
			)
		);
	}

	/**
	 * Build the ad code.
	 *
	 * @param string       $identifier A Google Ad Manager ad unit identifier.
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

		$ad_object = new DoubleClickAdSlot( $identifier, $sizes );
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
