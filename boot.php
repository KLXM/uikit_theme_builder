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

// Backend: UIkit und – je nach Einstellung – das Theme (nur in Vorschauen oder fürs ganze Backend), NACH allen Packages
rex_extension::register('PACKAGES_INCLUDED', static function () {
    if (rex::isBackend() && rex::getUser()) {
        \UikitThemeBuilder\BackendIntegration::register();
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
    
    // Nur für angemeldete Backend-Nutzer (Vorschau im Theme-Editor) – nicht öffentlich auf der Website
    if (!rex_backend_login::hasSession()) {
        http_response_code(403);
        echo 'Preview only for logged-in backend users';
        exit;
    }

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

// Template Manager Integration - installiere verfügbare Templates beim Boot
if (rex::isBackend() && rex::getUser()) {
    // Optionale automatische Installation: Nur einmalig triggern
    // rex_extension::register('BACKEND_READY', function() {
    //     \UikitThemeBuilder\TemplateInstaller::installAllTemplates();
    // });
}