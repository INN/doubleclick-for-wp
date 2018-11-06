# Developer API

A `global $doubleclick` variable is defined on plugin init, making it possible to define breakpoints
and place ads directly within your theme.

__Quick Function Links:__

 * [$doubleclick->register_breakpoint($identifier,$args)](#doubleclick-register_breakpointidentifierargs)
 * [$doubleclick->place_ad($identifier,$size,$breakpoints)](#doubleclick-place_adidentifiersizebreakpoints)

* * *

## 1. Define Breakpoints

##### $doubleclick->register_breakpoint($identifier,$args)

You can make it easier for users to target breakpoints by defining them in `functions.php`

##### Example:

```php
function ad_setup() {

	global $doubleclick;

	// Optionally define the network code directly in functions.php.
	// $doubleclick->network_code = "xxxxxxx";

	/* Define Breakpoints */
	$doubleclick->register_breakpoint('phone', array('min_width'=> 0,'max_width'=>720));
	$doubleclick->register_breakpoint('tablet', array('min_width'=>760,'max_width'=>1040));
	$doubleclick->register_breakpoint('desktop', array('min_width'=>1040,'max_width'=>1220));
	$doubleclick->register_breakpoint('xl', array('min_width'=>1220,'max_width'=>9999));

}
add_action('dfw_setup','ad_setup');
```

##### Paramaters:

__$identifier__

`String` A unique identifier for this breakpoint

__$args__

`Array` An array of properties about the breakpoint. Currently the only keys supported are min_width and max_width.

* * *

## 2. Place Ads

### $doubleclick->place_ad($identifier,$sizes,$args)

Prints DOM to display an ad at the given breakpoint.

##### Example:

```php

global $doubleclick;

// simple call:
$doubleclick->place_ad('my-identifier','300x250');

// more options:
$sizes = array(
		'phone' => '300x50'		// show a medium rectangle for phone and up.
		'tablet' => '728x90'	// show a leaderboard for tablet and up.
		'desktop' => ''			// show no ad for desktop and up.
	);
$args = array(
		'lazyLoad' => false 	// if set to true, the ad will load only once its within view on screen.
	);

$doubleclick->place_ad('my-identifier',$sizes,$args);
```


##### Paramaters:

__$identifier__

`String` The Google Ad Manager identifier for an ad unit (GAM does not require you to create an identifier. If this does not match a value defined in GAM, a network-wide ad will still be requested).

__$sizes__

`Array|String` The size for the ad. Either a string for all breakpoints, or an array of sizes for each breakpoint.

__$args__

`Array` (optional) An array of additional arguments. Values:

 - __lazyLoad__: (true/false) setting this to true and the ad will be loaded only once it's within view on the page. Default is false.

* * *

## 3. Troubleshooting

On any page with DoubleClick ads enabled (namely with DoubleClick js enqueued), load the included developer console to confirm data about advertisement delivery for each placement.

#### Append the URL with the parameter:
```
?google_force_console
```
