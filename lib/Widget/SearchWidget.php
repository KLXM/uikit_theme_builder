<?php

namespace UikitThemeBuilder\Widget;

/**
 * Search Widget - Einstellungen fuer Suchfelder
 */
class SearchWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Search';
    }

    public function getKey(): string
    {
        return 'search';
    }

    public function getDescription(): string
    {
        return 'Einstellungen fuer Suchfelder in Default-, Navbar- und Medium-Variante.';
    }

    public function getDefaultValues(): array
    {
        return [
            'search-default-background' => '@global-background',
            'search-default-border' => '@global-border',
            'search-default-border-width' => '@global-border-width',
            'search-default-focus-border' => '@global-primary-background',
            'search-medium-background' => '@global-background',
            'search-medium-border' => '@global-border',
            'search-medium-border-width' => '@global-border-width',
            'search-medium-focus-border' => '@global-primary-background',
            'search-navbar-border' => '@global-border',
            'search-navbar-border-width' => '@global-border-width',
            'search-navbar-focus-background' => '@global-background',
            'search-navbar-focus-border' => '@global-primary-background',
        ];
    }

    public function getFields(): array
    {
        $colorOptions = $this->getColorOptions();

        return [
            'search-default-background' => [
                'label' => 'Default Suche Hintergrund',
                'type' => 'select',
                'default' => '@global-background',
                'options' => $colorOptions,
                'help' => 'Hintergrund von normalen Suchfeldern.'
            ],
            'search-default-border' => [
                'label' => 'Default Suche Rahmenfarbe',
                'type' => 'select',
                'default' => '@global-border',
                'options' => $colorOptions,
                'help' => 'Rahmenfarbe fuer normale Suchfelder.'
            ],
            'search-default-border-width' => [
                'label' => 'Default Suche Rahmenstaerke',
                'type' => 'text',
                'default' => '@global-border-width',
                'placeholder' => 'z.B. 1px',
                'help' => 'Dicke des Rahmens bei normaler Suche.'
            ],
            'search-default-focus-border' => [
                'label' => 'Default Suche Fokus-Rahmen',
                'type' => 'select',
                'default' => '@global-primary-background',
                'options' => $colorOptions,
                'help' => 'Rahmenfarbe beim Fokus.'
            ],
            'search-medium-background' => [
                'label' => 'Medium Suche Hintergrund',
                'type' => 'select',
                'default' => '@global-background',
                'options' => $colorOptions,
                'help' => 'Hintergrund fuer mittelgrosse Suche.'
            ],
            'search-medium-border' => [
                'label' => 'Medium Suche Rahmenfarbe',
                'type' => 'select',
                'default' => '@global-border',
                'options' => $colorOptions,
                'help' => 'Rahmenfarbe fuer mittelgrosse Suche.'
            ],
            'search-medium-border-width' => [
                'label' => 'Medium Suche Rahmenstaerke',
                'type' => 'text',
                'default' => '@global-border-width',
                'placeholder' => 'z.B. 1px',
                'help' => 'Dicke des Rahmens bei mittelgrosser Suche.'
            ],
            'search-medium-focus-border' => [
                'label' => 'Medium Suche Fokus-Rahmen',
                'type' => 'select',
                'default' => '@global-primary-background',
                'options' => $colorOptions,
                'help' => 'Rahmenfarbe beim Fokus.'
            ],
            'search-navbar-border' => [
                'label' => 'Navbar Suche Rahmenfarbe',
                'type' => 'select',
                'default' => '@global-border',
                'options' => $colorOptions,
                'help' => 'Rahmenfarbe fuer Suchfeld in der Navigation.'
            ],
            'search-navbar-border-width' => [
                'label' => 'Navbar Suche Rahmenstaerke',
                'type' => 'text',
                'default' => '@global-border-width',
                'placeholder' => 'z.B. 1px',
                'help' => 'Dicke des Rahmens in der Navigation.'
            ],
            'search-navbar-focus-background' => [
                'label' => 'Navbar Suche Fokus-Hintergrund',
                'type' => 'select',
                'default' => '@global-background',
                'options' => $colorOptions,
                'help' => 'Hintergrundfarbe beim Fokus in der Navigation.'
            ],
            'search-navbar-focus-border' => [
                'label' => 'Navbar Suche Fokus-Rahmen',
                'type' => 'select',
                'default' => '@global-primary-background',
                'options' => $colorOptions,
                'help' => 'Rahmenfarbe beim Fokus in der Navigation.'
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
