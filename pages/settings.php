<?php

/**
 * UIKit Theme Builder - Einstellungen
 *
 * Backend: welches Theme, wo es wirkt (nur Vorschauen / ganzes Backend) und welche Bereiche als Vorschau gelten.
 * Dazu Google Fonts und Protokoll.
 */

use UikitThemeBuilder\BackendIntegration;
use UikitThemeBuilder\CssScoper;

$addon = rex_addon::get('uikit_theme_builder');
$form = rex_config_form::factory('uikit_theme_builder');

// --- Backend ---------------------------------------------------------------------------------------------------------
$form->addFieldset('Theme im Backend');

$themeSelect = $form->addSelectField('backend_theme', null, ['class' => 'form-control selectpicker']);
$themeSelect->setLabel('Theme');
$themeSelect->setNotice('Welches Theme im Backend für Vorschauen (Blöcke, Builder) genutzt wird. „Theme der Domain“ nimmt das Theme, das unter „YRewrite-Domains“ der Domain zugewiesen ist.');
$select = $themeSelect->getSelect();
$select->addOption('Kein Theme (nur UIkit)', '');
$select->addOption('Theme der Domain (automatisch)', BackendIntegration::DOMAIN_THEME);
$themesDir = rex_path::addonData('uikit_theme_builder', 'themes/saved/');
foreach (is_dir($themesDir) ? (glob($themesDir . '*.json') ?: []) : [] as $themeFile) {
    $themeName = basename($themeFile, '.json');
    $themeData = json_decode((string) file_get_contents($themeFile), true);
    $select->addOption((string) ($themeData['name'] ?? $themeName), $themeName);
}

$mode = $form->addRadioField('backend_mode');
$mode->setLabel('Wo das Theme wirkt');
$mode->addOption('Nur in Vorschauen (empfohlen) – REDAXO bleibt, wie es ist; das Theme gilt im Inhalt der Blöcke, in der Builder-Vorschau und in Bereichen mit der Klasse „uk-theme-scope“', 'scoped');
$mode->addOption('Im ganzen Backend – das Theme färbt auch REDAXO selbst (Schrift, Farben); Hintergrund, Überschriften, Links und Menü werden wieder auf REDAXO zurückgesetzt', 'full');
if (!in_array((string) $addon->getConfig('backend_mode', ''), BackendIntegration::MODES, true)) {
    $mode->setValue('scoped');
}

$scope = $form->addTextAreaField('backend_scope', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => CssScoper::DEFAULT_SCOPE, 'spellcheck' => 'false']);
$scope->setLabel('Vorschau-Bereiche');
$scope->setNotice('CSS-Selektoren, je Zeile einer – nur für „Nur in Vorschauen“. Leer lassen für den Standard: <code>' . rex_escape(str_replace("\n", ', ', CssScoper::DEFAULT_SCOPE)) . '</code>. Eigene Bereiche im Backend bekommen das Theme einfach mit der Klasse <code>uk-theme-scope</code>.');

// --- Google Fonts ----------------------------------------------------------------------------------------------------
$form->addFieldset('Google Fonts');
$field = $form->addInputField('text', 'google_fonts_api_key', null, ['class' => 'form-control', 'autocomplete' => 'off', 'spellcheck' => 'false']);
$field->setLabel('API-Schlüssel');
$field->setNotice('Nur für die Schriften-Suche unter „Google Fonts“ nötig. <a href="https://developers.google.com/fonts/docs/developer_api#APIKey" target="_blank" rel="noopener">API-Schlüssel erstellen</a>. Die Schriften selbst werden lokal gespeichert und ohne Google ausgeliefert.');

// --- Protokoll -------------------------------------------------------------------------------------------------------
$form->addFieldset('Protokoll');
$debug = $form->addCheckboxField('debug_log');
$debug->setLabel('Debug-Meldungen');
$debug->addOption('Ausführliche Meldungen ins Systemprotokoll schreiben (nur im REDAXO-Debug-Modus)', 1);

// Hinweis zum aktuellen Stand
$current = BackendIntegration::theme();
$configured = (string) $addon->getConfig('backend_theme', '');
$info = '';
if (BackendIntegration::DOMAIN_THEME === $configured && '' === $current) {
    $info = rex_view::warning('„Theme der Domain“ ist gewählt, aber der aktuellen Domain ist kein Theme zugewiesen – zuweisen unter „YRewrite-Domains“.');
} elseif ('' !== $current) {
    $info = rex_view::info('Aktiv im Backend: <strong>' . rex_escape($current) . '</strong> – ' . ('full' === BackendIntegration::mode() ? 'im ganzen Backend.' : 'nur in Vorschauen.'));
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Einstellungen', false);
$fragment->setVar('body', $info . $form->get(), false);
echo $fragment->parse('core/page/section.php');
