<?php

namespace UikitThemeBuilder\Widget;

/**
 * Card Widget - Einstellungen fuer Card-Darstellung
 */
class CardWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Cards';
    }

    public function getKey(): string
    {
        return 'card';
    }

    public function getDescription(): string
    {
        return 'Einstellungen fuer Karten, Header/Footer und Hover-Effekte.';
    }

    public function getDefaultValues(): array
    {
        return [
            'card-default-background' => '@global-background',
            'card-default-box-shadow' => '@global-medium-box-shadow',
            'card-default-header-border' => '@global-border',
            'card-default-header-border-width' => '@global-border-width',
            'card-default-footer-border' => '@global-border',
            'card-default-footer-border-width' => '@global-border-width',
            'card-default-hover-background' => '@global-background',
            'card-default-hover-box-shadow' => '@global-large-box-shadow',
            'card-primary-hover-background' => '@global-primary-background',
            'card-secondary-hover-background' => '@global-secondary-background',
            'card-badge-border-radius' => '500px',
            'card-badge-text-transform' => 'uppercase',
        ];
    }

    public function getFields(): array
    {
        $colorOptions = $this->getColorOptions();

        return [
            'card-default-background' => [
                'label' => 'Card Hintergrund',
                'type' => 'select',
                'default' => '@global-background',
                'options' => $colorOptions,
                'help' => 'Grundhintergrund fuer Standard-Cards.'
            ],
            'card-default-box-shadow' => [
                'label' => 'Card Schatten',
                'type' => 'text',
                'default' => '@global-medium-box-shadow',
                'placeholder' => 'z.B. @global-medium-box-shadow',
                'help' => 'Schatten im Normalzustand.'
            ],
            'card-default-header-border' => [
                'label' => 'Header Rahmenfarbe',
                'type' => 'select',
                'default' => '@global-border',
                'options' => $colorOptions,
                'help' => 'Trennlinie zwischen Header und Inhalt.'
            ],
            'card-default-header-border-width' => [
                'label' => 'Header Rahmenstaerke',
                'type' => 'text',
                'default' => '@global-border-width',
                'placeholder' => 'z.B. 1px',
                'help' => 'Dicke der Header-Trennlinie.'
            ],
            'card-default-footer-border' => [
                'label' => 'Footer Rahmenfarbe',
                'type' => 'select',
                'default' => '@global-border',
                'options' => $colorOptions,
                'help' => 'Trennlinie zwischen Inhalt und Footer.'
            ],
            'card-default-footer-border-width' => [
                'label' => 'Footer Rahmenstaerke',
                'type' => 'text',
                'default' => '@global-border-width',
                'placeholder' => 'z.B. 1px',
                'help' => 'Dicke der Footer-Trennlinie.'
            ],
            'card-default-hover-background' => [
                'label' => 'Hover Hintergrund (Standard Card)',
                'type' => 'select',
                'default' => '@global-background',
                'options' => $colorOptions,
                'help' => 'Hintergrund, wenn die Karte gehovert wird.'
            ],
            'card-default-hover-box-shadow' => [
                'label' => 'Hover Schatten (Standard Card)',
                'type' => 'text',
                'default' => '@global-large-box-shadow',
                'placeholder' => 'z.B. @global-large-box-shadow',
                'help' => 'Schatten beim Hover.'
            ],
            'card-primary-hover-background' => [
                'label' => 'Hover Hintergrund (Primary Card)',
                'type' => 'select',
                'default' => '@global-primary-background',
                'options' => $colorOptions,
                'help' => 'Hover-Hintergrund fuer Primary-Karten.'
            ],
            'card-secondary-hover-background' => [
                'label' => 'Hover Hintergrund (Secondary Card)',
                'type' => 'select',
                'default' => '@global-secondary-background',
                'options' => $colorOptions,
                'help' => 'Hover-Hintergrund fuer Secondary-Karten.'
            ],
            'card-badge-border-radius' => [
                'label' => 'Badge Rundung',
                'type' => 'text',
                'default' => '500px',
                'placeholder' => 'z.B. 500px',
                'help' => 'Macht Badges eher rund oder eckig.'
            ],
            'card-badge-text-transform' => [
                'label' => 'Badge Textstil',
                'type' => 'select',
                'default' => 'uppercase',
                'options' => [
                    'uppercase' => 'Grossbuchstaben',
                    'none' => 'Normal',
                    'lowercase' => 'Kleinbuchstaben',
                    'capitalize' => 'Wortanfang gross',
                ],
                'help' => 'Textdarstellung fuer Badges.'
            ],
        ];
    }

    public function renderForm(array $values = []): string
    {
        $output = '';

        foreach ($this->getFields() as $key => $field) {
            $value = $values[$key] ?? $field['default'];

            if ($field['type'] === 'select') {
                $input = $this->renderSelectInput($key, $field['options'], (string) $value);
            } else {
                $attrs = [];
                if (isset($field['placeholder'])) {
                    $attrs['placeholder'] = $field['placeholder'];
                }
                $input = $this->renderTextInput($key, (string) $value, $attrs);
            }

            $output .= $this->renderFormRow($field['label'], $input, $field['help'] ?? '');
        }

        return $output;
    }

    public function processFormData(array $formData): array
    {
        $processed = [];
        $defaults = $this->getDefaultValues();

        foreach ($this->getFields() as $key => $field) {
            if (array_key_exists($key, $formData) && '' !== (string) $formData[$key]) {
                $processed[$key] = trim((string) $formData[$key]);
            } else {
                $processed[$key] = $defaults[$key] ?? $field['default'];
            }
        }

        return $processed;
    }

    public function validateFormData(array $data): array
    {
        return [];
    }

    public function generateLessVariables(array $data): array
    {
        $variables = [];

        foreach ($data as $key => $value) {
            if ($value !== '' && $value !== null) {
                $variables[$key] = $value;
            }
        }

        return $variables;
    }

    private function getColorOptions(): array
    {
        return [
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
            '@global-inverse-color' => 'Inverse',
            '@global-border' => 'Border',
            '#ffffff' => 'Weiss',
            '#000000' => 'Schwarz',
        ];
    }
}
