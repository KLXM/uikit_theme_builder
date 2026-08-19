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
            'streamUrl' => \rex_url::frontendController(['theme_live_stream' => 'draft', 'theme' => $theme], false),
            'pushUrl' => \rex_url::frontendController(['rex-api-call' => 'uikit_theme_live_push'], false),
            'goLiveUrl' => \rex_url::frontendController(['rex-api-call' => 'uikit_theme_live_golive'], false),
            'stopUrl' => \rex_url::frontendController(['rex-api-call' => 'uikit_theme_live_stop'], false),
            'discardUrl' => \rex_url::frontendController(['rex-api-call' => 'uikit_theme_live_discard'], false),
            'saveUrl' => \rex_url::frontendController(['rex-api-call' => 'uikit_theme_live_save'], false),
            'isAdmin' => $user->isAdmin(),
        ];

        $pickrCss = $addon->getAssetsUrl('pickr/pickr.min.css');
        $pickrJs = $addon->getAssetsUrl('pickr/pickr.min.js');
        $editorCss = $addon->getAssetsUrl('live-editor/live-theme-editor.css');
        $editorJs = $addon->getAssetsUrl('live-editor/live-theme-editor.js');

        $configJson = htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<link rel="stylesheet" href="{$pickrCss}">
<link rel="stylesheet" href="{$editorCss}">
<div id="tb-live-editor-root" data-tb-live-config="{$configJson}"></div>
<script src="{$pickrJs}"></script>
<script src="{$editorJs}"></script>
HTML;
    }
}
