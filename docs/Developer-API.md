# Developer API

A `global $DoubleClick` variable is defined on plugin init, making it possible to define breakpoints
and place ads directly within your theme.

__Quick Function Links:__

 * [$DoubleClick->register_breakpoint($identifer,$args)](#doubleclick-register_breakpointidentiferargs)
 * [$DoubleClick->place_ad($identifer,$size,$breakpoints)](#doubleclick-place_adidentifersizebreakpoints)

* * *

## 1. Define Breakpoints

##### $DoubleClick->register_breakpoint($identifer,$args)

You can make it easier for users to target breakpoints by defining them in `functions.php`

##### Example:

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

##### Paramaters:

__$identifer__

`String` A unique identifier for this breakpoint

__$args__

`Array` An array of properties about the breakpoint. Currently the only keys supported are minWidth and maxWidth.

* * *

## 2. Place Ads

### $DoubleClick->place_ad($identifer,$sizes,$args)

Prints DOM to display an ad at the given breakpoint.

##### Example:

```php

global $DoubleClick;

// simple call:
$DoubleClick->place_ad('my-identifer','300x250');

// more options:
$sizes = array(
		'phone' => '300x50'		// show a medium rectangle for phone and up.
		'tablet' => '728x90'	// show a leaderboard for tablet and up.
		'desktop' => ''			// show no ad for desktop and up.
	);
$args = array(
		'lazyLoad' => false 	// if set to true, the ad will load only once its within view on screen.
	);

$DoubleClick->place_ad('my-identifier',$sizes,$args);
```


##### Paramaters:

__$identifer__

`String` The DFP identifier for an ad unit (DFP does not require you to create an identifier. If this does not match a value defined in DFP, a network-wide ad will still be requested).

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
