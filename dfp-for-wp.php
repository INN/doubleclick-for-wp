<?php
/*
Plugin Name: 	DoubleClick for Wordpress
Description: 	Gives site administrators control over ad units loaded and displayed on their site.
Author: 		Will Haynes
Author URI: 	http://willhaynes.com
License: 		Released under the MIT license
*/


/**
 * Global instances for doubleclick object.
 * 
 */
$DoubleClick = new DoubleClick();

function dfw_add_action() {
	/**
	 * 
	 * 
	 * 
	 */
	do_action('dfw_setup');
}
add_action('wp', 'dfw_add_action');


class DoubleClick {

	public $networkCode;

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

		echo "\n<script type='text/javascript'>\n";

		// Load each breakpoint

		$first = true;
		foreach($this->breakpoints as $b) :

			
			if(!$first) echo "else ";

			echo "if( " . $b->get_js_logic() . " ) { \n";
				echo "\t$('.dfw-{$b->identifier}').add('.dfw-all').dfp({ \n";
        	    	echo "\t\tdfpID: '". $this->networkCode ."',\n";
        	    	echo "\t\trefreshExisting: false\n";
        		echo "\t});\n";
			echo "} ";

			$first = false;

		endforeach;

		// Load for all breakpoints
/*
		echo "else {\n";
		echo "\t$('.dfw-all').dfp({ \n";
            echo "\t\tdfpID: '". $this->networkCode ."',\n";
            echo "\t\trefreshExisting: false\n";
        echo "\t});\n";
		echo "}";
*/
		echo "\n</script>\n";

	}

	/**
	 * 
	 * @param string $identifier A DFP
	 * @param mixed $breakpoint (string/array)
	 * @param $return Boolean. If this is true it will return a string instead.
	 */
	public function place_ad($identifier,$dimensions,$breakpoints = null) {

		// Paramater validation:
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

		if( is_string($breakpoints) ) {
			$breakpoints = array($breakpoints);
		}

		$adObject = new DoubleClickAdSlot($identifer,$adCode,$size,$breakpoints);
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

		echo "<div class='$classes' data-adunit='$identifier' data-dimensions='$dim'></div>";

		$size = explode('x',$dimensions[0]);
		$w = $size[0] - 20;
		$h = $size[1] - 20;

		// Print a fake debugging ad unit.
		/*
		echo "<div 
				style='
					background: rgba(0,0,0,.1);
					font-family: monospace;
					padding:10px;
					width:{$w}px;
					height:{$h}px;
					text-align:left;
					font-size:12px;
				'>";
			
			// Print the identifier
			echo "<b style='border-bottom:1px solid rgba(0,0,0,.2);display:inline-block;margin-bottom:6px;'>$identifier</b></br>";

			// Print the size.
			$sizes = "";

			foreach($dimensions as $i=>$d) {
				if( $i ) $sizes .= ", ";
				$sizes .= $d;
			}

			if(sizeof($dimensions)<=1) {
				echo "<b>size</b> ";
			} else {
				echo "<b>sizes</b> ";
			}

			echo "$sizes</br>";

			// Print the breakpoints.

			if(sizeof($breakpoints)<=1 && $breakpoints) {
				echo "<b>breakpoint</b> ";
			} else {
				echo "<b>breakpoints</b> ";
			}

			echo $breakpoints ? $adObject->breakpointIdentifier() : "all";
			echo "</br>";
		
		echo "</div>";
		*/
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

	public function __construct($identifer,$adCode,$size,$breakpoints = null) {

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


	public function __construct($identifier,$args = null) {
		
		if(isset($args['minWidth'])) 
			$this->minWidth = $args['minWidth'];

		if(isset($args['maxWidth']))
			$this->maxWidth = $args['maxWidth'];

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