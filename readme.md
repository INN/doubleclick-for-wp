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


#### Define Settings

Under `Settings > DoubleClick for WordPress` update the fields with your **network code** from DFP, and a place to define **breakpoints** to serve ads to.

#### Widgets

Add widgets to sidebars and configure their settings. __Values:__

 - __Identifier__: The DFP identifier to request.
 - __Width/Height__: Size ad to request (and display) to users.
 - __Show for breakpoints__: If you have breakpoints set up, you can show ads for only specific breakpoints. You should always avoid showing ad units that are not shown at a breakpoint.

#### Targeting

Four targeting criteria are automatically sent to DFP. 

If you'd like more control over where inventory is served, these should be defined in your DFP acccount under [__Inventory/Custom Targeting__](https://support.google.com/dfp_sb/answer/2983838?hl=en).

 - __inURL__: Target a piece of the page path. 

	> eg. targeting the string `news/` would match [example.com/**dvds/**](http://example.com/news/), [example.com/**dvds/**page1](http://example.com/news/page1) and [example.com/**dvds/**page2](http://example.com/news/page2)

 - __URLIs__: Target the entire page path.

	> eg. targeting the string `news/` will **only** match [example.com/**books/**](http://example.com/news/) and not [example.com/**books/page1**](http://example.com/news/page1).

 - __Domain__: Target based on the domain.

	> eg. run different advertising on [staging.example.com](http://staging.example.com) and [example.com](http://example.com).

 - __Query__: Target a ?query var.

	> eg. target the url [example.com/?**movie=12**](http://example.com/news/) with the 
targeting string `p:12`

__Note__: Targeting strings are limited to 40 characters long by DFP. Targeting URLIs or domains longer than that will result in error.

__Screenshot__: This screenshot shows how to configure **URLIs** targeting for urls containing `books/` or `dvds/` or `movies/` under `Inventory > Custom Targeting`.

![Targeting](http://i.imgur.com/pNGHKmx.png)

__Screenshot__: This screenshot targeting criteria is set on a line item to only display on pages containing `books/` or `dvds/` in the url.

![Targeting](http://i.imgur.com/254pAJw.png)

* * *

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
