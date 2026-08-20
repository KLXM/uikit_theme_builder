<?php

namespace UikitThemeBuilder\InfoCenterWidgets;

use KLXM\InfoCenter\AbstractWidget;
use UikitThemeBuilder\DomainContext;
use UikitThemeBuilder\GoogleFontsManager;
use UikitThemeBuilder\LiveThemeState;
use UikitThemeBuilder\TemplateHelper;
use UikitThemeBuilder\UikitThemeBuilderManager;

/**
 * Info-Center-Widget: lässt eingeloggte Redakteure Grundfarben/Schriftgrößen/Abstände des
 * aktuellen Domain-Themes live im Frontend testen (SSE-Vorschau, siehe LiveThemeState).
 * Rendert als normaler Info-Center-Eintrag (wrapContent()), kein eigenes Floating-Overlay.
 * Persistenz/Compile läuft weiterhin über die bestehende UikitThemeBuilderManager-Pipeline.
 */
class LiveThemeEditorWidget extends AbstractWidget
{
    /**
     * Echte UIkit-Standardwerte (sources/uikit/less/) für LESS-Variablen, die kein Widget-
     * Formular in diesem Addon jemals als eigenes Feld anbietet (also nie in der gespeicherten
     * Theme-JSON auftauchen, selbst wenn der Nutzer alle Widgets einmal durchgespeichert hat).
     * Nur für genau diese Lücke - alle anderen FIELDS-Einträge werden bereits über die echten
     * Theme-Daten befüllt.
     */
    private const UIKIT_DEFAULTS = [
        // sources/uikit/less/components/base.less
        'base-h1-line-height' => '1.2',
        // sources/uikit/less/components/variables.less
        'global-medium-gutter' => '40px',
    ];

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
        if (!$user || !LiveThemeState::canUseEditor($user)) {
            return '';
        }

        $theme = DomainContext::getCurrentTheme();
        if (!$theme) {
            return '';
        }

        // Nur anzeigen, wenn das aktuelle Template dieses Theme auch tatsächlich per
        // TemplateHelper::includeAllStyles() eingebunden hat - sonst bearbeitet der Redakteur
        // Werte, die auf der gerade betrachteten Seite gar nicht sichtbar werden (z.B. Templates,
        // die eine eigene statische CSS-Datei einbinden statt des Theme Builders).
        if (!TemplateHelper::isThemeIncluded($theme)) {
            return '';
        }

        $manager = new UikitThemeBuilderManager();
        $themeRecord = $manager->loadTheme($theme);
        $themeDataSections = $themeRecord['data'] ?? [];

        // Datengruppe dynamisch aus den tatsächlich verwendeten FIELDS-Gruppen ableiten, statt
        // sie hier hart zu pflegen - sonst fehlt bei jedem neuen 'group' (wie zuletzt
        // 'container') sonst leicht die Zuordnung beim Lesen des aktuellen Werts.
        $groups = [];
        foreach (LiveThemeState::FIELDS as $field) {
            $groups[$field['group']] = $themeDataSections[$field['group']] ?? [];
        }

        $currentValues = [];
        foreach (LiveThemeState::FIELDS as $key => $field) {
            $source = $groups[$field['group']] ?? [];
            // 'less' kann eine Liste sein (z.B. heading_line_height schreibt auf mehrere
            // Ebenen gleichzeitig) - zum Anzeigen des aktuellen Werts reicht die erste.
            $lessKey = is_array($field['less']) ? $field['less'][0] : $field['less'];
            if (isset($source[$lessKey])) {
                $currentValues[$key] = $source[$lessKey];
            } elseif (isset(self::UIKIT_DEFAULTS[$lessKey])) {
                // Theme hat diese Variable nie explizit gesetzt (z.B. weil das zugehörige
                // Formular sie gar nicht anbietet, wie base-h1-line-height/global-medium-gutter).
                // Ohne Fallback zeigt der Regler dann einen falschen Platzhalter statt des
                // tatsächlich gerenderten UIkit-Standardwerts - das war der gemeldete Bug
                // ("Live-Editor übernimmt die Theme-Werte manchmal nicht").
                $currentValues[$key] = self::UIKIT_DEFAULTS[$lessKey];
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
            // Spiegelt den echten Zustand beim Laden - z.B. nach einem Reload während eine
            // vorherige Live-Session noch aktiv ist, damit die Checkbox nicht "aus" anzeigt,
            // obwohl gerade live geschaltet ist.
            'isLiveActive' => file_exists(LiveThemeState::flagPath($theme)),
            // Globaler Schalter aus den Addon-Einstellungen - ohne den blendet das JS die
            // "Live schalten"-Checkbox erst gar nicht ein. Serverseitig zusätzlich in den
            // golive/stop-Endpoints geprüft (UI-Ausblenden allein wäre kein echter Schutz).
            'canBroadcast' => DomainContext::isLiveBroadcastEnabled(),
            'isAdmin' => $user->isAdmin(),
            'canStyle' => LiveThemeState::canStyle($user),
            'canSwitchTheme' => LiveThemeState::canSwitchTheme($user),
            // Lokal gehostete Google-Fonts-CSS-Dateien (siehe GoogleFontsManager/getGoogleFontsHtml())
            // - das JS baut daraus die URL für eine Font-Vorschau selbst, MUSS aber NIE live
            // fonts.googleapis.com kontaktieren (das war ein Bug: "Arial Black" wurde fälschlich
            // nicht als Systemschrift erkannt und live bei Google angefragt, 403).
            'fontsBaseUrl' => \rex_url::assets('addons/uikit_theme_builder/fonts/'),
            'fontOptions' => self::buildFontOptions(),
            'availableThemes' => DomainContext::getLiveEditorAvailableThemes($theme),
            'themeCssUrlTemplate' => \rex_url::addonAssets('uikit_theme_builder', 'themes/compiled/__THEME__.css'),
            'switchThemeUrl' => self::rootUrl(['rex-api-call' => 'uikit_theme_live_switch_theme']),
        ];

        // Cache-Buster wie rex_view::addCssFile()/addJsFile() ihn automatisch anhängen -
        // unsere <link>/<script>-Tags hier gehen aber direkt raus (nicht über rex_view), daher
        // müssen wir das manuell nachbilden, sonst bleiben Browser/Proxy nach einem Update auf
        // einer alten gecachten Version hängen.
        $editorCss = self::withCacheBuster($addon, 'live-editor/live-theme-editor.css');
        $editorJs = self::withCacheBuster($addon, 'live-editor/live-theme-editor.js');
        // Pickit Color (https://github.com/skerbis/pickit_color) - im Frontend nicht über
        // boot.php vorgeladen (das gilt nur backend-seitig), daher hier explizit einbinden.
        $colorPickerCss = self::withCacheBuster($addon, 'pickit-color/colorpicker.min.css');
        $colorPickerJs = self::withCacheBuster($addon, 'pickit-color/colorpicker.min.js');

        $configJson = htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8');

        $content = <<<HTML
<link rel="stylesheet" href="{$editorCss}">
<link rel="stylesheet" href="{$colorPickerCss}">
<script src="{$colorPickerJs}"></script>
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
     *
     * Separator explizit "&" statt REDAXOs Default: REDAXO-Core setzt ini_set(
     * 'arg_separator.output', '&amp;') (src/core/boot.php, für HTML-sicheren Output ohne
     * eigenes Escaping). Diese URLs landen hier aber NICHT direkt im HTML, sondern als Wert in
     * $config, das json_encode()+htmlspecialchars() für das data-tb-live-config-Attribut
     * durchläuft und im Browser per JSON.parse() gelesen wird - JSON.parse() dekodiert keine
     * HTML-Entities, ein "&amp;" bliebe also wörtlich in der URL stehen (kaputte EventSource-/
     * fetch()-Aufrufe). Für diesen JSON-Kontext wird daher der echte "&" gebraucht.
     */
    private static function rootUrl(array $params): string
    {
        return '/?' . http_build_query($params, '', '&');
    }

    /**
     * Font-Stack-Optionen (Wert => Label), identisch aufgebaut zu TypographyWidget::getFields()
     * (dieselben Fallback-Stacks pro Kategorie) - zeigt genau die Fonts, die der Theme Builder
     * an anderer Stelle bereits anbietet: System-Stack, System-Fonts, bereits geladene Google
     * Fonts. Neue Google Fonts hinzufügen bleibt Aufgabe des richtigen Editors (Font Browser).
     */
    private static function buildFontOptions(): array
    {
        $options = [
            '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif' => 'System Font Stack (empfohlen)',
        ];

        $fontManager = new GoogleFontsManager();
        foreach ($fontManager->getAllAvailableFonts() as $font) {
            $family = $font['family'];
            $category = $font['category'] ?? 'sans-serif';
            $fallback = match ($category) {
                'serif' => ', "Times New Roman", Times, serif',
                'monospace' => ', "SF Mono", Monaco, Consolas, monospace',
                'cursive' => ', cursive',
                default => ', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            };

            $value = '"' . $family . '"' . $fallback;
            $sourceLabel = 'google' === ($font['source'] ?? '') ? ' (Google)' : ' (System)';
            $options[$value] = $family . $sourceLabel;
        }

        return $options;
    }

    private static function withCacheBuster(\rex_addon $addon, string $relativePath): string
    {
        $url = $addon->getAssetsUrl($relativePath);
        $absolutePath = \rex_path::addonAssets($addon->getName(), $relativePath);
        $mtime = @filemtime($absolutePath);

        return $mtime ? $url . '?buster=' . $mtime : $url;
    }
}
