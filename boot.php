<?php 
namespace klxm\YFormCalendar;

use rex;
use rex_addon;
use rex_plugin;
use rex_view;
use rex_yform;
use rex_yform_manager_dataset;

$package = rex_addon::get('yform_calendar');
if (rex_plugin::get('yform', 'manager')->isAvailable()) {
    rex_yform::addTemplatePath($package->getPath('ytemplates'));
}

if (rex::isBackend()) {
    rex_view::addCssFile(rex_addon::get('yform_calendar')->getAssetsUrl('rrule.css'));
    rex_view::addJsFile(rex_addon::get('yform_calendar')->getAssetsUrl('rrule.js'));
}
