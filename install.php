<?php

/**
 * UIKit Theme Builder - Installation
 */

// Installiere Datenbanktabelle für Extra Styles mit rex_sql_table
rex_sql_table::get(rex::getTable('theme_builder_extra'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('slug', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('type', 'varchar(50)', false, 'background', null, 'card, section, background, border'))
    ->ensureColumn(new rex_sql_column('color', 'varchar(50)', false, '#ffffff', null, 'Hex oder RGBA'))
    ->ensureColumn(new rex_sql_column('backdrop_blur', 'int(3)', true, '0', null, 'Backdrop Blur in px, 0 = deaktiviert'))
    ->ensureColumn(new rex_sql_column('text_color', 'varchar(7)', true))
    ->ensureColumn(new rex_sql_column('link_color', 'varchar(7)', true))
    ->ensureColumn(new rex_sql_column('border_color', 'varchar(7)', true))
    ->ensureColumn(new rex_sql_column('border_width', 'int(2)', true, '1'))
    ->ensureColumn(new rex_sql_column('border_radius', 'varchar(20)', true))
    ->ensureColumn(new rex_sql_column('is_light', 'tinyint(1)', true, '0'))
    ->ensureColumn(new rex_sql_column('priority', 'int(10)', true, '100'))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)', true, '1'))
    ->ensureColumn(new rex_sql_column('compile_themes', 'text', true, null, null, 'JSON Array der Theme-Slugs'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureIndex(new rex_sql_index('slug', ['slug'], rex_sql_index::UNIQUE))
    ->ensure();

// Installiere Datenbanktabelle für Style-Sets
rex_sql_table::get(rex::getTable('uikit_style_sets'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('slug', 'varchar(255)', false, '', null, 'Eindeutiger Slug des Style-Sets'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)', false, '', null, 'Name des Style-Sets'))
    ->ensureColumn(new rex_sql_column('description', 'text', true, null, null, 'Beschreibung des Style-Sets'))
    ->ensureColumn(new rex_sql_column('styles_data', 'longtext', false, '[]', null, 'JSON Array der Styles'))
    ->ensureColumn(new rex_sql_column('created', 'datetime'))
    ->ensureColumn(new rex_sql_column('updated', 'datetime', true))
    ->ensureColumn(new rex_sql_column('is_active', 'tinyint(1)', false, '1', null, 'Style-Set aktiv/inaktiv'))
    ->ensureIndex(new rex_sql_index('slug', ['slug'], rex_sql_index::UNIQUE))
    ->ensure();

// Installiere Domain-Theme-Zuordnung Tabelle
rex_sql_table::get(rex::getTable('uikit_theme_domains'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('domain_id', 'int(11)', false, null, null, 'YRewrite Domain ID'))
    ->ensureColumn(new rex_sql_column('theme_name', 'varchar(255)', false, null, null, 'Theme Name'))
    ->ensureColumn(new rex_sql_column('enable_transparent', 'tinyint(1)', false, '1', null, 'Transparent/None Card-Stile aktivieren'))
    ->ensureColumn(new rex_sql_column('enable_light_dark', 'tinyint(1)', false, '1', null, 'Light/Dark Text-Stile aktivieren'))
    ->ensureColumn(new rex_sql_column('enable_bg_utilities', 'tinyint(1)', false, '1', null, 'Background Utility-Stile aktivieren'))
    ->ensureColumn(new rex_sql_column('created_date', 'datetime'))
    ->ensureColumn(new rex_sql_column('updated_date', 'datetime'))
    ->ensureIndex(new rex_sql_index('unique_domain', ['domain_id'], rex_sql_index::UNIQUE))
    ->ensureIndex(new rex_sql_index('theme_name', ['theme_name']))
    ->ensure();

// Installiere Theme-Farben-Cache Tabelle
rex_sql_table::get(rex::getTable('uikit_theme_colors'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('theme_name', 'varchar(255)', false, null, null, 'Theme Name'))
    ->ensureColumn(new rex_sql_column('color_type', 'varchar(50)', false, null, null, 'primary, secondary, default, muted, danger, warning, success'))
    ->ensureColumn(new rex_sql_column('color_value', 'varchar(50)', false, null, null, 'Hex, RGB oder RGBA'))
    ->ensureColumn(new rex_sql_column('color_label', 'varchar(255)', true, null, null, 'Beschreibung der Farbe'))
    ->ensureColumn(new rex_sql_column('ui_class', 'varchar(255)', true, null, null, 'UIKit CSS-Klasse (uk-card-primary, etc.)'))
    ->ensureColumn(new rex_sql_column('updated_date', 'datetime'))
    ->ensureIndex(new rex_sql_index('unique_theme_color', ['theme_name', 'color_type'], rex_sql_index::UNIQUE))
    ->ensureIndex(new rex_sql_index('theme_name', ['theme_name']))
    ->ensure();

// Verzeichnisse erstellen
$directories = [
    rex_path::addonData('uikit_theme_builder'),
    rex_path::addonData('uikit_theme_builder', 'themes'),
    rex_path::addonData('uikit_theme_builder', 'themes/saved'),
    rex_path::addonData('uikit_theme_builder', 'themes/compiled'),
    rex_path::addonData('uikit_theme_builder', 'themes/temp'),
    rex_path::addonData('uikit_theme_builder', 'themes/google_fonts'), // Google Fonts Cache
    rex_path::addonData('uikit_theme_builder', 'fonts'), // Lokale Google Fonts
    rex_path::addonData('uikit_theme_builder', 'icons'), // Custom Icons
    rex_path::addonData('uikit_theme_builder', 'icons/custom'), // Custom Icons SVG-Dateien
    rex_path::assets('addons/uikit_theme_builder/fonts') // Öffentliche Google Fonts
];

foreach ($directories as $dir) {
    if (!rex_dir::create($dir)) {
        throw new rex_functional_exception('Verzeichnis konnte nicht erstellt werden: ' . $dir);
    }
}

// UIKit ist bereits im AddOn enthalten - kein Copy aus Root nötig

// Extended Icons nach public/assets/uikit/ kopieren falls nicht vorhanden
$extendedIconsSource = rex_path::addonAssets('uikit_theme_builder', 'uikit/uikit-icons-extended.min.js');
$extendedIconsTarget = rex_path::assets('uikit/uikit-icons-extended.min.js');

if (!file_exists($extendedIconsTarget) && file_exists($extendedIconsSource)) {
    // Zielverzeichnis erstellen falls nötig
    $targetDir = dirname($extendedIconsTarget);
    if (!is_dir($targetDir)) {
        rex_dir::create($targetDir);
    }
    
    // Icons-Datei kopieren
    rex_file::copy($extendedIconsSource, $extendedIconsTarget);
}

// Standard Theme erstellen
$defaultTheme = [
    'name' => 'uikit_default',
    'created' => date('Y-m-d H:i:s'),
    'modified' => time(),
    'version' => '1.0.0',
    'data' => [
        'colors' => [
            // REDAXO Farbwelt aus be_style
            'global-color' => '#324050',                    // $color-a-dark (Text)
            'global-emphasis-color' => '#283542',           // $color-a-darker (Emphasis)
            'global-muted-color' => '#9ca5b2',             // $color-a (Muted)
            'global-link-color' => '#4b9ad9',              // $color-b (Links/Primary)
            'global-link-hover-color' => '#3a82c4',        // Dunklerer Blue
            'global-primary-background' => '#4b9ad9',       // $color-b (Primary)
            'global-secondary-background' => '#324050',     // $color-a-dark (Secondary)
            'global-success-background' => '#5bb585',       // $color-d (Green)
            'global-warning-background' => '#cfb550',       // Yellow
            'global-danger-background' => '#d9534f',        // Red
            'global-inverse-color' => '#ffffff',            // White
            'global-background' => '#f3f6fb',              // $color-a-lighter (Body BG)
            'global-border' => '#dfe3e9'                   // $color-a-light (Borders)
        ],
        'typography' => [
            'global-font-family' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
            'global-font-size' => '16px',
            'global-line-height' => '1.5',
            'base-heading-font-family' => 'inherit',
            'base-heading-font-weight' => 'normal',
            'base-heading-text-transform' => 'none',
            'global-small-font-size' => '0.875rem',
            'global-medium-font-size' => '1.25rem',
            'global-large-font-size' => '1.5rem',
            'global-xlarge-font-size' => '2rem',
            'global-2xlarge-font-size' => '2.625rem'
        ],
        'breakpoints' => [
            'breakpoint-small' => '640px',
            'breakpoint-medium' => '960px',
            'breakpoint-large' => '1200px',
            'breakpoint-xlarge' => '1600px'
        ],
        'borders' => [
            'global-border-width' => '1px',
            'border-rounded' => '4px'
        ],
        'containers' => [
            'container-max-width' => '1200px',
            'container-xsmall-max-width' => '750px',
            'container-small-max-width' => '960px',
            'container-large-max-width' => '1400px',
            'container-xlarge-max-width' => '1600px'
        ],
        'navbar' => [
            'navbar-background' => 'rgba(75, 154, 217, 0.95)',     // $color-b mit Transparenz
            'navbar-nav-item-height' => '80px',
            'navbar-nav-item-color' => '#ffffff',
            'navbar-nav-item-font-size' => '14px',
            'navbar-nav-item-font-family' => 'inherit',
            'navbar-nav-item-hover-color' => '#ffffff',
            'navbar-nav-item-padding-horizontal' => '16px',
            'navbar-dropdown-background' => 'rgba(255, 255, 255, 0.95)',
            'navbar-dropdown-width' => '220px',
            'navbar-dropdown-padding' => '12px',
            'navbar-dropdown-color' => '#324050'               // $color-a-dark
        ],
        'shadows' => [
            'box-shadow-small' => '0 2px 8px rgba(0,0,0,0.08)',
            'box-shadow-medium' => '0 5px 15px rgba(0,0,0,0.08)',
            'box-shadow-large' => '0 14px 25px rgba(0,0,0,0.16)',
            'box-shadow-xlarge' => '0 28px 50px rgba(0,0,0,0.16)',
            'drop-shadow-small' => 'drop-shadow(0 2px 8px rgba(0,0,0,0.08))',
            'drop-shadow-medium' => 'drop-shadow(0 5px 15px rgba(0,0,0,0.08))',
            'drop-shadow-large' => 'drop-shadow(0 14px 25px rgba(0,0,0,0.16))',
            'drop-shadow-xlarge' => 'drop-shadow(0 28px 50px rgba(0,0,0,0.16))'
        ]
    ]
];

// Standard Theme nur erstellen wenn nicht bereits vorhanden (verhindert Überschreibung bei Reinstall)
$defaultThemeFile = rex_path::addonData('uikit_theme_builder', 'themes/saved/uikit_default.json');

if (!file_exists($defaultThemeFile)) {
    file_put_contents(
        $defaultThemeFile,
        json_encode($defaultTheme, JSON_PRETTY_PRINT)
    );
    
    // Installation als abgeschlossen markieren
    $addon = rex_addon::get('uikit_theme_builder');
    $addon->setConfig('installed', true);
    $addon->setConfig('install_date', date('Y-m-d H:i:s'));
    $addon->setConfig('version', '1.0.0');
    
    echo 'UIKit Theme Builder wurde erfolgreich installiert.';
} else {
    echo 'UIKit Theme Builder wurde aktualisiert (bestehende Themes bleiben erhalten).';
}