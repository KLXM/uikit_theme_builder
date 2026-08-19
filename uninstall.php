<?php

/**
 * UIKit Theme Builder - Deinstallation
 */

// Tabelle entfernen
$sql = rex_sql::factory();
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTable('theme_builder_extra') . '`');

// Verzeichnisse entfernen (optional - auskommentiert um Themes zu behalten)
/*
$directories = [
    rex_path::addonData('uikit_theme_builder'),
    rex_path::addonAssets('uikit_theme_builder')
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        rex_dir::delete($dir);
    }
}
*/