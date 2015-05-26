<?php

/**
 * Global instances for DoubleClick object.
 * 
 */
$DoubleClick = new DoubleClick();


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
	do_action('dfw_setup');
}
add_action('wp_loaded', 'dfw_add_action');



class DoubleClick {

	/**
	 * Network code from DFP.
	 * 
	 * @var int
	 */
	public $networkCode;

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
	public $adSlots = array();

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
	 * @param string $networkCode The code for your dfp instance.
	 */
	public function __construct($networkCode = null) {

		$this->networkCode = $networkCode;

		// Script enqueue is static because we only ever want to print it once.
		if(!$this::$enqueued) {
			add_action('wp_footer', array($this, 'enqueue_scripts'));
			$this->enqueued = true;
		}

		add_action('wp_print_footer_scripts', array($this, 'footer_script'));

		$breakpoints = unserialize( get_option('dfw_breakpoints') );

		if( !empty($breakpoints) ):
			foreach($breakpoints as $b) {
				$args = array(
					'minWidth' => $b['min-width'],
					'maxWidth' => $b['max-width'],
					'_option'	=> true	// this breakpoint is set in WordPress options.
					);
				$this->register_breakpoint($b['identifier'],$args);
			}
		endif;

	}

	/**
	 * Register Breakpoint
	 * 
	 * @param DoubleClickBreakpoint
	 */
	public function register_breakpoint($identifier,$args = null) {

		$this->breakpoints[$identifier] = new DoubleClickBreakpoint($identifier,$args);

	}

	public function enqueue_scripts() {

		wp_register_script( 'jquery.dfp.min.js', plugins_url( 'js/jquery.dfp.min.js', __FILE__ ) , array('jquery'), '1.1.5', true );
		wp_register_script( 'jquery.dfw.js', plugins_url( 'js/jquery.dfw.js', __FILE__ ) , array('jquery'), '1.1.5', true );

		// Localize the script with other data
		// from the plugin.

		$mappings = array();

		foreach($this->adSlots as $ad) {
			if($ad->hasMapping()) {
				$mappings["mapping{$ad->id}"] = $ad->mapping();
			}
		}

		$data = array(
			'networkCode' => $this->networkCode,
			'mappings' => $mappings,
			'targeting' => $this->targeting()
			);

		wp_localize_script( 'jquery.dfw.js', 'dfw', $data );

		wp_enqueue_script( 'jquery.dfp.min.js' );
		wp_enqueue_script( 'jquery.dfw.js' );
	} 

	/**
	 * If the network code is set by the theme, return that.
	 * Else, try to return the front end option.
	 * 
	 * @return String network code.
	 */
	private function networkCode() {
		return isset($this->networkCode) ? $this->networkCode : get_option('dfw_network_code','xxxxxx');
	}

	public function footer_script() {

		if(!$this->debug) :
			
		echo "\n<script type='text/javascript'>\n";

		$mappings = array();

		foreach($this->adSlots as $ad) {
			if($ad->hasMapping()) {
				$mappings["mapping{$ad->id}"] = $ad->mapping();
			}
		}

		echo "\tjQuery('.dfw-unit:not(.dfw-lazy-load)').dfp({ \n";
        	echo "\t\tdfpID: '". $this->networkCode() ."',\n";
        	// echo "\t\trefreshExisting: false,\n";
        	echo "\t\tcollapseEmptyDivs:false,\n";
        	echo "\t\tsetTargeting: " . json_encode($this->targeting()) . ",\n";
        	echo "\t\tsizeMapping: " . json_encode($mappings);
        echo "\t});\n";
		
		echo "\n</script>\n";

		endif;
	}

	private function targeting() {
		
		/** @see http://codex.wordpress.org/Conditional_Tags */

		$targeting = array();

		$targeting['Page'] = array();

		// Homepage
		if( is_home() )
			$targeting['Page'][] = 'home';

		if( is_front_page() )
			$targeting['Page'][] = 'front-page';

		// Admin
		if( is_admin() )
			$targeting['Page'][] = 'admin';

		if( is_admin_bar_showing()  )
			$targeting['Page'][] = 'admin-bar-showing';

		// Templates
		if( is_single() )
			$targeting['Page'][] = 'single';

		if( is_post_type_archive() )
			$targeting['Page'][] = 'archive';

		if( is_author() )
			$targeting['Page'][] = 'author';

		if( is_date() )
			$targeting['Page'][] = 'date';

		if( is_search() )
			$targeting['Page'][] = 'search';	


		if( is_single() ) {

			$cats = get_the_category();
			$targeting['Category'] = array();

			if ($cats) {
				foreach($cats as $c) 
					$targeting['Category'][] = $c->slug;
			}
		}

		if( is_single() ) {

			$tags = get_the_tags();

			if ($tags) {
				$targeting['Tag'] = array();
				foreach($tags as $t) 
					$targeting['Tag'][] = $t->slug;
			}

		}

		// return the array of targeting criteria.
		return $targeting;
	}
	/**
	 * Place a DFP ad.
	 * 
	 * @param string $identifier A DFP
	 * @param string|array $dimensions the dimensions the ad could be.
	 * @param string|array $breakpoint breakpoints to target.
	 * @param array $targeting additional targeting options.
	 * @param $return Boolean. If this is true it will return a string instead.
	 */
	public function place_ad($identifier,$sizes,$args = null) {
		
		echo $this->get_ad_placement($identifier,$sizes,$args);

	}

	public function get_ad_placement($identifier,$sizes,$args = null) {

		global $post;

    	if( $args === null ) {
    		$args = array();
    	}

    	$defaults = array(
        	"lazyLoad" => false
    	);

		$args = array_replace_recursive($defaults, $args);

		$adObject = new DoubleClickAdSlot($identifier,$sizes);
		$this->adSlots[] = $adObject;

		// Print the ad tag.
		$classes = "dfw-unit";

		if($args['lazyLoad']) {
			$classes .= " dfw-lazy-load";
		}

		$id = $adObject->id;

		if( $adObject->hasMapping() ) {
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
	 * 
	 * 
	 * @param String $identifier
	 * @param Mixed $size
	 */
	public function __construct($identifer,$size) {

		global $DoubleClick;

		// doubleclick escapes '/' with '//' for some odd reason.
		// currently we don't try to fix this, but could with this line:
		// $this->identifier = str_replace('/','//',$identifier);
		$this->identifier = $identifer;
		
		$this->sizes = $size;

		$this->id = ++ DoubleClick::$count;

	}

	public function breakpointIdentifier() {
		
		return null;

	}

	/**
	 * If this ad unit has a size mapping.
	 * 
	 */
	public function hasMapping() {

		if( is_string( $this->sizes ) ) {
			return false;
		} else {
			return true;
		}

	}

	public function mapping() {

		global $DoubleClick;

		// Return false if there is no mapping
		if( !$this->hasMapping() )
			return false;
		
		foreach($this->sizes as $breakpointIdentifier=>$size) {
			
			$breakpoint = $DoubleClick->breakpoints[$breakpointIdentifier];

			//print_r($breakpoint);

			// The minimum browser width/height for this sizemapping.
			$browserHeight = 1;
			$browserWidth = $breakpoint->minWidth;

			$sizeStrings = explode(",",$size);	// eg. 300x250,336x300
			$sizeArray = array();

			foreach($sizeStrings as $s) {
				if( !empty($s) ) :
					$arr = explode("x",$s);		// eg. 300x250
					$w = (int)$arr[0];
					$h = (int)$arr[1];
					$sizeArray[] = array($w,$h);
				else :
					// $sizeArray[] = array();
				endif;
			}

			$mapping[] = array(
				'browser' => array($browserWidth,$browserHeight),
				'ad_sizes' => $sizeArray
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
	public $minWidth;

	/**
	 * Maximum width for the breakpoint
	 * 
	 * @var integer 
	 */
	public $maxWidth;

	/**
	 * Was this breakpoint added by a theme or
	 * through an option?
	 * 
	 * @var boolean
	 */
	public $option;


	public function __construct($identifier,$args = null) {
		
		if(isset($args['minWidth'])) 
			$this->minWidth = $args['minWidth'];

		if(isset($args['maxWidth']))
			$this->maxWidth = $args['maxWidth'];

		if(isset($args['_option']) && $args['_option'] ) {
			$this->option = true;
		}

		$this->identifier = $identifier;

	}

	/**
	 * Prints a javascript boolean statement for this breakpoint
	 * 
	 */
	public function js_logic() {

		echo $this->get_js_logic();
	
	}

	/**
	 * Returns a string with the boolean logic for the breakpoint.
	 * 
	 * @return String boolean logic for breakpoint.
	 */
	public function get_js_logic() {
		
		return "($this->minWidth <= document.documentElement.clientWidth && document.documentElement.clientWidth < $this->maxWidth)";
	
	}

}


/**
 * The dfp front end widget.
 * 
 */
include plugin_dir_path(__FILE__) . '/dfw-widget.php';

/**
 * Front end options for the widget.
 *
 */
include plugin_dir_path(__FILE__) . '/dfw-options.php';