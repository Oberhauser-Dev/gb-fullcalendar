<?php
/**
 * Plugin Name:     GB FullCalendar
 * Plugin URI:      https://github.com/oberhauser-dev/gb-fullcalendar/
 * Description:     gb-fullcalendar — is an updated alternative to the wp-fullcalendar as a Gutenberg plugin.
 * Version:         0.1.0
 * Author:          August Oberhauser
 * Author URI:      https://www.oberhauser.dev/
 * License:         GPL3+
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:     create-block
 *
 * @package         create-block
 */

include_once(ABSPATH . 'wp-admin/includes/plugin.php'); // load method for front-end

if (!is_plugin_active('wp-fullcalendar/wp-fullcalendar.php')) {
    // Define WPFC-Version to enable EM-wpfc API (ajax);
    if (!defined('WPFC_VERSION'))
        define('WPFC_VERSION', '2.1.0');
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
            'You need to run `npm start` or `npm run build` for the "create-block/gb-fullcalendar" block first.'
        );
    }
    $index_js = 'build/index.js';
    $script_asset = require($script_asset_path);
    wp_register_script(
        'create-block-gb-fullcalendar-block-editor',
        plugins_url($index_js, __FILE__),
        $script_asset['dependencies'],
        $script_asset['version']
    );

    // TODO may only load, if block is present, if possible.
    $client_js = 'build/client.js';
    wp_register_script(
        'create-block-gb-fullcalendar-block',
        plugins_url($client_js, __FILE__),
        $script_asset['dependencies'],
        $script_asset['version']
    );

    $editor_css = 'editor.css';
    wp_register_style(
        'create-block-gb-fullcalendar-block-editor',
        plugins_url($editor_css, __FILE__),
        array(),
        filemtime("$dir/$editor_css")
    );

    $style_css = 'style.css';
    wp_register_style(
        'create-block-gb-fullcalendar-block',
        plugins_url($style_css, __FILE__),
        array(),
        filemtime("$dir/$style_css")
    );

    register_block_type('create-block/gb-fullcalendar', array(
        'editor_script' => 'create-block-gb-fullcalendar-block-editor',
        'script' => 'create-block-gb-fullcalendar-block',
        'editor_style' => 'create-block-gb-fullcalendar-block-editor',
        'style' => 'create-block-gb-fullcalendar-block',
    ));

    if (is_admin()) {
        // Call always as admin, otherwise block cannot be added dynamically.
        localize_script();
        include('gb-fc-admin.php');
    } else {
        // Add shortcode
        add_shortcode('fullcalendar', 'calendar_via_shortcode');
    }
}

add_action('init', 'create_block_gb_fullcalendar_block_init');

/**
 * Only localize js variables if block is present in front-end.
 */
function create_block_gb_fullcalendar_block_enqueue_script()
{
    // Always enqueue script, as shortcode need localized script, too.
    // TODO may fix that  only load, when needed.
//    if (has_block('create-block/gb-fullcalendar')) {
    localize_script();
//    }
}

add_action('wp_enqueue_scripts', 'create_block_gb_fullcalendar_block_enqueue_script');

/**
 * Localize javascript variables for gb-fullcalendar.
 */
function localize_script()
{
    wp_localize_script(
        'create-block-gb-fullcalendar-block',
        'GbFcGlobal', // Array containing dynamic data for a JS Global.
        [
            'pluginDirPath' => plugin_dir_path(__DIR__),
            'pluginDirUrl' => plugin_dir_url(__DIR__),
            // Add more data here that you want to access from `cgbGlobal` object.
            'fc' => getFullCalendarArgs(),
            'fcExtra' => getFullCalendarExtraArgs(),
        ]
    );
}

/**
 * Set most options of FullCalendar.
 * See https://fullcalendar.io/docs.
 *
 * @return array the FC options to be localized.
 */
function getFullCalendarArgs()
{
    // Header Toolbar
    $headerToolbar = new stdClass();
    $headerToolbar->left = 'prevYear,prev,today,next,nextYear';
    $headerToolbar->center = 'title';
    $headerToolbar->right = implode(',', get_option('gbfc_available_views', array('dayGridMonth', 'timeGridWeek', 'timeGridDay', 'listCustom')));
    $headerToolbar = apply_filters('gbfc_calendar_header_vars', $headerToolbar);

    // Custom views
    $gbfc_available_views_duration = get_option('gbfc_available_views_duration', array('dayGridCustom' => 7, 'timeGridCustom' => 1, 'listCustom' => 30));
    $viewsTypeMap = [
        'dayGridCustom' => 'dayGrid',
        'timeGridCustom' => 'timeGrid',
        'listCustom' => 'list',
    ];
    $views = new stdClass();
    foreach ($gbfc_available_views_duration as $customViewKey => $duration) {
        $view = new stdClass();
        $view->type = $viewsTypeMap[$customViewKey];
        $view->duration = new stdClass();
        $view->duration->days = intval($duration);
        $views->$customViewKey = $view;
    }

    return [
        'themeSystem' => get_option('gbfc_themeSystem', 'standard'), // else: 'bootstrap'
        'firstDay' => get_option('start_of_week'),
        'editable' => false,
        'initialView' => get_option('gbfc_defaultView', 'dayGridMonth'), // Can be overwritten in shortcode
        'weekends' => get_option('gbfc_weekends', true) ? true : false,
        'headerToolbar' => $headerToolbar,
        'locale' => strtolower(str_replace('_', '-', get_locale())),
        'eventDisplay' => 'block', // See https://fullcalendar.io/docs/v5/eventDisplay
        // See https://fullcalendar.io/docs/v5/event-popover
        'dayMaxEventRows' => true,
        'dayMaxEvents' => true,
        'views' => $views,
        'showNonCurrentDates' => false, // TODO Cannot handle dates outside of range in events manager

        // eventBackgroundColor: 'white',
        // eventColor: 'white',
        // eventTextColor: 'black',

//        'gbfc_theme' => get_option('gbfc_theme_css') ? true : false,
//        'gbfc_theme_system' => get_option('gbfc_theme_system'),
//        'gbfc_limit' => get_option('gbfc_limit', 3),
//        'gbfc_limit_txt' => get_option('gbfc_limit_txt', 'more ...'),
        //'google_calendar_api_key' => get_option('gbfc_google_calendar_api_key', ''),
        //'google_calendar_ids' => preg_split('/\s+/', get_option('gbfc_google_calendar_ids', '')),
//        'timeFormat' => get_option('gbfc_timeFormat', 'h(:mm)t'),
//        'gbfc_qtips' => get_option('gbfc_qtips', true) == true,
//        'gbfc_dialog' => get_option('gbfc_dialog', true) == true,
    ];
}

/**
 * Set custom FullCalendar options. Needs a counterpart in "src/client.js"
 *
 * @return array the custom FC options to be localized.
 */
function getFullCalendarExtraArgs()
{
    $schema = is_ssl() ? 'https' : 'http';

    $args = []; // TODO fetch from settings
    $post_type = get_option('gbfc_default_type', 'event');
    //figure out what taxonomies to show
    $gbfc_post_taxonomies = get_option('gbfc_post_taxonomies');
    $search_taxonomies = array_keys($gbfc_post_taxonomies[$post_type]) ?? array();
    if (!empty($args['taxonomies'])) {
        //we accept taxonomies in arguments
        $search_taxonomies = explode(',', $args['taxonomies']);
        array_walk($search_taxonomies, 'trim');
        unset($args['taxonomies']);
    }
    //go through each post type taxonomy and display if told to
    $taxonomyNodes = [];
    foreach (get_object_taxonomies($post_type) as $taxonomy_name) {
        $taxonomy = get_taxonomy($taxonomy_name);
        if (in_array($taxonomy_name, $search_taxonomies)) {
            $isCategory = $taxonomy_name === EM_TAXONOMY_CATEGORY;
            // Default value
            $default_value = $args[$taxonomy_name] ?? 0;
            if ($isCategory && !empty($args['category'])) {
                $default_value = $args['category'];
            }
            if (!is_numeric($default_value)) {
                $default_value = get_term_by('slug', $default_value, $taxonomy_name)->term_id;
            }

            // See: https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
            $taxonomy_args = array(
                'hide_empty' => false, // Do not hide in order to not limit filter settings and hide them in front-end only.
                'hierarchical' => true,
                'taxonomy' => $taxonomy_name,
            );
            $taxonomy_args = apply_filters('gb_fc_taxonomy_args', $taxonomy_args, $taxonomy);
            $terms = get_terms($taxonomy_args);
            if (!$taxonomy_args['hide_empty'] || !empty($terms)) {
                // Add em category colors
                if ($isCategory) {
                    foreach ($terms as $term) {
                        $term->color = getEmTermColor($term->term_id);
                    }
                }

                // Custom display object for client
                $display_args = array_merge($taxonomy_args, array(
                    'echo' => true,
                    'class' => 'gbfc-taxonomy ' . $taxonomy_name,
                    'selected' => $default_value,
                    'name' => $taxonomy->labels->name,
                    'slug' => $taxonomy->name,
                    'show_option_all' => $taxonomy->labels->all_items,
                    'items' => $terms,
                    'is_empty' => count(get_terms($taxonomy_name, array('hide_empty' => true))) === 0 // taxonomy has only terms with no related posts
                ));
                $display_args = apply_filters('gb_fc_taxonomy_display_args', $display_args, $taxonomy);
                $taxonomyNodes[] = $display_args;
            }
        }
    }

    $gbfc_htmlFontSize = get_option('gbfc_htmlFontSize', 16);

    return [
        'ajaxUrl' => admin_url('admin-ajax.php', $schema),
        'taxonomyNodes' => $taxonomyNodes,
        'htmlFontSize' => $gbfc_htmlFontSize,
    ];
}

function getEmTermColor($term_id)
{
    // @see: plugins/events-manager/em-wpfc.php#start_el
    global $wpdb;
    if (defined('EM_META_TABLE')) {
        $color = $wpdb->get_var('SELECT meta_value FROM ' . EM_META_TABLE . " WHERE object_id='{$term_id}' AND meta_key='category-bgcolor' LIMIT 1");
    }
    return (!empty($color)) ? $color : '#a8d144';
}

/**
 * Returns the calendar HTML setup and primes the js to load at wp_footer
 * @param array $args
 * @return string
 */
function calendar_via_shortcode($args = array())
{
    //figure out what taxonomies to show
    $instanceId = hash('crc32', 'some settings');
    $gbFcLocal = new stdClass();
    $gbFcLocal->fc = new stdClass();
    $gbFcLocal->fcExtra = new stdClass();
    $gbFcLocal->fcExtra->initialTaxonomies = [];
    foreach ($args as $arg => $value) {
        if (substr($arg, 0, 3) === 'fc_') {
            // Convert fullcalendar specific parameters
            $termIdentifier = str_replace('_', '', lcfirst(ucwords(substr($arg, 3), '_')));
            $gbFcLocal->fc->$termIdentifier = $value;
        } else {
            $taxonomy = get_taxonomy($arg);
            if ($taxonomy) {
                $search_terms = explode(',', $value);
                array_walk($search_terms, 'trim');
                foreach ($search_terms as $key => $termIdentifier) {
                    if (!is_numeric($termIdentifier)) {
                        // Convert term slug to its id
                        $term = get_term_by('slug', $termIdentifier, $arg);
                        if ($term) {
                            $search_terms[$key] = $term->term_id;
                        } else {
                            unset($search_terms[$key]);
                        }
                    }
                }
                $gbFcLocal->fcExtra->initialTaxonomies[$arg] = $search_terms;
            }
        }
    }

    ob_start();
    ?>
    <div id="gbfc-wrapper-<?php echo $instanceId ?>" data-value="<?php echo $instanceId ?>" class="gbfc-wrapper">

    </div>
    <script>
		var GbFcLocal_<?php echo $instanceId?> = <?php echo json_encode($gbFcLocal) ?>
    </script>
    <?php
    do_action('wpfc_calendar_displayed', $args);
    return ob_get_clean();
}
