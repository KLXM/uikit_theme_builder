<?php

namespace UikitThemeBuilder\Widget;

/**
 * Components Widget - Modal, Offcanvas, etc.
 */
class ComponentsWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Components';
    }

    public function getKey(): string
    {
        return 'components';
    }

    public function getDescription(): string
    {
        return 'Komponenten-Einstellungen für Modals, Offcanvas, etc.';
    }

    public function getDefaultValues(): array
    {
        return [
            // Modal
            'modal-dialog-background' => '@global-background',
            'modal-dialog-border-radius' => '0',
            'modal-header-background' => '@global-muted-background',
            'modal-footer-background' => '@global-muted-background',
            'modal-title-font-size' => '@global-xlarge-font-size',
            
            // Offcanvas
            'offcanvas-bar-width' => '270px',
            'offcanvas-bar-width-s' => '350px',
            'offcanvas-bar-background' => '@global-secondary-background',
            'offcanvas-bar-color' => '@global-inverse-color'
        ];
    }

    public function getFields(): array
    {
        // Farben von ColorsWidget holen
        $colorOptions = $this->getColorOptions();
        
        return [
            // Modal
            'modal-dialog-background' => [
                'label' => 'Modal Hintergrund',
                'type' => 'select',
                'default' => '@global-background',
                'options' => $colorOptions
            ],
            'modal-dialog-border-radius' => [
                'label' => 'Modal Border Radius',
                'type' => 'text',
                'default' => '0',
                'placeholder' => '0, 8px, @border-rounded'
            ],
            'modal-header-background' => [
                'label' => 'Modal Header Hintergrund',
                'type' => 'select',
                'default' => '@global-muted-background',
                'options' => $colorOptions
            ],
            'modal-footer-background' => [
                'label' => 'Modal Footer Hintergrund',
                'type' => 'select',
                'default' => '@global-muted-background',
                'options' => $colorOptions
            ],
            'modal-title-font-size' => [
                'label' => 'Modal Titel Größe',
                'type' => 'text',
                'default' => '@global-xlarge-font-size',
                'placeholder' => '@global-xlarge-font-size oder 2rem'
            ],
            
            // Offcanvas
            'offcanvas-bar-width' => [
                'label' => 'Offcanvas Breite',
                'type' => 'text',
                'default' => '270px',
                'placeholder' => '270px'
            ],
            'offcanvas-bar-width-s' => [
                'label' => 'Offcanvas Breite (klein)',
                'type' => 'text',
                'default' => '350px',
                'placeholder' => '350px (bei kleineren Screens)'
            ],
            'offcanvas-bar-background' => [
                'label' => 'Offcanvas Hintergrund',
                'type' => 'select',
                'default' => '@global-secondary-background',
                'options' => $colorOptions
            ],
            'offcanvas-bar-color' => [
                'label' => 'Offcanvas Textfarbe',
                'type' => 'select',
                'default' => '@global-inverse-color',
                'options' => $colorOptions
            ]
        ];
    }

    public function renderForm(array $values = []): string
    {
        $fields = $this->getFields();
        
        // Felder in Kategorien gruppieren
        $categories = [
            'modal' => [
                'label' => 'Modal',
                'fields' => [
                    'modal-dialog-background', 'modal-dialog-border-radius',
                    'modal-header-background', 'modal-footer-background',
                    'modal-title-font-size'
                ]
            ],
            'offcanvas' => [
                'label' => 'Offcanvas',
                'fields' => [
                    'offcanvas-bar-width', 'offcanvas-bar-width-s',
                    'offcanvas-bar-background', 'offcanvas-bar-color'
                ]
            ]
        ];
        
        // Tab Navigation
        $output = '<ul class="uk-tab" uk-tab>';
        $isFirst = true;
        foreach ($categories as $catKey => $category) {
            $activeClass = $isFirst ? ' class="uk-active"' : '';
            $output .= '<li' . $activeClass . '><a href="#">' . $category['label'] . '</a></li>';
            $isFirst = false;
        }
        $output .= '</ul>';
        
        // Tab Content
        $output .= '<ul class="uk-switcher uk-margin">';
        
        foreach ($categories as $catKey => $category) {
            $output .= '<li>';
            
            foreach ($category['fields'] as $fieldKey) {
                if (!isset($fields[$fieldKey])) continue;
                
                $field = $fields[$fieldKey];
                $value = $values[$fieldKey] ?? $field['default'];
                
                // Field rendern basierend auf Typ
                switch ($field['type']) {
                    case 'select':
                        $input = $this->renderSelectInput($fieldKey, $field['options'], $value);
                        break;
                    case 'text':
                    default:
                        $attrs = [];
                        if (isset($field['placeholder'])) $attrs['placeholder'] = $field['placeholder'];
                        $input = $this->renderTextInput($fieldKey, $value, $attrs);
                        break;
                }
                
                $output .= $this->renderFormRow($field['label'], $input);
            }
            
            $output .= '</li>';
        }
        
        $output .= '</ul>';
        
        // JavaScript für rex:ready Event bei Tab-Wechsel
        $output .= '<script>
        (function() {
            if (typeof UIkit !== "undefined") {
                document.addEventListener("DOMContentLoaded", function() {
                    const tabs = document.querySelectorAll("[uk-tab]");
                    tabs.forEach(function(tabElement) {
                        UIkit.util.on(tabElement, "shown", function() {
                            if (typeof jQuery !== "undefined") {
                                jQuery(document).trigger("rex:ready");
                            }
                        });
                    });
                });
            }
        })();
        </script>';

        return $output;
    }

    public function processFormData(array $formData): array
    {
        $processed = [];
        $defaults = $this->getDefaultValues();
        
        foreach ($this->getFields() as $key => $field) {
            if (array_key_exists($key, $formData)) {
                $processed[$key] = $formData[$key];
            } elseif (isset($defaults[$key])) {
                $processed[$key] = $defaults[$key];
            }
        }
        
        return $processed;
    }

    public function validateFormData(array $data): array
    {
        $errors = [];
        return $errors;
    }

    public function generateLessVariables(array $data): array
    {
        $variables = [];
        
        foreach ($data as $key => $value) {
            // Auch '0' als gültigen Wert akzeptieren (z.B. für border-radius: 0)
            if ($value !== '' && $value !== null) {
                $variables[$key] = $value;
            }
        }
        
        return $variables;
    }
    
    /**
     * Gibt verfügbare Farboptionen zurück
     */
    private function getColorOptions(): array
    {
        $colorOptions = [
            '@global-background' => 'Background (Standard)',
            '@global-muted-background' => 'Muted Background',
            '@global-primary-background' => 'Primary',
            '@global-secondary-background' => 'Secondary',
            '@global-success-background' => 'Success',
            '@global-warning-background' => 'Warning',
            '@global-danger-background' => 'Danger',
            '@global-color' => 'Text',
            '@global-emphasis-color' => 'Emphasis',
            '@global-muted-color' => 'Muted',
            '@global-inverse-color' => 'Inverse (Hell auf Dunkel)',
            '#ffffff' => 'Weiß',
            '#000000' => 'Schwarz'
        ];

        return $colorOptions;
    }
}
