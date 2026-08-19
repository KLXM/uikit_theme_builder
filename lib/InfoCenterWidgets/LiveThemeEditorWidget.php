<?php

namespace UikitThemeBuilder\InfoCenterWidgets;

use KLXM\InfoCenter\AbstractWidget;
use UikitThemeBuilder\DomainContext;
use UikitThemeBuilder\LiveThemeState;
use UikitThemeBuilder\UikitThemeBuilderManager;

/**
 * Info-Center-Widget: lässt eingeloggte Redakteure Grundfarben/Schriftgrößen/Abstände des
 * aktuellen Domain-Themes live im Frontend testen (SSE-Vorschau, siehe LiveThemeState).
 * Rendert als normaler Info-Center-Eintrag (wrapContent()), kein eigenes Floating-Overlay.
 * Persistenz/Compile läuft weiterhin über die bestehende UikitThemeBuilderManager-Pipeline.
 */
class LiveThemeEditorWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct();
        $this->title = 'Live Theme Editor';
        $this->priority = 3;
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
        $spacing = $themeRecord['data']['spacing'] ?? [];
        $groups = ['colors' => $colors, 'typography' => $typography, 'spacing' => $spacing];

        $currentValues = [];
        foreach (LiveThemeState::FIELDS as $key => $field) {
            $source = $groups[$field['group']] ?? [];
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

        // Cache-Buster wie rex_view::addCssFile()/addJsFile() ihn automatisch anhängen -
        // unsere <link>/<script>-Tags hier gehen aber direkt raus (nicht über rex_view), daher
        // müssen wir das manuell nachbilden, sonst bleiben Browser/Proxy nach einem Update auf
        // einer alten gecachten Version hängen.
        $editorCss = self::withCacheBuster($addon, 'live-editor/live-theme-editor.css');
        $editorJs = self::withCacheBuster($addon, 'live-editor/live-theme-editor.js');

        $configJson = htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8');

        $content = <<<HTML
<link rel="stylesheet" href="{$editorCss}">
<div id="tb-live-editor-root" class="tb-live-body" data-tb-live-config="{$configJson}">
    <p class="tb-live-loading">Lade …</p>
</div>
<script src="{$editorJs}"></script>
HTML;

        return $this->wrapContent($content);
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

    private static function withCacheBuster(\rex_addon $addon, string $relativePath): string
    {
        $url = $addon->getAssetsUrl($relativePath);
        $absolutePath = \rex_path::addonAssets($addon->getName(), $relativePath);
        $mtime = @filemtime($absolutePath);

        return $mtime ? $url . '?buster=' . $mtime : $url;
    }
}
