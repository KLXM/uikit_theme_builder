<?php

namespace UikitThemeBuilder\Widget;

/**
 * Container Widget - UIKit Container Größen
 */
class ContainerWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Container';
    }

    public function getKey(): string
    {
        return 'container';
    }

    public function getDescription(): string
    {
        return 'Konfiguration der UIKit-Container-Größen für verschiedene Breakpoints';
    }

    public function getFields(): array
    {
        return [
            'container-max-width' => [
                'label' => 'Standard Container',
                'type' => 'number',
                'default' => '1200',
                'unit' => 'px',
                'min' => 800,
                'max' => 2000,
                'help' => 'Standard Container max-width (Default: 1200px)'
            ],
            'container-xsmall-max-width' => [
                'label' => 'XSmall Container',
                'type' => 'number',
                'default' => '750',
                'unit' => 'px',
                'min' => 500,
                'max' => 1000,
                'help' => 'XSmall Container max-width (Default: 750px)'
            ],
            'container-small-max-width' => [
                'label' => 'Small Container',
                'type' => 'number',
                'default' => '960',
                'unit' => 'px',
                'min' => 600,
                'max' => 1200,
                'help' => 'Small Container max-width (Default: 960px)'
            ],
            'container-large-max-width' => [
                'label' => 'Large Container',
                'type' => 'number',
                'default' => '1400',
                'unit' => 'px',
                'min' => 1200,
                'max' => 1800,
                'help' => 'Large Container max-width (Default: 1400px)'
            ],
            'container-xlarge-max-width' => [
                'label' => 'XLarge Container',
                'type' => 'number',
                'default' => '1600',
                'unit' => 'px',
                'min' => 1400,
                'max' => 2000,
                'help' => 'XLarge Container max-width (Default: 1600px)'
            ]
        ];
    }

    public function getDefaultValues(): array
    {
        return [
            'container-max-width' => '1200px',
            'container-xsmall-max-width' => '750px',
            'container-small-max-width' => '960px',
            'container-large-max-width' => '1400px',
            'container-xlarge-max-width' => '1600px'
        ];
    }

    public function renderForm(array $values = []): string
    {
        $fields = $this->getFields();
        $output = '';

        // Debug-Ausgabe hinzufügen
        if (\rex::isDebugMode()) {
            $output .= '<div class="uk-alert-primary" uk-alert>';
            $output .= '<h4>🐛 Debug: ContainerWidget Werte</h4>';
            $output .= '<pre>' . htmlspecialchars(print_r($values, true)) . '</pre>';
            $output .= '</div>';
        }

        $output .= '<div class="uk-grid-small uk-child-width-1-2@m" uk-grid>';

        foreach ($fields as $key => $field) {
            // Wert aus den gespeicherten Daten holen oder Default verwenden
            $value = $values[$key] ?? $field['default'];
            
            // Falls gespeicherter Wert "px" enthält, entfernen wir es für das input field
            if (is_string($value) && substr($value, -2) === 'px') {
                $value = (int) str_replace('px', '', $value);
            }
            
            // Sicherstellen dass es eine Zahl ist
            $value = (int) $value;
            
            $output .= '<div>';
            
            $input = '<div class="uk-flex uk-flex-middle">';
            $input .= '<input type="number" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" class="uk-input uk-width-small" min="' . $field['min'] . '" max="' . $field['max'] . '">';
            $input .= '<span class="uk-text-meta uk-margin-small-left">' . $field['unit'] . '</span>';
            $input .= '</div>';
            
            if (!empty($field['help'])) {
                $input .= '<div class="uk-text-meta uk-text-small uk-margin-small-top">' . htmlspecialchars($field['help']) . '</div>';
            }
            
            $output .= $this->renderFormRow($field['label'], $input);
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    public function processFormData(array $formData): array
    {
        $processed = [];
        
        // Debug: Was kommt rein
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('ContainerWidget::processFormData - Input: ' . print_r($formData, true));
        }
        
        foreach ($this->getFields() as $key => $field) {
            if (isset($formData[$key])) {
                $value = (int) $formData[$key];
                // Validierung der Min/Max-Werte
                $value = max($field['min'], min($field['max'], $value));
                $processed[$key] = $value . 'px';
            } else {
                // Falls Feld nicht übertragen wurde, Default-Wert verwenden
                $processed[$key] = $field['default'] . 'px';
            }
        }
        
        // Debug: Was kommt raus
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('ContainerWidget::processFormData - Output: ' . print_r($processed, true));
        }
        
        return $processed;
    }

    public function validateFormData(array $data): array
    {
        $errors = [];
        $fields = $this->getFields();
        
        foreach ($data as $key => $value) {
            $numValue = (int) str_replace('px', '', $value);
            
            if (isset($fields[$key])) {
                $field = $fields[$key];
                
                if ($numValue < $field['min']) {
                    $errors[] = $field['label'] . ' muss mindestens ' . $field['min'] . 'px sein';
                }
                
                if ($numValue > $field['max']) {
                    $errors[] = $field['label'] . ' darf maximal ' . $field['max'] . 'px sein';
                }
            }
        }
        
        // Logische Reihenfolge prüfen
        $containers = [];
        foreach ($data as $key => $value) {
            $containers[$key] = (int) str_replace('px', '', $value);
        }
        
        if (isset($containers['container-xsmall-max-width'], $containers['container-small-max-width']) && $containers['container-xsmall-max-width'] >= $containers['container-small-max-width']) {
            $errors[] = 'XSmall Container muss kleiner als Small Container sein';
        }
        
        if (isset($containers['container-small-max-width'], $containers['container-max-width']) && $containers['container-small-max-width'] >= $containers['container-max-width']) {
            $errors[] = 'Small Container muss kleiner als Standard Container sein';
        }
        
        if (isset($containers['container-max-width'], $containers['container-large-max-width']) && $containers['container-max-width'] >= $containers['container-large-max-width']) {
            $errors[] = 'Standard Container muss kleiner als Large Container sein';
        }
        
        if (isset($containers['container-large-max-width'], $containers['container-xlarge-max-width']) && $containers['container-large-max-width'] >= $containers['container-xlarge-max-width']) {
            $errors[] = 'Large Container muss kleiner als XLarge Container sein';
        }
        
        return $errors;
    }

    public function generateLessVariables(array $data): array
    {
        $variables = [];
        
        // Debug: Was kommt rein
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('ContainerWidget::generateLessVariables - Input: ' . print_r($data, true));
        }
        
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                // Keys haben bereits Hyphens - direkt verwenden
                $variables[$key] = $value;
            }
        }
        
        // Debug: Was kommt raus
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('ContainerWidget::generateLessVariables - Output: ' . print_r($variables, true));
        }
        
        return $variables;
    }
}