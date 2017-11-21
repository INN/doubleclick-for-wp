=== DoubleClick for WordPress ===
Contributors: inn_nerds, willhaynes24
Donate link: https://inn.org/donate
Tags: ads, doubleclick, publishers, news
Requires at least: 4.0.0
Tested up to: 4.9
Stable tag: 0.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Serve DoubleClick ads natively in WordPress. Built to make serving and targeting responsive ads easy.

== Description ==

This WordPress plugin gives site administrators an easy way to serve DFP inventory on their WordPress site.

Implementing is simple. Configure your network code and input your identifiers. No need to copy and paste ad codes or header tags — the plugin generates all of this for you.

For more advanced documentation for developers and advanced users see [the official plugin docs](https://github.com/INN/DoubleClick-for-WordPress/blob/master/docs/index.md).


== Installation ==

1. Upload the plugin directory to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Define a network code and optional ad breakpoint information in plugin settings.
4. Add DoubleClick ad widgets to your sidebars.

For more advanced documentation for developers and advanced users see [the official plugin docs](https://github.com/INN/DoubleClick-for-WordPress/tree/master/docs/index.md).


== Changelog ==

= 0.2.1 =

- Widget now includes a default stylesheet:
	- setting `.display-none` to `display: none;` to support `jQuery.dfp.js`' utility styles
	- centering ad units within the ad widgets.
	- creating a `:before` pseudoelement with the text 'Advertisement', to follow DoubleClick recommended practices on labeling ads
- Documentation improvements
- Fix for case where the widget element's closing tag was not output
- Numerous small bugfixes
- Tested up to WordPress 4.6

= 0.1 =

- Initial beta release.
- Add support for displaying different sizes of ad unit based on the size of the viewport

