<?php

// see events-manager/em-posts.php
if( !defined('EM_POST_TYPE_EVENT') ) define('EM_POST_TYPE_EVENT','event');
if( !defined('EM_POST_TYPE_LOCATION') ) define('EM_POST_TYPE_LOCATION','location');
if( !defined('EM_TAXONOMY_CATEGORY') ) define('EM_TAXONOMY_CATEGORY','event-categories');
if( !defined('EM_TAXONOMY_TAG') ) define('EM_TAXONOMY_TAG','event-tags');

/**
 * Set most options of FullCalendar.
 * See https://fullcalendar.io/docs.
 *
 * @return array the FC options to be localized.
 */
function getFullCalendarArgs()
{
    $gbfc_enabledViews = get_option('gbfc_enabledViews', []);
    $gbfc_viewsDurationDays = get_option('gbfc_viewsDurationDays', []);
    // Header Toolbar
    $headerToolbar = new stdClass();
    $headerToolbar->left = 'prevYear,prev,today,next,nextYear';
    $headerToolbar->center = 'title';
    $headerToolbar->right = implode(',', $gbfc_enabledViews);
    $headerToolbar = apply_filters('gbfc_calendar_header_vars', $headerToolbar);

    // Custom views
    $fcOptions = json_decode(file_get_contents(__DIR__ . "/../res/FcOptions.json"));
    $fcViews = $fcOptions->views;
    $views = new stdClass();
    foreach ($fcViews as $viewOption) {
        // Always allow all custom views, as local settings may have it as default.
        // But only the ones, who have a duration day count.
        $viewKey = $viewOption->value;
        if (array_key_exists($viewKey, $gbfc_viewsDurationDays)) {
            $view = new stdClass();
            $view->type = $viewOption->type;
            $view->duration = new stdClass();
            $view->duration->days = intval($gbfc_viewsDurationDays[$viewKey]);
            $views->$viewKey = $view;
        }
    }

    return [
        'themeSystem' => get_option('gbfc_themeSystem', 'standard'),
        'firstDay' => get_option('start_of_week'),
        'editable' => false,
        'initialView' => get_option('gbfc_initialView', 'dayGridMonth'), // Can be overwritten in shortcode
        'weekends' => get_option('gbfc_weekends', true) ? true : false,
        'headerToolbar' => $headerToolbar,
        'locale' => strtolower(str_replace('_', '-', get_locale())),
        'eventDisplay' => 'block', // See https://fullcalendar.io/docs/v5/eventDisplay
        // See https://fullcalendar.io/docs/v5/event-popover
        'dayMaxEventRows' => true,
        'dayMaxEvents' => true,
        'views' => $views,
        // TODO Can be removed, but causes much overhead, as whole month of start date is fetched from EM.
        'showNonCurrentDates' => false,
        'contentHeight' => 'auto', // See: https://stackoverflow.com/questions/63906327/react-fullcalendar-error-cannot-read-property-seg-of-undefined

        // eventBackgroundColor: 'white',
        // eventColor: 'white',
        // eventTextColor: 'black',

        // TODO handle / documentate / implement following options properly:
        //'google_calendar_api_key' => get_option('gbfc_google_calendar_api_key', ''),
        //'google_calendar_ids' => preg_split('/\s+/', get_option('gbfc_google_calendar_ids', '')),
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
    $post_type = get_option('gbfc_postType', 'event');
    //figure out what taxonomies to show
    $gbfc_postTaxonomies = get_option('gbfc_postTaxonomies');
    $search_taxonomies = array_keys($gbfc_postTaxonomies[$post_type] ?? array());
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

    $gbfc_htmlFontSize = floatval(get_option('gbfc_htmlFontSize', 16));
    $gbfc_tooltips = boolval(get_option('gbfc_tooltips', false));
    $gbfc_tooltipPlacement = get_option('gbfc_tooltipPlacement', 'top');
    $gbfc_eventPostType = get_option('gbfc_postType', 'event');

    return [
        'ajaxUrl' => admin_url('admin-ajax.php', $schema),
        'eventAction' => 'WP_FullCalendar',
        'eventPostType' => $gbfc_eventPostType,
        'tooltipAction' => 'gbfc_tooltip_content',
        'taxonomyNodes' => $taxonomyNodes,
        'htmlFontSize' => $gbfc_htmlFontSize,
        'tooltips' => $gbfc_tooltips,
        'tooltipPlacement' => $gbfc_tooltipPlacement,
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
function calendar_via_shortcode($args = [])
{
    if (empty($args)) {
        // Avoid empty string
        $args = [];
    }
    //figure out what taxonomies to show
    $gbFcLocal = new stdClass();
    $gbFcLocal->fc = new stdClass();
    $gbFcLocal->fcExtra = new stdClass();
    $gbFcLocal->fcExtra->initialTaxonomies = [];
    /**
     * Filter parameters for EventsManager
     * https://wp-events-plugin.com/documentation/event-search-attributes/
     */
    $gbFcLocal->fcExtra->emSearchAttributes = [];
    foreach ($args as $arg => $value) {
        if (substr($arg, 0, 3) === 'fc_') {
            // Convert fullcalendar specific parameters
            $termIdentifier = str_replace('_', '', lcfirst(ucwords(substr($arg, 3), '_')));
            $gbFcLocal->fc->$termIdentifier = $value;
        } else {
            switch (strtolower($arg)) {
                case 'category':
                case 'categories':
                case 'event-category':
                    $arg = EM_TAXONOMY_CATEGORY;
                    break;
                case 'tag':
                case 'tags':
                    $arg = EM_TAXONOMY_TAG;
                    break;
                case 'event-location':
                case 'event-locations':
                    $arg = EM_POST_TYPE_LOCATION;
                    break;
            }
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
            } else {
                // If not a taxonomy, then send as events manager filter
                $gbFcLocal->fcExtra->emSearchAttributes[$arg] = $value;
            }
        }
    }
    // Create unique instance id from local gbFc prefs.
    $gbFcLocalJSON = json_encode($gbFcLocal);
    $instanceId = hash('crc32', $gbFcLocalJSON);

    ob_start();
    ?>
    <div id="gbfc-wrapper-<?php echo $instanceId ?>" data-value="<?php echo $instanceId ?>" class="gbfc-wrapper">

    </div>
    <script>
		var GbFcLocal_<?php echo $instanceId?> = <?php echo $gbFcLocalJSON ?>
    </script>
    <?php
    do_action('wpfc_calendar_displayed', $args);
    return ob_get_clean();
}
