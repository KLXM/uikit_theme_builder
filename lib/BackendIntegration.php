<?php

namespace UikitThemeBuilder;

use rex_addon;
use rex_config;
use rex_path;
use rex_url;
use rex_view;

/**
 * Theme im REDAXO-Backend (Einstellungen → Backend):
 *
 *  - backend_theme: '' (kein Theme), '__domain__' (Theme der aktuellen YRewrite-Domain) oder Name eines Themes
 *  - backend_mode:  'scoped' (Standard) – Theme nur in Vorschauen (CssScoper, Datei <theme>.backend.css);
 *                   'full' – Theme fürs ganze Backend (bisheriges Verhalten) mit backend-overrides.css
 *  - backend_scope: Bereiche für 'scoped' (CSS-Selektoren je Zeile; leer = CssScoper::DEFAULT_SCOPE)
 *
 * UIkit selbst (compiled_uikit) wird wie bisher immer geladen – Widgets, Icons und Listen im Backend brauchen es.
 * Eigene Bereiche im Backend bekommen das Theme mit der Klasse „uk-theme-scope“.
 */
final class BackendIntegration
{
    public const DOMAIN_THEME = '__domain__';
    public const MODES = ['scoped', 'full'];

    public static function register(): void
    {
        $theme = self::theme();
        $base = rex_path::addonAssets('uikit_theme_builder', 'compiled_uikit/css/uikit.min.css');

        if ('' === $theme || !is_file(self::themeFile($theme))) {
            if ('' !== $theme) {
                Log::warningOnce('missing_theme:' . $theme, 'Backend Theme file not found: ' . self::themeFile($theme) . ' - Loading default UIkit CSS');
            }
            if (is_file($base)) {
                rex_view::addCssFile(rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/css/uikit.min.css'));
            }

            return;
        }

        if ('full' === self::mode()) {
            // Theme mit Komponenten-Auswahl: dem Backend fehlen dann Komponenten (Form, Table, Tab …) – vollständiges UIkit zuerst
            if (self::hasDisabledComponents($theme) && is_file($base)) {
                rex_view::addCssFile(rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/css/uikit.min.css'));
            }
            rex_view::addCssFile(self::versioned('themes/compiled/' . $theme . '.css'));
            // danach: REDAXO-Grundlagen zurücksetzen (Hintergrund hell/dunkel, Überschriften, Links, Menü)
            rex_view::addCssFile(self::versioned('backend-overrides.css'));
        } else {
            if (is_file($base)) {
                rex_view::addCssFile(rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/css/uikit.min.css'));
            }
            $scoped = self::ensureScoped($theme);
            if (null !== $scoped) {
                rex_view::addCssFile(self::versioned('themes/compiled/' . basename($scoped)));
            }
        }
        self::addFonts();
    }

    /** Theme fürs Backend ('' = keins); „Theme der Domain“ aufgelöst. */
    public static function theme(): string
    {
        $theme = (string) rex_config::get('uikit_theme_builder', 'backend_theme', '');
        if (self::DOMAIN_THEME === $theme) {
            $theme = (string) DomainContext::getCurrentTheme();
        }

        return preg_match('~^[A-Za-z0-9_-]+$~', $theme) ? $theme : '';
    }

    public static function mode(): string
    {
        $mode = (string) rex_config::get('uikit_theme_builder', 'backend_mode', 'scoped');

        return in_array($mode, self::MODES, true) ? $mode : 'scoped';
    }

    public static function scope(): string
    {
        return CssScoper::scopeSelector((string) rex_config::get('uikit_theme_builder', 'backend_scope', ''));
    }

    /**
     * Eingegrenzte Fassung eines Themes (assets/themes/compiled/<theme>.backend.css) – neu erzeugt, wenn sie fehlt, das Theme
     * neu kompiliert wurde oder sich die Bereiche geändert haben. null, wenn das Theme fehlt.
     */
    public static function ensureScoped(string $theme): ?string
    {
        $dir = rex_path::addonAssets('uikit_theme_builder', 'themes/compiled/');
        $source = is_file($dir . $theme . '.min.css') ? $dir . $theme . '.min.css' : $dir . $theme . '.css';
        if (!is_file($source)) {
            return null;
        }
        $target = $dir . $theme . '.backend.css';
        $scope = self::scope();
        $stamp = '/* uikit_theme_builder backend: ' . md5($scope . '|' . filemtime($source) . '|' . filesize($source)) . ' */';
        if (is_file($target)) {
            $handle = fopen($target, 'r');
            $first = $handle ? rtrim((string) fgets($handle)) : '';
            if ($handle) {
                fclose($handle);
            }
            if ($first === $stamp) {
                return $target;
            }
        }
        $css = CssScoper::scope((string) file_get_contents($source), $scope);
        \rex_file::put($target, $stamp . "\n" . $css);

        return $target;
    }

    /** Nach dem Kompilieren: eingegrenzte Fassung verwerfen (wird beim nächsten Backend-Aufruf neu erzeugt). */
    public static function invalidate(string $theme): void
    {
        $file = rex_path::addonAssets('uikit_theme_builder', 'themes/compiled/' . $theme . '.backend.css');
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private static function themeFile(string $theme): string
    {
        return rex_path::addonAssets('uikit_theme_builder', 'themes/compiled/' . $theme . '.css');
    }

    private static function versioned(string $rel): string
    {
        $path = rex_path::addonAssets('uikit_theme_builder', $rel);

        return rex_url::addonAssets('uikit_theme_builder', $rel) . (is_file($path) ? '?v=' . filemtime($path) : '');
    }

    private static function hasDisabledComponents(string $theme): bool
    {
        try {
            $data = (new UikitThemeBuilderManager())->loadTheme($theme);

            return [] !== Widget\ComponentSelectionWidget::disabledFrom((array) ($data['data'] ?? []));
        } catch (\Throwable) {
            return false;
        }
    }

    /** Google Fonts des Backend-Themes (lokal gespeichert, fonts.json). */
    private static function addFonts(): void
    {
        $file = rex_path::addonData('uikit_theme_builder') . 'fonts.json';
        if (!is_file($file)) {
            return;
        }
        $fonts = json_decode((string) file_get_contents($file), true);
        if (!is_array($fonts)) {
            return;
        }
        foreach ($fonts as $family => $info) {
            if (!isset($info['css_file']) || !is_file((string) $info['css_file'])) {
                continue;
            }
            if ('' !== (string) ($info['css_url'] ?? '')) {
                $url = (string) $info['css_url'];
            } elseif (str_starts_with((string) $info['css_file'], rex_path::assets())) {
                $url = rex_url::assets(str_replace(rex_path::assets(), '', (string) $info['css_file']));
            } else {
                $url = rex_url::addonAssets('uikit_theme_builder', str_replace(rex_path::addonAssets('uikit_theme_builder'), '', (string) $info['css_file']));
            }
            rex_view::addCssFile($url);
            Log::debug('Backend Font loaded: ' . $family . ' from ' . $url);
        }
    }

    /** Für Hinweise in den Einstellungen: Ist das Theme des Backends gerade eingebunden? */
    public static function isActive(): bool
    {
        return '' !== self::theme() && rex_addon::get('uikit_theme_builder')->isAvailable();
    }
}
