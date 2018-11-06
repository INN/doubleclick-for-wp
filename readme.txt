=== Super Cool Ad Manager Plugin ===
Contributors: innlabs, willhaynes24
Donate link: https://inn.org/donate
Tags: ads, doubleclick, publishers, news
Requires at least: 4.0.0
Requires PHP: 5.3
Tested up to: 5.0-beta3
Stable tag: 0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Serve Google Ad Manager ads natively in WordPress. Built to make serving and targeting responsive ads easy.

== Description ==

This WordPress plugin gives site administrators an easy way to serve DFP inventory on their WordPress site.

Implementing is simple. Configure your network code and input your identifiers. No need to copy and paste ad codes or header tags — the plugin generates all of this for you.

For more advanced documentation for developers and advanced users see [the official plugin docs](https://github.com/INN/DoubleClick-for-WordPress/blob/master/docs/readme.md).

To contribute to this plugin, please [visit it on GitHub](https://github.com/INN/doubleclick-for-wp/issues), or send us an email at [support@inn.org](mailto:support@inn.org).


== Installation ==

1. Upload the plugin directory to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Define a network code and optional ad breakpoint information in plugin settings.
4. Add ad widgets to your sidebars.

For more advanced documentation for developers and advanced users see [the official plugin docs](https://github.com/INN/DoubleClick-for-WordPress/tree/master/docs/readme.md).


== Changelog ==

= 0.3 =

Thanks to WordPress.org user [onlineforbusiness](https://wordpress.org/support/users/onlineforbusiness/) and GitHub users [dbeniaminov](https://github.com/dbeniaminov) and [tienvooracht](https://github.com/tienvooracht) for their contributions to this release.

New features:

- Adds a Gutenberg Block for manual insertion of ads in posts. [Pull request #82](https://github.com/INN/doubleclick-for-wp/pull/82) for [issue #70](https://github.com/INN/doubleclick-for-wp/issues/70).
- Adds a PHP filter `'dfw_js_data'`, to allow modification of the `window.dfw` object before it's output on the page. Refactors widgets and blocks to draw all details needed from `window.dfw` instead of separately-enqueued JSON blobs. This should allow you to `setRequestNonPersonalizedAds. [PR #81](https://github.com/INN/doubleclick-for-wp/pull/81) for [issue #63](https://github.com/INN/doubleclick-for-wp/issues/63), with thanks to GitHub user [tienvooracht](https://github.com/tienvooracht) for the idea.
- Tested up to WordPress 5.0-beta3

Changes and fixes:

- Rebrands from "DoubleClick for WordPress" to "Super Cool Ad Manager Plugin", because [Google merged DoubleClick for Publishers into Google Ad Manager](https://www.blog.google/products/admanager/introducing-google-ad-manager/).
- **Potentially-breaking changes**: Renames a number of varibles from `camelCase` to `snake_case` in [pull request #46](https://github.com/INN/doubleclick-for-wp/pull/46) for Wordpress.com VIP compatibility. This includes renaming the global variable `$DoubleClick` to `$doubleclick`, and many of its properties from `$DoubleClick->camelCase` to `$doubleclick->snake_case`. If you modify this plugin's features via filters or actions, or if you call this plugin's functions and methods directly from your code, 
- Updates to `jquery.dfp.js` version 2.4.2, adding `setCentering` support. [PR #67](https://github.com/INN/doubleclick-for-wp/pull/67) for [issue #66](https://github.com/INN/doubleclick-for-wp/issues/66)
- Adds "Category" targeting on category archive. [PR #72](https://github.com/INN/doubleclick-for-wp/pull/72) for [issue #61](https://github.com/INN/doubleclick-for-wp/issues/61).
- Adds "Tag" targeting on tag archive. [PR #74](https://github.com/INN/doubleclick-for-wp/pull/74) for [issue #29](https://github.com/INN/doubleclick-for-wp/issues/29).
- Fixes a number of PHP warnings and errors, including [issue #8](https://github.com/INN/doubleclick-for-wp/issues/8) and [issue #37](https://github.com/INN/doubleclick-for-wp/issues/37) in [PR #76](https://github.com/INN/doubleclick-for-wp/pull/76) and [issue #31](https://github.com/INN/doubleclick-for-wp/issues/31) in [PR #80](https://github.com/INN/doubleclick-for-wp/pull/80/).
- Fixes a targeting problem that occurred when wp_footer ran outside The Loop. ([PR #87](https://github.com/INN/doubleclick-for-wp/pull/87) for [this support topic](https://wordpress.org/support/topic/categories-tags-bug-and-solution/).)

Documentation updates:

- Adds "Ad unit" label to widget settings for the "Identifier" setting, to match Google's language. [PR #73](https://github.com/INN/doubleclick-for-wp/pull/73) for [issue #26](https://github.com/INN/doubleclick-for-wp/issues/26).
- Adds GitHub Pull Request template and Contributing guidelines files.
- Adds a plugin text domain: `dfw`. ([PR #76](https://github.com/INN/doubleclick-for-wp/pull/76))
- Adds the GPL2 license to the plugin header; this plugin has been GPL2 since 2015 but that wasn't marked in a WordPress-accessible way. ([PR #76](https://github.com/INN/doubleclick-for-wp/pull/76))
- Moves the documentation index file from `docs/index.md` to `docs/readme.md` in the GitHub repository, so that the Markdown will display to all who visit [the docs directory](https://github.com/INN/doubleclick-for-wp/tree/master/docs). ([PR #80](https://github.com/INN/doubleclick-for-wp/pull/80))
- Moves plugin functions and files around within the plugin; see [PR #72](https://github.com/INN/doubleclick-for-wp/pull/81) for details.
- Removes 'single' page targeting from post-type archives and from static front pages. [PR #72](https://github.com/INN/doubleclick-for-wp/pull/72) for [issue #61](https://github.com/INN/doubleclick-for-wp/issues/61), thanks to GitHub user [dbeniaminov](https://github.com/dbeniaminov).

= 0.2.1 =

- Widget now includes a default stylesheet:
	- setting `.display-none` to `display: none;` to support `jQuery.dfp.js`' utility styles
	- centering ad units within the ad widgets.
	- creating a `:before` pseudoelement with the text 'Advertisement', to follow Google Ad Manager recommended practices on labeling ads
- Documentation improvements
- Fix for case where the widget element's closing tag was not output
- Numerous small bugfixes
- Tested up to WordPress 4.6

= 0.1 =

- Initial beta release.
- Add support for displaying different sizes of ad unit based on the size of the viewport

== Upgrade Notice ==

= 0.3 =

This update adds support for direct insertion of ads in posts via Gutenberg, filter-based modificaiton of ad options, and resolves a number of bugs.

This update renames the global variable `$DoubleClick` to `$doubleclick`, along with many of that class's properties. If you modify this plugin's functionality via filters or custom functionality, check your code before deploying to production.
