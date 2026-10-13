<?php

namespace UikitThemeBuilder;

/**
 * Grenzt kompiliertes Theme-CSS auf einen Bereich ein – für das REDAXO-Backend: Das Theme wirkt dann nur in Vorschauen
 * (Blöcke, Builder, eigene Bereiche mit Klasse „uk-theme-scope“), die Oberfläche von REDAXO bleibt unberührt.
 *
 * - Jeder Selektor bekommt den Bereich vorangestellt: „.uk-button“ → „:is(<Bereich>) .uk-button“.
 * - html, body und :root werden durch den Bereich selbst ersetzt (Grundschrift, Farben, CSS-Variablen des Themes).
 * - @media/@supports/@layer/@container werden durchlaufen, @keyframes/@font-face/@page bleiben unverändert,
 *   @charset/@import entfallen.
 */
final class CssScoper
{
    /** Standard-Bereiche: Inhalt der Artikel-Blöcke, Vorschau des Builders, eigene Bereiche. */
    public const DEFAULT_SCOPE = ".rex-slice-output .panel-body\n.slice-rendered\n.uk-theme-scope";

    /**
     * Nie vorhandene ID in der Bereichsliste: :is() nimmt die höchste Spezifität seiner Liste – so gewinnt das Theme in den
     * Vorschauen gegen Backend-Stile (REDAXO, Dunkelmodus anderer AddOns wie „body.rex-theme-dark .slice-rendered p“).
     */
    public const SPECIFICITY_BOOST = '#uikit-theme-builder-scope';

    /** Stand der Eingrenzung – ändert sich die Logik, werden vorhandene <theme>.backend.css neu erzeugt (Stempel). */
    public const VERSION = '4';

    /** Selektorliste (je Zeile oder durch Komma getrennt) → „:is(a, b, c, #uikit-theme-builder-scope)“; leer = Standard. */
    public static function scopeSelector(string $lines): string
    {
        $parts = preg_split('~[\r\n,]+~', '' !== trim($lines) ? $lines : self::DEFAULT_SCOPE) ?: [];
        $parts = array_values(array_unique(array_filter(array_map('trim', $parts), static fn (string $p): bool => '' !== $p && !preg_match('~[{};]~', $p))));
        $parts = $parts ?: ['.uk-theme-scope'];
        $parts[] = self::SPECIFICITY_BOOST;

        return ':is(' . implode(', ', $parts) . ')';
    }

    /**
     * Grundregeln vor dem Theme: Textelemente erben die Farbe, Tabellen sind durchsichtig – wie auf der Website (Spezifität
     * des Bereichs, :where = 0).
     * Backend-Stile, die in Vorschauen Text einfärben (z. B. Dunkelmodus: „.slice-rendered p { color: #bbb }“), greifen
     * bzw. REDAXOs „table { background-color: #202b35 }“ so nicht mehr; Regeln des Themes (gleicher Bereich + eigener Selektor) gewinnen weiterhin.
     */
    public static function baseReset(string $scope): string
    {
        return $scope . ' :where(p, li, dt, dd, h1, h2, h3, h4, h5, h6, td, th, caption, blockquote, figcaption, label){color:inherit}' . "\n"
            . $scope . ' :where(table, thead, tbody, tfoot, tr, td, th){background-color:transparent}' . "\n";
    }

    public static function scope(string $css, string $scope): string
    {
        $css = (string) preg_replace('~/\*.*?\*/~s', '', $css);
        $out = self::process($css, $scope);
        $out = (string) preg_replace('~\s+~', ' ', $out);
        $out = (string) preg_replace('~\s*([{};])\s*~', '$1', $out);

        return str_replace('}', "}\n", $out);
    }

    private static function process(string $css, string $scope): string
    {
        $out = [];
        $i = 0;
        $n = strlen($css);
        while ($i < $n) {
            $open = strpos($css, '{', $i);
            if (false === $open) {
                break;
            }
            $semi = strpos($css, ';', $i);
            if (false !== $semi && $semi < $open && str_starts_with(ltrim(substr($css, $i, $semi - $i)), '@')) {
                $i = $semi + 1; // @charset/@import/@namespace ohne Block
                continue;
            }
            $prelude = trim(substr($css, $i, $open - $i));
            $depth = 1;
            $p = $open + 1;
            while ($p < $n && $depth > 0) {
                $c = $css[$p];
                if ('{' === $c) {
                    ++$depth;
                } elseif ('}' === $c) {
                    --$depth;
                }
                ++$p;
            }
            $body = substr($css, $open + 1, $p - $open - 2);
            $i = $p;
            if ('' === $prelude) {
                continue;
            }
            if (str_starts_with($prelude, '@')) {
                $name = strtolower((string) strtok($prelude, " \t\n("));
                $out[] = in_array($name, ['@media', '@supports', '@layer', '@container', '@document'], true)
                    ? $prelude . '{' . self::process($body, $scope) . '}'
                    : $prelude . '{' . $body . '}';
                continue;
            }
            $selectors = [];
            foreach (self::splitSelectors($prelude) as $sel) {
                if ('' !== trim($sel)) {
                    $selectors[] = self::scopeOne(trim($sel), $scope);
                }
            }
            if ($selectors) {
                $out[] = implode(',', $selectors) . '{' . trim($body) . '}';
            }
        }

        return implode("\n", $out);
    }

    private static function scopeOne(string $sel, string $scope): string
    {
        $rest = $sel;
        $replaced = false;
        while (preg_match('/^(?:html|body|:root)(?=$|[\s.:#\[>+~])/', $rest, $m)) {
            $replaced = true;
            $rest = substr($rest, strlen($m[0]));
            // Klassen/Attribute direkt an html/body (z. B. body.page) gibt es im Backend so nicht
            $rest = ltrim((string) preg_replace('/^(?:[.:#\[][^\s>+~]*)/', '', $rest));
            $rest = (string) preg_replace('/^[>+~]\s*/', '', $rest);
        }
        if ($replaced) {
            return $scope . ('' !== $rest ? ' ' . $rest : '');
        }

        return $scope . ' ' . $sel;
    }

    /** @return list<string> */
    private static function splitSelectors(string $prelude): array
    {
        $parts = [];
        $depth = 0;
        $cur = '';
        $len = strlen($prelude);
        for ($k = 0; $k < $len; ++$k) {
            $c = $prelude[$k];
            if ('(' === $c || '[' === $c) {
                ++$depth;
            } elseif (')' === $c || ']' === $c) {
                --$depth;
            }
            if (',' === $c && 0 === $depth) {
                $parts[] = $cur;
                $cur = '';
            } else {
                $cur .= $c;
            }
        }
        $parts[] = $cur;

        return $parts;
    }
}
