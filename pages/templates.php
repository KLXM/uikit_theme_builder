<?php

use UikitThemeBuilder\TemplateInstaller;

/**
 * UIKit Theme Builder - Template Management.
 */

$addon = rex_addon::get('uikit_theme_builder');

// Modal für Einstellungen
$modal_action = rex_request::post('show_modal', 'string', '');
if ('install' === $modal_action && $template = rex_request::post('template_name', 'string', '')) {
    try {
        $metadata = TemplateInstaller::getTemplateMetadata($template);
    } catch (Exception $e) {
        echo rex_view::error('Fehler beim Laden der Template-Metadaten: ' . htmlspecialchars($e->getMessage()));
        $metadata = [];
    }
    ?>
    <div class="uk-modal uk-open uk-flex uk-flex-center uk-flex-middle" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <h2 class="uk-modal-title"><?= $addon->i18n('install_template') ?></h2>
            
            <form method="post">
                <fieldset class="uk-fieldset">
                    <legend class="uk-legend"><?= htmlspecialchars($template) ?></legend>
                    
                    <div class="uk-margin">
                        <label class="uk-form-label"><?= $addon->i18n('template_key') ?></label>
                        <input class="uk-input" type="text" name="install_key" value="<?= htmlspecialchars($template) ?>" required>
                        <small><?= $addon->i18n('template_key_help') ?></small>
                    </div>
                    
                    <div class="uk-margin">
                        <label class="uk-form-label"><?= $addon->i18n('template_display_name') ?></label>
                        <input class="uk-input" type="text" name="install_name" value="<?= htmlspecialchars($metadata['name'] ?? $template) ?>" required>
                        <small><?= $addon->i18n('template_display_name_help') ?></small>
                    </div>
                    
                    <?php if (!empty($metadata['description'])): ?>
                    <div class="uk-margin">
                        <label class="uk-form-label"><?= $addon->i18n('description') ?></label>
                        <div class="uk-text-small uk-text-muted">
                            <?= htmlspecialchars($metadata['description']) ?>
                        </div>
                    </div>
                    <?php endif ?>
                </fieldset>
                
                <div class="uk-margin uk-text-right">
                    <button type="button" class="uk-button uk-button-default" onclick="location.reload();">
                        <?= $addon->i18n('cancel') ?>
                    </button>
                    <?= rex_csrf_token::factory('uikit-template-install')->getHiddenField() ?>
                    <input type="hidden" name="template_name" value="<?= htmlspecialchars($template) ?>">
                    <input type="hidden" name="install_template" value="1">
                    <button type="submit" class="uk-button uk-button-primary">
                        <?= $addon->i18n('install_template') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

// Sicherheitsprüfung - Installation durchführen
if (rex_request::post('install_template', 'string', '') && rex_csrf_token::factory('uikit-template-install')->isValid()) {
    try {
        $templateName = rex_request::post('template_name', 'string');
        $installKey = rex_request::post('install_key', 'string', $templateName);
        $installName = rex_request::post('install_name', 'string', $templateName);

        // Prüfe ob Template bereits existiert
        $existingCheck = rex_sql::factory();
        $existingCheck->setQuery('SELECT id FROM ' . rex::getTable('template') . ' WHERE `key` = ?', [$installKey]);
        $wasExisting = $existingCheck->getRows() > 0;

        $templateId = TemplateInstaller::installTemplate($templateName, $installKey, $installName);

        if (false !== $templateId) {
            $action = $wasExisting ? 'aktualisiert' : 'installiert';
            echo rex_view::success(
                'Template erfolgreich ' . $action . ': <strong>' . htmlspecialchars($installKey) . '</strong> (ID: ' . $templateId . ')',
            );
        } else {
            echo rex_view::error($addon->i18n('template_installation_failed'));
        }
    } catch (Exception $e) {
        echo rex_view::error('Fehler beim Installieren des Templates: ' . htmlspecialchars($e->getMessage()));
    }
}

// Verfügbare Templates anzeigen
try {
    $templates = TemplateInstaller::getAvailableTemplates();
} catch (Exception $e) {
    echo rex_view::error('Fehler beim Laden der Templates: ' . htmlspecialchars($e->getMessage()));
    $templates = [];
}

// Prüfe ob template_manager installiert und aktiv ist
$hasTemplateManager = rex_addon::get('template_manager') && rex_addon::get('template_manager')->isAvailable();

// Info-Box wenn template_manager aktiv ist
if ($hasTemplateManager) {
    echo '<div class="uk-alert-info" uk-alert>';
    echo '<a class="uk-alert-close" uk-close></a>';
    echo '<p>';
    echo '<strong>' . $addon->i18n('template_manager_integration') . '</strong><br>';
    echo $addon->i18n('template_manager_integration_help');
    echo ' <a href="' . rex_url::backendPage('template_manager/settings') . '" class="uk-link">';
    echo $addon->i18n('configure_templates');
    echo '</a>';
    echo '</p>';
    echo '</div>';
}

if (empty($templates)) {
    echo rex_view::info($addon->i18n('no_templates_available'));
} else {
    echo '<h2>' . $addon->i18n('available_templates') . '</h2>';
    echo '<div class="uk-grid uk-grid-medium uk-child-width-1-3@m uk-child-width-1-2@s" uk-grid>';

    foreach ($templates as $template) {
        $metadata = TemplateInstaller::getTemplateMetadata($template);
        $name = $metadata['name'] ?? $template;
        $description = $metadata['description'] ?? $addon->i18n('no_description');

        echo '<div>';
        echo '<div class="uk-card uk-card-default uk-card-hover">';
        echo '<div class="uk-card-header">';
        echo '<h3 class="uk-card-title uk-margin-remove">' . htmlspecialchars($name) . '</h3>';
        echo '<small class="uk-text-muted">' . htmlspecialchars($template) . '</small>';
        echo '</div>';
        echo '<div class="uk-card-body">';
        echo '<p class="uk-text-small uk-margin-small">' . htmlspecialchars($description) . '</p>';
        echo '</div>';
        echo '<div class="uk-card-footer">';

        // Installieren Button
        echo '<form method="post" style="display: inline;">';
        echo '<input type="hidden" name="show_modal" value="install">';
        echo '<input type="hidden" name="template_name" value="' . htmlspecialchars($template) . '">';
        echo '<button type="submit" class="uk-button uk-button-primary uk-button-small">';
        echo '<i class="rex-icon rex-icon-download"></i> ' . $addon->i18n('install');
        echo '</button>';
        echo '</form>';

        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';
}
