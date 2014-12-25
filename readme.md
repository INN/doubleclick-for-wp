# DoubleClick for Wordpress

A wordpress plugin built for system administrators to manage and serve their ads responsively within the wordpress ecosystem.

Using this plugin it's possible to define ad units for different screen widths. The plugin takes care of the nitty gritty details of loading those slots and creating the display tags.

Pulls ads asynchronously with a single request. Uses [coop182's jquery dfp implementation](https://github.com/coop182/jquery.dfp.js).

## Usage

In `functions.php`, hook to `dfw_setup` and using the global `$DoubleClick` object and define your network code and register your breakpoints.

	function ad_setup() {
	
		global $DoubleClick;
	
		$DoubleClick->networkCode = "xxxxxxx";
			
		/* breakpoints */
		$DoubleClick->register_breakpoint('phone', array('minWidth'=>	0,'maxWidth'=>720));
		$DoubleClick->register_breakpoint('tablet', array('minWidth'=>760,'maxWidth'=>1040));
		$DoubleClick->register_breakpoint('desktop', array('minWidth'=>1040,'maxWidth'=>1220));
		$DoubleClick->register_breakpoint('xl', array('minWidth'=>1220,'maxWidth'=>9999));
	
	}
	add_action('dfw_setup','ad_setup');

Then when you want to place an ad call `$DoubleClick->place_ad`

	// Places a 728x90 leaderboard ad for all breakpoints but mobile.
	$DoubleClick->place_ad('bh:leaderboard','728x90',array('desktop','xl','tablet'));

	// Places an ad for all breakpoints.
	$DoubleClick->place_ad('bh:leaderboard','300x250');


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

