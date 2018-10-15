<?php

/**
 * Global instances for DoubleClick object.
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

/**
 * The main plugin class.
 */
class DoubleClick {

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

	}

	/**
	 * Register Breakpoint
	 *
	 * @param string       $identifier the breakpoint to register.
	 * @param string|array $args additional args.
	 */
	public function register_breakpoint( $identifier, $args = null ) {
		$this->breakpoints[ $identifier ] = new DoubleClickBreakpoint( $identifier, $args );
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
			plugins_url( 'js/vendor/jquery.dfp.js/jquery.dfp' . $suffix . '.js', __FILE__ ),
			array( 'jquery' ),
			DFP_VERSION,
			true
		);
		wp_register_script(
			'jquery.dfw.js',
			plugins_url( 'js/jquery.dfw.js', __FILE__ ),
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
			'network_code' => $this->network_code,
			'mappings' => $mappings,
			'targeting' => $this->targeting(),
		);

		wp_localize_script( 'jquery.dfw.js', 'dfw', $data );
		wp_enqueue_script( 'jquery.dfw.js' );

		wp_enqueue_style(
			'dfp',
			plugins_url( 'css/dfp.css', __FILE__ ),
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
		return isset( $this->network_code ) ? $this->network_code : get_option( 'dfw_network_code','xxxxxx' );
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
				jQuery('.dfw-unit:not(.dfw-lazy-load)').dfp({
					dfpID: '<?php echo esc_js( $this->network_code() ); ?>',
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
			$cats = get_the_category();
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
			$targeting['Category'][] = $c->slug;
		}

		if ( is_single() ) {
			$tags = get_the_tags();
			if ( $tags ) {
				$targeting['Tag'] = array();
				foreach ( $tags as $t ) {
					$targeting['Tag'][] = $t->slug;
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
				),
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


class DoubleClickAdSlot {

	/**
	 * DFP ad code.
	 *
	 * @var String.
	 */
	public $identifer;

	/**
	 * Either a string of sizes, or a size mapping.
	 *
	 * @var Array|String
	 */
	public $sizes;

	/**
	 * Each ad gets a unique number to identify it.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * @param String $identifier
	 * @param Mixed $size
	 */
	public function __construct( $identifer, $size ) {

		global $doubleclick;

		// doubleclick escapes '/' with '//' for some odd reason.
		// currently we don't try to fix this, but could with this line:
		// $this->identifier = str_replace('/','//',$identifier);
		$this->identifier = $identifer;
		$this->sizes = $size;
		$this->id = ++ DoubleClick::$count;
	}

	public function breakpoint_identifier() {
		return null;
	}

	/**
	 * If this ad unit has a size mapping.
	 *
	 */
	public function has_mapping() {
		if ( is_string( $this->sizes ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function mapping() {
		global $doubleclick;

		// Return false if there is no mapping
		if ( ! $this->has_mapping() ) {
			return false;
		}

		$mapping = array();

		if ( empty( $this->sizes ) ) {
			return $mapping;
		}

		foreach ( $this->sizes as $breakpoint_identifier => $size ) {
			$breakpoint = $doubleclick->breakpoints[ $breakpoint_identifier ];

			// The minimum browser width/height for this sizemapping.
			$browser_height = 1;
			$browser_width = (int) $breakpoint->min_width;

			$size_strings = explode( ',', $size );	// eg. 300x250,336x300
			$size_array = array();

			foreach ( $size_strings as $size ) {
				if ( ! empty( $size ) ) {
					$arr = explode( 'x', $size );		// eg. 300x250
					$width = (int) $arr[0];
					$height = (int) $arr[1];
					$size_array[] = array( $width, $height );
				}
			}

			$mapping[] = array(
				'browser' => array( $browser_width, $browser_height ),
				'ad_sizes' => $size_array,
			);
		}

		return $mapping;
	}
}

class DoubleClickBreakpoint {

	/**
	 * Slug of the breakpoint
	 *
	 * @var string
	 */
	public $identifier = '';

	/**
	 * Minimum width for the breakpoint
	 *
	 * @var integer
	 */
	public $min_width;

	/**
	 * Maximum width for the breakpoint
	 *
	 * @var integer
	 */
	public $max_width;

	/**
	 * Was this breakpoint added by a theme or
	 * through an option?
	 *
	 * @var boolean
	 */
	public $option;

	public function __construct( $identifier, $args = null ) {
		if ( isset( $args['min_width'] ) ) {
			$this->min_width = $args['min_width'];
		}

		if ( isset( $args['max_width'] ) ) {
			$this->max_width = $args['max_width'];
		}

		if ( isset( $args['_option'] ) && $args['_option'] ) {
			$this->option = true;
		}

		$this->identifier = $identifier;
	}

	/**
	 * Prints a javascript boolean statement for this breakpoint
	 */
	public function js_logic() {
		echo esc_js( $this->get_js_logic() );
	}

	/**
	 * Returns a string with the boolean logic for the breakpoint.
	 *
	 * @return String boolean logic for breakpoint.
	 */
	public function get_js_logic() {
		return "($this->min_width <= document.documentElement.clientWidth && document.documentElement.clientWidth < $this->max_width)";
	}

}


/**
 * The dfp front end widget.
 */
include plugin_dir_path( __FILE__ ) . '/dfw-widget.php';

/**
 * Front end options for the widget.
 */
include plugin_dir_path( __FILE__ ) . '/dfw-options.php';
