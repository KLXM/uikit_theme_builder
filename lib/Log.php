<?php

namespace UikitThemeBuilder;

/**
 * Protokoll des AddOns ohne Flut im Systemlog:
 * - debug(): nur im REDAXO-Debug-Modus UND mit der AddOn-Einstellung „debug_log“ (Standard aus) – die Meldungen entstehen
 *   sonst bei jedem Backend-/Frontend-Aufruf
 * - warningOnce(): eine Warnung je Schlüssel nur einmal (z. B. fehlende Theme-Datei), bis resetOnce() sie wieder freigibt
 */
final class Log
{
    public static function enabled(): bool
    {
        return \rex::isDebugMode() && (bool) \rex_addon::get('uikit_theme_builder')->getConfig('debug_log', false);
    }

    public static function debug(string $message): void
    {
        if (self::enabled()) {
            \rex_logger::factory()->debug($message, [], 'uikit_theme_builder');
        }
    }

    public static function warningOnce(string $key, string $message): void
    {
        $addon = \rex_addon::get('uikit_theme_builder');
        $logged = (array) $addon->getConfig('logged_once', []);
        if (isset($logged[$key])) {
            return;
        }
        $logged[$key] = time();
        $addon->setConfig('logged_once', $logged);
        \rex_logger::factory()->log('warning', $message, [], 'uikit_theme_builder');
    }

    public static function resetOnce(string $key): void
    {
        $addon = \rex_addon::get('uikit_theme_builder');
        $logged = (array) $addon->getConfig('logged_once', []);
        if (isset($logged[$key])) {
            unset($logged[$key]);
            $addon->setConfig('logged_once', $logged);
        }
    }
}
