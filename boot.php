<?php

/**
 * UIKit Theme Builder AddOn - Bootstrap
 * Visueller Theme Builder für UIKit3 Frameworks
 */

// REX-API registrieren
rex_api_function::register('uikit_theme_builder', \UikitThemeBuilder\UikitThemeBuilderApi::class);
rex_api_function::register('uikit_theme_icons', 'rex_api_uikit_theme_icons');

$registerForNames = static function (string $legacyName, callable $callable): void {
    $names = [$legacyName];
    if (str_starts_with($legacyName, 'BUILDER_')) {
        $names[] = 'BUILDER_' . substr($legacyName, strlen('BUILDER_'));
    }

    foreach (array_values(array_unique($names)) as $name) {
        rex_extension::register($name, $callable);
    }
};

// Theme-Provider Extension-Points fuer builder (generisch nutzbar)
$registerForNames('BUILDER_THEME_PROVIDER_AVAILABLE', static function (rex_extension_point $ep): bool {
    return true;
});

$registerForNames('BUILDER_THEME_CHOICES', static function (rex_extension_point $ep): array {
    $choices = \UikitThemeBuilder\DomainContext::getAvailableThemes();
    return is_array($choices) ? $choices : [];
});

$registerForNames('BUILDER_THEME_CONTEXT_RESET', static function (rex_extension_point $ep) {
    \UikitThemeBuilder\DomainContext::resetContext();
    return $ep->getSubject();
});

$registerForNames('BUILDER_THEME_CONTEXT_SET', static function (rex_extension_point $ep) {
    $themeName = trim((string) $ep->getSubject());
    if ($themeName !== '') {
        \UikitThemeBuilder\DomainContext::setTheme($themeName);
    }

    return $ep->getSubject();
});

$registerForNames('BUILDER_THEME_BACKGROUND_OPTIONS', static function (rex_extension_point $ep): array {
    $framework = (string) $ep->getParam('framework', 'uikit');
    if ($framework !== 'uikit') {
        $subject = $ep->getSubject();
        return is_array($subject) ? $subject : [];
    }

    $options = \UikitThemeBuilder\DomainContext::getBackgroundOptions();
    return is_array($options) ? $options : [];
});

$registerForNames('BUILDER_THEME_TEXT_COLOR_OPTIONS', static function (rex_extension_point $ep): array {
    $framework = (string) $ep->getParam('framework', 'uikit');
    if ($framework !== 'uikit') {
        $subject = $ep->getSubject();
        return is_array($subject) ? $subject : [];
    }

    $options = \UikitThemeBuilder\DomainContext::getTextColorOptions();
    return is_array($options) ? $options : [];
});

$registerForNames('BUILDER_THEME_CARD_STYLE_OPTIONS', static function (rex_extension_point $ep): array {
    $framework = (string) $ep->getParam('framework', 'uikit');
    if ($framework !== 'uikit') {
        $subject = $ep->getSubject();
        return is_array($subject) ? $subject : [];
    }

    $options = \UikitThemeBuilder\DomainContext::getCardStyleOptions();
    return is_array($options) ? $options : [];
});

$registerForNames('BUILDER_FRAMEWORK_NORMALIZE', static function (rex_extension_point $ep): string {
    $framework = trim((string) $ep->getSubject());
    if ($framework === 'bootstrap') {
        return 'uikit';
    }

    return $framework;
});

// Globale Backend-Assets laden
if (rex::isBackend() && rex::getUser()) {
    // UIKit Core aus compiled_uikit (wird für Icon-Rendering benötigt)
    rex_view::addJsFile(rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/js/uikit.min.js'));
    
    // Pickit Color (https://github.com/skerbis/pickit_color) - eigene Colorpicker-Bibliothek
    rex_view::addCssFile(rex_url::addonAssets('uikit_theme_builder', 'pickit-color/colorpicker.min.css'));
    rex_view::addJsFile(rex_url::addonAssets('uikit_theme_builder', 'pickit-color/colorpicker.min.js'));
    
    // UIKit Icon Picker
    rex_view::addJsFile(rex_url::addonAssets('uikit_theme_builder', 'js/uikit-icon-picker.js'));
    
    // Custom Icons: Icon-Liste für Icon Picker bereitstellen
    $iconBuilder = new UikitThemeBuilder\CustomIconBuilder();
    $availableIcons = $iconBuilder->getAllAvailableIcons();
    rex_view::setJsProperty('uikitAvailableIcons', $availableIcons);
    rex_view::setJsProperty(
        'uikitThemeIconsApiUrl',
        rex_url::backendController(['rex-api-call' => 'uikit_theme_icons'], false)
    );
    
    // Extended Icons-Datei laden (enthält ALLE Icons: UIkit Standard + Custom)
    // WICHTIG: NICHT die Standard uikit-icons.min.js laden, da Extended alles enthält
    if ($iconBuilder->hasExtendedIcons()) {
        rex_view::addJsFile($iconBuilder->getExtendedIconsUrl());
    }

    // TinyMCE Plugin registrieren (UIKit Icon Picker)
    if (rex_addon::get('tinymce')->isAvailable() && class_exists(\FriendsOfRedaxo\TinyMce\PluginRegistry::class)) {
        \FriendsOfRedaxo\TinyMce\PluginRegistry::addPlugin(
            'uikit_iconpicker',
            rex_url::addonAssets('uikit_theme_builder', 'js/tinymce-uikit-iconpicker-plugin.js'),
            'uikit_iconpicker'
        );
    }
}

// Backend Theme über Extension Point NACH allen anderen Packages laden
rex_extension::register('PACKAGES_INCLUDED', function() {
    if (rex::isBackend() && rex::getUser()) {
        $backendTheme = rex_config::get('uikit_theme_builder', 'backend_theme', '');
        
        // Wenn kein Theme konfiguriert: Standard UIKit CSS laden
        if (empty($backendTheme)) {
            $defaultCss = rex_path::addonAssets('uikit_theme_builder', 'compiled_uikit/css/uikit.min.css');
            if (file_exists($defaultCss)) {
                $defaultUrl = rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/css/uikit.min.css');
                rex_view::addCssFile($defaultUrl);
                if (rex::isDebugMode()) {
                    rex_logger::factory()->debug('Backend: Default UIKit CSS loaded from compiled_uikit', [], 'uikit_theme_builder');
                }
            }
            return;
        }
        
        // Theme CSS laden
        $themeFile = rex_path::addonAssets('uikit_theme_builder', 'themes/compiled/' . $backendTheme . '.css');
        if (file_exists($themeFile)) {
            $themeUrl = rex_url::addonAssets('uikit_theme_builder', 'themes/compiled/' . $backendTheme . '.css');
            rex_view::addCssFile($themeUrl);

            // Backend-Overrides NACH dem Theme laden: setzt Basisstile (Code-Auszeichnungen,
            // Body-Hintergrund etc.), die mit dem REDAXO-Backend kollidieren, wieder zurück
            rex_view::addCssFile(rex_url::addonAssets('uikit_theme_builder', 'backend-overrides.css'));

            // Google Fonts für Backend-Theme laden (direkt über Dateisystem)
            $fontsDataFile = rex_path::addonData('uikit_theme_builder') . 'fonts.json';
            if (file_exists($fontsDataFile)) {
                $fontsData = json_decode(file_get_contents($fontsDataFile), true);
                if (is_array($fontsData)) {
                    foreach ($fontsData as $fontFamily => $fontInfo) {
                        if (!isset($fontInfo['css_file']) || !file_exists($fontInfo['css_file'])) {
                            continue;
                        }

                        if (isset($fontInfo['css_url']) && '' !== (string) $fontInfo['css_url']) {
                            $fontUrl = (string) $fontInfo['css_url'];
                        } elseif (str_starts_with((string) $fontInfo['css_file'], rex_path::assets())) {
                            $relativePath = str_replace(rex_path::assets(), '', (string) $fontInfo['css_file']);
                            $fontUrl = rex_url::assets($relativePath);
                        } else {
                            $relativePath = str_replace(rex_path::addonAssets('uikit_theme_builder'), '', (string) $fontInfo['css_file']);
                            $fontUrl = rex_url::addonAssets('uikit_theme_builder', $relativePath);
                        }

                        rex_view::addCssFile($fontUrl);
                        if (rex::isDebugMode()) {
                            rex_logger::factory()->debug('Backend Font loaded: ' . $fontFamily . ' from ' . $fontUrl, [], 'uikit_theme_builder');
                        }
                    }
                }
            }
        } else {
            rex_logger::factory()->log('warning', 'Backend Theme file not found: ' . $themeFile . ' - Loading default UIKit CSS', [], 'uikit_theme_builder');
            
            // Fallback: Standard UIKit CSS laden
            $defaultCss = rex_path::addonAssets('uikit_theme_builder', 'compiled_uikit/css/uikit.min.css');
            if (file_exists($defaultCss)) {
                $defaultUrl = rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/css/uikit.min.css');
                rex_view::addCssFile($defaultUrl);
            }
        }
    }
});

// Verzeichnisse erstellen falls nicht vorhanden
$directories = [
    'themes/saved',
    'themes/compiled',
    'themes/temp'
];

foreach ($directories as $dir) {
    $fullPath = rex_path::addonData('uikit_theme_builder', $dir);
    if (!is_dir($fullPath)) {
        rex_dir::create($fullPath);
    }
}

// UIKit Source kopieren falls nicht vorhanden
$uikitPath = rex_path::addon('uikit_theme_builder', 'sources/uikit');
if (!is_dir($uikitPath)) {
    $sourceUikit = rex_path::frontend('uikit');
    if (is_dir($sourceUikit)) {
        rex_dir::copy($sourceUikit, $uikitPath);
    }
}

// Preview-Route über Output-Buffer - robuste Implementierung
if (rex_request('previewtheme', 'string')) {
    $themeName = rex_request('previewtheme', 'string');
    
    // Sicherheitscheck
    if (empty($themeName) || !preg_match('/^[a-zA-Z0-9_-]+$/', $themeName)) {
        http_response_code(400);
        echo 'Invalid theme name';
        exit;
    }
    
    try {
        // Output Buffer vollständig leeren und Headers setzen
        rex_response::cleanOutputBuffers();
        
        // Zusätzliche Headers für iframe-Kompatibilität
        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: SAMEORIGIN');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Preview Controller laden und ausgeben
        $previewController = new UikitThemeBuilder\PreviewController();
        $previewController->showPreview($themeName);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo '<h1>Preview Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    
    exit; // Normale REDAXO-Verarbeitung stoppen
}

// Live Theme Editor: SSE-Stream. "draft" = private Vorschau der eigenen Session (z.B. um
// mehrere eigene Tabs synchron zu halten). "public" = Broadcast an alle Besucher, aber NUR
// solange die "Live schalten"-Checkbox im Editor aktiv ist (explizites Opt-in, siehe
// LiveThemeState::flagPath()) - kein automatischer Broadcast ohne diese Checkbox.
$themeLiveStreamMode = rex_request('theme_live_stream', 'string', '');
if ('' !== $themeLiveStreamMode) {
    $themeLiveStreamTheme = rex_request('theme', 'string', '');
    UikitThemeBuilder\LiveThemeState::streamSse($themeLiveStreamMode, $themeLiveStreamTheme);
    exit;
}

// Live Theme Editor: Bridge-CSS laden für (a) eingeloggte Redakteure mit Editor-Recht (eigene
// private Vorschau) ODER (b) alle Besucher, solange für das Domain-Theme "Live schalten" aktiv
// ist (flagPath()) - NICHT unconditional für jeden Besucher immer. Die Bridge-Regeln nutzen
// var(--tb-live-x, inherit) !important - "inherit" ist auf html/body (kein Elternelement)
// gleichbedeutend mit "initial" und überschreibt dort IMMER Hintergrund/Farbe/Schrift des
// echten Themes, auch ganz ohne aktive Live-Vorschau. Unconditional laden hätte also jede Seite
// für jeden Besucher kaputt gemacht.
// Zusätzlich nur, wenn das aktuelle Template das Domain-Theme auch tatsächlich per
// TemplateHelper::includeAllStyles() eingebunden hat (siehe LiveThemeEditorWidget) - sonst würde
// die Bridge-CSS die Seite kaputt machen, obwohl das Widget dort gar nicht angezeigt wird
// (z.B. Templates mit eigener statischer CSS-Datei).
if (rex::isFrontend()) {
    rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
        $theme = UikitThemeBuilder\DomainContext::getCurrentTheme();
        if (!$theme || !UikitThemeBuilder\TemplateHelper::isThemeIncluded($theme)) {
            return;
        }

        $user = rex_backend_login::createUser();
        $isEditor = $user && UikitThemeBuilder\LiveThemeState::canUseEditor($user);
        // Zusätzlich zum Flag prüfen wir den globalen Addon-Schalter: wird die Einstellung
        // deaktiviert, während eine Live-Session noch läuft, soll der Broadcast SOFORT enden
        // (nicht erst wenn jemand "Live-Session beenden" klickt) - sonst könnte ein Admin die
        // Einstellung deaktivieren und trotzdem denkt, Besucher sehen nichts mehr live.
        $liveActive = UikitThemeBuilder\DomainContext::isLiveBroadcastEnabled()
            && UikitThemeBuilder\LiveThemeState::isBroadcastActive($theme);

        if (!$isEditor && !$liveActive) {
            return;
        }

        $content = $ep->getSubject();
        $addon = rex_addon::get('uikit_theme_builder');

        // Cache-Buster manuell anhängen (Filemtime) - dieser Tag geht direkt raus statt über
        // rex_view::addCssFile(), das das sonst automatisch übernehmen würde.
        $bridgeCssPath = rex_path::addonAssets('uikit_theme_builder', 'live-editor/live-theme-editor-bridge.css');
        $bridgeCssMtime = @filemtime($bridgeCssPath);
        $bridgeCssUrl = $addon->getAssetsUrl('live-editor/live-theme-editor-bridge.css') . ($bridgeCssMtime ? '?buster=' . $bridgeCssMtime : '');
        $bridgeCssTag = '<link rel="stylesheet" href="' . $bridgeCssUrl . '"></head>';
        $content = str_ireplace('</head>', $bridgeCssTag, $content);

        // Public-Listener nur für Besucher OHNE eigenes Editor-Widget (das synct sich schon
        // selbst über den Draft-Stream) und nur solange "Live schalten" aktiv ist.
        if (!$isEditor && $liveActive) {
            // Bewusst keine rex_url::frontendController() (=.../index.php?...) - unter YRewrite
            // würde "index.php" als Artikel-Pfad gesucht und 404en, bevor der Stream startet.
            // REDAXO-Core setzt ini_set('arg_separator.output', '&amp;') (src/core/boot.php) -
            // http_build_query() liefert hier also schon HTML-sicheren Output; KEIN zusätzliches
            // rex_escape() (sonst "&amp;amp;", kaputte URL im Browser).
            $streamUrl = '/?' . http_build_query(['theme_live_stream' => 'public', 'theme' => $theme]);
            $listenerJsPath = rex_path::addonAssets('uikit_theme_builder', 'live-editor/live-theme-public-listener.js');
            $listenerJsMtime = @filemtime($listenerJsPath);
            $listenerJsUrl = $addon->getAssetsUrl('live-editor/live-theme-public-listener.js') . ($listenerJsMtime ? '?buster=' . $listenerJsMtime : '');
            $themeCssUrlTemplate = rex_url::addonAssets('uikit_theme_builder', 'themes/compiled/__THEME__.css');
            $fontsBaseUrl = rex_url::assets('addons/uikit_theme_builder/fonts/');
            $listenerTag = '<script src="' . $listenerJsUrl . '" data-stream-url="' . $streamUrl . '" data-theme-css-template="' . rex_escape($themeCssUrlTemplate) . '" data-fonts-base-url="' . rex_escape($fontsBaseUrl) . '"></script></body>';
            $content = str_ireplace('</body>', $listenerTag, $content);
        }

        $ep->setSubject($content);
    });
}

// Backend-Warnung: solange "Live schalten" für irgendein Theme aktiv ist, sollen Admins das auf
// JEDER Backend-Seite sehen (leicht vergessen, wieder auszuschalten - eine laufende Session
// betrifft die echte, öffentliche Website). Automatisches 1h-Timeout als zusätzliches
// Sicherheitsnetz sitzt in LiveThemeState::isBroadcastActive() - diese Warnung ist nur die
// sichtbare Erinnerung dazu, kein Ersatz dafür.
rex_extension::register('PAGE_HEADER', function (rex_extension_point $ep) {
    if (!rex::isBackend() || !rex::getUser() || !rex::getUser()->isAdmin()) {
        return $ep->getSubject();
    }

    $activeThemes = [];
    foreach (UikitThemeBuilder\DomainContext::getAvailableThemes() as $themeName => $themeLabel) {
        $startedAt = UikitThemeBuilder\LiveThemeState::broadcastStartedAt($themeName);
        if (null !== $startedAt) {
            $activeThemes[] = rex_escape($themeLabel) . ' (seit ' . date('H:i', $startedAt) . ' Uhr)';
        }
    }

    if ([] === $activeThemes) {
        return $ep->getSubject();
    }

    // Kein rex_view::warning() (normaler Alert-Box-Baustein, fließt im normalen Content-Fluss) -
    // der REDAXO-Header ist sticky/fixed und würde eine PAGE_HEADER-Box sofort überdecken.
    // Stattdessen ein kleines, selbst positioniertes Label mit hohem z-index, unabhängig vom
    // Seitenlayout - titelt zusätzlich (title-Attribut) den vollen Text für den Hover.
    $label = 'LIVE für Besucher: ' . implode(', ', $activeThemes);
    $settingsUrl = rex_url::backendPage('uikit_theme_builder/settings');
    $badge = '<a href="' . rex_escape($settingsUrl) . '" '
        . 'title="Läuft automatisch nach 1 Stunde ab, kann im Live Theme Editor über die \'Live schalten\'-Checkbox vorher beendet werden." '
        . 'style="position:fixed;top:0;left:50%;transform:translateX(-50%);z-index:99999;'
        . 'background:#e74c3c;color:#fff;padding:3px 12px;font:600 11px/1.4 -apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif;'
        . 'border-radius:0 0 6px 6px;box-shadow:0 2px 6px rgba(0,0,0,.3);white-space:nowrap;text-decoration:none;">'
        . rex_escape($label) . '</a>';

    return $ep->getSubject() . $badge;
});

// Live Theme Editor Widget im Info Center registrieren - dem in info_center/README.md
// dokumentierten Muster folgend: Registrierung vom konsumierenden Addon aus, verzögert bis
// PACKAGES_INCLUDED (rex_extension::LATE), damit alle Addons inkl. info_center sicher
// fertig gebootet sind. info_center selbst muss dafür NICHT verändert werden.
if (rex_addon::get('info_center')->isAvailable()) {
    rex_extension::register('PACKAGES_INCLUDED', function () {
        $infoCenter = \KLXM\InfoCenter\InfoCenter::getInstance();
        $widget = new UikitThemeBuilder\InfoCenterWidgets\LiveThemeEditorWidget();
        $infoCenter->registerWidget($widget);
    }, rex_extension::LATE);
}

// Template Manager Integration - installiere verfügbare Templates beim Boot
if (rex::isBackend() && rex::getUser()) {
    // Optionale automatische Installation: Nur einmalig triggern
    // rex_extension::register('BACKEND_READY', function() {
    //     \UikitThemeBuilder\TemplateInstaller::installAllTemplates();
    // });
}