<?php

class GbFcActions
{
    public static function getDefaultOptions()
    {
        $fcOptions = json_decode(file_get_contents(__DIR__ . "/../res/FcOptions.json"));
        $package = json_decode(file_get_contents(__DIR__ . '/../package.json'), true);

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

        return [
            'gbfc_postType' => 'event',
            'gbfc_postTaxonomies' => [],
            'gbfc_enabledViews' => $gbfc_enabledViews,
            'gbfc_viewsDurationDays' => $gbfc_viewsDurationDays,
            'gbfc_initialView' => 'listCustom',
            'gbfc_themeSystem' => 'standard', // else: 'bootstrap'
            'gbfc_htmlFontSize' => 16,
            'gbfc_tooltips' => true,
            'gbfc_tooltipPlacement' => 'top',
            'gbfc_tooltipImage' => true,
            'gbfc_tooltipImageMaxWidth' => 75,
            'gbfc_tooltipImageMaxHeight' => 75,
            'gbfc_version' => $package['version'],
        ];
    }

    /**
     * If called, assume we're installing/updated.
     * Existing options will not be updated.
     */
    public static function initOrMigrateOptions()
    {
        require_once('gb-fc-migration.php');

        $options = getWpFcDefaults(static::getDefaultOptions());
        foreach ($options as $option => $val) {
            add_option($option, $val);
        }
    }

    public static function resetToWpFcOptions()
    {
        require_once('gb-fc-migration.php');

        $options = getWpFcDefaults(static::getDefaultOptions());
        foreach ($options as $option => $val) {
            update_option($option, $val);
        }
    }

    public static function resetOptions()
    {
        $options = static::getDefaultOptions();
        foreach ($options as $option => $val) {
            update_option($option, $val);
        }
    }

    public static function deleteOptions()
    {
        $options = static::getDefaultOptions();
        foreach ($options as $option => $val) {
            delete_option($option);
        }
    }
}
