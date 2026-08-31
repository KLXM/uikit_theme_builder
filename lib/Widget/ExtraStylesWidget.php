<?php

namespace UikitThemeBuilder\Widget;

/**
 * Extra Styles Widget - Repeater für eigene UIKit Styles
 * Direkt integriert in den Theme Builder
 */
class ExtraStylesWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Extra Styles';
    }

    public function getKey(): string
    {
        return 'extra_styles';
    }

    public function getDescription(): string
    {
        return 'Eigene CSS-Klassen für UIKit - Cards, Sections, Backgrounds etc.';
    }

    public function getDefaultValues(): array
    {
        return [
            'styles' => [
                [
                    'name' => 'Hero Section',
                    'slug' => 'hero-section',
                    'type' => 'section',
                    'background_color' => '#1e87f0',
                    'text_color' => '#ffffff',
                    'link_color' => '#ffffff',
                    'border_color' => 'transparent',
                    'border_width' => '0',
                    'border_radius' => '0',
                    'backdrop_blur' => '0',
                    'enabled' => true
                ]
            ]
        ];
    }

    public function renderForm(array $values = []): string
    {
        $styles = $values['styles'] ?? $this->getDefaultValues()['styles'];
        $output = '';
        
        // Widget-CSS einbinden - Pickit Color selbst kommt bereits global aus boot.php
        // (Backend), ein zweites Include würde dessen Auto-Init doppelt laufen lassen.
        $output .= '<link rel="stylesheet" href="' . \rex_url::addonAssets('uikit_theme_builder', 'css/extra-styles-widget.css') . '">';
        
        // Container mit UIKit
        $output .= '<div class="extra-styles-widget uk-margin-medium">';
        $output .= '<div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">';
        $output .= '<div>';
        $output .= '<h3 class="uk-h4 uk-margin-remove"><span uk-icon="paint-bucket"></span> Extra Styles</h3>';
        $output .= '<p class="uk-text-meta uk-margin-remove-top">Erstelle eigene CSS-Klassen für UIKit-Komponenten</p>';
        $output .= '</div>';
        $output .= '<button type="button" class="uk-button uk-button-primary uk-button-small" onclick="addExtraStyle()">';
        $output .= '<span uk-icon="plus"></span> Neuen Style hinzufügen';
        $output .= '</button>';
        $output .= '</div>';
        
        // Styles Container
        $output .= '<div id="extra-styles-container" class="uk-child-width-1-1">';
        
        foreach ($styles as $index => $style) {
            $output .= $this->renderStyleItem($index, $style);
        }
        
        $output .= '</div>'; // Ende Container
        $output .= '</div>'; // Ende Widget
        
        // JavaScript für Funktionalität
        $output .= $this->renderJavaScript();
        
        return $output;
    }

    private function renderStyleItem(int $index, array $style): string
    {
        $name = $style['name'] ?? '';
        $slug = $style['slug'] ?? '';
        $type = $style['type'] ?? 'background';
        $backgroundColor = $style['background_color'] ?? '#ffffff';
        $textColor = $style['text_color'] ?? '';
        $linkColor = $style['link_color'] ?? '';
        $borderColor = $style['border_color'] ?? '#e5e5e5';
        $borderWidth = $style['border_width'] ?? '0';
        $borderRadius = $style['border_radius'] ?? '8px';
        $backdropBlur = $style['backdrop_blur'] ?? '0';
        $enabled = $style['enabled'] ?? true;
        
        $output = '<div class="extra-style-item uk-margin" data-index="' . $index . '">';
        $output .= '<div class="uk-card uk-card-default uk-margin">';
        
        // Card Header mit Toggle und Actions
        $output .= '<div class="uk-card-header">';
        $output .= '<div class="uk-flex uk-flex-between uk-flex-middle">';
        $output .= '<div class="uk-flex uk-flex-middle">';
        
        // Enable Toggle
        $checkedAttr = $enabled ? 'checked' : '';
        $output .= '<label class="uk-margin-small-right" title="Aktiviert/Deaktiviert">';
        $output .= '<input type="checkbox" name="extra_styles[styles][' . $index . '][enabled]" value="1" ' . $checkedAttr . ' class="uk-checkbox" onchange="toggleExtraStyle(' . $index . ')">';
        $output .= '</label>';
        
        // Name und Type Badge
        $output .= '<div>';
        $output .= '<h4 class="uk-margin-remove uk-text-bold">' . \rex_escape($name ?: 'Unbenannt') . '</h4>';
        $output .= '<span class="uk-label uk-label-' . $this->getTypeBadgeColor($type) . ' uk-margin-small-left">' . $this->getTypeLabel($type) . '</span>';
        $output .= '</div>';
        $output .= '</div>';
        
        // Actions
        $output .= '<button type="button" class="uk-button uk-button-danger uk-button-small" onclick="removeExtraStyle(' . $index . ')" title="Löschen">';
        $output .= '<span uk-icon="trash"></span>';
        $output .= '</button>';
        $output .= '</div>';
        $output .= '</div>';
        
        // Card Body mit 2-spaltigem Layout
        $output .= '<div class="uk-card-body">';
        $output .= '<div class="uk-grid uk-grid-small" uk-grid>';
        
        // Linke Spalte: Formular (2/3)
        $output .= '<div class="uk-width-2-3@m">';
        $output .= '<div class="uk-grid uk-grid-small uk-child-width-1-2@s" uk-grid>';
        
        // Name (Full Width)
        $output .= '<div class="uk-width-1-1">';
        $output .= '<label class="uk-form-label">Name</label>';
        $output .= '<input type="text" name="extra_styles[styles][' . $index . '][name]" value="' . \rex_escape($name) . '" class="uk-input uk-form-small" placeholder="z.B. Hero Section" oninput="updateStyleName(' . $index . ', this.value); generateSlug(' . $index . ')">';
        $output .= '</div>';
        
        // Slug
        $output .= '<div>';
        $output .= '<label class="uk-form-label">Slug <small class="uk-text-meta">(CSS-Klasse)</small></label>';
        $output .= '<input type="text" name="extra_styles[styles][' . $index . '][slug]" value="' . \rex_escape($slug) . '" class="uk-input uk-form-small" placeholder="hero-section" pattern="[a-z0-9-]+" oninput="updatePreview(' . $index . ')">';
        $output .= '</div>';
        
        // Type
        $output .= '<div>';
        $output .= '<label class="uk-form-label">Typ</label>';
        $output .= '<select name="extra_styles[styles][' . $index . '][type]" class="uk-select uk-form-small" onchange="updateStyleType(' . $index . '); updatePreview(' . $index . ')">';
        $output .= '<option value="background"' . ($type === 'background' ? ' selected' : '') . '>Background</option>';
        $output .= '<option value="card"' . ($type === 'card' ? ' selected' : '') . '>Card</option>';
        $output .= '<option value="section"' . ($type === 'section' ? ' selected' : '') . '>Section</option>';
        $output .= '<option value="button"' . ($type === 'button' ? ' selected' : '') . '>Button</option>';
        $output .= '</select>';
        $output .= '</div>';
        
        // Hintergrundfarbe
        $output .= '<div>';
        $output .= '<label class="uk-form-label">Hintergrundfarbe</label>';
        $output .= '<input type="text" id="extrastyle-' . $index . '-background-color" name="extra_styles[styles][' . $index . '][background_color]" value="' . \rex_escape($backgroundColor) . '" class="uk-input tb-color-input" autocomplete="off">';
        $output .= '</div>';

        // Textfarbe
        $output .= '<div>';
        $output .= '<label class="uk-form-label">Textfarbe <small class="uk-text-meta">(optional)</small></label>';
        $output .= '<input type="text" id="extrastyle-' . $index . '-text-color" name="extra_styles[styles][' . $index . '][text_color]" value="' . \rex_escape($textColor ?: '#333333') . '" class="uk-input tb-color-input" autocomplete="off">';
        $output .= '</div>';

        // Linkfarbe
        $output .= '<div>';
        $output .= '<label class="uk-form-label">Linkfarbe <small class="uk-text-meta">(optional)</small></label>';
        $output .= '<input type="text" id="extrastyle-' . $index . '-link-color" name="extra_styles[styles][' . $index . '][link_color]" value="' . \rex_escape($linkColor ?: '#1e87f0') . '" class="uk-input tb-color-input" autocomplete="off">';
        $output .= '</div>';

        // Rahmenfarbe
        $output .= '<div>';
        $output .= '<label class="uk-form-label">Rahmenfarbe</label>';
        $output .= '<input type="text" id="extrastyle-' . $index . '-border-color" name="extra_styles[styles][' . $index . '][border_color]" value="' . \rex_escape($borderColor) . '" class="uk-input tb-color-input" autocomplete="off">';
        $output .= '</div>';
        
        // Rahmenstärke
        $output .= '<div>';
        $output .= '<label class="uk-form-label">Rahmenstärke</label>';
        $output .= '<div class="uk-flex uk-flex-middle">';
        $output .= '<input type="number" name="extra_styles[styles][' . $index . '][border_width]" value="' . \rex_escape($borderWidth) . '" class="uk-input uk-form-small uk-width-expand" min="0" max="20" oninput="updatePreview(' . $index . ')">';
        $output .= '<span class="uk-text-small uk-margin-small-left">px</span>';
        $output .= '</div>';
        $output .= '</div>';
        
        // Eckenradius
        $output .= '<div>';
        $output .= '<label class="uk-form-label">Eckenradius</label>';
        $output .= '<input type="text" name="extra_styles[styles][' . $index . '][border_radius]" value="' . \rex_escape($borderRadius) . '" class="uk-input uk-form-small" placeholder="8px" oninput="updatePreview(' . $index . ')">';
        $output .= '</div>';
        
        // Backdrop Blur
        $output .= '<div>';
        $output .= '<label class="uk-form-label">Backdrop Blur</label>';
        $output .= '<div class="uk-flex uk-flex-middle">';
        $output .= '<input type="number" name="extra_styles[styles][' . $index . '][backdrop_blur]" value="' . \rex_escape($backdropBlur) . '" class="uk-input uk-form-small uk-width-expand" min="0" max="50" oninput="updatePreview(' . $index . ')">';
        $output .= '<span class="uk-text-small uk-margin-small-left">px</span>';
        $output .= '</div>';
        $output .= '</div>';
        
        $output .= '</div>'; // Ende Form Grid
        $output .= '</div>'; // Ende Form Column
        
        // Rechte Spalte: Preview (1/3)
        $output .= '<div class="uk-width-1-3@m">';
        $output .= '<div class="uk-margin-bottom">';
        $output .= '<h5 class="uk-text-center uk-margin-remove">Live-Vorschau</h5>';
        $output .= '<div id="preview-' . $index . '" class="extra-style-preview uk-padding uk-margin-small-top uk-text-center" style="min-height: 150px; border: 1px solid #e5e5e5; border-radius: 5px;">';
        $output .= '<h6 class="uk-h6 uk-margin-small">Beispiel Titel</h6>';
        $output .= '<p class="uk-text-small uk-margin-small">Live-Vorschau deines Extra Styles.</p>';
        $output .= '<a href="#" class="uk-link uk-text-small">Beispiel Link</a>';
        $output .= '<div class="uk-margin-small-top">';
        $output .= '<span class="uk-label uk-label-success uk-text-small">Preview</span>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';
        
        // CSS Code Details
        $output .= '<details>';
        $output .= '<summary class="uk-text-small uk-text-meta uk-flex uk-flex-middle">';
        $output .= '<span uk-icon="code" class="uk-margin-small-right"></span>CSS-Code';
        $output .= '</summary>';
        $output .= '<pre id="css-' . $index . '" class="uk-background-muted uk-padding-small uk-text-small uk-margin-small-top" style="font-size: 10px; line-height: 1.3; overflow-x: auto; max-height: 150px;">';
        $output .= '</pre>';
        $output .= '</details>';
        $output .= '</div>'; // Ende Preview Column
        
        $output .= '</div>'; // Ende 2-Spalten Grid
        $output .= '</div>'; // Ende Card Body
        $output .= '</div>'; // Ende Card
        $output .= '</div>'; // Ende Item
        
        return $output;
    }

    private function renderJavaScript(): string
    {
        // Höchsten Index der existierenden Items finden
        $styles = $_POST['extra_styles']['styles'] ?? $this->getDefaultValues()['styles'];
        $maxIndex = -1;
        foreach ($styles as $index => $style) {
            if (is_numeric($index) && $index > $maxIndex) {
                $maxIndex = $index;
            }
        }
        
        // JavaScript aus separater Datei laden
        $jsFile = \rex_url::addonAssets('uikit_theme_builder', 'js/extra-styles-widget.js');
        
        return '<script src="' . $jsFile . '"></script>' .
               '<script>extraStyleIndex = ' . $maxIndex . ';</script>';
    }

    private function getTypeLabel(string $type): string
    {
        return match($type) {
            'background' => 'Background',
            'card' => 'Card',
            'section' => 'Section',
            'button' => 'Button',
            default => ucfirst($type)
        };
    }

    private function getTypeBadgeColor(string $type): string
    {
        return match($type) {
            'background' => 'default',
            'card' => 'primary',
            'section' => 'secondary',
            'button' => 'success',
            default => 'default'
        };
    }

    public function processFormData(array $postData): array
    {
        $styles = $postData['extra_styles']['styles'] ?? [];
        
        // Debug: Raw POST data für extra_styles
        $debugInfo = [
            'total_post_keys' => array_keys($postData),
            'extra_styles_keys' => array_keys($postData['extra_styles'] ?? []),
            'styles_count' => count($styles),
            'styles_indices' => array_keys($styles)
        ];
        \rex_logger::factory()->info('ExtraStylesWidget processFormData Debug: ' . json_encode($debugInfo));
        
        // Debug: Jeder einzelne Style
        foreach ($styles as $index => $style) {
            $styleDebug = [
                'index' => $index,
                'has_name' => !empty($style['name']),
                'has_slug' => !empty($style['slug']),
                'slug_value' => $style['slug'] ?? 'EMPTY',
                'enabled_raw' => $style['enabled'] ?? 'NOT_SET',
                'enabled_processed' => isset($style['enabled']) && $style['enabled'] === '1'
            ];
            \rex_logger::factory()->info('ExtraStylesWidget Style ' . $index . ': ' . json_encode($styleDebug));
        }
        
        // Alle Styles verarbeiten, die mindestens einen Slug haben
        $processedStyles = [];
        foreach ($styles as $index => $style) {
            // Enabled-Status: wenn nicht gesetzt, als false behandeln
            $enabled = isset($style['enabled']) && $style['enabled'] === '1';
            
            // Style ist gültig wenn: Slug vorhanden UND (enabled ODER hat relevante Werte)
            $hasSlug = !empty($style['slug']);
            $hasRelevantValues = !empty($style['background_color']) || !empty($style['text_color']) || 
                               !empty($style['border_width']) || !empty($style['border_radius']);
            
            if ($hasSlug && ($enabled || $hasRelevantValues)) {
                // Slug sanitizen
                $style['slug'] = preg_replace('/[^a-z0-9-]/', '', strtolower($style['slug']));
                
                // Enabled explizit setzen
                $style['enabled'] = $enabled;
                
                // Leere Werte mit Defaults füllen
                $style['name'] = $style['name'] ?? '';
                $style['type'] = $style['type'] ?? 'background';
                $style['background_color'] = $style['background_color'] ?? '#ffffff';
                $style['text_color'] = $style['text_color'] ?? '';
                $style['link_color'] = $style['link_color'] ?? '';
                $style['border_color'] = $style['border_color'] ?? '#e5e5e5';
                $style['border_width'] = $style['border_width'] ?? '0';
                $style['border_radius'] = $style['border_radius'] ?? '0';
                $style['backdrop_blur'] = $style['backdrop_blur'] ?? '0';
                
                $processedStyles[] = $style;
                \rex_logger::factory()->info('ExtraStylesWidget processed style: ' . $style['slug'] . ' (enabled: ' . ($enabled ? 'yes' : 'no') . ')');
            } else {
                \rex_logger::factory()->info('ExtraStylesWidget skipped style at index ' . $index . ' - hasSlug: ' . ($hasSlug ? 'yes' : 'no') . ', enabled: ' . ($enabled ? 'yes' : 'no'));
            }
        }
        
        \rex_logger::factory()->info('ExtraStylesWidget processFormData - processed ' . count($processedStyles) . ' styles');
        
        return [
            'styles' => $processedStyles
        ];
    }

    public function getFields(): array
    {
        return [
            'styles' => [
                'type' => 'repeater',
                'label' => 'Extra Styles',
                'fields' => [
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'slug' => ['type' => 'text', 'label' => 'Slug'],
                    'type' => ['type' => 'select', 'label' => 'Typ', 'options' => [
                        'background' => 'Background',
                        'card' => 'Card',
                        'section' => 'Section',
                        'button' => 'Button'
                    ]],
                    'background_color' => ['type' => 'color', 'label' => 'Hintergrundfarbe'],
                    'text_color' => ['type' => 'color', 'label' => 'Textfarbe'],
                    'link_color' => ['type' => 'color', 'label' => 'Linkfarbe'],
                    'border_color' => ['type' => 'color', 'label' => 'Rahmenfarbe'],
                    'border_width' => ['type' => 'number', 'label' => 'Rahmenstärke'],
                    'border_radius' => ['type' => 'text', 'label' => 'Eckenradius'],
                    'backdrop_blur' => ['type' => 'number', 'label' => 'Backdrop Blur'],
                    'enabled' => ['type' => 'checkbox', 'label' => 'Aktiviert']
                ]
            ]
        ];
    }

    public function validateFormData(array $data): array
    {
        $errors = [];
        $styles = $data['styles'] ?? [];
        
        foreach ($styles as $index => $style) {
            if (empty($style['name'])) {
                $errors["styles.{$index}.name"] = 'Name ist erforderlich';
            }
            
            if (empty($style['slug'])) {
                $errors["styles.{$index}.slug"] = 'Slug ist erforderlich';
            } elseif (!preg_match('/^[a-z0-9-]+$/', $style['slug'])) {
                $errors["styles.{$index}.slug"] = 'Slug darf nur Kleinbuchstaben, Zahlen und Bindestriche enthalten';
            }
            
            // Farbvalidierung
            $colorFields = ['background_color', 'text_color', 'link_color', 'border_color'];
            foreach ($colorFields as $field) {
                if (!empty($style[$field]) && !preg_match('/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$|^transparent$/', $style[$field])) {
                    $errors["styles.{$index}.{$field}"] = 'Ungültiges Farbformat';
                }
            }
        }
        
        return $errors;
    }

    public function generateLessVariables(array $data): array
    {
        // Extra Styles verwenden direkte CSS-Klassen, keine LESS-Variablen
        return [];
    }

    public function generateLess(array $values): string
    {
        $styles = $values['styles'] ?? [];
        $less = '';
        
        \rex_logger::factory()->info('ExtraStylesWidget generateLess - processing ' . count($styles) . ' styles');
        
        foreach ($styles as $index => $style) {
            // Style ist gültig wenn Slug vorhanden ist (enabled wird bereits in processFormData geprüft)
            if (empty($style['slug'])) {
                \rex_logger::factory()->info('ExtraStylesWidget skipping style ' . $index . ' - no slug');
                continue;
            }
            
            // Style explizit disabled
            if (isset($style['enabled']) && $style['enabled'] === false) {
                \rex_logger::factory()->info('ExtraStylesWidget skipping style ' . $index . ' - disabled');
                continue;
            }
            
            $className = '.uk-' . ($style['type'] ?? 'background') . '-' . $style['slug'];
            $less .= "\n// Extra Style: " . ($style['name'] ?: 'Unnamed') . "\n";
            $less .= $className . " {\n";
            
            // CSS-Properties generieren
            $hasProperties = false;
            
            if (!empty($style['background_color']) && $style['background_color'] !== '#ffffff') {
                $less .= "    background-color: " . $style['background_color'] . ";\n";
                $hasProperties = true;
            }
            
            if (!empty($style['text_color'])) {
                $less .= "    color: " . $style['text_color'] . ";\n";
                $hasProperties = true;
            }
            
            if (!empty($style['border_width']) && (int)$style['border_width'] > 0) {
                $borderColor = !empty($style['border_color']) ? $style['border_color'] : '#e5e5e5';
                $less .= "    border: " . $style['border_width'] . "px solid " . $borderColor . ";\n";
                $hasProperties = true;
            }
            
            if (!empty($style['border_radius']) && $style['border_radius'] !== '0' && $style['border_radius'] !== '0px') {
                $less .= "    border-radius: " . $style['border_radius'] . ";\n";
                $hasProperties = true;
            }
            
            if (!empty($style['backdrop_blur']) && (int)$style['backdrop_blur'] > 0) {
                $less .= "    backdrop-filter: blur(" . $style['backdrop_blur'] . "px);\n";
                $less .= "    -webkit-backdrop-filter: blur(" . $style['backdrop_blur'] . "px);\n";
                $hasProperties = true;
            }
            
            $less .= "}\n";
            
            // Link-Styles falls Linkfarbe gesetzt ist
            if (!empty($style['link_color'])) {
                $less .= "\n" . $className . " a,\n" . $className . " .uk-link {\n";
                $less .= "    color: " . $style['link_color'] . ";\n";
                $less .= "}\n";
                
                $less .= "\n" . $className . " a:hover,\n" . $className . " .uk-link:hover {\n";
                $less .= "    color: darken(" . $style['link_color'] . ", 10%);\n";
                $less .= "}\n";
                $hasProperties = true;
            }
            
            if ($hasProperties) {
                \rex_logger::factory()->info('ExtraStylesWidget generated CSS for: ' . $style['slug'] . ' (' . $className . ')');
            } else {
                \rex_logger::factory()->warning('ExtraStylesWidget style has no properties: ' . $style['slug']);
            }
        }
        
        \rex_logger::factory()->info('ExtraStylesWidget generateLess completed - CSS length: ' . strlen($less));
        return $less;
    }
}