<?php

$options = [
    'gbfc_postType',
    'gbfc_postTaxonomies',
    'gbfc_enabledViews',
    'gbfc_viewsDurationDays',
    'gbfc_initialView',
    'gbfc_themeSystem',
    'gbfc_htmlFontSize',
    'gbfc_tooltips',
    'gbfc_tooltipPlacement',
    'gbfc_tooltipImage',
    'gbfc_tooltipImageMaxWidth',
    'gbfc_tooltipImageMaxHeight',
    'gbfc_version',
];
foreach ($options as $option) {
    delete_option($option);
}
