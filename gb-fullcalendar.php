<?php
/**
 * Plugin Name:     GB FullCalendar
 * Plugin URI:      https://github.com/oberhauser-dev/gb-fullcalendar/
 * Description:     GB FullCalendar is a Gutenberg block for displaying events.
 * Version:         0.2.2
 * Requires at least: 5.3.2
 * Tested up to:    6.9
 * Requires PHP:    7.0.0
 * Author:          August Oberhauser
 * Author URI:      https://www.oberhauser.dev/
 * License:         GPL3+
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:     gb-fullcalendar
 *
 * @package         oberhauser-dev
 */

include_once(ABSPATH . 'wp-admin/includes/plugin.php'); // load method for front-end
require_once 'php/gb-fc.php';
include_once 'php/gb-fc-ajax.php';
include_once 'php/gb-fc-actions.php';

if (!is_plugin_active('wp-fullcalendar/wp-fullcalendar.php')) {
    // Define WPFC-Version to enable EM-wpfc API (ajax);
    if (!defined('WPFC_VERSION'))
        define('WPFC_VERSION', '2.2.0');
}

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function create_block_gb_fullcalendar_block_init()
{
    $dir = dirname(__FILE__);

    $script_asset_path = "$dir/build/index.asset.php";
    if (!file_exists($script_asset_path)) {
        throw new Error(
            'You need to run `npm start` or `npm run build` for the "oberhauser-dev/gb-fullcalendar" block first.'
        );
    }
    $index_js = 'build/index.js';
    $script_asset = require($script_asset_path);
    wp_register_script(
        'gb-fullcalendar-block-editor',
        plugins_url($index_js, __FILE__),
        $script_asset['dependencies'],
        $script_asset['version']
    );

    $editor_css = 'build/index.css';
    wp_register_style(
        'gb-fullcalendar-block-editor',
        plugins_url($editor_css, __FILE__),
        array(),
        filemtime("$dir/$editor_css")
    );

    // Replaced by view.css
    /*$style_css = 'build/style-index.css';
    wp_register_style(
        'gb-fullcalendar-block',
        plugins_url($style_css, __FILE__),
        array(),
        filemtime("$dir/$style_css")
    );*/

    $client_js = 'build/view.js';
    wp_register_script(
        'gb-fullcalendar-block-client',
        plugins_url($client_js, __FILE__),
        $script_asset['dependencies'],
        $script_asset['version']
    );

    $client_css = 'build/view.css';
    wp_register_style(
        'gb-fullcalendar-block-client',
        plugins_url($client_css, __FILE__),
        array(),
        filemtime("$dir/$client_css")
    );

    localize_script();

    if (is_admin()) {
        // Call always as admin, otherwise block cannot be added dynamically.
        include_once('php/gb-fc-admin.php');
    }

    /**
     * Create ajax endpoints.
     * https://codex.wordpress.org/Plugin_API/Action_Reference/wp_ajax_(action)
     */
    // TODO some time rename "WP_FullCalendar" to "gbfc_events"
    //overrides the ajax calls for event data
    if (defined('DOING_AJAX') && DOING_AJAX && !empty($_REQUEST['type'])) { //only needed during ajax requests anyway
        if ($_REQUEST['type'] === EM_POST_TYPE_EVENT) {
            add_filter('wpfc_fullcalendar_args', ['GbFcAjax', 'filter_ajax_em_event_args']);
        } else {
            add_action('wp_ajax_WP_FullCalendar', ['GbFcAjax', 'ajax_events']);
            add_action('wp_ajax_nopriv_WP_FullCalendar', ['GbFcAjax', 'ajax_events']);
        }
    }

    add_action('wp_ajax_gbfc_tooltip_content', ['GbFcAjax', 'ajax_tooltip_content']);
    add_action('wp_ajax_nopriv_gbfc_tooltip_content', ['GbFcAjax', 'ajax_tooltip_content']);

    // Register block
    register_block_type('oberhauser-dev/gb-fullcalendar', array(
        'editor_script' => 'gb-fullcalendar-block-editor',
        'editor_style' => 'gb-fullcalendar-block-editor',
        'script' => 'gb-fullcalendar-block-client',
        'style' => 'gb-fullcalendar-block-client',
    ));

    // Register shortcode
    add_shortcode('fullcalendar', 'call_shortcode');
}

add_action('init', 'create_block_gb_fullcalendar_block_init');

// action links (e.g. Settings)
function gbfc_settings_link($links)
{
    array_unshift($links, '<a href="' . admin_url('options-general.php?page=gb-fullcalendar') . '">' . __('Settings', 'gb-fullcalendar') . '</a>');

    // Add remove wipe data option.
    $plugin_data = get_plugin_data(__FILE__);
    $url = wp_nonce_url(admin_url('admin-post.php?action=gbfc_uninstall'), 'gbfc_uninstall');
    $links[] = '<span class="delete"><a href="' . $url
        . '" onclick="return confirm(\'Are you sure you want to uninstall ' . $plugin_data['Name']
        . '? All preferences will be removed!\')">Uninstall</a></span>';
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'gbfc_settings_link', 10, 1);

/**
 * Admin post action hook, without the need of specifying own endpoint / handler.
 * https://codex.wordpress.org/Plugin_API/Action_Reference/admin_post_(action)
 */
function gbfc_admin_uninstall()
{
    check_admin_referer('gbfc_uninstall');
    $plugins = [plugin_basename(__FILE__)];
    deactivate_plugins($plugins);
    GbFcActions::deleteOptions();
    delete_plugins($plugins);
    wp_redirect($_SERVER['HTTP_REFERER']);
    exit();
}

add_action('admin_post_gbfc_uninstall', 'gbfc_admin_uninstall');

function gbfc_admin_reset()
{
    check_admin_referer('gbfc_reset');
    GbFcActions::resetOptions();
    wp_redirect($_SERVER['HTTP_REFERER']);
    exit();
}

add_action('admin_post_gbfc_reset', 'gbfc_admin_reset');

function gbfc_admin_resetToWpFc()
{
    check_admin_referer('gbfc_resetToWpFc');
    GbFcActions::resetToWpFcOptions();
    wp_redirect($_SERVER['HTTP_REFERER']);
    exit();
}

add_action('admin_post_gbfc_resetToWpFc', 'gbfc_admin_resetToWpFc');

function call_shortcode($args = [])
{
    // Only add script, when shortcode is used
    wp_enqueue_script('gb-fullcalendar-block-client');
    wp_enqueue_style('gb-fullcalendar-block-client');
    return calendar_via_shortcode($args);
}

/**
 * Localize javascript variables for gb-fullcalendar.
 */
function localize_script()
{
    wp_localize_script(
        'gb-fullcalendar-block-client',
        'GbFcGlobal', // Array containing dynamic data for a JS Global.
        [
            'pluginDirPath' => plugin_dir_path(__FILE__),
            'pluginDirUrl' => plugin_dir_url(__FILE__),
            // Add more data here that you want to access from `cgbGlobal` object.
            'fc' => getFullCalendarArgs(),
            'fcExtra' => getFullCalendarExtraArgs(),
        ]
    );
}
