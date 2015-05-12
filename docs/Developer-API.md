# Developer API

A `global $DoubleClick` variable is defined on plugin init, making it possible to define breakpoints
and place ads directly within your theme.

__Quick Function Links:__

 * [$DoubleClick->register_breakpoint($identifer,$args)](#doubleclick-register_breakpointidentiferargs)
 * [$DoubleClick->place_ad($identifer,$size,$breakpoints)](#doubleclick-place_adidentifersizebreakpoints)

* * *

## 1. Define Breakpoints

You can make it easier for users to target breakpoints by defining them in `functions.php`

```php
function ad_setup() {

	global $DoubleClick;

	// Optionally define the network code directly in functions.php.
	// $DoubleClick->networkCode = "xxxxxxx";
		
	/* Define Breakpoints */
	$DoubleClick->register_breakpoint('phone', array('minWidth'=> 0,'maxWidth'=>720));
	$DoubleClick->register_breakpoint('tablet', array('minWidth'=>760,'maxWidth'=>1040));
	$DoubleClick->register_breakpoint('desktop', array('minWidth'=>1040,'maxWidth'=>1220));
	$DoubleClick->register_breakpoint('xl', array('minWidth'=>1220,'maxWidth'=>9999));

}
add_action('dfw_setup','ad_setup');
```

##### $DoubleClick->register_breakpoint($identifer,$args)
    
Define a new breakpoint. This will output javascript to only load the ad if the target screen is between the min and max width.

__$identifer__

`String` A unique identifier for this breakpoint

__$args__
`Array` An array of properties about the breakpoint. Currently the only keys supported are minWidth and maxWidth.

* * * 

## 2. Place Ads

If you would like to hard code ad placement into a theme, you can use the built in
`$DoubleClick->place_ad()` function to print the DOM to include an ad.

_(First ensure a network code is defined in `functions.php`, or an ad may not be loaded)_

```php
// Places a 728x90 leaderboard ad for all breakpoints but mobile.
$DoubleClick->place_ad('site-leaderboard','728x90',array('desktop','xl','tablet'));

// Places an ad for all breakpoints.
$DoubleClick->place_ad('site-rect','300x250');
```

#### $DoubleClick->place_ad($identifer,$size,$breakpoints)
    
Prints DOM to display an ad at the given breakpoint.

__$identifer__

`String` The DFP identifier for an ad unit (DFP does not require you to create an identifier. If this does not match a value defined in DFP, a network-wide ad will still be requested).

__$size__

`String` A string corresponding to the size ad to load. Format should be width 'x' height.

__$breakpoints__

`Array` (optional) An array of breakpoints (listed by identifier) to display this ad for.

* * *

# Class Members

Global and function stubs for plugin classes.

```
class DoubleClick {

	public $networkCode;
	public $debug = false;
	public $breakpoints = array();
	public $adSlots = array();
	private static $enqueued = false;
	private static $mapping = array();

	public function __construct($networkCode = null)
	public function register_breakpoint($identifier,$args = null)
	public static function enqueue_scripts() 
	private function networkCode()
	public function footer_script()
	private function targeting()
	public function place_ad($identifier,$dimensions,$breakpoints = null)
	public function get_ad_placement($identifier,$dimensions,$breakpoints = null)

}
```

```
class DoubleClickAdSlot {

	public $adCode;
	public $size;
	public $identifier;
	public $breakpoints = null;
	public $targeting = null;
	public $DoubleClickObject;

	public function __construct($identifer,$adCode,$size,$breakpoints = null,$targeting = null) 
	public function breakpointIdentifier()

}
```

```
class DoubleClickBreakpoint {

	public $identifier = '';
	public $minWidth;
	public $maxWidth;
	public $option;

	public function __construct($identifier,$args = null)
	public function js_logic()
	public function get_js_logic()

}
```
