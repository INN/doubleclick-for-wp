# Release checklist

See also https://github.com/INN/docs/blob/master/projects/wordpress-plugins/release.sh.md

- [ ] Are we on the latest version of `jquery.dfp.js`? See [related docs for updating that dependency](./dev-update-dependencies.md)

## Testing before release:

Plugin settings:

- [ ] Does the settings page work?
- [ ] Do settings saved in a previous version of this plugin work?

Frontend tests:

- [ ] `window.dfw` contains`dfpID` and `network_code`, which are the same value, which is that of `get_option('dfw_network_code')`
- [ ] a filter modifying `dfw_js_data` has its modifications output in the frontend `window.dfw` object

Widget tests

- [ ] A widget's breakpoints are reflected in the widget's corresponding `mapping*` entry in `window.dfw`
- [ ] A widget outputs its HTML, and the widget's settings are added to `window.dfw`
- [ ] Do settings saved in a previous version of this plugin work?

Gutenberg tests

- [ ] the block, when inserted, has controls
- [ ] the block renders on the frontend as a widget does
- [ ] on a site with Gutenberg not installed, the plugin functions
- [ ] on a 4.9 site with Gutenberg installed, the plugin functions
- [ ] on a 5.0 site, the plugin functions
- [ ] Do settings saved in a previous version of this plugin work?

Plugin metadata:

- [ ] Does the minimum PHP required version need to be updated in `readme.txt`?
- [ ] Does the WordPress "Tested up to" version need to be updated? Check with [the phpcs PHPCompatibility rules](https://github.com/PHPCompatibility/PHPCompatibility).
