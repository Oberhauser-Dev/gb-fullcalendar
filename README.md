## GB FullCalendar
- Contributors:      August Oberhauser
- Tags:              block, fullcalendar, react
- Requires at least: 5.3.2
- Tested up to:      5.4.2
- Stable tag:        0.1.0
- Requires PHP:      7.0.0
- License:           GPL-3.0-or-later
- License URI:       https://www.gnu.org/licenses/gpl-3.0.html

GB FullCalendar is a branch of the popular WP FullCalendar plugin written as a Gutenberg block.

## Description

Thanks to the Gutenberg-Blocks since the end of 2018, it has become incredibly easy to add and layout your blocks in a 
visual way, but still can be used with Short-Codes, too.

### Features

- Month/Week/Day/List views
- Filter by taxonomy, such as category, tag etc.
- Integrates seamlessly with [Events Manager](http://wordpress.org/extend/plugins/events-manager/)

### Credits

Thanks to [Marcus Sykes](https://profiles.wordpress.org/netweblogic/) for his previous work on WP Fullcalendar, which was partially in here.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/gb-fullcalendar` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress


## Frequently Asked Questions (FAQ)

None yet.

## Screenshots

![Desktop](./assets/GB-FullCalendar-desktop.png)
![Mobile & Theme](./assets/GB-FullCalendar-theme-mobil.png)

## Changelog

See [changelog file](./CHANGELOG.md).

## Contribution

You are invited to help in form of Merge-Requests or proposing issues with the expected solutions.

To develop install the packages via `npm i`. Then start webpack compiling via `npm start`.
To build a plugin file run `npm run build` and include the following files in a folder named `gb-fullcalendar` and compress it to a zip file.

```
build/
editor.css
gb-fc-admin.php
gb-fullcalendar.php
package.json
style.css
```

**The plugin is freely to use. We are not liable for any damage caused by using the plugin!**
