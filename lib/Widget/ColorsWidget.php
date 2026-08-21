<?php

namespace UikitThemeBuilder\Widget;

/**
 * Colors Widget - UIKit Standard Farben
 */
class ColorsWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Colors';
    }

    public function getKey(): string
    {
        return 'colors';
    }

    public function getDescription(): string
    {
        return 'Konfiguration der UIKit-Standard-Farbpalette';
    }

    public function getFields(): array
    {
        return [
            'global-primary-background' => [
                'label' => 'Primary',
                'type' => 'color',
                'default' => '#1e87f0',
                'help' => 'Hauptfarbe für Buttons und Akzente'
            ],
            'global-secondary-background' => [
                'label' => 'Secondary',
                'type' => 'color',
                'default' => '#222',
                'help' => 'Sekundärfarbe für dunkle Bereiche'
            ],
            'global-success-background' => [
                'label' => 'Success',
                'type' => 'color',
                'default' => '#32d296',
                'help' => 'Farbe für Erfolgs-Meldungen'
            ],
            'global-warning-background' => [
                'label' => 'Warning',
                'type' => 'color',
                'default' => '#faa05a',
                'help' => 'Farbe für Warnungen'
            ],
            'global-danger-background' => [
                'label' => 'Danger',
                'type' => 'color',
                'default' => '#f0506e',
                'help' => 'Farbe für Fehler'
            ],
            'global-color' => [
                'label' => 'Text',
                'type' => 'color',
                'default' => '#666',
                'help' => 'Standard-Textfarbe'
            ],
            'global-emphasis-color' => [
                'label' => 'Emphasis',
                'type' => 'color',
                'default' => '#333',
                'help' => 'Farbe für hervorgehobenen Text'
            ],
            'global-muted-color' => [
                'label' => 'Muted',
                'type' => 'color',
                'default' => '#999',
                'help' => 'Gedämpfte Textfarbe'
            ],
            'global-link-color' => [
                'label' => 'Link',
                'type' => 'color',
                'default' => '#1e87f0',
                'help' => 'Farbe für Links'
            ],
            'global-link-hover-color' => [
                'label' => 'Link Hover',
                'type' => 'color',
                'default' => '#0f6ecd',
                'help' => 'Farbe für Links beim Hover'
            ],
            'global-inverse-color' => [
                'label' => 'Inverse',
                'type' => 'color',
                'default' => '#fff',
                'help' => 'Umgekehrte Textfarbe'
            ],
            'global-border' => [
                'label' => 'Border',
                'type' => 'color',
                'default' => '#e5e5e5',
                'help' => 'Standard-Borderfarbe'
            ]
        ];
    }
    
    public function getDefaultValues(): array
    {
        // REDAXO Farbwelt aus be_style/redaxo
        return [
            'global-primary-background' => '#4b9ad9',      // $color-b (REDAXO Blue)
            'global-secondary-background' => '#324050',    // $color-a-dark (REDAXO Dark Grey)
            'global-success-background' => '#5bb585',      // $color-d (REDAXO Green)
            'global-warning-background' => '#cfb550',      // REDAXO Yellow
            'global-danger-background' => '#d9534f',       // REDAXO Red
            'global-color' => '#324050',                   // $color-a-dark (Text)
            'global-emphasis-color' => '#283542',          // $color-a-darker (Emphasis)
            'global-muted-color' => '#9ca5b2',            // $color-a (Muted Grey)
            'global-link-color' => '#4b9ad9',             // $color-b (Link Blue)
            'global-link-hover-color' => '#3a82c4',       // Dunklerer Blue
            'global-background' => '#f3f6fb',             // $color-a-lighter (Body Background)
            'global-muted-background' => '#f3f6fb',       // $color-a-lighter
            'global-border' => '#dfe3e9'                  // $color-a-light (Borders)
        ];
    }
    
    public function renderForm(array $currentValues = []): string
    {
        $values = array_merge($this->getDefaultValues(), $currentValues);
        
        $html = '<div class="uk-grid-small" uk-grid>';
        
        // Theme Farben
        $html .= '<div class="uk-width-1-2@m">';
        $html .= '<h4>Theme-Farben</h4>';
        
        $html .= $this->renderFormRow(
            'Primary',
            $this->renderColorPicker('global-primary-background', $values['global-primary-background']),
            'Hauptfarbe für Buttons und Akzente'
        );
        
        $html .= $this->renderFormRow(
            'Secondary',
            $this->renderColorPicker('global-secondary-background', $values['global-secondary-background']),
            'Sekundärfarbe für dunkle Bereiche'
        );
        
        $html .= $this->renderFormRow(
            'Success',
            $this->renderColorPicker('global-success-background', $values['global-success-background']),
            'Farbe für Erfolgs-Meldungen'
        );
        
        $html .= $this->renderFormRow(
            'Warning',
            $this->renderColorPicker('global-warning-background', $values['global-warning-background']),
            'Farbe für Warnungen'
        );
        
        $html .= $this->renderFormRow(
            'Danger',
            $this->renderColorPicker('global-danger-background', $values['global-danger-background']),
            'Farbe für Fehler'
        );
        
        $html .= '</div>';
        
        // Text & Link Farben
        $html .= '<div class="uk-width-1-2@m">';
        $html .= '<h4>Text & Links</h4>';
        
        $html .= $this->renderFormRow(
            'Text Color',
            $this->renderColorPicker('global-color', $values['global-color']),
            'Standard-Textfarbe'
        );
        
        $html .= $this->renderFormRow(
            'Emphasis Color',
            $this->renderColorPicker('global-emphasis-color', $values['global-emphasis-color']),
            'Farbe für wichtige Texte'
        );
        
        $html .= $this->renderFormRow(
            'Muted Color',
            $this->renderColorPicker('global-muted-color', $values['global-muted-color']),
            'Farbe für weniger wichtige Texte'
        );
        
        $html .= $this->renderFormRow(
            'Link Color',
            $this->renderColorPicker('global-link-color', $values['global-link-color']),
            'Standard-Linkfarbe'
        );
        
        $html .= $this->renderFormRow(
            'Link Hover',
            $this->renderColorPicker('global-link-hover-color', $values['global-link-hover-color']),
            'Linkfarbe beim Hover'
        );
        
        // Hintergrund & Rahmen
        $html .= '<h4>Hintergrund & Rahmen</h4>';
        
        $html .= $this->renderFormRow(
            'Background',
            $this->renderColorPicker('global-background', $values['global-background']),
            'Standard-Hintergrundfarbe'
        );
        
        $mutedBgColor = $values['global-muted-background'];
        $mutedBgWarning = '';
        
        // Prüfe ob Muted Background zu gesättigt ist
        if ($this->isVibrantColor($mutedBgColor)) {
            $mutedBgWarning = '<div class="uk-alert-warning" uk-alert><p><span uk-icon="warning"></span> <strong>Achtung:</strong> Muted Background sollte ein heller Pastelton sein, keine kräftige Farbe.</p></div>';
        }
        
        $html .= $this->renderFormRow(
            'Muted Background',
            $this->renderColorPicker('global-muted-background', $mutedBgColor) . $mutedBgWarning,
            'Sollte ein heller Pastelton sein (z.B. #f3f6fb, #fafafa). Wird für sanfte Hintergründe verwendet.'
        );
        
        $html .= $this->renderFormRow(
            'Border Color',
            $this->renderColorPicker('global-border', $values['global-border']),
            'Standard-Rahmenfarbe'
        );
        
        // Kontrast-Prüfungen
        $bgColor = $values['global-background'];
        $mutedBgColor = $values['global-muted-background'];
        $textColor = $values['global-color'];
        $emphasisColor = $values['global-emphasis-color'];
        
        $warnings = [];
        
        // 1. Emphasis Color vs Background
        $contrastEmphasisBg = $this->calculateContrast($emphasisColor, $bgColor);
        if ($contrastEmphasisBg < 4.5) {
            $level = $contrastEmphasisBg < 3.0 ? 'danger' : 'warning';
            $warnings[] = [
                'level' => $level,
                'title' => $contrastEmphasisBg < 3.0 ? 'Kontrast-Warnung' : 'Kontrast-Hinweis',
                'message' => 'Der Kontrast zwischen <strong>Emphasis Color</strong> und <strong>Background</strong> ist ' . ($contrastEmphasisBg < 3.0 ? 'zu gering' : 'ausreichend für große Texte') . ' (Verhältnis: ' . number_format($contrastEmphasisBg, 2) . ':1).',
                'recommendation' => $contrastEmphasisBg < 3.0 ? 'Mindestens 3:1 für größere Texte empfohlen.' : 'Für kleine Texte wird 4.5:1 empfohlen.'
            ];
        }
        
        // 2. Text Color vs Background
        $contrastTextBg = $this->calculateContrast($textColor, $bgColor);
        if ($contrastTextBg < 4.5) {
            $level = $contrastTextBg < 3.0 ? 'danger' : 'warning';
            $warnings[] = [
                'level' => $level,
                'title' => $contrastTextBg < 3.0 ? 'Kontrast-Warnung' : 'Kontrast-Hinweis',
                'message' => 'Der Kontrast zwischen <strong>Text Color</strong> und <strong>Background</strong> ist ' . ($contrastTextBg < 3.0 ? 'zu gering' : 'ausreichend für große Texte') . ' (Verhältnis: ' . number_format($contrastTextBg, 2) . ':1).',
                'recommendation' => $contrastTextBg < 3.0 ? 'Mindestens 3:1 für größere Texte empfohlen.' : 'Für kleine Texte wird 4.5:1 empfohlen.'
            ];
        }
        
        // 3. Text Color vs Muted Background
        $contrastTextMutedBg = $this->calculateContrast($textColor, $mutedBgColor);
        if ($contrastTextMutedBg < 4.5) {
            $level = $contrastTextMutedBg < 3.0 ? 'danger' : 'warning';
            $warnings[] = [
                'level' => $level,
                'title' => $contrastTextMutedBg < 3.0 ? 'Kontrast-Warnung' : 'Kontrast-Hinweis',
                'message' => 'Der Kontrast zwischen <strong>Text Color</strong> und <strong>Muted Background</strong> ist ' . ($contrastTextMutedBg < 3.0 ? 'zu gering' : 'ausreichend für große Texte') . ' (Verhältnis: ' . number_format($contrastTextMutedBg, 2) . ':1).',
                'recommendation' => $contrastTextMutedBg < 3.0 ? 'Mindestens 3:1 für größere Texte empfohlen.' : 'Für kleine Texte wird 4.5:1 empfohlen.'
            ];
        }
        
        // Warnungen ausgeben
        if (!empty($warnings)) {
            foreach ($warnings as $warning) {
                $alertClass = $warning['level'] === 'danger' ? 'uk-alert-danger' : 'uk-alert-warning';
                $icon = $warning['level'] === 'danger' ? 'warning' : 'info';
                
                $html .= '<div class="' . $alertClass . '" uk-alert style="margin-top: 20px;">';
                $html .= '<p><span uk-icon="' . $icon . '"></span> <strong>' . $warning['title'] . ':</strong> ' . $warning['message'] . '</p>';
                $html .= '<p>' . $warning['recommendation'] . '</p>';
                $html .= '<p><em>Hinweis: Diese Prüfung basiert auf den zuletzt gespeicherten Werten und aktualisiert sich erst nach dem Speichern/Kompilieren.</em></p>';
                $html .= '</div>';
            }
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    public function processFormData(array $formData): array
    {
        $processed = [];
        $defaults = $this->getDefaultValues();
        
        foreach ($defaults as $key => $defaultValue) {
            if (isset($formData[$key])) {
                $color = trim($formData[$key]);
                if ($this->isValidColor($color)) {
                    $processed[$key] = $color;
                } else {
                    $processed[$key] = $defaultValue;
                }
            } else {
                $processed[$key] = $defaultValue;
            }
        }
        
        return $processed;
    }
    
    public function validateFormData(array $formData): array
    {
        $errors = [];
        $defaults = $this->getDefaultValues();
        
        foreach ($defaults as $key => $defaultValue) {
            if (isset($formData[$key])) {
                $color = trim($formData[$key]);
                if (!$this->isValidColor($color)) {
                    $errors[$key] = 'Ungültiges Farbformat. Verwenden Sie HEX (#RRGGBB), RGB(A) oder HSL(A).';
                }
            }
        }
        
        return $errors;
    }

    /**
     * Berechnet das Kontrastverhältnis zwischen zwei Farben nach WCAG 2.1
     * @param string $color1 Erste Farbe (HEX, RGB, etc.)
     * @param string $color2 Zweite Farbe (HEX, RGB, etc.)
     * @return float Kontrastverhältnis (1-21)
     */
    private function calculateContrast(string $color1, string $color2): float
    {
        $rgb1 = $this->hexToRgb($color1);
        $rgb2 = $this->hexToRgb($color2);
        
        if (!$rgb1 || !$rgb2) {
            return 21.0; // Bei Fehler maximalen Kontrast annehmen
        }
        
        $l1 = $this->getRelativeLuminance($rgb1);
        $l2 = $this->getRelativeLuminance($rgb2);
        
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);
        
        return ($lighter + 0.05) / ($darker + 0.05);
    }
    
    /**
     * Konvertiert HEX-Farbe zu RGB-Array
     * @param string $hex HEX-Farbe (mit oder ohne #)
     * @return array|null [r, g, b] oder null bei Fehler
     */
    private function hexToRgb(string $hex): ?array
    {
        $hex = ltrim($hex, '#');
        
        // Nur 6-stellige HEX-Codes (ohne Alpha)
        if (strlen($hex) === 8) {
            $hex = substr($hex, 0, 6);
        }
        
        if (strlen($hex) !== 6) {
            return null;
        }
        
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }
    
    /**
     * Berechnet relative Luminanz nach WCAG 2.1
     * @param array $rgb [r, g, b] Werte 0-255
     * @return float Luminanz 0-1
     */
    private function getRelativeLuminance(array $rgb): float
    {
        $rgb = array_map(function($val) {
            $val = $val / 255;
            return $val <= 0.03928 ? $val / 12.92 : pow(($val + 0.055) / 1.055, 2.4);
        }, $rgb);
        
        return 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
    }
    
    /**
     * Prüft ob eine Farbe kräftig/gesättigt ist (für Warnung bei Muted Background)
     * @param string $color HEX-Farbe
     * @return bool True wenn Farbe zu kräftig ist
     */
    private function isVibrantColor(string $color): bool
    {
        $rgb = $this->hexToRgb($color);
        if (!$rgb) {
            return false;
        }
        
        // Konvertiere RGB zu HSL
        $r = $rgb[0] / 255;
        $g = $rgb[1] / 255;
        $b = $rgb[2] / 255;
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        
        // Berechne Sättigung
        if ($max === $min) {
            $s = 0; // Graustufe
        } else {
            $s = $l > 0.5 ? ($max - $min) / (2 - $max - $min) : ($max - $min) / ($max + $min);
        }
        
        // Warnung wenn:
        // - Sättigung > 40% UND Helligkeit < 90% (zu kräftig)
        // - ODER Helligkeit < 60% (zu dunkel für Muted Background)
        return ($s > 0.4 && $l < 0.9) || $l < 0.6;
    }
    
    /**
     * Validiert verschiedene Farbformate (HEX, RGB, RGBA, HSL, HSLA)
     */
    private function isValidColor(string $color): bool
    {
        // HEX Format: #RRGGBB oder #RRGGBBAA
        if (preg_match('/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/', $color)) {
            return true;
        }
        
        // RGB Format: rgb(r, g, b)
        if (preg_match('/^rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\)$/', $color)) {
            return true;
        }
        
        // RGBA Format: rgba(r, g, b, a)
        if (preg_match('/^rgba\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*(0|1|0?\.\d+)\s*\)$/', $color)) {
            return true;
        }
        
        // HSL Format: hsl(h, s%, l%)
        if (preg_match('/^hsl\(\s*\d{1,3}\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%\s*\)$/', $color)) {
            return true;
        }
        
        // HSLA Format: hsla(h, s%, l%, a)
        if (preg_match('/^hsla\(\s*\d{1,3}\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%\s*,\s*(0|1|0?\.\d+)\s*\)$/', $color)) {
            return true;
        }
        
        return false;
    }
    
    public function generateLessVariables(array $data): array
    {
        // Farben bereinigen: Alpha-Kanal entfernen wenn vorhanden
        $cleanedData = [];
        foreach ($data as $key => $value) {
            // Prüfe ob Wert ein Hex-Color mit Alpha-Kanal ist (#RRGGBBaa)
            if (preg_match('/^#[0-9a-fA-F]{8}$/', $value)) {
                // Entferne Alpha-Kanal (letzten 2 Zeichen)
                $cleanedData[$key] = substr($value, 0, 7);
            } else {
                $cleanedData[$key] = $value;
            }
        }
        
        return $cleanedData;
    }

}