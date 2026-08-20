<?php

namespace UikitThemeBuilder;

/**
 * Zentrale Datei-/Validierungs-Logik für den Live Theme Editor (Frontend-Overlay + SSE).
 * Hält Draft-/Public-/Flag-Zustand als flache JSON-Dateien, analog zum bestehenden
 * "themes/compiled" / "fonts.json" Muster dieses Addons (kein DB-Schema nötig). Draft ist
 * immer privat (nur eigene Session, siehe canUseEditor()); Public/Flag existieren nur, wenn
 * der Redakteur die "Live schalten"-Checkbox im Editor explizit aktiviert hat (Opt-in, kein
 * automatischer Broadcast).
 */
class LiveThemeState
{
    /**
     * Kurzschlüssel (vom Widget/JS verwendet) => LESS-Variable + CSS Custom Property + Typ + Theme-Datengruppe.
     * Die Datengruppe entspricht dem Widget-Key in UikitThemeBuilderManager::compileTheme() (colors/typography).
     */
    public const FIELDS = [
        // Farben (komplette ColorsWidget-Palette)
        'primary' => ['less' => 'global-primary-background', 'css' => '--tb-live-primary', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'secondary' => ['less' => 'global-secondary-background', 'css' => '--tb-live-secondary', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'success' => ['less' => 'global-success-background', 'css' => '--tb-live-success', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'warning' => ['less' => 'global-warning-background', 'css' => '--tb-live-warning', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'danger' => ['less' => 'global-danger-background', 'css' => '--tb-live-danger', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'color' => ['less' => 'global-color', 'css' => '--tb-live-color', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'emphasis' => ['less' => 'global-emphasis-color', 'css' => '--tb-live-emphasis', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'muted' => ['less' => 'global-muted-color', 'css' => '--tb-live-muted', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'link' => ['less' => 'global-link-color', 'css' => '--tb-live-link', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'link_hover' => ['less' => 'global-link-hover-color', 'css' => '--tb-live-link-hover', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'inverse' => ['less' => 'global-inverse-color', 'css' => '--tb-live-inverse', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'border' => ['less' => 'global-border', 'css' => '--tb-live-border', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'background' => ['less' => 'global-background', 'css' => '--tb-live-background', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'muted_background' => ['less' => 'global-muted-background', 'css' => '--tb-live-muted-background', 'type' => 'color', 'group' => 'colors', 'section' => 'colors'],
        'heading_color' => ['less' => 'base-heading-color', 'css' => '--tb-live-heading-color', 'type' => 'color', 'group' => 'typography', 'section' => 'colors'],

        // Typografie
        'font_family' => ['less' => 'global-font-family', 'css' => '--tb-live-font-family', 'type' => 'font', 'group' => 'typography', 'section' => 'typography'],
        'heading_font_family' => ['less' => 'base-heading-font-family', 'css' => '--tb-live-heading-font-family', 'type' => 'font', 'group' => 'typography', 'section' => 'typography'],
        'font_size' => ['less' => 'global-font-size', 'css' => '--tb-live-font-size', 'type' => 'size', 'group' => 'typography', 'section' => 'typography'],
        'line_height' => ['less' => 'global-line-height', 'css' => '--tb-live-line-height', 'type' => 'number', 'group' => 'typography', 'section' => 'typography'],
        // Kein einzelnes "heading-line-height"-Feld in TypographyWidget - UIkit selbst hat
        // aber pro Ebene eigene Variablen (base-h1..h4-line-height); ein Regler schreibt hier
        // bewusst auf alle vier gleichzeitig ("less" als Liste statt einzelnem String).
        'heading_line_height' => ['less' => ['base-h1-line-height', 'base-h2-line-height', 'base-h3-line-height', 'base-h4-line-height'], 'css' => '--tb-live-heading-line-height', 'type' => 'number', 'group' => 'typography', 'section' => 'typography'],
        'h1_size' => ['less' => 'base-h1-font-size-m', 'css' => '--tb-live-h1-size', 'type' => 'size', 'group' => 'typography', 'section' => 'typography'],
        'h2_size' => ['less' => 'base-h2-font-size-m', 'css' => '--tb-live-h2-size', 'type' => 'size', 'group' => 'typography', 'section' => 'typography'],
        'h3_size' => ['less' => 'base-h3-font-size', 'css' => '--tb-live-h3-size', 'type' => 'size', 'group' => 'typography', 'section' => 'typography'],
        'h4_size' => ['less' => 'base-h4-font-size', 'css' => '--tb-live-h4-size', 'type' => 'size', 'group' => 'typography', 'section' => 'typography'],

        // Abstände
        'margin' => ['less' => 'global-margin', 'css' => '--tb-live-margin', 'type' => 'size', 'group' => 'spacing', 'section' => 'spacing'],
        'gutter' => ['less' => 'global-gutter', 'css' => '--tb-live-gutter', 'type' => 'size', 'group' => 'spacing', 'section' => 'spacing'],
        // .uk-padding-small/-large nutzen jeweils eigene Gutter-Stufen, nicht global-gutter.
        'gutter_small' => ['less' => 'global-small-gutter', 'css' => '--tb-live-gutter-small', 'type' => 'size', 'group' => 'spacing', 'section' => 'spacing'],
        'gutter_large' => ['less' => ['global-medium-gutter', 'global-large-gutter'], 'css' => '--tb-live-gutter-large', 'type' => 'size', 'group' => 'spacing', 'section' => 'spacing'],
        'container_width' => ['less' => 'container-max-width', 'css' => '--tb-live-container-width', 'type' => 'size', 'group' => 'container', 'section' => 'spacing'],
        'container_width_small' => ['less' => 'container-small-max-width', 'css' => '--tb-live-container-width-small', 'type' => 'size', 'group' => 'container', 'section' => 'spacing'],
        'container_width_large' => ['less' => 'container-large-max-width', 'css' => '--tb-live-container-width-large', 'type' => 'size', 'group' => 'container', 'section' => 'spacing'],

        // Navigation (Navbar/Dropdown) - Gruppe 'navbar' entspricht NavbarWidget::getKey(), dessen
        // Feld-Keys bereits die echten UIkit-LESS-Variablennamen sind (siehe NavbarWidget::getFields()).
        'navbar_background' => ['less' => 'navbar-background', 'css' => '--tb-live-navbar-background', 'type' => 'color', 'group' => 'navbar', 'section' => 'navigation'],
        'navbar_item_color' => ['less' => 'navbar-nav-item-color', 'css' => '--tb-live-navbar-item-color', 'type' => 'color', 'group' => 'navbar', 'section' => 'navigation'],
        'navbar_item_hover_color' => ['less' => 'navbar-nav-item-hover-color', 'css' => '--tb-live-navbar-item-hover-color', 'type' => 'color', 'group' => 'navbar', 'section' => 'navigation'],
        'navbar_item_height' => ['less' => 'navbar-nav-item-height', 'css' => '--tb-live-navbar-item-height', 'type' => 'size', 'group' => 'navbar', 'section' => 'navigation'],
        // NavbarWidget::generateLessVariables() spiegelt 'navbar-dropdown-color' beim vollen
        // Speichern zusätzlich auf 'navbar-dropdown-nav-item-color' - hier direkt auf beide
        // schreiben, damit auch die Live-Vorschau (vor dem Speichern) korrekt aussieht.
        'navbar_dropdown_color' => ['less' => ['navbar-dropdown-color', 'navbar-dropdown-nav-item-color'], 'css' => '--tb-live-navbar-dropdown-color', 'type' => 'color', 'group' => 'navbar', 'section' => 'navigation'],
        'navbar_dropdown_hover_color' => ['less' => 'navbar-dropdown-nav-item-hover-color', 'css' => '--tb-live-navbar-dropdown-hover-color', 'type' => 'color', 'group' => 'navbar', 'section' => 'navigation'],
        'navbar_dropdown_background' => ['less' => 'navbar-dropdown-background', 'css' => '--tb-live-navbar-dropdown-background', 'type' => 'color', 'group' => 'navbar', 'section' => 'navigation'],
        'navbar_dropdown_width' => ['less' => 'navbar-dropdown-width', 'css' => '--tb-live-navbar-dropdown-width', 'type' => 'size', 'group' => 'navbar', 'section' => 'navigation'],
    ];

    /**
     * Grundrecht: darf das Live-Theme-Editor-Widget überhaupt sehen/öffnen (Admins immer).
     * Eigenes Recht statt info_center[] - das war nur ein Platzhalter, bevor der Editor
     * eigene Rechte hatte, und hätte jedem Info-Center-Nutzer automatisch Zugriff gegeben.
     */
    public static function canUseEditor(\rex_user $user): bool
    {
        return $user->isAdmin() || $user->hasPerm('uikit_theme_builder[live_editor]');
    }

    /** Darf die Stil-Akkordeons (Typografie/Farben/Maße/Navigation) bearbeiten. */
    public static function canStyle(\rex_user $user): bool
    {
        return $user->isAdmin() || $user->hasPerm('uikit_theme_builder[live_editor_style]');
    }

    /** Darf die Theme-Auswahl live vorschauen (das dauerhafte Übernehmen bleibt Admin-only). */
    public static function canSwitchTheme(\rex_user $user): bool
    {
        return $user->isAdmin() || $user->hasPerm('uikit_theme_builder[live_editor_theme]');
    }

    /**
     * Eingeloggter Redakteur mit Zugriff auf den privaten Draft.
     *
     * @throws \Exception
     */
    public static function requireUser(): \rex_user
    {
        $user = \rex_backend_login::createUser();
        if (!$user || !self::canUseEditor($user)) {
            throw new \Exception('Keine Berechtigung.');
        }

        return $user;
    }

    /**
     * Für Aktionen mit Wirkung auf alle Besucher (Live schalten / beenden) oder dauerhafter
     * Wirkung auf das echte Theme (Speichern / Theme dauerhaft übernehmen) reicht Admin.
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

        // Sonderfall Theme-Wechsel: kein FIELDS-Eintrag (schreibt keinen LESS-Wert, sondern
        // wird als komplett anderes kompiliertes Theme-Stylesheet eingehängt), daher separat
        // behandelt statt über den generischen FIELDS-Loop unten.
        if (isset($raw['_switch_theme']) && is_string($raw['_switch_theme'])) {
            try {
                self::validateTheme($raw['_switch_theme']);
                $clean['_switch_theme'] = $raw['_switch_theme'];
            } catch (\Exception $e) {
                // ungültiger/unbekannter Theme-Name - stillschweigend verwerfen wie sonst auch
            }
        }

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
            } elseif ('font' === $type && self::isValidFontStack($value)) {
                $clean[$key] = $value;
            } elseif ('number' === $type && self::isValidNumber($value)) {
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

    /** Unitless Werte wie die Zeilenhöhe (z.B. "1.5"). */
    private static function isValidNumber(string $number): bool
    {
        return (bool) preg_match('/^\d(\.\d{1,3})?$/', $number);
    }

    /**
     * "inherit" oder ein Font-Stack wie in ColorsWidget/TypographyWidget üblich, z.B.
     * '"Open Sans", -apple-system, sans-serif'. Kein Enum, da Nutzer beliebige bereits
     * geladene Google Fonts wählen können - stattdessen ein sicheres Zeichen-Whitelisting.
     */
    private static function isValidFontStack(string $font): bool
    {
        if ('inherit' === $font) {
            return true;
        }

        return strlen($font) <= 200 && (bool) preg_match('/^[A-Za-z0-9 ,\-_."\']+$/', $font);
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

    /**
     * Öffentlich sichtbarer Zustand, solange die "Live schalten"-Checkbox im Editor gesetzt ist
     * (explizites Opt-in, kein automatischer Broadcast) - siehe flagPath().
     */
    public static function publicPath(string $theme): string
    {
        return self::baseDir() . 'public-' . $theme . '.json';
    }

    /** Existenz = "Live schalten" ist für dieses Theme gerade aktiv (siehe isBroadcastActive()). */
    public static function flagPath(string $theme): string
    {
        return self::baseDir() . 'active-' . $theme . '.flag';
    }

    /**
     * Sicherheitsnetz gegen "vergessen auszuschalten": eine Live-Session bleibt nie dauerhaft
     * bestehen, sondern läuft nach dieser Zeit automatisch aus (kein Cronjob nötig, siehe
     * isBroadcastActive()).
     */
    private const BROADCAST_MAX_AGE_SECONDS = 3600;

    /**
     * Ist "Live schalten" für dieses Theme gerade aktiv UND noch nicht abgelaufen? Ablauf wird
     * lazy geprüft (bei jedem Aufruf, kein Hintergrund-Job) - bei Überschreitung werden Flag +
     * Public-Datei gleich mit aufgeräumt, damit kein verwaister Zustand liegen bleibt. Alte,
     * vor diesem Feature per touch() erzeugte Flags (kein 'startedAt' im Inhalt) laufen NICHT
     * automatisch ab, da kein Startzeitpunkt bekannt ist - sie werden aber beim nächsten
     * "Live schalten"-Klick ohnehin durch ein neues, korrektes Flag ersetzt.
     */
    public static function isBroadcastActive(string $theme): bool
    {
        $flagPath = self::flagPath($theme);
        if (!file_exists($flagPath)) {
            return false;
        }

        $flag = self::readJson($flagPath);
        $startedAt = isset($flag['startedAt']) ? (int) $flag['startedAt'] : 0;

        if ($startedAt > 0 && (time() - $startedAt) > self::BROADCAST_MAX_AGE_SECONDS) {
            self::deleteIfExists($flagPath);
            self::deleteIfExists(self::publicPath($theme));

            return false;
        }

        return true;
    }

    /**
     * Wer hat "Live schalten" für dieses Theme aktiviert (userId aus dem Flag-Inhalt, von
     * golive.php geschrieben). Damit dürfen NUR die weiteren Pushes genau dieses Admins
     * während der laufenden Session in public.json gespiegelt werden - sonst könnte jeder
     * andere Redakteur mit bloßem live_editor_style-Recht (kein Admin, dürfte selbst gar
     * kein "Live schalten" auslösen) über seinen eigenen Draft-Push live auf die öffentliche
     * Website durchschreiben, sobald irgendein Admin zufällig gerade live ist.
     */
    public static function liveBroadcastOwnerId(string $theme): ?int
    {
        if (!self::isBroadcastActive($theme)) {
            return null;
        }

        $flag = self::readJson(self::flagPath($theme));

        return isset($flag['userId']) ? (int) $flag['userId'] : null;
    }

    /**
     * Zeitpunkt (Unix-Timestamp), seit dem "Live schalten" für dieses Theme aktiv ist, oder
     * null wenn gerade keine Session läuft. Für die Backend-Warnung (siehe boot.php).
     */
    public static function broadcastStartedAt(string $theme): ?int
    {
        if (!self::isBroadcastActive($theme)) {
            return null;
        }

        $flag = self::readJson(self::flagPath($theme));

        return isset($flag['startedAt']) ? (int) $flag['startedAt'] : null;
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
     * SSE-Stream für einen verbundenen Client ausgeben. "draft" = private Vorschau der eigenen
     * Session (z.B. um mehrere eigene Tabs synchron zu halten), "public" = Broadcast an alle
     * Besucher, aber NUR solange die "Live schalten"-Checkbox im Editor aktiv ist (flagPath()) -
     * explizites Opt-in, kein automatischer Broadcast. Läuft für maximal ~25s, danach beendet
     * sich der Handler selbst (EventSource reconnected automatisch) - vermeidet dauerhaft
     * blockierte PHP-FPM Worker bei vielen gleichzeitigen Verbindungen.
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
            if (!$user || !self::canUseEditor($user)) {
                http_response_code(403);
                return;
            }
            $path = self::draftPath($theme, $user->getId());
        } else {
            // Zusätzlich zum Flag den globalen Addon-Schalter prüfen: wird er deaktiviert,
            // während eine Live-Session noch läuft, soll auch eine bereits offene
            // EventSource-Verbindung nicht einfach weiter (oder nach Reconnect erneut)
            // Live-Daten bekommen - das Flag allein bliebe sonst bis zum expliziten
            // "Live-Session beenden" bestehen.
            if (!DomainContext::isLiveBroadcastEnabled() || !self::isBroadcastActive($theme)) {
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

            if ('public' === $mode && (!DomainContext::isLiveBroadcastEnabled() || !self::isBroadcastActive($theme))) {
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
