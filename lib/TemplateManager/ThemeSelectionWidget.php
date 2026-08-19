<?php

namespace UikitThemeBuilder\TemplateManager;

use FriendsOfRedaxo\TemplateManager\Widget\AbstractWidget;
use UikitThemeBuilder\UikitThemeBuilderManager;

/**
 * Theme Selection Widget für Template Manager
 * Ermöglicht Auswahl eines UIKit Themes pro Domain/Sprache
 */
class ThemeSelectionWidget extends AbstractWidget
{
    public function getKey(): string
    {
        return 'tm_uikit_theme';
    }

    public function getName(): string
    {
        return 'UIKit Theme';
    }

    public function getDescription(): string
    {
        return 'Wählen Sie ein kompiliertes UIKit Theme für diese Domain/Sprache';
    }

    public function getCategory(): string
    {
        return 'theme';
    }

    public function getDefaultValues(): array
    {
        return [
            'theme_name' => '',
            'auto_load' => true
        ];
    }

    public function render(array $values, int $clangId, int $domainId): string
    {
        $key = $this->getKey();
        $currentTheme = $values[$key]['theme_name'] ?? '';
        $autoLoad = $values[$key]['auto_load'] ?? true;
        
        // Verfügbare Themes laden
        $themeManager = new UikitThemeBuilderManager();
        $themes = $themeManager->listThemes();
        
        // Theme-Optionen für Select aufbereiten
        $themeOptions = ['' => '-- Kein Theme --'];
        foreach ($themes as $themeName => $themeData) {
            $themeOptions[$themeName] = $themeName . ' (v' . $themeData['version'] . ')';
        }
        
        $html = '';
        
        // Theme Auswahl - Field-Name mit clang_id!
        $themeSelect = $this->renderSelect(
            'settings[' . $clangId . '][' . $key . '][theme_name]',
            $themeOptions,
            $currentTheme
        );
        $html .= $this->renderFormRow('Theme', $themeSelect, 'Wählen Sie ein kompiliertes Theme aus');
        
        // Auto-Load Checkbox - Field-Name mit clang_id!
        $autoLoadCheckbox = $this->renderCheckbox(
            'settings[' . $clangId . '][' . $key . '][auto_load]',
            'Theme automatisch im Frontend laden',
            $autoLoad
        );
        $html .= $this->renderFormRow('', $autoLoadCheckbox, 'CSS wird automatisch im &lt;head&gt; eingebunden');
        
        // Theme-Vorschau (wenn Theme gewählt)
        if ($currentTheme && isset($themes[$currentTheme])) {
            $previewUrl = \rex_url::backendPage('uikit_theme_builder/editor', ['theme' => $currentTheme]);
            $html .= '<div class="form-group">';
            $html .= '<div class="col-sm-9 col-sm-offset-3">';
            $html .= '<a href="' . $previewUrl . '" class="btn btn-sm btn-default" target="_blank">';
            $html .= '<i class="rex-icon fa-eye"></i> Theme bearbeiten';
            $html .= '</a>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        return $html;
    }

    /**
     * Gibt die Theme CSS URL zurück (für Frontend-Nutzung)
     */
    public static function getThemeCssUrl(string $themeName): string
    {
        return \UikitThemeBuilder\PathManager::getThemesCompiledPublicUrl($themeName . '.css');
    }
}
