<?php
/*
Plugin Name: 	DoubleClick for Wordpress
Description: 	Gives site administrators control over ad units loaded and displayed on their site.
Author: 		Will Haynes
Author URI: 	http://badgerherald.com
License: 		Copyright (c) 2013 The Badger Herald
*/


/**
 * Global instances for doubleclick object.
 * 
 */
$DoubleClick = new DoubleClick();

function dfw_add_action() {
	/**
	 * 
	 */
	do_action('dfw_setup_ad_units');
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

			add_action('wp_head', array(get_called_class(), 'header_script'));
			$this->enqueued = true;

		}

		// Footer script is not static because we could have multiple objects.
		add_action('wp_head', array($this, 'footer_script'));

		// 
		add_action('shutdown', array($this, 'check_delivery'));
	
	}

	/**
	 * Register Breakpoint
	 * 
	 * @param DoubleClickBreakpoint
	 */
	public function register_breakpoint($identifier,$args = null) {
		$this->breakpoints[$identifier] = new DoubleClickBreakpoint($identifier,$args);
	}

	/**
	 * Register ad slot.
	 * 
	 */
	public function register_adslot($identifier, $adCode = null, $size = null, $breakpoints = null) {
		
		if( $adCode == null ) {
			$this->adSlots[$identifier] = $this->definedAdSlots[$identifier];
		} else {
			$this->define_adslot($identifier,$adCode,$size,$breakpoints);
			$this->register_adslot($identifier);
		}

		return $identifier;

	}

	/**
	 * Define an ad slot.
	 * 
	 */
	public function define_adslot($identifier,$adCode,$size,$breakpoints = null) {
		
		$newAd = new DoubleClickAdSlot($identifier,$adCode,$size,$breakpoints);
		$newAd->DoubleClickObject = $this;
		$this->definedAdSlots[$identifier] = $newAd;

	}

	public static function header_script() {

		echo "
		<script type='text/javascript'>
		var googletag = googletag || {};
		googletag.cmd = googletag.cmd || [];
		(function() {
		var gads = document.createElement('script');
		gads.async = true;
		gads.type = 'text/javascript';
		var useSSL = 'https:' == document.location.protocol;
		gads.src = (useSSL ? 'https:' : 'http:') + 
		'//www.googletagservices.com/tag/js/gpt.js';
		var node = document.getElementsByTagName('script')[0];
		node.parentNode.insertBefore(gads, node);
		})();
		</script>";

	}

	public function footer_script() {

		echo "\n<script type='text/javascript'>\n";
		echo "googletag.cmd.push(function() {\n";
		
		foreach($this->adSlots as $a) {
			if($a->breakpoints == null) {
				$a->define_slot();
			}
		}

		foreach ($this->breakpoints as $b) {

			echo "if(" . $b->get_js_logic() . ") {\n";
			echo "console.log('adding ".$b->identifier." units');\n";
			
			foreach($this->adSlots as $a) {
				
				if(in_array($b->identifier,$a->breakpoints)) {
					$a->define_slot();
				}

			}

			echo "}\n";
		}

		echo "googletag.pubads().enableSingleRequest();\n";
		echo "googletag.enableServices();\n";
		echo "});";

		echo "</script>";

	}

	/**
	 * 
	 * @param $identifier
	 * @param $breakpoint
	 * @param $return Boolean. If this is true it will return a string instead.
	 */
	public function display_ad($identifier,$breakpoint = null,$return = false) {

		if(array_key_exists($identifier,$this->adSlots)) {
			if($return) {
				return $this->adSlots[$identifier]->display($breakpoint,$return);
			}
			else {
				$this->adSlots[$identifier]->display($breakpoint,$return);
			}
		}

	}

	public function check_delivery() {

		// error_log("======= checking ad delivery.");
		$err = false;
		foreach ($this->adSlots as $ad) {
			# code...
			if(!$ad->fully_delivered()) {
				error_log(" - " . $ad->identifier . " Failed to deliver " . print_r($ad->not_delivered_for(),true));
				$err = true;
			}
		}

		if(!$err) {
			// error_log(" - All ads delivered successfully.");
		}
		// error_log("======= done checking ad delivery.");
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
		
		$this->adCode = str_replace('/','//',$adCode);

		if( is_array( $size[0] )) {

			$this->size = $size;

		} else if( is_array( $size ) ) {

			$this->size = array($size);

		}

		if( is_string( $breakpoints ) ) {

			$this->breakpoints = array( $breakpoints );

		} else if( is_array( $breakpoints ) ) {

			$this->breakpoints = $breakpoints;

		}

	}

	/**
	 * Prints the javascript statement that defines the slot.
	 * Called from DoubleClick->print_footer();
	 * 
	 */
	public function define_slot() {

		echo "googletag.defineSlot( ";
				
		// Ad Unit Tag
		echo "'/{$this->DoubleClickObject->networkCode}/$this->adCode'";
		
		echo ",";
		
		// Size
		if( sizeof($this->size)==1 ) {
		
			echo "[" . $this->size[0][0] . "," . $this->size[0][1] . "]";
		
		} else {
			
			echo "[";
			$first = true;

			foreach ($this->size as $sz) {

				if( !$first ) {
					echo ", ";
				} else { $first = false; }
			
				echo "[" . $sz[0] . "," . $sz[1] . "]";
			
			}
		
			echo "]";
		
		}

		echo ",";

		echo "'dfw-" . $this->identifier . "'";
				
		echo " ).addService(googletag.pubads());\n";
	}

	public function display($breakpoints = null,$return = false) {

		if( is_string($breakpoints) ) {
			$breakpoints = array($breakpoints);
		} 
		else if( is_null($breakpoints) ) {
			$breakpoints = $this->breakpoints;
		}

		$displayBool = "";
		if( !$breakpoints ) {
			$displayBool = "true";
		} 
		else {
			$first = true;
			foreach($breakpoints as $b) {
				if(!$first) {
					$displayBool .= " || ";
				} 
				else { $first = false; }
				$displayBool .= $this->DoubleClickObject->breakpoints[$b]->get_js_logic();
			}
		}

		$s = "";
		$s .= "\n<!-- $this->adCode -->\n";
		$s .= "<div id='dfw-" . $this->identifier . "' style='width:{$this->size[0][0]}px;height:{$this->size[0][1]}px;'>";
		$s .= "<script type='text/javascript'>";
		$s .= "if($displayBool) {";
		$s .= "googletag.cmd.push(function() { googletag.display('dfw-" . $this->identifier . "'); });";
		$s .= "}";
		$s .= "</script>";
		$s .= "</div>";

		$this->displayedFor = array_merge($this->displayedFor,$breakpoints);

		if($return){
			return $s;
		} else {
			echo $s;
		}

	}

	public function fully_delivered() {

		//error_log( "#####not delivered: " .$this->identifier . print_r($this->not_delivered_for(),true) );
		if( sizeof($this->not_delivered_for()) != 0 )
			return false;
		else
			return true;


	}

	public function not_delivered_for() {
		//error_log( $this->identifier );
		//error_log( print_r($this->displayedFor,true) . sizeof($this->displayedFor) );
		//error_log( print_r($this->breakpoints,true) . sizeof($this->breakpoints) );
		return array_diff($this->breakpoints,$this->displayedFor);
	}

}