<?php

class GbFcAjax
{
    /**
     * AJAX endpoint for fullcalendar events.
     * Echos a JSON object
     */
    public static function ajax_events()
    {
        global $post;
        //maybe excessive, but easy sanitization of start/end query params
        $_REQUEST['start'] = $_POST['start'] = date('Y-m-d', strtotime($_REQUEST['start']));
        $_REQUEST['end'] = $_POST['end'] = date('Y-m-d', strtotime($_REQUEST['end']));
        $args = array('scope' => array($_REQUEST['start'], $_REQUEST['end']), 'owner' => false, 'status' => 1, 'order' => 'ASC', 'orderby' => 'post_date', 'full' => 1);
        //get post type and taxonomies, determine if we're filtering by taxonomy
        $post_types = get_post_types(array('public' => true), 'names');
        $post_type = !empty($_REQUEST['type']) && in_array($_REQUEST['type'], $post_types) ? $_REQUEST['type'] : get_option('wpfc_default_type');

        $args['post_type'] = $post_type;
        $args['post_status'] = 'publish'; //only show published status
        $args['posts_per_page'] = -1;
        if ($args['post_type'] == 'attachment') $args['post_status'] = 'inherit';
        $args['tax_query'] = array();
        foreach (get_object_taxonomies($post_type) as $taxonomy_name) {
            if (!empty($_REQUEST[$taxonomy_name])) {
                $args['tax_query'][] = array(
                    'taxonomy' => $taxonomy_name,
                    'field' => 'id',
                    'terms' => $_REQUEST[$taxonomy_name]
                );
            }
        }
        //initiate vars
        $args = apply_filters('wpfc_fullcalendar_args', $args);
        $limit = get_option('wpfc_limit', 3);
        $items = array();
        $item_dates_more = array();
        $item_date_counts = array();

        //Create our own loop here and tamper with the where sql for date ranges, as per http://codex.wordpress.org/Class_Reference/WP_Query#Time_Parameters
        function wpfc_temp_filter_where($where = '')
        {
            global $wpdb;
            $where .= $wpdb->prepare(" AND post_date >= %s AND post_date < %s", $_REQUEST['start'], $_REQUEST['end']);
            return $where;
        }

        add_filter('posts_where', 'wpfc_temp_filter_where');
        do_action('wpfc_before_wp_query');
        $the_query = new WP_Query($args);
        remove_filter('posts_where', 'wpfc_temp_filter_where');
        //loop through each post and slot them into the array of posts to return to browser
        while ($the_query->have_posts()) {
            $the_query->the_post();
            $color = "#a8d144";
            $post_date = substr($post->post_date, 0, 10);
            $post_timestamp = strtotime($post->post_date);
            if (empty($item_date_counts[$post_date]) || $item_date_counts[$post_date] < $limit) {
                $title = $post->post_title;
                $item = array("title" => $title, "color" => $color, "start" => date('Y-m-d\TH:i:s', $post_timestamp), "end" => date('Y-m-d\TH:i:s', $post_timestamp), "url" => get_permalink($post->ID), 'post_id' => $post->ID);
                $items[] = apply_filters('wpfc_ajax_post', $item, $post);
                $item_date_counts[$post_date] = (!empty($item_date_counts[$post_date])) ? $item_date_counts[$post_date] + 1 : 1;
            } elseif (empty($item_dates_more[$post_date])) {
                $item_dates_more[$post_date] = 1;
                $day_ending = $post_date . "T23:59:59";
                //TODO archives not necesarrily working
                $more_array = array("title" => get_option('wpfc_limit_txt', 'more ...'), "color" => get_option('wpfc_limit_color', '#fbbe30'), "start" => $day_ending, 'post_id' => 0, 'className' => 'wpfc-more');
                global $wp_rewrite;
                $archive_url = get_post_type_archive_link($post_type);
                if (!empty($archive_url) || $post_type == 'post') { //posts do have archives
                    $archive_url = trailingslashit($archive_url);
                    $archive_url .= $wp_rewrite->using_permalinks() ? date('Y/m/', $post_timestamp) : '?m=' . date('Ym', $post_timestamp);
                    $more_array['url'] = $archive_url;
                }
                $items[] = apply_filters('wpfc_ajax_more', $more_array, $post_date);
            }
        }
        echo json_encode(apply_filters('wpfc_ajax', $items));
        die(); //normally we'd wp_reset_postdata();
    }

    /**
     * Fix list view missing weeks
     * @param $args array the EM_Calendar args.
     */
    public static function filter_ajax_em_event_args($args)
    {
        //get the month/year between the start/end dates and feed these to EM
        $dateTimeStart = new DateTime($args['start']);
        $dateTimeStart->setDate($dateTimeStart->format('Y'), $dateTimeStart->format('m'), 1);
        $dateTimeEnd = new DateTime($args['end']);
        $dateTimeInterval = $dateTimeStart->diff($dateTimeEnd);
        $args['month'] = $dateTimeStart->format('n');
        $args['year'] = $dateTimeStart->format('Y');
        // FIXME at least 6 weeks, else JSON parse error
        $args['number_of_weeks'] = max(intval(ceil($dateTimeInterval->days / 7.0)), 6);

        return $args;
    }

    /**
     * AJAX endpoint for tooltip content for a calendar item.
     * Echos a JSON object
     */
    public static function ajax_tooltip_content()
    {
        $content = new stdClass();
        if (!empty($_REQUEST['post_id'])) {
            $post = get_post($_REQUEST['post_id']);
            if ($post->post_type == 'attachment') {
                $content->imageUrl = wp_get_attachment_image_url($post->ID, 'thumbnail');
            } else {
                $content->excerpt = (!empty($post)) ? $post->post_excerpt : '';
                if (get_option('gbfc_tooltipImage', true)) {
                    $post_image_url = get_the_post_thumbnail_url($post->ID);
                    if (!empty($post_image_url)) {
                        $content->imageUrl = $post_image_url;
                        $content->imageDimensions = [
                            intval(get_option('gbfc_tooltipImageMaxWidth', 75)),
                            intval(get_option('gbfc_tooltipImageMaxHeight', 75))
                        ];
                    }
                }
            }
        }
        // Apply content filter of Events Manager
        $content->excerpt = apply_filters('wpfc_qtip_content', $content->excerpt);
        $content = apply_filters('gbfc_tooltip_content', $content);
        echo json_encode($content);
        die();
    }
}
