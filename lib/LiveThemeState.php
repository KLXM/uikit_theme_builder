<?php

namespace UikitThemeBuilder;

/**
 * Zentrale Datei-/Validierungs-Logik für den Live Theme Editor (Frontend-Overlay + SSE).
 * Hält Draft-/Public-/Flag-Zustand als flache JSON-Dateien, analog zum bestehenden
 * "themes/compiled" / "fonts.json" Muster dieses Addons (kein DB-Schema nötig).
 */
class LiveThemeState
{
    /**
     * Kurzschlüssel (vom Widget/JS verwendet) => LESS-Variable + CSS Custom Property + Typ + Theme-Datengruppe.
     * Die Datengruppe entspricht dem Widget-Key in UikitThemeBuilderManager::compileTheme() (colors/typography).
     */
    public const FIELDS = [
        'primary' => ['less' => 'global-primary-background', 'css' => '--tb-live-primary', 'type' => 'color', 'group' => 'colors'],
        'secondary' => ['less' => 'global-secondary-background', 'css' => '--tb-live-secondary', 'type' => 'color', 'group' => 'colors'],
        'background' => ['less' => 'global-background', 'css' => '--tb-live-background', 'type' => 'color', 'group' => 'colors'],
        'color' => ['less' => 'global-color', 'css' => '--tb-live-color', 'type' => 'color', 'group' => 'colors'],
        'link' => ['less' => 'global-link-color', 'css' => '--tb-live-link', 'type' => 'color', 'group' => 'colors'],
        'font_size' => ['less' => 'global-font-size', 'css' => '--tb-live-font-size', 'type' => 'size', 'group' => 'typography'],
        'h1_size' => ['less' => 'base-h1-font-size-m', 'css' => '--tb-live-h1-size', 'type' => 'size', 'group' => 'typography'],
        'h2_size' => ['less' => 'base-h2-font-size-m', 'css' => '--tb-live-h2-size', 'type' => 'size', 'group' => 'typography'],
        'h3_size' => ['less' => 'base-h3-font-size', 'css' => '--tb-live-h3-size', 'type' => 'size', 'group' => 'typography'],
        'margin' => ['less' => 'global-margin', 'css' => '--tb-live-margin', 'type' => 'size', 'group' => 'spacing'],
        'gutter' => ['less' => 'global-gutter', 'css' => '--tb-live-gutter', 'type' => 'size', 'group' => 'spacing'],
    ];

    /**
     * Eingeloggter Redakteur mit Zugriff auf den privaten Draft (Admin oder info_center[]-Recht,
     * identisch zur Berechtigung, die info_center selbst für die Frontend-Ausgabe verlangt).
     *
     * @throws \Exception
     */
    public static function requireUser(): \rex_user
    {
        $user = \rex_backend_login::createUser();
        if (!$user || !($user->isAdmin() || $user->hasPerm('info_center[]'))) {
            throw new \Exception('Keine Berechtigung.');
        }

        return $user;
    }

    /**
     * Für Aktionen mit Wirkung auf alle Besucher (Go Live / Stop / Save) reicht Admin.
     *
     * @throws \Exception
     */
    public static function requireAdmin(): \rex_user
    {
        $user = \rex_backend_login::createUser();
        if (!$user || !$user->isAdmin()) {
            throw new \Exception('Nur Administratoren dürfen diese Aktion ausführen.');
        }

        return $user;
    }

    /**
     * @throws \Exception
     */
    public static function validateTheme(string $theme): void
    {
        if ('' === $theme || !preg_match('/^[a-zA-Z0-9_-]+$/', $theme)) {
            throw new \Exception('Ungültiger Theme-Name.');
        }

        $themes = DomainContext::getAvailableThemes();
        if (!isset($themes[$theme])) {
            throw new \Exception("Theme '{$theme}' nicht gefunden.");
        }
    }

    /**
     * Filtert/validiert ein rohes Werte-Array auf die bekannten FIELDS-Kurzschlüssel.
     * Unbekannte Keys oder ungültige Werte werden stillschweigend verworfen.
     */
    public static function sanitizeValues(array $raw): array
    {
        $clean = [];
        foreach ($raw as $key => $value) {
            if (!isset(self::FIELDS[$key]) || !is_string($value)) {
                continue;
            }

            $value = trim($value);
            $type = self::FIELDS[$key]['type'];

            if ('color' === $type && self::isValidColor($value)) {
                $clean[$key] = $value;
            } elseif ('size' === $type && self::isValidSize($value)) {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    private static function isValidColor(string $color): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/', $color)
            || (bool) preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+)\s*)?\)$/', $color)
            || (bool) preg_match('/^hsla?\(\s*\d{1,3}\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%\s*(,\s*(0|1|0?\.\d+)\s*)?\)$/', $color);
    }

    private static function isValidSize(string $size): bool
    {
        return (bool) preg_match('/^\d{1,4}(\.\d{1,3})?(px|rem|em|%)$/', $size);
    }

    private static function baseDir(): string
    {
        $dir = \rex_path::addonData('uikit_theme_builder', 'live/');
        if (!is_dir($dir)) {
            \rex_dir::create($dir);
        }

        return $dir;
    }

    public static function draftPath(string $theme, int $userId): string
    {
        return self::baseDir() . 'draft-' . $theme . '-' . $userId . '.json';
    }

    public static function publicPath(string $theme): string
    {
        return self::baseDir() . 'public-' . $theme . '.json';
    }

    public static function flagPath(string $theme): string
    {
        return self::baseDir() . 'active-' . $theme . '.flag';
    }

    /**
     * Alle Draft-Dateien eines Themes einsammeln (z.B. für Cleanup nach dem Speichern).
     *
     * @return list<string>
     */
    public static function allDraftPaths(string $theme): array
    {
        $files = glob(self::baseDir() . 'draft-' . $theme . '-*.json');

        return false === $files ? [] : $files;
    }

    public static function readJson(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    public static function writeJson(string $path, array $data): void
    {
        file_put_contents($path, json_encode($data));
    }

    public static function deleteIfExists(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * SSE-Stream für einen verbundenen Client ausgeben. Läuft für maximal ~25s, danach
     * beendet sich der Handler selbst (EventSource reconnected automatisch) - vermeidet
     * dauerhaft blockierte PHP-FPM Worker bei vielen gleichzeitigen Verbindungen.
     */
    public static function streamSse(string $mode, string $theme): void
    {
        if (!in_array($mode, ['draft', 'public'], true)) {
            return;
        }

        try {
            self::validateTheme($theme);
        } catch (\Exception $e) {
            http_response_code(404);
            return;
        }

        if ('draft' === $mode) {
            $user = \rex_backend_login::createUser();
            if (!$user || !($user->isAdmin() || $user->hasPerm('info_center[]'))) {
                http_response_code(403);
                return;
            }
            $path = self::draftPath($theme, $user->getId());
        } else {
            if (!file_exists(self::flagPath($theme))) {
                http_response_code(204);
                return;
            }
            $path = self::publicPath($theme);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $lastMtime = 0;
        $start = time();

        while (true) {
            if (connection_aborted()) {
                break;
            }

            if ('public' === $mode && !file_exists(self::flagPath($theme))) {
                echo "event: stop\ndata: {}\n\n";
                flush();
                break;
            }

            if (time() - $start > 25) {
                break;
            }

            clearstatcache(true, $path);
            $mtime = file_exists($path) ? filemtime($path) : 0;

            if ($mtime !== $lastMtime) {
                $lastMtime = $mtime;
                $data = file_exists($path) ? file_get_contents($path) : '{}';
                echo 'data: ' . $data . "\n\n";
                flush();
            }

            usleep(500000);
        }
    }
}
