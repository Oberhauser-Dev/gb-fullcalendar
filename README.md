## GB FullCalendar
- Contributors:      August Oberhauser
- Tags:              block, fullcalendar, react
- Requires at least: 5.3.2
- Tested up to:      5.4.2
- Stable tag:        0.1.0
- Requires PHP:      7.0.0
- License:           GPL-3.0-or-later
- License URI:       https://www.gnu.org/licenses/gpl-3.0.html

GB FullCalendar is a Gutenberg block for displaying events. It's build on the popular WP FullCalendar plugin.

## Description

Thanks to the Gutenberg-Blocks since the end of 2018, it has become incredibly easy to add and layout your blocks in a 
visual way, but still can be used with Shortcodes, too.

### Features

- Month / Week / Day and List views
- Filter by taxonomy, such as category, tag etc.
- Supports custom post types and custom taxonomies
- Integrates seamlessly with [Events Manager](http://wordpress.org/extend/plugins/events-manager/)
- Customize [FullCalendar settings](https://fullcalendar.io/docs)
- Tooltips
- Custom [themes and styles](./docs/Themes-Styles.md)
- Supports IE 11

### Credits

Thanks to [Marcus Sykes](https://profiles.wordpress.org/netweblogic/) for his previous work on WP Fullcalendar, which is partially integrated here.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/gb-fullcalendar` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

## Usage

<b>
    Note: There's no guarantee that GB FullClanedar works simultaneously with WP
    FullCalendar. It's recommended to only enable one plugin at the same time!
</b>

### Gutenberg Block

Simply add the Gutenberg Block <b>GB FullCalendar</b>, which is located under the Widgets section. 
When you select your block, you can adjust the block-settings in the sidebar.

### Shortcode

You are able to use the <code>[fullcalendar]</code> shortcode in one of your posts or pages, too.
We introduced new features in order to allow more customization:

To adjust all the <a href="https://fullcalendar.io/docs">settings of FullCalendar</a> use following rule:
- Convert all Camel-case words to lower-case words separated with an underscore.
- Add the prefix <code>fc_</code>

Example: 
[<code>initialView: "listCustom"</code>](https://fullcalendar.io/docs/initialView) is converted to 
<code>[fullcalendar fc_initial_view="listCustom"]</code>.               

Further you can declare your default [<code>taxonomy terms</code>](https://developer.wordpress.org/themes/basics/categories-tags-custom-taxonomies/#custom-taxonomies) 
by either the term id or the term slug as follows:

```
[fullcalendar 
 category="concert, cinema, 11, theatre" 
 your_custom_taxonomy_slug="your_custom_term_slug, another_custom_term_id"]
```

Note that <code>category</code> is a synonym for the taxonomy <code>event-categories</code>
defined by <a href="https://wp-events-plugin.com/">Events Manager</a>.

### Taxonomies and Terms

You can define your own [taxonomies and terms](./docs/EM-Taxonomies-Terms.md) in order to categorize your events more precisely.

### Themes and Styles

See documentation for [themes and styles](./docs/Themes-Styles.md)

## Frequently Asked Questions (FAQ)

None yet.

## Screenshots

![Desktop](./assets/GB-FullCalendar-desktop.png)
![Mobile & Theme](./assets/GB-FullCalendar-theme-mobil.png)

## Changelog

See [changelog file](./CHANGELOG.md).

## Contribution

You are invited to help in form of Merge-Requests or proposing issues with the expected solutions.

To develop install the packages via `yarn install`. Then start webpack compiling via `yarn start`.
To build a plugin file run `yarn run build` and include the following files in a folder named `gb-fullcalendar` and compress it to a zip file.

```
build/
php/
res/
editor.css
gb-fullcalendar.php
uninstall.php
package.json
style.css
```

The code is written is provided as ESNext, but uses Babel and Webpack to provide compatibility to ancient browsers.

**The plugin is free to use. We are not liable for any damage caused by using the plugin!**
