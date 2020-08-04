<?php
// If called, assume we're installing/updated
// Existing options will not be updated

$fcOptions = json_decode(file_get_contents(__DIR__ . "/../res/FcOptions.json"));

add_option('gbfc_postType', get_option('wpfc_default_type', 'event'));
add_option('gbfc_postTaxonomies', get_option('wpfc_post_taxonomies', []));

$gbfc_enabledViews = [];
$gbfc_viewsDurationDays = [];
foreach ($fcOptions->views as $view) {
    if (isset($view->enabled) && $view->enabled) {
        $gbfc_enabledViews[] = $view->value;
    }
    if (isset($view->customDurationDays)) {
        $gbfc_viewsDurationDays[$view->value] = $view->customDurationDays;
    }
}
add_option('gbfc_viewsDurationDays', $gbfc_viewsDurationDays);

$wpfc_availableViews = get_option('wpfc_available_views', null);
if (!empty($wpfc_availableViews)) {
    // Overwrite default with wpfc available views
    $gbfc_enabledViews = [];
    foreach ($wpfc_availableViews as $wpfcView) {
        $gbfc_enabledViews[] = wpfcViewToGbfcView($wpfcView);
    }
}
add_option('gbfc_enabledViews', $gbfc_enabledViews);

$wpfc_defaultView = get_option('wpfc_defaultView', '');
add_option('gbfc_initialView', wpfcViewToGbfcView($wpfc_defaultView));

add_option('gbfc_themeSystem', 'standard'); // else: 'bootstrap'
add_option('gbfc_htmlFontSize', 16);

$wpfc_limit = get_option('wpfc_limit');
if ($wpfc_limit && $wpfc_limit < 1000) {
    // Event limit option should be disabled by default and let fullcalendar handle it.
    // Keep name wpfc_limit as it is referred in events-manager/em-wpfc.php -> if not set default is 3.
    update_option('wpfc_limit', 1000);
} else {
    add_option('wpfc_limit', 1000);
}

add_option('gbfc_tooltips', get_option('wpfc_qtips', true));
//add_option('wpfc_qtips_style', get_option('dbem_emfc_qtips_style', 'light'));
$qTipPlacement = explode(' ', get_option('wpfc_qtips_at', 'top center'));
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
            $tooltipPlacement = implode('-', $qTipPlacement);
    }
} else {
    $tooltipPlacement = 'top';
}
add_option('gbfc_tooltipPlacement', $tooltipPlacement);
//add_option('wpfc_qtips_rounded', get_option('dbem_emfc_qtips_rounded', false));
add_option('gbfc_tooltipImage', get_option('wpfc_qtips_image', true));
add_option('gbfc_tooltipImageMaxWidth', get_option('wpfc_qtips_image_w', 75));
add_option('gbfc_tooltipImageMaxHeight', get_option('wpfc_qtips_image_h', 75));

$package = json_decode(file_get_contents(__DIR__ . '/../package.json'), true);

//update version
update_option('gbfc_version', $package['version']);

function wpfcViewToGbfcView($wpfcView)
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
            return 'listCustom';
    }
}
