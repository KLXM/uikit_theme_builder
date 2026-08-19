<?php

namespace UikitThemeBuilder\Widget;

use UikitThemeBuilder\StyleSetManager;

/**
 * StyleSetSelectionWidget - Vereinfachtes Widget zur Auswahl von Style-Sets
 */
class StyleSetSelectionWidget extends AbstractWidget
{
    private StyleSetManager $styleSetManager;

    public function __construct()
    {
        $this->styleSetManager = new StyleSetManager();
    }

    public function getName(): string
    {
        return 'Style-Sets';
    }

    public function getKey(): string
    {
        return 'style_sets';
    }

    public function getTitle(): string
    {
        return 'Extra Style-Sets';
    }

    public function getDescription(): string
    {
        return 'Wählen Sie die Style-Sets aus, die in diesem Theme verfügbar sein sollen.';
    }

    public function getFields(): array
    {
        return [
            'selected_style_sets' => [
                'type' => 'checkbox',
                'label' => 'Ausgewählte Style-Sets',
                'description' => 'Style-Sets die in diesem Theme verfügbar sein sollen'
            ]
        ];
    }

    public function validateFormData(array $data): array
    {
        return []; // Keine spezielle Validierung nötig
    }

    public function generateLessVariables(array $data): array
    {
        // Style-Sets generieren keine LESS-Variablen, sondern CSS-Klassen
        return [];
    }

    public function generateLess(array $data): string
    {
        $selectedStyleSets = $data['selected_style_sets'] ?? [];
        
        if (empty($selectedStyleSets)) {
            return '';
        }
        
        $less = "\n// Style-Sets - Extra Styles from StyleSetSelectionWidget\n";
        
        foreach ($selectedStyleSets as $styleSetId) {
            $styleSet = $this->styleSetManager->getStyleSetById((int)$styleSetId);
            
            if (!$styleSet || !$styleSet['is_active']) {
                continue;
            }
            
            $styles = $styleSet['styles_data'];
            if (empty($styles)) {
                continue;
            }
            
            $less .= "\n// Style-Set: " . $styleSet['name'] . "\n";
            
            foreach ($styles as $style) {
                // Style ist gültig wenn Slug vorhanden ist
                if (empty($style['slug'])) {
                    continue;
                }
                
                // Style explizit disabled
                if (isset($style['enabled']) && $style['enabled'] === false) {
                    continue;
                }
                
                $className = '.uk-' . ($style['type'] ?? 'background') . '-' . $style['slug'];
                $less .= "\n// Extra Style: " . ($style['name'] ?: 'Unnamed') . "\n";
                $less .= $className . " {\n";
                
                // CSS-Properties generieren
                if (!empty($style['background_color']) && $style['background_color'] !== '#ffffff') {
                    $less .= "    background-color: " . $style['background_color'] . ";\n";
                }
                
                if (!empty($style['text_color'])) {
                    $less .= "    color: " . $style['text_color'] . ";\n";
                }
                
                if (!empty($style['border_width']) && (int)$style['border_width'] > 0) {
                    $borderColor = !empty($style['border_color']) ? $style['border_color'] : '#e5e5e5';
                    $less .= "    border: " . $style['border_width'] . "px solid " . $borderColor . ";\n";
                }
                
                if (!empty($style['border_radius']) && $style['border_radius'] !== '0' && $style['border_radius'] !== '0px') {
                    $less .= "    border-radius: " . $style['border_radius'] . ";\n";
                }
                
                if (!empty($style['backdrop_blur']) && (int)$style['backdrop_blur'] > 0) {
                    $less .= "    backdrop-filter: blur(" . $style['backdrop_blur'] . "px);\n";
                    $less .= "    -webkit-backdrop-filter: blur(" . $style['backdrop_blur'] . "px);\n";
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
                }
            }
        }
        
        return $less;
    }

    public function renderForm(array $data = []): string
    {
        // Sicherstellen dass selected_style_sets ein Array ist
        $selectedStyleSets = [];
        if (isset($data['selected_style_sets']) && is_array($data['selected_style_sets'])) {
            $selectedStyleSets = $data['selected_style_sets'];
        }
        
        $styleSets = $this->styleSetManager->getAllStyleSets(true); // nur aktive
        
        $output = '';
        
        // Info-Box
        $output .= '<div class="uk-alert uk-alert-primary uk-margin-small">';
        $output .= '<h5><span uk-icon="info"></span> Extra Style-Sets</h5>';
        $output .= '<p>Style-Sets werden separat verwaltet und können hier für dieses Theme ausgewählt werden. ';
        $output .= '<a href="' . \rex_url::backendPage('uikit_theme_builder/extra_styles') . '" target="_blank">Zur Style-Sets Verwaltung <span uk-icon="external-link"></span></a></p>';
        $output .= '</div>';
        
        // Hidden Field damit der Key immer im POST ist (auch bei keiner Auswahl)
        $output .= '<input type="hidden" name="style_sets[_submitted]" value="1">';

        if (empty($styleSets)) {
            $output .= '<div class="uk-alert uk-alert-warning">';
            $output .= '<h6>Keine Style-Sets verfügbar</h6>';
            $output .= '<p>Es sind noch keine Style-Sets erstellt worden. ';
            $output .= '<a href="' . \rex_url::backendPage('uikit_theme_builder/extra_styles', ['func' => 'add']) . '" target="_blank">Erstes Style-Set erstellen <span uk-icon="plus"></span></a></p>';
            $output .= '</div>';
            return $output;
        }

        // Style-Sets zur Auswahl
        $output .= '<div class="uk-grid uk-grid-small uk-child-width-1-1@s uk-child-width-1-2@m" uk-grid>';
        
        foreach ($styleSets as $styleSet) {
            // Strict type comparison - beide zu int casten
            $styleSetId = (int)$styleSet['id'];
            $isSelected = in_array($styleSetId, array_map('intval', $selectedStyleSets), true);
            $stylesCount = count($styleSet['styles_data']);
            
            $output .= '<div>';
            $output .= '<div class="uk-card uk-card-default uk-card-small' . ($isSelected ? ' uk-card-primary' : '') . '">';
            $output .= '<div class="uk-card-header">';
            
            // Checkbox
            $output .= '<label class="uk-flex uk-flex-middle">';
            $output .= '<input type="checkbox" name="style_sets[selected_style_sets][]" value="' . $styleSetId . '"' . ($isSelected ? ' checked' : '') . ' class="uk-checkbox uk-margin-small-right">';
            $output .= '<div class="uk-width-expand">';
            $output .= '<h5 class="uk-card-title uk-margin-remove">' . \rex_escape($styleSet['name']) . '</h5>';
            
            if (!empty($styleSet['description'])) {
                $output .= '<p class="uk-text-small uk-margin-remove">' . \rex_escape($styleSet['description']) . '</p>';
            }
            
            $output .= '</div>';
            $output .= '<span class="uk-badge">' . $stylesCount . ' Style' . ($stylesCount != 1 ? 's' : '') . '</span>';
            $output .= '</label>';
            $output .= '</div>';
            
            // Style-Vorschau
            if ($stylesCount > 0) {
                $output .= '<div class="uk-card-body uk-padding-small">';
                $output .= '<div class="style-preview-mini">';
                
                $previewCount = 0;
                foreach ($styleSet['styles_data'] as $style) {
                    if (empty($style['slug']) || empty($style['enabled']) || $previewCount >= 3) {
                        break;
                    }
                    
                    $output .= '<div class="style-preview-chip" ';
                    $output .= 'style="background-color: ' . \rex_escape($style['background_color'] ?? '#ffffff') . '; ';
                    $output .= 'color: ' . \rex_escape($style['text_color'] ?? '#333') . ';" ';
                    $output .= 'title="' . \rex_escape($style['name'] ?: $style['slug']) . '">';
                    $output .= '.uk-' . \rex_escape($style['type']) . '-' . \rex_escape($style['slug']);
                    $output .= '</div>';
                    
                    $previewCount++;
                }
                
                if ($stylesCount > 3) {
                    $output .= '<div class="style-preview-chip uk-text-muted">+' . ($stylesCount - 3) . ' weitere</div>';
                }
                
                $output .= '</div>';
                $output .= '</div>';
            }
            
            $output .= '</div>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        // JavaScript für Live-Interaktion
        $output .= $this->renderJavaScript();
        
        return $output;
    }

    public function processFormData(array $postData): array
    {
        // Wichtig: Checkbox-Arrays können leer sein wenn nichts ausgewählt ist
        // In diesem Fall ist der Key gar nicht im POST-Array
        $selectedStyleSets = [];
        
        if (isset($postData['style_sets']['selected_style_sets']) && is_array($postData['style_sets']['selected_style_sets'])) {
            $selectedStyleSets = $postData['style_sets']['selected_style_sets'];
        }
        
        // Validierung: nur existierende Style-Sets
        $validStyleSets = [];
        foreach ($selectedStyleSets as $styleSetId) {
            if (is_numeric($styleSetId)) {
                $styleSetIdInt = (int)$styleSetId;
                if ($this->styleSetManager->getStyleSetById($styleSetIdInt)) {
                    $validStyleSets[] = $styleSetIdInt;
                }
            }
        }
        
        // Auch ein leeres Array ist ein gültiger Wert (= keine Style-Sets ausgewählt)
        return [
            'selected_style_sets' => $validStyleSets
        ];
    }

    public function getDefaultValues(): array
    {
        return [
            'selected_style_sets' => []
        ];
    }

    private function renderJavaScript(): string
    {
        return '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Card-Interaktion bei Checkbox-Änderung (nur visuelles Feedback)
            document.querySelectorAll("input[name*=\'selected_style_sets\']").forEach(function(checkbox) {
                checkbox.addEventListener("change", function() {
                    const card = this.closest(".uk-card");
                    if (this.checked) {
                        card.classList.add("uk-card-primary");
                        card.classList.remove("uk-card-default");
                    } else {
                        card.classList.add("uk-card-default");
                        card.classList.remove("uk-card-primary");
                    }
                });
            });
            
            // DEAKTIVIERT: Card-Click führt zu Problemen beim Form-Submit
            // Card-Click nur auf dem Label-Bereich, nicht auf der ganzen Card
            /*
            document.querySelectorAll(".uk-card").forEach(function(card) {
                card.addEventListener("click", function(e) {
                    // Nur wenn nicht direkt auf Checkbox geklickt wurde
                    if (e.target.type !== "checkbox") {
                        const checkbox = card.querySelector("input[type=checkbox]");
                        if (checkbox) {
                            checkbox.checked = !checkbox.checked;
                            checkbox.dispatchEvent(new Event("change"));
                        }
                    }
                });
            });
            */
        });
        </script>
        
        <style>
        .style-preview-mini {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        
        .style-preview-chip {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            border: 1px solid rgba(0,0,0,0.1);
            white-space: nowrap;
            font-family: monospace;
        }
        
        .uk-card {
            transition: all 0.2s ease;
        }
        
        .uk-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .uk-card-primary .style-preview-chip {
            opacity: 0.9;
        }
        
        /* Label macht die ganze Card klickbar */
        .uk-card label {
            cursor: pointer;
        }
        </style>';
    }
}