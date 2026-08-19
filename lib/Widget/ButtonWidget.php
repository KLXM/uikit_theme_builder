<?php

namespace UikitThemeBuilder\Widget;

/**
 * Button Widget - Settings for UIkit Buttons.
 */
class ButtonWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Buttons';
    }

    public function getKey(): string
    {
        return 'buttons';
    }

    public function getDescription(): string
    {
        return 'Darstellung und Farben der Buttons anpassen.';
    }

    public function getDefaultValues(): array
    {
        return [
            // Standard
            'button-font-size' => '@global-small-font-size',
            'button-text-transform' => 'uppercase',
            'button-border-radius' => '0',
            'button-border-width' => '@global-border-width',

            // Padding
            'button-padding-horizontal' => '@global-gutter',

            // Primary
            'button-primary-background' => '@global-primary-background',
            'button-primary-color' => '@global-inverse-color',
            'button-primary-hover-background' => 'darken(@button-primary-background, 5%)',
            'button-primary-hover-color' => '@global-inverse-color',

            // Secondary
            'button-secondary-background' => '@global-secondary-background',
            'button-secondary-color' => '@global-inverse-color',
            'button-secondary-hover-background' => 'darken(@button-secondary-background, 5%)',
            'button-secondary-hover-color' => '@global-inverse-color',

            // Default
            'button-default-background' => 'transparent',
            'button-default-color' => '@global-emphasis-color',
            'button-default-hover-background' => 'transparent',
            'button-default-hover-color' => '@global-emphasis-color',
            'button-default-border' => '@global-border',
            'button-default-hover-border' => 'darken(@global-border, 20%)',
        ];
    }

    public function getFields(): array
    {
        $colorOptions = $this->getColorOptions();

        return [
            'button-font-size' => [
                'label' => 'Schriftgröße',
                'type' => 'text',
                'default' => '@global-small-font-size',
                'placeholder' => 'z.B. @global-small-font-size, 14px',
            ],
            'button-text-transform' => [
                'label' => 'Text Transformation',
                'type' => 'text',
                'default' => 'uppercase',
                'placeholder' => 'none, uppercase, lowercase',
            ],
            'button-border-radius' => [
                'label' => 'Border Radius (Rundung)',
                'type' => 'text',
                'default' => '0',
                'placeholder' => '0, 4px, 500px',
            ],
            'button-border-width' => [
                'label' => 'Rahmenbreite',
                'type' => 'text',
                'default' => '@global-border-width',
                'placeholder' => '1px, 2px',
            ],
            'button-padding-horizontal' => [
                'label' => 'Innenabstand (Horizontal)',
                'type' => 'text',
                'default' => '@global-gutter',
                'placeholder' => '15px',
            ],

            // Primary
            'button-primary-background' => [
                'label' => 'Hintergrund (Primary)',
                'type' => 'select',
                'default' => '@global-primary-background',
                'options' => $colorOptions,
            ],
            'button-primary-color' => [
                'label' => 'Textfarbe (Primary)',
                'type' => 'select',
                'default' => '@global-inverse-color',
                'options' => $colorOptions,
            ],
            'button-primary-hover-background' => [
                'label' => 'Hover Hintergrund (Primary)',
                'type' => 'select',
                'default' => 'darken(@button-primary-background, 5%)',
                'options' => $colorOptions,
            ],
            'button-primary-hover-color' => [
                'label' => 'Hover Textfarbe (Primary)',
                'type' => 'select',
                'default' => '@global-inverse-color',
                'options' => $colorOptions,
            ],

            // Secondary
            'button-secondary-background' => [
                'label' => 'Hintergrund (Secondary)',
                'type' => 'select',
                'default' => '@global-secondary-background',
                'options' => $colorOptions,
            ],
            'button-secondary-color' => [
                'label' => 'Textfarbe (Secondary)',
                'type' => 'select',
                'default' => '@global-inverse-color',
                'options' => $colorOptions,
            ],
            'button-secondary-hover-background' => [
                'label' => 'Hover Hintergrund (Secondary)',
                'type' => 'select',
                'default' => 'darken(@button-secondary-background, 5%)',
                'options' => $colorOptions,
            ],
            'button-secondary-hover-color' => [
                'label' => 'Hover Textfarbe (Secondary)',
                'type' => 'select',
                'default' => '@global-inverse-color',
                'options' => $colorOptions,
            ],

            // Default
            'button-default-background' => [
                'label' => 'Hintergrund (Default)',
                'type' => 'text', // using text for transparent, default is a select with primary/secondary.
                'default' => 'transparent',
                'placeholder' => 'transparent, #fff',
            ],
            'button-default-color' => [
                'label' => 'Textfarbe (Default)',
                'type' => 'select',
                'default' => '@global-emphasis-color',
                'options' => $colorOptions,
            ],
            'button-default-hover-background' => [
                'label' => 'Hover Hintergrund (Default)',
                'type' => 'text',
                'default' => 'transparent',
                'placeholder' => 'transparent, #f8f8f8',
            ],
            'button-default-hover-color' => [
                'label' => 'Hover Textfarbe (Default)',
                'type' => 'select',
                'default' => '@global-emphasis-color',
                'options' => $colorOptions,
            ],
            'button-default-border' => [
                'label' => 'Rahmenfarbe (Default)',
                'type' => 'text',
                'default' => '@global-border',
                'placeholder' => '@global-border, #ddd',
            ],
            'button-default-hover-border' => [
                'label' => 'Hover Rahmenfarbe (Default)',
                'type' => 'text',
                'default' => 'darken(@global-border, 20%)',
                'placeholder' => 'darken(@global-border, 20%)',
            ],
        ];
    }

    public function renderForm(array $values = []): string
    {
        $fields = $this->getFields();

        $categories = [
            'general' => [
                'label' => 'Allgemein',
                'fields' => [
                    'button-font-size', 'button-text-transform',
                    'button-border-radius', 'button-border-width',
                    'button-padding-horizontal',
                ],
            ],
            'primary' => [
                'label' => 'Primary (uk-button-primary)',
                'fields' => [
                    'button-primary-background', 'button-primary-color',
                    'button-primary-hover-background', 'button-primary-hover-color',
                ],
            ],
            'secondary' => [
                'label' => 'Secondary (uk-button-secondary)',
                'fields' => [
                    'button-secondary-background', 'button-secondary-color',
                    'button-secondary-hover-background', 'button-secondary-hover-color',
                ],
            ],
            'default' => [
                'label' => 'Default (uk-button-default)',
                'fields' => [
                    'button-default-background', 'button-default-color',
                    'button-default-hover-background', 'button-default-hover-color',
                    'button-default-border', 'button-default-hover-border',
                ],
            ],
        ];

        $output = '<ul class="uk-tab" uk-tab>';
        $isFirst = true;
        foreach ($categories as $catKey => $category) {
            $activeClass = $isFirst ? ' class="uk-active"' : '';
            $output .= '<li' . $activeClass . '><a href="#">' . $category['label'] . '</a></li>';
            $isFirst = false;
        }
        $output .= '</ul>';

        $output .= '<ul class="uk-switcher uk-margin">';

        foreach ($categories as $catKey => $category) {
            $output .= '<li>';

            foreach ($category['fields'] as $fieldKey) {
                if (!isset($fields[$fieldKey])) {
                    continue;
                }

                $field = $fields[$fieldKey];
                $value = $values[$fieldKey] ?? $field['default'];

                switch ($field['type']) {
                    case 'select':
                        $input = $this->renderSelectInput($fieldKey, $field['options'], $value);
                        break;
                    case 'text':
                    default:
                        $attrs = [];
                        if (isset($field['placeholder'])) {
                            $attrs['placeholder'] = $field['placeholder'];
                        }
                        $input = $this->renderTextInput($fieldKey, $value, $attrs);
                        break;
                }

                $output .= $this->renderFormRow($field['label'], $input);
            }

            $output .= '</li>';
        }

        $output .= '</ul>';

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
            if (isset($formData[$key]) && '' !== $formData[$key]) {
                $processed[$key] = $formData[$key];
            } else {
                $processed[$key] = $defaults[$key] ?? '';
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
            if ('' !== $value && null !== $value) {
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
