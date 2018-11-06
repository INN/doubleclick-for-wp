# Developer instructions

## Updating jquery.dfp.js

Sometimes [jquery.dfp.js](https://github.com/coop182/jquery.dfp.js/releases) releases updates. Because this plugin uses [`bower`](https://bower.io/) to manage that dependency, you'll need to:

1. Find the most-recent release of `jquery.dfp.js` on [the releases list](https://github.com/coop182/jquery.dfp.js/releases)
2. Update the corresponding version number in `bower.json` in this plugin
3. Run `bower update jquery.dfp.js` from the same directory as `bower.json`.

## Releasing the plugin

This plugin has used `release.sh` for releasing. See the checklist at https://github.com/INN/docs/blob/master/projects/wordpress-plugins/release.sh.md
