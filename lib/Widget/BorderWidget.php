<?php

namespace UikitThemeBuilder\Widget;

class BorderWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Borders';
    }

    public function getKey(): string
    {
        return 'borders';
    }

    public function getDescription(): string
    {
        return 'Border-Stile, Breiten und Radius-Einstellungen';
    }

    public function getDefaultValues(): array
    {
        return [
            'global-border-width' => '1px',
            'border-rounded' => '4px'
        ];
    }

    public function getFields(): array
    {
        return [
            'global-border-width' => [
                'label' => 'Global Border Breite',
                'type' => 'number',
                'default' => '1',
                'unit' => 'px',
                'min' => 0,
                'max' => 10,
                'step' => 1,
                'help' => 'Globale Breite für Rahmen (UIKit Standard)'
            ],
            'border-rounded' => [
                'label' => 'Border Radius',
                'type' => 'number',
                'default' => '4',
                'unit' => 'px',
                'min' => 0,
                'max' => 50,
                'step' => 1,
                'help' => 'Standard Radius für abgerundete Ecken (UIKit Standard)'
            ]
        ];
    }

    public function renderForm(array $values = []): string
    {
        $fields = $this->getFields();
        $output = '';

        foreach ($fields as $key => $field) {
            $value = $values[$key] ?? $field['default'];
            
            // Wenn Wert mit 'px' gespeichert ist, nur die Zahl extrahieren
            if (is_string($value) && str_ends_with($value, 'px')) {
                $value = (int) str_replace('px', '', $value);
            }
            
            $input = '<div class="uk-flex uk-flex-middle">';
            $input .= '<input type="number" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" class="uk-input uk-width-small" min="' . $field['min'] . '" max="' . $field['max'] . '" step="' . $field['step'] . '">';
            $input .= '<span class="uk-text-meta uk-margin-small-left">' . $field['unit'] . '</span>';
            
            // Live Preview für Border Radius
            if (strpos($key, 'rounded') !== false) {
                $input .= '<div class="uk-margin-small-left" style="width: 30px; height: 30px; background: white; border: 1px solid #ddd; border-radius: ' . $value . 'px;"></div>';
            }
            
            $input .= '</div>';
            
            if (!empty($field['help'])) {
                $input .= '<div class="uk-text-meta uk-text-small uk-margin-small-top">' . htmlspecialchars($field['help']) . '</div>';
            }
            
            $output .= $this->renderFormRow($field['label'], $input);
        }

        return $output;
    }

    public function processFormData(array $formData): array
    {
        $processed = [];
        $defaults = $this->getDefaultValues();
        
        foreach ($this->getFields() as $key => $field) {
            if (isset($formData[$key]) && $formData[$key] !== '') {
                $value = (int) $formData[$key];
                // Validierung der Min/Max-Werte
                $value = max($field['min'], min($field['max'], $value));
                $processed[$key] = $value . 'px';
            } else {
                // Fallback auf Standardwert
                $defaultValue = $defaults[$key] ?? $field['default'];
                // Sicherstellen, dass Standardwert 'px' hat
                if (is_numeric($defaultValue)) {
                    $processed[$key] = $defaultValue . 'px';
                } else {
                    $processed[$key] = $defaultValue;
                }
            }
        }
        
        // Backward compatibility: Alte Felder ignorieren
        $oldFields = ['border_width', 'border_rounded_small', 'border_rounded_medium', 'border_rounded_large', 'border_rounded_pill'];
        foreach ($oldFields as $oldField) {
            unset($processed[$oldField]);
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
        
        return $errors;
    }

    public function generateLessVariables(array $data): array
    {
        $variables = [];
        
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                // Keys haben bereits Hyphens - direkt verwenden
                $variables[$key] = $value;
            }
        }
        
        return $variables;
    }
}