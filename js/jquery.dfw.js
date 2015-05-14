// 
// A few lines of javascript to help the DoubleClick for WordPress plugin.
// Extends jquery.dfw.min.js with some event listeners specific to us.
//
// 

;(function ( $, window, document, undefined ) {



	// On window scroll check if there are any new ads
	// to load.
	var lazyLoad = function() {

		// all the lazy load ads on the page.
    	var lazyLoaders = $('.dfw-lazy-load:not(.dfw-loaded)');

    	// jQuery object to hold ads that actually need loading.
    	var toLoad = $([]);

    	// window scrolltop.
    	var st = $(document).scrollTop();

    	// window height.
    	var wh = $(window).height();

    	// determind with lazyLoaders needs toLoad.
    	lazyLoaders.each(function () {

    		var top = $(this).offset().top;

    		// how much futher down the page to load 
    		// the ad (so it's at least loading before it's on screen).
    		var buffer = 200;
    		
    		if(top < st + wh + buffer) {
    			toLoad = toLoad.add( $(this) );
    		}

    	});

    	// if we have some ads, then load 'em!
    	if(toLoad.length > 0) {

    		$(toLoad).dfp({
    			"dfpID": dfw.networkCode,
    			"collapseEmptyDivs": false,
    			"sizeMapping": dfw.mappings,
    			"setTargeting": dfw.targeting
    		}).addClass('dfw-loaded');
    	}

	} 

	$(document).ready(function(){

		// Watch for scroll events, and determine whether any ads
		// need lazy loading. Wait a few seconds for images and text to render
		// and units to reach their "true" depth on the page.
		setTimeout( function(){ 
			$(window).scroll( lazyLoad ); 
		}, 3000);
		
	});
    

    function callBack() {
    	alert('hi');
    }


})(jQuery, window, document );
