<?php

namespace UikitThemeBuilder\Widgets;

use KLXM\InfoCenter\AbstractWidget;
use UikitThemeBuilder\DomainContext;
use UikitThemeBuilder\LiveThemeState;
use UikitThemeBuilder\UikitThemeBuilderManager;

/**
 * Info-Center-Widget: schwebendes Overlay im Frontend, mit dem eingeloggte Redakteure
 * Grundfarben/Schriftgrößen des aktuellen Domain-Themes live testen können (SSE-Vorschau,
 * siehe LiveThemeState). Persistenz/Compile läuft weiterhin über die bestehende
 * UikitThemeBuilderManager-Pipeline - dieses Widget ist nur die Vorschau-Schicht davor.
 */
class LiveThemeEditorWidget extends AbstractWidget
{
    public function getTitle(): string
    {
        return 'Live Theme Editor';
    }

    public function supportsLazyLoading(): bool
    {
        return false;
    }

    public function render(): string
    {
        if (!\rex::isFrontend()) {
            return '';
        }

        $user = \rex_backend_login::createUser();
        if (!$user || !($user->isAdmin() || $user->hasPerm('info_center[]'))) {
            return '';
        }

        $theme = DomainContext::getCurrentTheme();
        if (!$theme) {
            return '';
        }

        $manager = new UikitThemeBuilderManager();
        $themeRecord = $manager->loadTheme($theme);
        $colors = $themeRecord['data']['colors'] ?? [];
        $typography = $themeRecord['data']['typography'] ?? [];

        $currentValues = [];
        foreach (LiveThemeState::FIELDS as $key => $field) {
            $source = 'colors' === $field['group'] ? $colors : $typography;
            if (isset($source[$field['less']])) {
                $currentValues[$key] = $source[$field['less']];
            }
        }

        $addon = \rex_addon::get('uikit_theme_builder');

        $config = [
            'theme' => $theme,
            'values' => $currentValues,
            'fields' => LiveThemeState::FIELDS,
            'streamUrl' => self::rootUrl(['theme_live_stream' => 'draft', 'theme' => $theme]),
            'pushUrl' => self::rootUrl(['rex-api-call' => 'uikit_theme_live_push']),
            'goLiveUrl' => self::rootUrl(['rex-api-call' => 'uikit_theme_live_golive']),
            'stopUrl' => self::rootUrl(['rex-api-call' => 'uikit_theme_live_stop']),
            'discardUrl' => self::rootUrl(['rex-api-call' => 'uikit_theme_live_discard']),
            'saveUrl' => self::rootUrl(['rex-api-call' => 'uikit_theme_live_save']),
            'isAdmin' => $user->isAdmin(),
        ];

        $editorCss = $addon->getAssetsUrl('live-editor/live-theme-editor.css');
        $editorJs = $addon->getAssetsUrl('live-editor/live-theme-editor.js');

        $configJson = htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<link rel="stylesheet" href="{$editorCss}">
<div id="tb-live-editor-root" data-tb-live-config="{$configJson}"></div>
<script src="{$editorJs}"></script>
HTML;
    }

    /**
     * Baut eine "nackte" Root-relative URL (/?key=value) statt rex_url::frontendController()
     * (.../index.php?...). Unter aktivem YRewrite behandelt der Path-Resolver "index.php" als
     * zu suchenden Artikel-Pfad und antwortet mit 404, BEVOR der rex-api-call überhaupt
     * verarbeitet wird - ein leerer Pfad ("/") landet dagegen korrekt bei der Startseite und
     * lässt REDAXO die Query-Params normal auswerten. Gleiches Muster wie boot.php's
     * bestehende previewtheme-Route ("/?previewtheme=...").
     */
    private static function rootUrl(array $params): string
    {
        return '/?' . http_build_query($params);
    }
}
