<?php

/**
 * @var rex_addon $this
 * @psalm-scope-this rex_addon
 */

rex_yform_manager_table::deleteCache();

$content = rex_file::get(rex_path::addon('yform_calendar', 'tableset/tableset.json'));
if (is_string($content) && '' !== $content) {
    rex_yform_manager_table_api::importTablesets($content);
}

rex_delete_cache();
