<?php
/*
Plugin Name: 	DoubleClick for WordPress
Description: 	The simplest way to serve DoubleClick ads in WordPress.
Author: 		Will Haynes for INN
Author URI: 	http://github.com/inn
*/

/**
 * Global instances for doubleclick object.
 * 
 */
$DoubleClick = new DoubleClick();

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

	public $networkCode;

	public $debug = false;

	public $breakpoints = array();
	public $adSlots = array();
	public $definedAdSlots = array();

	/**
	 * Whether we have hooked enqueue of the script
	 * to wp head.
	 * 
	 * @var boolean
	 */
	private static $enqueued = false;

	/**
	 * Create a new DoubleClick object
	 * 
	 * @param string $networkCode The code for your dfp instance.
	 */
	public function __construct($networkCode = null) {

		$this->networkCode = $networkCode;

		// Script enqueue is static because we only ever want to print it once.
		if(!$this->enqueued) {
			add_action('wp_enqueue_scripts', array(get_called_class(), 'enqueue_scripts'));
			$this->enqueued = true;
		}

		add_action('wp_print_footer_scripts', array($this, 'footer_script'));

		$breakpoints = unserialize( get_option('dfw_breakpoints') );

		foreach($breakpoints as $b) {
			$args = array(
				'minWidth' => $b->minWidth,
				'maxWidth' => $b->maxWidth,
				'_option'	=> true	// this breakpoint is set in WordPress options.
				);
			$this->register_breakpoint($b->identifier,$args);
		}
	
	}

	/**
	 * Register Breakpoint
	 * 
	 * @param DoubleClickBreakpoint
	 */
	public function register_breakpoint($identifier,$args = null) {

		$this->breakpoints[$identifier] = new DoubleClickBreakpoint($identifier,$args);

	}

	public static function enqueue_scripts() {

		wp_enqueue_script( 'jquery.dfp.js', plugins_url( 'js/jquery.dfp.min.js', __FILE__ ) , array('jquery'), '1.1.5', true );

	} 

	public function footer_script() {

		if(!$this->debug) :
			
		echo "\n<script type='text/javascript'>\n";

		// Load each breakpoint

		$first = true;
		foreach($this->breakpoints as $b) :

			if(!$first) echo "else ";

			echo "if( " . $b->get_js_logic() . " ) { \n";
				echo "\tjQuery('.dfw-{$b->identifier}').add('.dfw-all').dfp({ \n";
        	    	echo "\t\tdfpID: '". $this->networkCode ."',\n";
        	    	echo "\t\trefreshExisting: false\n";
        		echo "\t});\n";
			echo "} ";

			$first = false;

		endforeach;

		echo "\n</script>\n";

		endif;
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
	public function place_ad($identifier,$dimensions,$breakpoints = null,$targeting = null) {
		
		echo $this->get_ad_placement($identifier,$dimensions,$breakpoints,$targeting);

	}

	public function get_ad_placement($identifier,$dimensions,$breakpoints = null, $targeting = null) {

		// $dimensions validation

		if( is_string($dimensions) ) {
			$dimensions = array($dimensions);
			$dim = "";
			foreach($dimensions as $i=>$d) {
				if( $i ) {
					$dim .= ",";
				}
				$dim .= $d;
			}
		}

		// $breakpoints validation

		if( is_string($breakpoints) ) {
			$breakpoints = array($breakpoints);
		}

		// $targeting validation

		if( is_null($targeting) ) {
			$targeting = array();
		}

		if( !isset( $targeting['page'] ) ) {

			if(is_home())
				$targeting['page'] = 'homepage';
			if(is_single())
				$targeting['page'] = 'story';
			if(is_archive())
				$targeting['page'] = 'archive';
			if(is_page())
				$targeting['page'] = 'page';
			else
				$targeting['page'] = 'other';

		}

		$adObject = new DoubleClickAdSlot($identifer,$adCode,$size,$breakpoints,$targeting);
		$this->adSlots[] = $adObject;

		// Print the ad tag.

		$classes = "dfw-unit";

		if($breakpoints):
			foreach($breakpoints as $i=>$b) {
				$classes .= " dfw-" . $b;
			}
		else:
			$classes .= " dfw-all";
		endif;

		$ad = "<div class='$classes' data-adunit='$identifier' data-dimensions='$dim' data-targeting='$targets'></div>";

		$size = explode('x',$dimensions[0]);
		$w = $size[0];
		$h = $size[1];

		// Print a fake debugging ad unit.
		
		if($this->debug) {
			$ad = "<div 
					style='
						background: 	rgba(0,0,0,.1);
						font-family: 	monospace;
						padding:		10px;
						width:			{$w}px;
						height:			{$h}px;
						text-align:		left;
						font-size:		12px;
						box-sizing:		border-box;'
					class='$classes' 
					>";
				
				// Print the identifier
				$ad .= "<b style='border-bottom:1px solid rgba(0,0,0,.2);display:inline-block;margin-bottom:6px;'>$identifier</b></br>";
	
				// Print the size.
				$sizes = "";
	
				foreach($dimensions as $i=>$d) {
					if( $i ) $sizes .= ", ";
					$sizes .= $d;
				}
	
				if(sizeof($dimensions)<=1) {
					$ad .= "<b>size</b> ";
				} else {
					$ad .= "<b>sizes</b> ";
				}
	
				$ad .= "$sizes</br>";
	
				// Print the breakpoints.
	
				if(sizeof($breakpoints)<=1 && $breakpoints) {
					$ad .= "<b>breakpoint</b> ";
				} else {
					$ad .= "<b>breakpoints</b> ";
				}
	
				$ad .= $breakpoints ? $adObject->breakpointIdentifier() : "all";
				$ad .= "</br>";
			
			$ad .= "</div>";
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
	public $adCode;

	/**
	 * Array of sizes.
	 * 
	 * @var Array.
	 */
	public $size;

	/**
	 * Unique identifier for this ad slot.
	 * 
	 * @var String.
	 */
	public $identifier;

	/**
	 * Array of associated breakpoints.
	 * 
	 * @var Array.
	 */
	public $breakpoints = null;

	/**
	 * Array of targeting options.
	 * 
	 * @var Array.
	 */
	public $targeting = null;

	/**
	 * The associated DoubleClick Object
	 * 
	 * @var DoubleClick
	 */
	public $DoubleClickObject;

	/**
	 * 
	 * 
	 */
	private $displayedFor = array();

	public function __construct($identifer,$adCode,$size,$breakpoints = null,$targeting = null) {

		$this->identifier = $identifer;
		
		// $this->adCode = str_replace('/','//',$adCode);
		$this->adCode = $adCode;

		if( is_string( $size ) ) {

			$this->size = array($size);

		} else if( is_array( $size ) ) {

			$this->size = $size;

		}

		if( is_string( $breakpoints ) ) {

			$this->breakpoints = array( $breakpoints );

		} else if( is_array( $breakpoints ) ) {

			sort($breakpoints);
			$this->breakpoints = $breakpoints;

		}

		$this->targeting = $targeting;

	}

	public function breakpointIdentifier() {
		
		$s = "";

		foreach($this->breakpoints as $i=>$b) {
			if( $i ) {
				$s .= "+";
			}
			$s .= $b;	
		}
		return $s;

	}


}

class DoubleClickBreakpoint {

	/**
	 * Name of the breakpoint
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