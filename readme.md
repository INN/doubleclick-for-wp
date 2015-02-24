# DoubleClick for WordPress

Serve DoubleClick within WordPress. Choose which breakpoints to load a particular ad for.

Uses [coop182's jquery dfp implementation](https://github.com/coop182/jquery.dfp.js).

## Theme Developers

### Define Breakpoints

You can make it easier for users to target breakpoints by defining them in `functions.php`

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

	// Places a 728x90 leaderboard ad for all breakpoints but mobile.
	$DoubleClick->place_ad('site-leaderboard','728x90',array('desktop','xl','tablet'));

	// Places an ad for all breakpoints.
	$DoubleClick->place_ad('site-rect','300x250');

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
