<?php 
$package = rex_addon::get('yform_calendar');
if (rex_plugin::get('yform', 'manager')->isAvailable()) {
    rex_yform::addTemplatePath($package->getPath('ytemplates'));
}

if (rex::isBackend()) {
    rex_view::addCssFile($this->getAssetsUrl('rrule.css'));
}
