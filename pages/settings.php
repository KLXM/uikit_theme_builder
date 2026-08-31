<?php

/**
 * UIKit Theme Builder - Einstellungen
 */

$content = '';
$message = '';

// Config Form erstellen
$form = rex_config_form::factory('uikit_theme_builder');

$field = $form->addInputField('text', 'google_fonts_api_key', null, ['class' => 'form-control']);
$field->setLabel('Google Fonts API Key');
$field->setNotice('API Key für Google Fonts. <a href="https://developers.google.com/fonts/docs/developer_api#APIKey" target="_blank">Hier API Key erstellen</a>');

// Theme-Auswahl für Backend
$themeSelect = $form->addSelectField('backend_theme', null, ['class' => 'form-control']);
$themeSelect->setLabel('Backend Theme');
$themeSelect->setNotice('Wähle ein Theme, das automatisch im REDAXO Backend geladen werden soll');

// Theme-Optionen laden
$select = $themeSelect->getSelect();
$select->addOption('Kein Theme (Standard REDAXO)', '');

// Gespeicherte Themes aus Verzeichnis laden
$themesDir = rex_path::addonData('uikit_theme_builder', 'themes/saved/');
if (is_dir($themesDir)) {
    $themes = glob($themesDir . '*.json');
    foreach ($themes as $themeFile) {
        $themeName = basename($themeFile, '.json');
        $themeData = json_decode(file_get_contents($themeFile), true);
        $label = $themeData['name'] ?? $themeName;
        $select->addOption($label, $themeName);
    }
}

// Live Theme Editor - welche Themes im "Theme wechseln"-Dropdown wählbar sind
$liveThemesField = $form->addSelectField('live_editor_available_themes', null, ['class' => 'form-control', 'multiple' => 'multiple', 'size' => 6]);
$liveThemesField->setLabel('Live Theme Editor: wählbare Themes');
$liveThemesField->setNotice('Nichts ausgewählt = alle Themes wählbar. Mehrfachauswahl mit Strg/Cmd. Das jeweils aktuell zugewiesene Theme bleibt immer wählbar.');
$liveThemesSelect = $liveThemesField->getSelect();
$liveThemesSelect->setMultiple(true);
foreach (\UikitThemeBuilder\DomainContext::getAvailableThemes() as $themeName => $label) {
    $liveThemesSelect->addOption($label, $themeName);
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Einstellungen', false);
$fragment->setVar('body', $form->get(), false);

echo $fragment->parse('core/page/section.php');
