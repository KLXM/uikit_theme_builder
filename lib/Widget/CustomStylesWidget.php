<?php

namespace UikitThemeBuilder\Widget;

/**
 * Custom Styles Widget - Freie LESS-Code Eingabe
 */
class CustomStylesWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Custom Styles';
    }

    public function getKey(): string
    {
        return 'custom_styles';
    }

    public function getDescription(): string
    {
        return 'Freier LESS-Code für individuelle Anpassungen und Custom Styles';
    }

    public function getFields(): array
    {
        return [
            'custom_less_code' => [
                'label' => 'Custom LESS Code',
                'type' => 'textarea',
                'default' => '',
                'help' => 'Hier kannst du beliebigen LESS-Code eingeben. Beispiel: .my-custom-class { color: @global-primary-background; }'
            ],
            'custom_variables' => [
                'label' => 'Custom Variablen',
                'type' => 'textarea', 
                'default' => '',
                'help' => 'Definiere eigene LESS-Variablen. Beispiel: @my-custom-color: #ff6b6b;'
            ],
            'custom_mixins' => [
                'label' => 'Custom Mixins',
                'type' => 'textarea',
                'default' => '',
                'help' => 'Erstelle wiederverwendbare LESS-Mixins. Beispiel: .my-mixin(@color) { background: @color; }'
            ]
        ];
    }

    public function renderForm(array $values = []): string
    {
        $output = '';
        
        // Custom LESS Code
        $customCode = $values['custom_less_code'] ?? '';
        $output .= '<div class="uk-margin">';
        $output .= '<label class="uk-form-label uk-text-bold">Custom LESS Code</label>';
        $output .= '<p class="uk-text-small uk-text-muted">Hier kannst du beliebigen LESS-Code eingeben für individuelle Anpassungen.</p>';
        $output .= '<textarea name="custom_styles[custom_less_code]" rows="8" class="uk-textarea uk-width-1-1 codemirror" 
                    data-codemirror-theme="dracula" 
                    data-codemirror-mode="css" 
                    data-codemirror-options=\'{"indentWithTabs": false, "lineNumbers": true, "theme": "dracula", "mode": "text/x-less", "autoCloseBrackets": true, "matchBrackets": true, "indentUnit": 2}\'
                    placeholder="// Beispiel:
.my-custom-button {
    background: @global-primary-background;
    border-radius: 8px;
    
    &:hover {
        background: darken(@global-primary-background, 10%);
    }
}">' . rex_escape($customCode) . '</textarea>';
        $output .= '</div>';

        // Custom Variables
        $customVars = $values['custom_variables'] ?? '';
        $output .= '<div class="uk-margin">';
        $output .= '<label class="uk-form-label uk-text-bold">Custom Variablen</label>';
        $output .= '<p class="uk-text-small uk-text-muted">Definiere eigene LESS-Variablen die du in deinem Custom Code verwenden kannst.</p>';
        $output .= '<textarea name="custom_styles[custom_variables]" rows="4" class="uk-textarea uk-width-1-1 codemirror" 
                    data-codemirror-theme="dracula" 
                    data-codemirror-mode="css" 
                    data-codemirror-options=\'{"indentWithTabs": false, "lineNumbers": true, "theme": "dracula", "mode": "text/x-less", "autoCloseBrackets": true, "matchBrackets": true, "indentUnit": 2}\'
                    placeholder="// Beispiel:
@my-brand-color: #ff6b6b;
@my-spacing: 24px;
@my-border-radius: 12px;">' . rex_escape($customVars) . '</textarea>';
        $output .= '</div>';

        // Custom Mixins
        $customMixins = $values['custom_mixins'] ?? '';
        $output .= '<div class="uk-margin">';
        $output .= '<label class="uk-form-label uk-text-bold">Custom Mixins</label>';
        $output .= '<p class="uk-text-small uk-text-muted">Erstelle wiederverwendbare LESS-Mixins für komplexere Styles.</p>';
        $output .= '<textarea name="custom_styles[custom_mixins]" rows="6" class="uk-textarea uk-width-1-1 codemirror" 
                    data-codemirror-theme="dracula" 
                    data-codemirror-mode="css" 
                    data-codemirror-options=\'{"indentWithTabs": false, "lineNumbers": true, "theme": "dracula", "mode": "text/x-less", "autoCloseBrackets": true, "matchBrackets": true, "indentUnit": 2}\'
                    placeholder="// Beispiel:
.my-gradient-bg(@start, @end) {
    background: linear-gradient(135deg, @start 0%, @end 100%);
}

.my-shadow(@level: 1) when (@level = 1) {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.my-shadow(@level: 1) when (@level = 2) {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}">' . rex_escape($customMixins) . '</textarea>';
        $output .= '</div>';

        // Hilfe-Box
        $output .= '<div class="uk-alert-primary" uk-alert>';
        $output .= '<h4>💡 Tipps für Custom Styles:</h4>';
        $output .= '<ul class="uk-list">';
        $output .= '<li><strong>Variablen nutzen:</strong> Verwende UIKit-Variablen wie <code>@global-primary-background</code></li>';
        $output .= '<li><strong>Namespacing:</strong> Präfixe deine Klassen (z.B. <code>.my-</code>) um Konflikte zu vermeiden</li>';
        $output .= '<li><strong>LESS-Features:</strong> Nutze Nesting, Mixins und Funktionen</li>';
        $output .= '<li><strong>Responsive:</strong> Verwende UIKit-Breakpoints <code>@media (min-width: @breakpoint-small)</code></li>';
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }

    public function processFormData(array $formData): array
    {
        $data = [];
        
        if (isset($formData['custom_styles'])) {
            $customData = $formData['custom_styles'];
            
            $data['custom_less_code'] = trim($customData['custom_less_code'] ?? '');
            $data['custom_variables'] = trim($customData['custom_variables'] ?? '');
            $data['custom_mixins'] = trim($customData['custom_mixins'] ?? '');
        }
        
        return $data;
    }

    public function getDefaultValues(): array
    {
        return [
            'custom_less_code' => '',
            'custom_variables' => '',
            'custom_mixins' => ''
        ];
    }

    public function validateFormData(array $data): array
    {
        $errors = [];
        
        // LESS-Syntax Basis-Validierung (optional)
        if (!empty($data['custom_less_code'])) {
            // Prüfe auf offene Klammern
            $openBraces = substr_count($data['custom_less_code'], '{');
            $closeBraces = substr_count($data['custom_less_code'], '}');
            
            if ($openBraces !== $closeBraces) {
                $errors[] = 'Custom LESS Code: Anzahl der öffnenden und schließenden Klammern stimmt nicht überein';
            }
        }
        
        return $errors;
    }

    public function generateLessVariables(array $data): array
    {
        // Custom Styles Widget generiert keine UIKit-Variablen
        // sondern fügt eigenen LESS-Code hinzu
        return [];
    }

    public function generateLess(array $data): string
    {
        $less = '';
        
        // Custom Variables zuerst (damit sie in nachfolgendem Code verwendet werden können)
        if (!empty($data['custom_variables'])) {
            $less .= "// Custom Variables\n";
            $less .= $data['custom_variables'] . "\n\n";
        }
        
        // Custom Mixins als nächstes
        if (!empty($data['custom_mixins'])) {
            $less .= "// Custom Mixins\n";
            $less .= $data['custom_mixins'] . "\n\n";
        }
        
        // Custom LESS Code zuletzt
        if (!empty($data['custom_less_code'])) {
            $less .= "// Custom LESS Code\n";
            $less .= $data['custom_less_code'] . "\n\n";
        }
        
        return $less;
    }
}