<?php

namespace UikitThemeBuilder\Widget;

/**
 * Form Widget - Einstellungen fuer Formulareingaben
 */
class FormWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Forms';
    }

    public function getKey(): string
    {
        return 'form';
    }

    public function getDescription(): string
    {
        return 'Einstellungen fuer Input-Felder, Labels und Radiobuttons.';
    }

    public function getDefaultValues(): array
    {
        return [
            'form-background' => '@global-background',
            'form-focus-background' => '@global-background',
            'form-border-width' => '@global-border-width',
            'form-border' => '@global-border',
            'form-focus-border' => '@global-primary-background',
            'form-disabled-border' => '@global-border',
            'form-danger-border' => '@global-danger-background',
            'form-success-border' => '@global-success-background',
            'form-label-color' => '@global-emphasis-color',
            'form-label-font-size' => '@global-small-font-size',
            'form-radio-border' => 'darken(@global-border, 10%)',
            'form-radio-focus-border' => '@global-primary-background',
            'form-stacked-margin-bottom' => '5px',
        ];
    }

    public function getFields(): array
    {
        $colorOptions = $this->getColorOptions();

        return [
            'form-background' => [
                'label' => 'Eingabefeld Hintergrund',
                'type' => 'select',
                'default' => '@global-background',
                'options' => $colorOptions,
                'help' => 'Grundfarbe von Input, Select und Textarea.'
            ],
            'form-focus-background' => [
                'label' => 'Hintergrund bei Fokus',
                'type' => 'select',
                'default' => '@global-background',
                'options' => $colorOptions,
                'help' => 'Hintergrundfarbe, wenn das Feld aktiv ist.'
            ],
            'form-border-width' => [
                'label' => 'Rahmenstaerke',
                'type' => 'text',
                'default' => '@global-border-width',
                'placeholder' => 'z.B. 1px oder @global-border-width',
                'help' => 'Dicke des Feldrahmens.'
            ],
            'form-border' => [
                'label' => 'Rahmenfarbe',
                'type' => 'select',
                'default' => '@global-border',
                'options' => $colorOptions,
                'help' => 'Standardfarbe des Feldrahmens.'
            ],
            'form-focus-border' => [
                'label' => 'Rahmenfarbe bei Fokus',
                'type' => 'select',
                'default' => '@global-primary-background',
                'options' => $colorOptions,
                'help' => 'Markierungsfarbe bei Klick in ein Feld.'
            ],
            'form-disabled-border' => [
                'label' => 'Rahmenfarbe deaktivierter Felder',
                'type' => 'select',
                'default' => '@global-border',
                'options' => $colorOptions,
                'help' => 'Wird fuer disabled Felder verwendet.'
            ],
            'form-danger-border' => [
                'label' => 'Rahmenfarbe Fehlerzustand',
                'type' => 'select',
                'default' => '@global-danger-background',
                'options' => $colorOptions,
                'help' => 'Wird fuer fehlerhafte Felder genutzt.'
            ],
            'form-success-border' => [
                'label' => 'Rahmenfarbe Erfolgszustand',
                'type' => 'select',
                'default' => '@global-success-background',
                'options' => $colorOptions,
                'help' => 'Wird fuer erfolgreich validierte Felder genutzt.'
            ],
            'form-label-color' => [
                'label' => 'Label-Farbe',
                'type' => 'select',
                'default' => '@global-emphasis-color',
                'options' => $colorOptions,
                'help' => 'Textfarbe von Formular-Labels.'
            ],
            'form-label-font-size' => [
                'label' => 'Label-Schriftgroesse',
                'type' => 'text',
                'default' => '@global-small-font-size',
                'placeholder' => 'z.B. 14px oder @global-small-font-size',
                'help' => 'Groesse der Label-Beschriftung.'
            ],
            'form-radio-border' => [
                'label' => 'Radiobutton Rahmenfarbe',
                'type' => 'text',
                'default' => 'darken(@global-border, 10%)',
                'placeholder' => 'z.B. @global-border oder darken(...)',
                'help' => 'Rahmenfarbe fuer Checkbox/Radio.'
            ],
            'form-radio-focus-border' => [
                'label' => 'Radiobutton Fokusfarbe',
                'type' => 'select',
                'default' => '@global-primary-background',
                'options' => $colorOptions,
                'help' => 'Farbe bei Fokus auf Checkbox/Radio.'
            ],
            'form-stacked-margin-bottom' => [
                'label' => 'Abstand zwischen Feldern',
                'type' => 'text',
                'default' => '5px',
                'placeholder' => 'z.B. 5px',
                'help' => 'Vertikaler Abstand in gestapelten Formularen.'
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
