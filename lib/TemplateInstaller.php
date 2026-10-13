<?php

/**
 * Template Installer für UIKit Theme Builder.
 *
 * Verwaltet die Installation von mitgelieferten Templates
 * Templates werden direkt in der Datenbank erstellt (wie REDAXO Core)
 */

namespace UikitThemeBuilder;

use rex;
use rex_addon;
use rex_extension;
use rex_extension_point;
use rex_file;
use rex_logger;
use rex_sql;
use rex_sql_exception;
use rex_template_cache;

use const PATHINFO_EXTENSION;
use const PATHINFO_FILENAME;

class TemplateInstaller
{
    /**
     * Liste aller verfügbaren Templates.
     * @return array<string>
     */
    public static function getAvailableTemplates(): array
    {
        $addon = rex_addon::get('uikit_theme_builder');
        $templatesPath = $addon->getPath('templates');
        $templates = [];

        if (is_dir($templatesPath)) {
            foreach (scandir($templatesPath) as $file) {
                if ('php' === pathinfo($file, PATHINFO_EXTENSION) && '.' !== $file && '..' !== $file) {
                    $templates[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }

        return $templates;
    }

    /**
     * Template Metadaten auslesen.
     * @return array<string, string>
     */
    public static function getTemplateMetadata(string $templateName): array
    {
        $addon = rex_addon::get('uikit_theme_builder');
        $templatePath = $addon->getPath('templates') . '/' . $templateName . '.php';

        if (!file_exists($templatePath)) {
            return [];
        }

        $content = rex_file::get($templatePath);
        $metadata = [];

        // Ersten DocBlock auslesen und in Zeilen zerlegen
        if (!preg_match('/\/\*\*(.*?)\*\//s', $content, $docMatch)) {
            return $metadata;
        }

        $lines = [];
        foreach (preg_split('/\R/', $docMatch[1]) as $rawLine) {
            $line = trim(preg_replace('/^\s*\*\s?/', '', $rawLine));
            if ('' !== $line) {
                $lines[] = $line;
            }
        }

        // Ab DOMAIN_SETTINGS folgen nur noch Einstellungsdefinitionen
        $docLines = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, 'DOMAIN_SETTINGS')) {
                break;
            }
            $docLines[] = $line;
        }

        // @name / @description bevorzugen
        foreach ($docLines as $line) {
            if (preg_match('/^@name\s+(.+)$/', $line, $m)) {
                $metadata['name'] = trim($m[1]);
            } elseif (preg_match('/^@description\s+(.+)$/', $line, $m)) {
                $metadata['description'] = trim($m[1]);
            }
        }

        // Fallbacks: erste Zeile ohne Tag = Name, zweite Zeile ohne Tag = Beschreibung
        $plainLines = array_values(array_filter($docLines, static fn ($line) => !str_starts_with($line, '@')));
        if (!isset($metadata['name']) && isset($plainLines[0])) {
            $metadata['name'] = $plainLines[0];
        }
        if (!isset($metadata['description'])) {
            $candidate = $plainLines[1] ?? null;
            if (null !== $candidate && $candidate !== ($metadata['name'] ?? null)) {
                $metadata['description'] = $candidate;
            }
        }

        return $metadata;
    }

    /**
     * Template in die Datenbank installieren (REDAXO Core kompatibel)
     * Überschreibt existierende Templates mit demselben Key.
     *
     * @param string $templateName Name der Template-Datei (ohne .php)
     * @param string $templateKey Eindeutiger Key für das Template
     * @param string $templateDisplayName Anzeigename in REDAXO
     * @return bool|int Template-ID bei Erfolg, false bei Fehler
     */
    public static function installTemplate(string $templateName, string $templateKey, string $templateDisplayName = ''): bool|int
    {
        if (empty($templateDisplayName)) {
            $templateDisplayName = $templateName;
        }

        $addon = rex_addon::get('uikit_theme_builder');
        $sourcePath = $addon->getPath('templates') . '/' . $templateName . '.php';

        if (!file_exists($sourcePath)) {
            return false;
        }

        // Template-Content laden
        $templateContent = rex_file::get($sourcePath);

        try {
            // Prüfe ob Template mit diesem Key bereits existiert
            $existingTemplate = rex_sql::factory();
            $existingTemplate->setQuery('SELECT id FROM ' . rex::getTable('template') . ' WHERE `key` = ?', [$templateKey]);
            $existingId = $existingTemplate->getRows() > 0 ? (int) $existingTemplate->getValue('id') : null;

            // Template in der Datenbank erstellen/aktualisieren - EXAKT wie REDAXO Core
            $templateSql = rex_sql::factory();
            $templateSql->setTable(rex::getTable('template'));
            $templateSql->setValue('key', $templateKey);
            $templateSql->setValue('name', $templateDisplayName);
            $templateSql->setValue('content', $templateContent);
            $templateSql->setValue('active', 1);

            // Attributes: Alle Module und Kategorien erlaubt
            $attributes = [
                'ctype' => [],
                'modules' => [1 => ['all' => 1]], // Alle Module
                'categories' => ['all' => 1], // Alle Kategorien
            ];
            $templateSql->setArrayValue('attributes', $attributes);

            $isUpdate = false;
            if ($existingId) {
                // UPDATE: Vorhandenes Template überschreiben
                $templateSql->addGlobalUpdateFields();
                $templateSql->setWhere(['id' => $existingId]);
                $templateSql->update();
                $templateId = $existingId;
                $isUpdate = true;
            } else {
                // INSERT: Neues Template erstellen
                $templateSql->addGlobalCreateFields();
                $templateSql->insert();
                $templateId = (int) $templateSql->getLastId();
            }

            // Template-Cache löschen
            rex_template_cache::delete($templateId);

            // Extension Point registrieren
            $extensionPoint = $isUpdate ? 'TEMPLATE_UPDATED' : 'TEMPLATE_ADDED';
            rex_extension::registerPoint(new rex_extension_point($extensionPoint, '', [
                'id' => $templateId,
                'key' => $templateKey,
                'name' => $templateDisplayName,
                'content' => $templateContent,
                'active' => 1,
            ]));

            return $templateId;
        } catch (rex_sql_exception $e) {
            rex_logger::factory()->error('Template Installation Fehler: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Alle verfügbaren Templates installieren.
     * @return array<string, bool>
     */
    public static function installAllTemplates(): array
    {
        $result = [];
        $templates = self::getAvailableTemplates();

        foreach ($templates as $template) {
            $result[$template] = false !== self::installTemplate($template, $template);
        }

        return $result;
    }
}
