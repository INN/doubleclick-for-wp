# DoubleClick for Wordpress

A wordpress plugin built for system administrators to manage and serve their ads responsively within the wordpress ecosystem.

Using this plugin it's possible to define ad units for different screen widths. The plugin takes care of the nitty gritty details of loading those slots and creating the display tags.

Pulls ads asynchronously with a single request.

## Setup

In `functions.php`, hook to `dfw_setup_ad_units` and using the global `$DoubleClick` object:

1. Set your network code.
2. Define breakpoints for your site using `$DoubleClick->register_breakpoint`.
3. Register each of your ad units using `$DoubleClick->register_adslot`.

An example:

	function my_ad_setup() {
	
		global $DoubleClick;
		
		$DoubleClick->networkCode = "xxxxxxxx";
		$DoubleClick->register_breakpoint('phone',array('minWidth'=>	0,'maxWidth'=>720));
		$DoubleClick->register_breakpoint('ipad',	array('minWidth'=>720,'maxWidth'=>1040));
		$DoubleClick->register_breakpoint('desktop',	array('minWidth'=>1040,'maxWidth'=>9999));
		$DoubleClick->register_adslot(
			'leaderboard',
			'wjh/leaderboard/728x90',
			array("728","90"),
			array('ipad','desktop')
			);
		
		$DoubleClick->register_adslot(
			'small-rect',
			'wjh/smallrect/300x250',
			array("300","250"),
			'phone'
			);
	}
	add_action('dfw_setup_ad_units','my_ad_setup');

Then, use `$DoubleClick->display_ad($identifier)` in your template files to display that ad. `display_ad` will surround the ad tag in a javascript if statement that will only call that ad if it's applicable for the current device breakpoint.

## Function reference

### $DoubleClick->register_breakpoint

##### Usage

    $DoubleClick->register_breakpoint($identifer,$args)
    
Define a new breakpoint. This will output javascript to only load the ad if the target screen is between the min and max width.

    

##### Paramaters

###### $identifer
`String` A unique identifier for this breakpoint

###### $args
`Array` An array of properties about the breakpoint. Currently the only keys supported are minWidth and maxWidth.

### $DoubleClick->register_adslot

##### Usage

    $DoubleClick->register_adslot($identifier,$adCode,$sizes,$breakpoints = null)
    
##### Parameters

###### $identifier
`String` A unique identifier for this breakpoint

###### $adcode
`String` The adcode ad defined in DFP

###### $sizes
`Array` If only one size, this is an array where [0] is width and [1] is height. If more than one size, this is an array of arrays (as described in the previous sentence).

###### $breakpoints
`Optional` `Mixed` Either a string or array of strings. Use this to specify which breakpoint(s) this ad should be loaded for.




