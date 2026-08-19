<?php

namespace UikitThemeBuilder\Widget;

class BreakpointsWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Breakpoints';
    }

    public function getKey(): string
    {
        return 'breakpoints';
    }

    public function getDescription(): string
    {
        return 'Responsive Breakpoints für verschiedene Bildschirmgrößen';
    }

    public function getDefaultValues(): array
    {
        return [
            'breakpoint-small' => '640px',
            'breakpoint-medium' => '960px',
            'breakpoint-large' => '1200px',
            'breakpoint-xlarge' => '1600px'
        ];
    }

    public function getFields(): array
    {
        return [
            'breakpoint-small' => [
                'label' => 'Small Breakpoint',
                'type' => 'number',
                'default' => '640',
                'unit' => 'px',
                'min' => 320,
                'max' => 1200,
                'help' => 'Phone landscape - @breakpoint-small (Default: 640px)'
            ],
            'breakpoint-medium' => [
                'label' => 'Medium Breakpoint',
                'type' => 'number',
                'default' => '960',
                'unit' => 'px',
                'min' => 640,
                'max' => 1600,
                'help' => 'Tablet landscape - @breakpoint-medium (Default: 960px)'
            ],
            'breakpoint-large' => [
                'label' => 'Large Breakpoint',
                'type' => 'number',
                'default' => '1200',
                'unit' => 'px',
                'min' => 960,
                'max' => 2000,
                'help' => 'Desktop - @breakpoint-large (Default: 1200px)'
            ],
            'breakpoint-xlarge' => [
                'label' => 'XLarge Breakpoint',
                'type' => 'number',
                'default' => '1600',
                'unit' => 'px',
                'min' => 1200,
                'max' => 2400,
                'help' => 'Large screens - @breakpoint-xlarge (Default: 1600px)'
            ]
        ];
    }

    public function renderForm(array $values = []): string
    {
        $fields = $this->getFields();
        $output = '';

        // Debug-Ausgabe hinzufügen
        if (\rex::isDebugMode()) {
            $output .= '<div class="uk-alert-primary" uk-alert>';
            $output .= '<h4>🐛 Debug: BreakpointsWidget Werte</h4>';
            $output .= '<pre>' . htmlspecialchars(print_r($values, true)) . '</pre>';
            $output .= '</div>';
        }

        foreach ($fields as $key => $field) {
            // Wert aus den gespeicherten Daten holen oder Default verwenden
            $value = $values[$key] ?? $field['default'];
            
            // Falls gespeicherter Wert "px" enthält, entfernen wir es für das input field
            if (is_string($value) && substr($value, -2) === 'px') {
                $value = (int) str_replace('px', '', $value);
            }
            
            // Sicherstellen dass es eine Zahl ist
            $value = (int) $value;
            
            $input = '<div class="uk-flex uk-flex-middle">';
            $input .= '<input type="number" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" class="uk-input uk-width-small" min="' . $field['min'] . '" max="' . $field['max'] . '">';
            $input .= '<span class="uk-text-meta uk-margin-small-left">' . $field['unit'] . '</span>';
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
        
        // Debug: Schauen was ankommt
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('BreakpointsWidget::processFormData - Input data: ' . print_r($formData, true));
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
        
        // Debug: Schauen was rausgeht
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('BreakpointsWidget::processFormData - Output data: ' . print_r($processed, true));
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
        $breakpoints = [];
        foreach ($data as $key => $value) {
            $breakpoints[$key] = (int) str_replace('px', '', $value);
        }
        
        if (isset($breakpoints['breakpoint-small'], $breakpoints['breakpoint-medium']) && $breakpoints['breakpoint-small'] >= $breakpoints['breakpoint-medium']) {
            $errors[] = 'Small Breakpoint muss kleiner als Medium Breakpoint sein';
        }
        
        if (isset($breakpoints['breakpoint-medium'], $breakpoints['breakpoint-large']) && $breakpoints['breakpoint-medium'] >= $breakpoints['breakpoint-large']) {
            $errors[] = 'Medium Breakpoint muss kleiner als Large Breakpoint sein';
        }
        
        if (isset($breakpoints['breakpoint-large'], $breakpoints['breakpoint-xlarge']) && $breakpoints['breakpoint-large'] >= $breakpoints['breakpoint-xlarge']) {
            $errors[] = 'Large Breakpoint muss kleiner als XLarge Breakpoint sein';
        }
        
        if (isset($breakpoints['breakpoint-xlarge'], $breakpoints['breakpoint-2xlarge']) && $breakpoints['breakpoint-xlarge'] >= $breakpoints['breakpoint-2xlarge']) {
            $errors[] = 'XLarge Breakpoint muss kleiner als 2XLarge Breakpoint sein';
        }
        
        return $errors;
    }

    public function generateLessVariables(array $data): array
    {
        $variables = [];
        
        // Debug: Was kommt rein
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('BreakpointsWidget::generateLessVariables - Input: ' . print_r($data, true));
        }
        
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                // Keys haben bereits Hyphens - direkt verwenden
                $variables[$key] = $value;
            }
        }
        
        // Debug: Was kommt raus
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('BreakpointsWidget::generateLessVariables - Output: ' . print_r($variables, true));
        }
        
        return $variables;
    }
}