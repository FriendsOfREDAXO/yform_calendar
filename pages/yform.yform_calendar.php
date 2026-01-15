<?php

/**
 * YForm Calendar Setup
 */

/** @var rex_addon $this */

echo rex_view::title($this->i18n('yform_calendar_setup_title'));

$package = rex_addon::get('yform_calendar');
$content = '';

// Handle Installation/Deinstallation
$action = rex_request('action', 'string', '');
$success = '';
$error = '';

if ('install' === $action) {
    try {
        rex_yform_manager_table::deleteCache();
        
        $contentFile = rex_file::get(rex_path::addon('yform_calendar', 'tableset/tableset.json'));
        if (!is_string($contentFile) || '' === $contentFile) {
            throw new Exception(rex_i18n::msg('yform_calendar_error') . ': Tableset-Datei nicht gefunden');
        }
        
        rex_yform_manager_table_api::importTablesets($contentFile);
        rex_yform_manager_table::deleteCache();
        rex_delete_cache();
        
        $success = rex_i18n::msg('yform_calendar_install_success');
    } catch (Exception $e) {
        $error = rex_i18n::msg('yform_calendar_error') . ': ' . $e->getMessage();
    }
}

if ('uninstall' === $action) {
    try {
        rex_yform_manager_table::deleteCache();
        
        // Lösche die YForm-Tabelle durch Datenbankoperation
        $sql = rex_sql::factory();
        $sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTable('yform_calendar') . '`');
        
        // Lösche auch die YForm Konfiguration
        $configSql = rex_sql::factory();
        $configSql->setQuery('DELETE FROM `' . rex::getTable('yform_table') . '` WHERE `table_name` = ?', [rex::getTable('yform_calendar')]);
        
        rex_yform_manager_table::deleteCache();
        rex_delete_cache();
        
        $success = rex_i18n::msg('yform_calendar_uninstall_success');
    } catch (Exception $e) {
        $error = rex_i18n::msg('yform_calendar_error') . ': ' . $e->getMessage();
    }
}

// Check if tableset is installed
$tablesetInstalled = false;
try {
    $sql = rex_sql::factory();
    $sql->setQuery('DESCRIBE `' . rex::getTable('yform_calendar') . '`');
    $tablesetInstalled = true;
} catch (Exception $e) {
    $tablesetInstalled = false;
}

// Messages
if ($success) {
    $content .= rex_view::success($success);
}
if ($error) {
    $content .= rex_view::error($error);
}

// Status-Card
ob_start();
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= rex_i18n::msg('yform_calendar_tableset_status') ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <p><?= rex_i18n::msg('yform_calendar_setup_description') ?></p>
                <div class="alert alert-<?= $tablesetInstalled ? 'success' : 'warning' ?>" role="alert">
                    <strong><?= $tablesetInstalled ? '✓' : '✗' ?></strong>
                    <?= rex_i18n::msg($tablesetInstalled ? 'yform_calendar_tableset_installed' : 'yform_calendar_tableset_not_installed') ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="btn-group btn-group-vertical btn-block">
                    <?php if ($tablesetInstalled): ?>
                        <a href="<?= rex_url::currentBackendPage(['action' => 'install']) ?>" 
                           class="btn btn-warning" 
                           onclick="return confirm('<?= rex_i18n::msg('yform_calendar_reinstall_tableset') ?>?')">
                            <i class="fa fa-refresh"></i> <?= rex_i18n::msg('yform_calendar_reinstall_tableset') ?>
                        </a>
                        <a href="<?= rex_url::currentBackendPage(['action' => 'uninstall']) ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('<?= rex_i18n::msg('yform_calendar_uninstall_tableset') ?>?')">
                            <i class="fa fa-trash"></i> <?= rex_i18n::msg('yform_calendar_uninstall_tableset') ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= rex_url::currentBackendPage(['action' => 'install']) ?>" 
                           class="btn btn-success">
                            <i class="fa fa-download"></i> <?= rex_i18n::msg('yform_calendar_install_tableset') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content .= ob_get_clean();

// Direct output wie bei yrewrite_scheme
echo $content;
