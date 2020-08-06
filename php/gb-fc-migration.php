<?php

/**
 * Get the wp_options of WpFc and convert them to GbFc. Use GbFc defaults as fallback.
 *
 * • wpfc_available_views   - converted to gbfc_enabledViews
 * • wpfc_default_type      - converted to gbfc_postType
 * • wpfc_defaultView       - converted to gbfc_initialView
 * • wpfc_limit             - handled by FullCalendar (needed by EM, default is set to 1000)
 * • wpfc_limit_txt         - handled by FullCalendar
 * • wpfc_post_taxonomies   - converted to gbfc_postTaxonomies
 * • wpfc_qtips             - converted to gbfc_tooltips
 * • wpfc_qtips_at          - converted to gbfc_tooltipPlacement
 * • wpfc_qtips_image       - converted to gbfc_tooltipImage
 * • wpfc_qtips_image_h     - converted to gbfc_tooltipImageMaxWidth
 * • wpfc_qtips_image_w     - converted to gbfc_tooltipImageMaxHeight
 * • wpfc_qtips_my          - handled by Material-UI Tooltip
 * • wpfc_qtips_rounded     - not implemented (compare with Material-UI guidelines)
 * • wpfc_qtips_shadow      - not implemented (compare with Material-UI guidelines)
 * • wpfc_qtips_style       - not implemented (should be handled via css variables or Material-UI theme)
 * • wpfc_scripts_limit     - not implemented
 * • wpfc_theme             - handled by GbFc (replaced by gbfc_themeSystem)
 * • wpfc_theme_css         - handled by GbFc (replaced by css variables)
 * • wpfc_timeFormat        - handled by FullCalendar
 * • wpfc_version           - conversion not reasonable
 *
 * @param $fallbackOptions
 * @return array - The WpFc options.
 */
function getWpFcDefaults($fallbackOptions)
{
    $wpfcOptions = [];

    $migrateOption = function ($gbOptionName, $wpOptionName) use ($fallbackOptions, &$wpfcOptions) {
        $wpfcOptions[$gbOptionName] = get_option($wpOptionName, $fallbackOptions[$gbOptionName]);
    };

    $useDefaultOption = function ($gbOptionName) use ($fallbackOptions, &$wpfcOptions) {
        $wpfcOptions[$gbOptionName] = $fallbackOptions[$gbOptionName];
    };

    $migrateOption('gbfc_postType', 'wpfc_default_type');
    $migrateOption('gbfc_postTaxonomies', 'wpfc_post_taxonomies');
    $useDefaultOption('gbfc_viewsDurationDays');
    $wpfcOptions['gbfc_enabledViews'] = wpfcEnabledViewsToGbfcEnabledViews(get_option('wpfc_available_views'), $fallbackOptions['gbfc_enabledViews']);
    $wpfcOptions['gbfc_initialView'] = wpfcViewToGbfcView(get_option('wpfc_defaultView'), $fallbackOptions['gbfc_initialView']);
    $useDefaultOption('gbfc_themeSystem');
    $useDefaultOption('gbfc_htmlFontSize');
    $migrateOption('gbfc_tooltips', 'wpfc_qtips');
    //add_option('wpfc_qtips_style', get_option('dbem_emfc_qtips_style', 'light'));
    $wpfcOptions['gbfc_tooltipPlacement'] = qTipPlacementToTooltipPlacement(get_option('wpfc_qtips_at'), $fallbackOptions['gbfc_tooltipPlacement']);
    //add_option('wpfc_qtips_rounded', get_option('dbem_emfc_qtips_rounded', false));
    $migrateOption('gbfc_tooltipImage', 'wpfc_qtips_image');
    $migrateOption('gbfc_tooltipImageMaxWidth', 'wpfc_qtips_image_w');
    $migrateOption('gbfc_tooltipImageMaxHeight', 'wpfc_qtips_image_h');

    $wpfc_limit = get_option('wpfc_limit');
    if (empty($wpfc_limit) || $wpfc_limit < 1000) {
        // Event limit option should be disabled by default and let fullcalendar handle it.
        // Keep name wpfc_limit as it is referred in events-manager/em-wpfc.php -> if not set default is 3,
        // which is not enough to list all events.
        update_option('wpfc_limit', 1000);
    }

    //update version
    update_option('gbfc_version', $fallbackOptions['gbfc_version']);

    return $wpfcOptions;
}

function wpfcViewToGbfcView($wpfcView, $default = 'listCustom')
{
    switch ($wpfcView) {
        case 'month':
            return 'dayGridMonth';
        case 'basicWeek':
            return 'dayGridWeek';
        case 'basicDay':
            return 'dayGridDay';
        case 'agendaWeek':
            return 'timeGridWeek';
        case 'agendaDay':
            return 'timeGridDay';
        default:
            return $default;
    }
}

function wpfcEnabledViewsToGbfcEnabledViews($wpfc_availableViews, $default = [])
{
    if (empty($wpfc_availableViews)) {
        return $default;
    }
    $wpfc_enabledViews = [];
    foreach ($wpfc_availableViews as $wpfcView) {
        $enabledView = wpfcViewToGbfcView($wpfcView, 'listCustom');
        if (!in_array($enabledView, $wpfc_enabledViews)) {
            $wpfc_enabledViews[] = $enabledView;
        }
    }
    return $wpfc_enabledViews;
}

function qTipPlacementToTooltipPlacement($wpfcTooltip, $default = 'top')
{
    if (!empty($wpfcTooltip)) {
        $qTipPlacement = explode(' ', $wpfcTooltip);
        if (count($qTipPlacement) > 1) {
            switch ($qTipPlacement[1]) {
                case 'left':
                case 'top:':
                    $qTipPlacement[1] = 'start';
                    break;
                case 'right':
                case 'bottom:':
                    $qTipPlacement[1] = 'end';
                    break;
                case 'center:':
                default:
                    unset($qTipPlacement[1]);
            }
            return implode('-', $qTipPlacement);
        }
    }
    return $default;
}
