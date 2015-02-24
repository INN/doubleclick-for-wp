# DoubleClick for WordPress

Serve DoubleClick ads in WordPress. Built to make serving responsive ads easy.

Uses [coop182's jquery dfp implementation](https://github.com/coop182/jquery.dfp.js).

* * *

__Table of Contents__

 - [__Site Administrators__](#site-administrators)
 	- [__Define Settings__](#define-settings) _(Network code and breakpoints)_
 	- [__Add a widget__](#widgets)
 	- [__Targeting__](#targeting)
 - [__Theme Developers__](#theme-developers)
	- [__Define Breakpoints__](#define-breakpoints) _(Define breakpoings in `functions.php`)_
	- [__Place Ads__](#place-ads) _(Place ads directly in template files)_
 - [__License__](#license)

* * *

## Site Administrators

### Define Settings

_coming soon..._

### Widgets

_coming soon..._

### Targeting

_coming soon..._

## Theme Developers

A `global $DoubleClick` variable is defined on plugin init, making it possible to define breakpoints
and place ads directly within your theme.

### Define Breakpoints

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

###### $identifer
`String` A unique identifier for this breakpoint

###### $args
`Array` An array of properties about the breakpoint. Currently the only keys supported are minWidth and maxWidth.

### Place Ads

If you'd like to hard code ad placement into a theme, you can use the built in 
`$DoubleClick->place_ad()` function to print the DOM to include an ad.

Ensure a network code is defined in `functions.php`, or an ad may not be loaded.

```php
// Places a 728x90 leaderboard ad for all breakpoints but mobile.
$DoubleClick->place_ad('site-leaderboard','728x90',array('desktop','xl','tablet'));

// Places an ad for all breakpoints.
$DoubleClick->place_ad('site-rect','300x250');
```

##### $DoubleClick->place_ad($identifer,$size,$breakpoints)
    
Prints DOM to display an ad at the given breakpoint.

###### $identifer

`String` The DFP identifier for an ad unit (DFP does not require you to create an identifier. If this does not match a value defined in DFP, a network-wide ad will still be requested).

###### $size

`String` A string corresponding to the size ad to load. Format should be `YYxZZ` (width by height).

###### $breakpoints

`Array` An array of breakpoints (listed by identifier) to display this ad for.

## License

GPLv2
