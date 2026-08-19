<?php

/**
 * UIKit Theme Builder - Domain-Theme-Zuordnung
 */

use UikitThemeBuilder\UikitThemeBuilderManager;
use UikitThemeBuilder\DomainContext;

$addon = rex_addon::get('uikit_theme_builder');

// Wenn YRewrite nicht installiert oder verfügbar ist, Hinweis anzeigen
if (!rex_addon::exists('yrewrite') || !rex_addon::get('yrewrite')->isAvailable()) {
    echo rex_view::info($addon->i18n('domains_yrewrite_not_available'));
    return;
}

// Domains aus YRewrite holen
$domains = rex_yrewrite::getDomains();

// Default-Domain herausfiltern (wird nicht benötigt)
$domains = array_filter($domains, function($domain) {
    return $domain->getId() > 0; // Nur echte Domains, keine Default-Domain
});

// Theme-Manager für Theme-Liste
$themeManager = new UikitThemeBuilderManager();
$themes = $themeManager->listThemes();

// Formular abgesendet?
if (rex_post('save', 'bool')) {
    $success = true;
    $debugInfo = []; // Debug-Informationen sammeln
    
    foreach ($domains as $domain) {
        $domainId = $domain->getId();
        $themeName = rex_post('domain_' . $domainId, 'string', '');
        // Multiselect: Array mit aktivierten Utilities
        $utilities = rex_post('utilities_' . $domainId, 'array', []);
        $enableTransparent = in_array('transparent', $utilities) ? 1 : 0;
        $enableLightDark = in_array('lightdark', $utilities) ? 1 : 0;
        $enableBgUtilities = in_array('bgutil', $utilities) ? 1 : 0;
        
        // Debug: Was kommt rein?
        if (rex::isDebugMode()) {
            $debugInfo[] = "Domain ID: $domainId, Theme: '$themeName', Utilities: " . implode(',', $utilities);
        }
        
        // Prüfen ob bereits eine Zuordnung existiert
        $checkSql = rex_sql::factory();
        $checkSql->setQuery('SELECT id FROM ' . rex::getTable('uikit_theme_domains') . ' WHERE domain_id = ?', [$domainId]);
        $exists = $checkSql->getRows() > 0;
        
        if ($themeName || $exists) {
            // Theme zuordnen oder aktualisieren (oder Utilities aktualisieren bei bestehendem Eintrag)
            $sql = rex_sql::factory(); // Neues SQL-Objekt für Update/Insert
            
            if ($exists) {
                // Update
                $sql->setTable(rex::getTable('uikit_theme_domains'));
                $sql->setWhere('domain_id = :domain_id', ['domain_id' => $domainId]);
                
                if ($themeName) {
                    $sql->setValue('theme_name', $themeName);
                } else {
                    // Kein Theme gewählt = Theme entfernen, aber Utilities bleiben
                    $sql->setValue('theme_name', '');
                }
                
                $sql->setValue('enable_transparent', $enableTransparent);
                $sql->setValue('enable_light_dark', $enableLightDark);
                $sql->setValue('enable_bg_utilities', $enableBgUtilities);
                $sql->setValue('updated_date', date('Y-m-d H:i:s'));
                
                try {
                    $sql->update();
                } catch (rex_sql_exception $e) {
                    $success = false;
                    rex_logger::factory()->log('error', 'Domain Update Error: ' . $e->getMessage(), [], 'uikit_theme_builder');
                }
            } else {
                // Insert - nur wenn Theme oder Utilities gesetzt sind
                if ($themeName || $enableTransparent || $enableLightDark || $enableBgUtilities) {
                    $sql->setTable(rex::getTable('uikit_theme_domains'));
                    $sql->setValue('domain_id', $domainId);
                    $sql->setValue('theme_name', $themeName);
                    $sql->setValue('enable_transparent', $enableTransparent);
                    $sql->setValue('enable_light_dark', $enableLightDark);
                    $sql->setValue('enable_bg_utilities', $enableBgUtilities);
                    $sql->setValue('created_date', date('Y-m-d H:i:s'));
                    $sql->setValue('updated_date', date('Y-m-d H:i:s'));
                    
                    try {
                        $sql->insert();
                    } catch (rex_sql_exception $e) {
                        $success = false;
                        rex_logger::factory()->log('error', 'Domain Insert Error: ' . $e->getMessage(), [], 'uikit_theme_builder');
                    }
                }
            }
        }
    }
    
    // Cache leeren
    DomainContext::clearCache();
    
    // Erfolgsmeldung
    if ($success) {
        $msg = $addon->i18n('domains_settings_saved');
        if (rex::isDebugMode() && !empty($debugInfo)) {
            $msg .= '<br><small>Debug: ' . implode('<br>', $debugInfo) . '</small>';
        }
        echo rex_view::success($msg);
    } else {
        echo rex_view::error($addon->i18n('domains_settings_error'));
    }
}

// Aktuelle Zuordnungen laden
$currentMappings = [];
$sql = rex_sql::factory();
$sql->setQuery('SELECT domain_id, theme_name, enable_transparent, enable_light_dark, enable_bg_utilities FROM ' . rex::getTable('uikit_theme_domains'));

foreach ($sql as $row) {
    $currentMappings[$row->getValue('domain_id')] = [
        'theme_name' => $row->getValue('theme_name'),
        'enable_transparent' => (int)$row->getValue('enable_transparent'),
        'enable_light_dark' => (int)$row->getValue('enable_light_dark'),
        'enable_bg_utilities' => (int)$row->getValue('enable_bg_utilities'),
    ];
}

// Formular erstellen
$content = '<form action="' . rex_url::currentBackendPage() . '" method="post">';

$content .= '<table class="table table-striped table-hover">';
$content .= '<thead><tr>';
$content .= '<th>' . $addon->i18n('domains_domain') . '</th>';
$content .= '<th>' . $addon->i18n('domains_url') . '</th>';
$content .= '<th>' . $addon->i18n('domains_theme') . '</th>';
$content .= '<th class="text-center">' . $addon->i18n('domains_utilities') . '</th>';
$content .= '</tr></thead>';
$content .= '<tbody>';

foreach ($domains as $domain) {
    $domainId = $domain->getId();
    $domainName = $domain->getName();
    $domainUrl = $domain->getUrl();
    $mapping = $currentMappings[$domainId] ?? null;
    $currentTheme = $mapping['theme_name'] ?? '';
    $enableTransparent = $mapping['enable_transparent'] ?? 0;
    $enableLightDark = $mapping['enable_light_dark'] ?? 0;
    $enableBgUtilities = $mapping['enable_bg_utilities'] ?? 0;
    
    $content .= '<tr>';
    $content .= '<td><strong>' . rex_escape($domainName) . '</strong></td>';
    $content .= '<td><small class="text-muted">' . rex_escape($domainUrl) . '</small></td>';
    $content .= '<td>';
    $content .= '<div class="rex-select-style">';
    $content .= '<select class="form-control" name="domain_' . $domainId . '">';
    $content .= '<option value=""' . (empty($currentTheme) ? ' selected' : '') . '>-- ' . $addon->i18n('domains_no_theme') . ' --</option>';
    
    foreach ($themes as $themeName => $themeInfo) {
        $selected = ($currentTheme === $themeName) ? ' selected' : '';
        $content .= '<option value="' . rex_escape($themeName) . '"' . $selected . '>' . rex_escape($themeName) . '</option>';
    }
    
    $content .= '</select>';
    $content .= '</div>';
    $content .= '</td>';
    $content .= '<td>';
    $content .= '<div class="rex-select-style">';
    $content .= '<select class="form-control" name="utilities_' . $domainId . '[]" multiple size="3">';
    $content .= '<option value="transparent"' . ($enableTransparent ? ' selected' : '') . ' title="' . $addon->i18n('domains_utility_transparent_desc') . '">' . $addon->i18n('domains_utility_transparent') . '</option>';
    $content .= '<option value="lightdark"' . ($enableLightDark ? ' selected' : '') . ' title="' . $addon->i18n('domains_utility_lightdark_desc') . '">' . $addon->i18n('domains_utility_lightdark') . '</option>';
    $content .= '<option value="bgutil"' . ($enableBgUtilities ? ' selected' : '') . ' title="' . $addon->i18n('domains_utility_bgutil_desc') . '">' . $addon->i18n('domains_utility_bgutil') . '</option>';
    $content .= '</select>';
    $content .= '</div>';
    $content .= '<small class="help-block text-muted">' . $addon->i18n('domains_utility_help') . '</small>';
    $content .= '</td>';
    $content .= '</tr>';
}

$content .= '</tbody></table>';

$content .= '<div class="form-group">';
$content .= '<button class="btn btn-save rex-primary-action" type="submit" name="save" value="1">';
$content .= '<i class="rex-icon rex-icon-save"></i> ' . $addon->i18n('domains_save');
$content .= '</button>';
$content .= '</div>';
$content .= '</form>';

// Hinweis zur Domain-Konfiguration
$notice = '<div class="alert alert-info">';
$notice .= '<h4><i class="rex-icon fa-info-circle"></i> ' . $addon->i18n('domains_info_title') . '</h4>';
$notice .= '<p>' . $addon->i18n('domains_info_text') . '</p>';
$notice .= '<ul>';
$notice .= '<li>' . $addon->i18n('domains_info_module') . '</li>';
$notice .= '<li>' . $addon->i18n('domains_info_css') . '</li>';
$notice .= '<li>' . $addon->i18n('domains_info_colors') . '</li>';
$notice .= '</ul>';
$notice .= '</div>';

// Debug-Info wenn Debug-Modus aktiv
$debugInfo = '';
if (rex::isDebugMode()) {
    $context = DomainContext::getContext();
    $debugInfo = '<div class="alert alert-warning">';
    $debugInfo .= '<h5><i class="rex-icon fa-bug"></i> Debug Information</h5>';
    $debugInfo .= '<dl class="dl-horizontal">';
    $debugInfo .= '<dt>Aktuelle Domain:</dt><dd>' . ($context['domain_name'] ?? 'Keine') . '</dd>';
    $debugInfo .= '<dt>Domain ID:</dt><dd>' . ($context['domain_id'] ?? 'Keine') . '</dd>';
    $debugInfo .= '<dt>Zugeordnetes Theme:</dt><dd>' . ($context['theme'] ?? 'Kein Theme') . '</dd>';
    $debugInfo .= '<dt>Theme CSS URL:</dt><dd><code>' . ($context['theme_css_url'] ?? 'Keine') . '</code></dd>';
    $debugInfo .= '</dl>';
    $debugInfo .= '</div>';
}

// Fragment erstellen und ausgeben
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('domains_page_title'), false);
$fragment->setVar('body', $notice . $debugInfo . $content, false);

echo $fragment->parse('core/page/section.php');
