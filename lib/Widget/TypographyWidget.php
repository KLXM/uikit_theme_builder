<?php

namespace UikitThemeBuilder\Widget;

/**
 * Typography Widget - Schriftarten und Text-Styling
 */
class TypographyWidget extends AbstractWidget
{
    private \UikitThemeBuilder\GoogleFontsManager $fontManager;
    
    public function __construct()
    {
        $this->fontManager = new \UikitThemeBuilder\GoogleFontsManager();
    }
    
    public function getName(): string
    {
        return 'Typography';
    }

    public function getKey(): string
    {
        return 'typography';
    }

    public function getDescription(): string
    {
        return 'Typografie-Einstellungen für Schriftarten, Größen und Zeilenhöhen';
    }

    public function getDefaultValues(): array
    {
        return [
            'global-font-family' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
            'global-font-weight' => 'normal',
            'global-font-size' => '16px',
            'global-line-height' => '1.5',
            'global-2xlarge-font-size' => '2.625rem',
            'global-xlarge-font-size' => '2rem',
            'global-large-font-size' => '1.5rem',
            'global-medium-font-size' => '1.25rem',
            'global-small-font-size' => '0.875rem',
            // Text Utilities
            'text-lead-font-size' => '@global-large-font-size',
            'text-lead-color' => '@global-emphasis-color',
            'text-meta-font-size' => '@global-small-font-size',
            'text-small-font-size' => '@global-small-font-size',
            'text-large-font-size' => '@global-large-font-size',
            'dropcap-font-size' => '((@global-line-height * 3) * 1em)',
            'dropcap-margin-right' => '10px',
            'dropcap-color' => 'inherit',
            // Headings
            'base-heading-font-family' => 'inherit',
            'base-heading-font-weight' => 'normal',
            'base-heading-color' => 'inherit',
            'base-h1-color' => 'inherit',
            'base-h2-color' => 'inherit',
            'base-h3-color' => 'inherit',
            'base-h4-color' => 'inherit',
            'base-h5-color' => 'inherit',
            'base-h6-color' => 'inherit',
            'base-h1-font-size-m' => '2.625rem', // @global-2xlarge-font-size
            'base-h2-font-size-m' => '2rem',     // @global-xlarge-font-size
            'base-h3-font-size' => '1.5rem',
            'base-h4-font-size' => '1.25rem',
            'base-h5-font-size' => '16px',
            'base-h6-font-size' => '0.875rem',
            // UIkit Heading Sizes (Hero)
            'heading-small-font-size-m' => '3.25rem',
            'heading-medium-font-size-l' => '4rem',
            'heading-large-font-size-l' => '6rem',
            'heading-xlarge-font-size-l' => '8rem',
            'heading-2xlarge-font-size-l' => '11rem',
            // Heading Variants
            'heading-divider-border' => '@global-border',
            'heading-divider-border-width' => '~"calc(0.2px + 0.05em)"',
            'heading-bullet-border' => '@global-border',
            'heading-bullet-border-width' => '~"calc(5px + 0.1em)"',
            'heading-line-border' => '@global-border',
            'heading-line-border-width' => '~"calc(0.2px + 0.05em)"',
            // Blockquote
            'base-blockquote-font-size' => '@global-medium-font-size',
            'base-blockquote-font-style' => 'italic',
            'base-blockquote-font-family' => 'inherit',
            'base-blockquote-color' => 'inherit',
            // Divider
            'divider-icon-color' => '@global-border',
            // Font Fallbacks (nicht als LESS-Variablen generiert, nur für interne Verwendung)
            'font-fallback-sans-serif' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
            'font-fallback-serif' => '"Times New Roman", Times, serif',
            'font-fallback-monospace' => '"SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, "Courier New", monospace',
            'font-fallback-cursive' => 'cursive'
        ];
    }

    public function getFields(): array
    {
        // Verfügbare Fonts dynamisch abrufen
        $availableFonts = $this->fontManager->getAllAvailableFonts();
        
        // Font-Optionen dynamisch erstellen
        $fontOptions = [
            '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif' => 'System Font Stack (empfohlen)',
            'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' => 'Modern System UI'
        ];
        
        // Gruppierung nach Quelle
        $groupedFonts = [];
        foreach ($availableFonts as $font) {
            $source = $font['source'];
            if (!isset($groupedFonts[$source])) {
                $groupedFonts[$source] = [];
            }
            $groupedFonts[$source][] = $font;
        }
        
        // Google Fonts hinzufügen mit konfigurierbaren Fallbacks
        if (isset($groupedFonts['google'])) {
            foreach ($groupedFonts['google'] as $font) {
                $fontFamily = $font['family'];
                $category = $font['category'];
                
                // Fallback basierend auf Kategorie bestimmen
                $fallbackKey = match($category) {
                    'serif' => 'font-fallback-serif',
                    'sans-serif' => 'font-fallback-sans-serif', 
                    'monospace' => 'font-fallback-monospace',
                    'cursive' => 'font-fallback-cursive',
                    default => 'font-fallback-sans-serif'
                };
                
                // Standard-Fallback falls Konfiguration nicht verfügbar
                $defaultFallback = match($category) {
                    'serif' => ', "Times New Roman", Times, serif',
                    'sans-serif' => ', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                    'monospace' => ', "SF Mono", Monaco, Consolas, monospace', 
                    'cursive' => ', cursive',
                    default => ', sans-serif'
                };
                
                $fontValue = '"' . $fontFamily . '"' . $defaultFallback;
                $fontOptions[$fontValue] = $fontFamily . ' (von Google)';
            }
        }
        
        // System Fonts hinzufügen
        if (isset($groupedFonts['system'])) {
            foreach ($groupedFonts['system'] as $font) {
                $fontFamily = $font['family'];
                $fallback = $font['category'] === 'serif' ? ', serif' : ', sans-serif';
                $fontValue = $fontFamily . $fallback;
                $fontOptions[$fontValue] = $fontFamily . ' (System)';
            }
        }
        
        // Zusätzliche beliebte Font-Stacks
        $fontOptions = array_merge($fontOptions, [
            // Serif Stacks  
            'Georgia, "Times New Roman", Times, serif' => 'Georgia Stack',
            '"Times New Roman", Times, serif' => 'Times Stack',
            
            // Monospace
            '"SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, monospace' => 'Monospace Stack'
        ]);

        // Farboptionen für Headlines (UIKit Standardfarben + inherit)
        $colorOptions = $this->getColorOptions();

        return [
            // Global Typography
            'global-font-family' => [
                'label' => 'Hauptschrift',
                'type' => 'select',
                'default' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                'options' => $fontOptions
            ],
            'global-font-weight' => [
                'label' => 'Standard-Schriftstärke',
                'type' => 'select',
                'default' => 'normal',
                'options' => [
                    '300' => 'Light (300)',
                    'normal' => 'Normal (400)',
                    '500' => 'Medium (500)',
                    '600' => 'Semi-Bold (600)',
                    'bold' => 'Bold (700)'
                ]
            ],
            'global-font-size' => [
                'label' => 'Basis-Schriftgröße',
                'type' => 'text',
                'default' => '16px',
                'placeholder' => 'z.B. 16px, 1rem'
            ],
            'global-line-height' => [
                'label' => 'Zeilenhöhe',
                'type' => 'text',
                'default' => '1.5',
                'placeholder' => 'z.B. 1.5, 1.2'
            ],
            
            // Headings
            'base-heading-font-family' => [
                'label' => 'Überschriften-Schrift',
                'type' => 'select',
                'default' => 'inherit',
                'options' => array_merge([
                    'inherit' => 'Von Hauptschrift erben'
                ], $fontOptions)
            ],
            'base-heading-font-weight' => [
                'label' => 'Überschriften-Gewicht',
                'type' => 'select',
                'default' => 'normal',
                'options' => [
                    '300' => 'Light (300)',
                    'normal' => 'Normal (400)',
                    '500' => 'Medium (500)',
                    '600' => 'Semi-Bold (600)',
                    'bold' => 'Bold (700)'
                ]
            ],
            'base-heading-color' => [
                'label' => 'Überschriften-Farbe (Standard)',
                'type' => 'select',
                'default' => 'inherit',
                'options' => $colorOptions
            ],
            'base-h1-color' => [
                'label' => 'H1-Farbe',
                'type' => 'select',
                'default' => 'inherit',
                'options' => $colorOptions
            ],
            'base-h2-color' => [
                'label' => 'H2-Farbe',
                'type' => 'select',
                'default' => 'inherit',
                'options' => $colorOptions
            ],
            'base-h3-color' => [
                'label' => 'H3-Farbe',
                'type' => 'select',
                'default' => 'inherit',
                'options' => $colorOptions
            ],
            'base-h4-color' => [
                'label' => 'H4-Farbe',
                'type' => 'select',
                'default' => 'inherit',
                'options' => $colorOptions
            ],
            'base-h5-color' => [
                'label' => 'H5-Farbe',
                'type' => 'select',
                'default' => 'inherit',
                'options' => $colorOptions
            ],
            'base-h6-color' => [
                'label' => 'H6-Farbe',
                'type' => 'select',
                'default' => 'inherit',
                'options' => $colorOptions
            ],
            'base-heading-text-transform' => [
                'label' => 'Überschriften-Transformation',
                'type' => 'select',
                'default' => 'none',
                'options' => [
                    'none' => 'Normal',
                    'uppercase' => 'GROSSBUCHSTABEN',
                    'lowercase' => 'kleinbuchstaben',
                    'capitalize' => 'Erster Buchstabe Groß'
                ]
            ],
            
            // Base Heading Sizes
            'base-h1-font-size-m' => [
                'label' => 'H1 Größe',
                'help' => 'Standardgröße ab Tablet. Mobile wird automatisch skaliert (0.85x).',
                'type' => 'text',
                'default' => '2.625rem',
                'placeholder' => '2.625rem oder px'
            ],
            'base-h2-font-size-m' => [
                'label' => 'H2 Größe',
                'help' => 'Standardgröße ab Tablet. Mobile wird automatisch skaliert (0.85x).',
                'type' => 'text',
                'default' => '2rem',
                'placeholder' => '2rem oder px'
            ],
            'base-h3-font-size' => [
                'label' => 'H3 Größe',
                'type' => 'text',
                'default' => '1.5rem',
                'placeholder' => '1.5rem oder px'
            ],
            'base-h4-font-size' => [
                'label' => 'H4 Größe',
                'type' => 'text',
                'default' => '1.25rem',
                'placeholder' => '1.25rem oder px'
            ],
            'base-h5-font-size' => [
                'label' => 'H5 Größe',
                'type' => 'text',
                'default' => '16px',
                'placeholder' => '16px'
            ],
            'base-h6-font-size' => [
                'label' => 'H6 Größe',
                'type' => 'text',
                'default' => '0.875rem',
                'placeholder' => '0.875rem'
            ],
            
            // UIkit Heading Sizes
            'heading-small-font-size-m' => [
                'label' => 'Heading Small (.uk-heading-small)',
                'help' => 'Größe ab Tablet. Mobile wird skaliert.',
                'type' => 'text',
                'default' => '3.25rem',
                'placeholder' => '3.25rem'
            ],
            'heading-medium-font-size-l' => [
                'label' => 'Heading Medium (.uk-heading-medium)',
                'help' => 'Größe ab Desktop. Tablet/Mobile werden skaliert.',
                'type' => 'text',
                'default' => '4rem',
                'placeholder' => '4rem'
            ],
            'heading-large-font-size-l' => [
                'label' => 'Heading Large (.uk-heading-large)',
                'help' => 'Größe ab Desktop. Tablet/Mobile werden skaliert.',
                'type' => 'text',
                'default' => '6rem',
                'placeholder' => '6rem'
            ],
            'heading-xlarge-font-size-l' => [
                'label' => 'Heading XLarge (.uk-heading-xlarge)',
                'help' => 'Größe ab Desktop. Tablet/Mobile werden skaliert.',
                'type' => 'text',
                'default' => '8rem',
                'placeholder' => '8rem'
            ],
            'heading-2xlarge-font-size-l' => [
                'label' => 'Heading 2XLarge (.uk-heading-2xlarge)',
                'help' => 'Größe ab Desktop. Tablet/Mobile werden skaliert.',
                'type' => 'text',
                'default' => '11rem',
                'placeholder' => '11rem'
            ],
            
            // Font Sizes
            'global-small-font-size' => [
                'label' => 'Kleine Schrift',
                'type' => 'text',
                'default' => '0.875rem',
                'placeholder' => '0.875rem (14px)'
            ],
            'global-medium-font-size' => [
                'label' => 'Mittlere Schrift',
                'type' => 'text',
                'default' => '1.25rem',
                'placeholder' => '1.25rem (20px)'
            ],
            'global-large-font-size' => [
                'label' => 'Große Schrift',
                'type' => 'text',
                'default' => '1.5rem',
                'placeholder' => '1.5rem (24px)'
            ],
            'global-xlarge-font-size' => [
                'label' => 'Extra große Schrift',
                'type' => 'text',
                'default' => '2rem',
                'placeholder' => '2rem (32px)'
            ],
            'global-2xlarge-font-size' => [
                'label' => '2x große Schrift',
                'type' => 'text',
                'default' => '2.625rem',
                'placeholder' => '2.625rem (42px)'
            ],
            
            // Text Utility Classes
            'text-lead-font-size' => [
                'label' => 'Lead Text Größe',
                'type' => 'text',
                'default' => '@global-large-font-size',
                'placeholder' => '@global-large-font-size oder 1.5rem'
            ],
            'text-lead-color' => [
                'label' => 'Lead Text Farbe',
                'type' => 'select',
                'default' => '@global-emphasis-color',
                'options' => array_merge(
                    ['@global-emphasis-color' => 'Emphasis (Standard)', '@global-color' => 'Text', '@global-muted-color' => 'Muted'],
                    $colorOptions
                )
            ],
            'text-meta-font-size' => [
                'label' => 'Meta Text Größe',
                'type' => 'text',
                'default' => '@global-small-font-size',
                'placeholder' => '@global-small-font-size oder 0.875rem'
            ],
            'text-small-font-size' => [
                'label' => 'Small Text Größe',
                'type' => 'text',
                'default' => '@global-small-font-size',
                'placeholder' => '@global-small-font-size oder 0.875rem'
            ],
            'text-large-font-size' => [
                'label' => 'Large Text Größe',
                'type' => 'text',
                'default' => '@global-large-font-size',
                'placeholder' => '@global-large-font-size oder 1.5rem'
            ],
            'dropcap-font-size' => [
                'label' => 'Dropcap Größe',
                'type' => 'text',
                'default' => '((@global-line-height * 3) * 1em)',
                'placeholder' => '4.5em oder ((@global-line-height * 3) * 1em)'
            ],
            'dropcap-margin-right' => [
                'label' => 'Dropcap Abstand',
                'type' => 'text',
                'default' => '10px',
                'placeholder' => '10px'
            ],
            'dropcap-color' => [
                'label' => 'Dropcap Farbe',
                'type' => 'select',
                'default' => 'inherit',
                'options' => $colorOptions
            ],
            
            // Heading Variants
            'heading-divider-border' => [
                'label' => 'Heading Divider - Farbe',
                'type' => 'select',
                'default' => '@global-border',
                'options' => array_merge(
                    ['@global-border' => 'Global Border (Standard)'],
                    $colorOptions
                )
            ],
            'heading-divider-border-width' => [
                'label' => 'Heading Divider - Breite',
                'type' => 'text',
                'default' => '~"calc(0.2px + 0.05em)"',
                'placeholder' => '~"calc(0.2px + 0.05em)" oder 1px'
            ],
            'heading-bullet-border' => [
                'label' => 'Heading Bullet - Farbe',
                'type' => 'select',
                'default' => '@global-border',
                'options' => array_merge(
                    ['@global-border' => 'Global Border (Standard)'],
                    $colorOptions
                )
            ],
            'heading-bullet-border-width' => [
                'label' => 'Heading Bullet - Breite',
                'type' => 'text',
                'default' => '~"calc(5px + 0.1em)"',
                'placeholder' => '~"calc(5px + 0.1em)" oder 5px'
            ],
            'heading-line-border' => [
                'label' => 'Heading Line - Farbe',
                'type' => 'select',
                'default' => '@global-border',
                'options' => array_merge(
                    ['@global-border' => 'Global Border (Standard)'],
                    $colorOptions
                )
            ],
            'heading-line-border-width' => [
                'label' => 'Heading Line - Breite',
                'type' => 'text',
                'default' => '~"calc(0.2px + 0.05em)"',
                'placeholder' => '~"calc(0.2px + 0.05em)" oder 1px'
            ],
            
            // Blockquote
            'base-blockquote-font-size' => [
                'label' => 'Blockquote Schriftgröße',
                'type' => 'text',
                'default' => '@global-medium-font-size',
                'placeholder' => '@global-medium-font-size oder 1.25rem'
            ],
            'base-blockquote-font-style' => [
                'label' => 'Blockquote Schriftstil',
                'type' => 'select',
                'default' => 'italic',
                'options' => [
                    'normal' => 'Normal',
                    'italic' => 'Kursiv'
                ]
            ],
            'base-blockquote-font-family' => [
                'label' => 'Blockquote Schriftart',
                'type' => 'select',
                'default' => 'inherit',
                'options' => array_merge([
                    'inherit' => 'Von Hauptschrift erben'
                ], $fontOptions)
            ],
            'base-blockquote-color' => [
                'label' => 'Blockquote Farbe',
                'type' => 'select',
                'default' => 'inherit',
                'options' => array_merge([
                    'inherit' => 'Von Text-Farbe erben'
                ], $colorOptions)
            ],
            
            // Divider Icon
            'divider-icon-color' => [
                'label' => 'Divider Icon Farbe',
                'type' => 'select',
                'default' => '@global-border',
                'options' => array_merge(
                    ['@global-border' => 'Global Border (Standard)'],
                    $colorOptions
                )
            ],
            
            // Fallback-Konfiguration (nicht als LESS-Variablen, nur intern)
            'font-fallback-sans-serif' => [
                'label' => 'Sans-Serif Fallback',
                'type' => 'select',
                'default' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                'options' => [
                    '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif' => 'Standard System Stack',
                    'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' => 'Modern System UI',
                    '"Helvetica Neue", Helvetica, Arial, sans-serif' => 'Helvetica Stack',
                    '"Arial", "Helvetica Neue", Helvetica, sans-serif' => 'Arial Stack',
                    '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif' => 'Windows Stack',
                    '"Ubuntu", "Liberation Sans", "DejaVu Sans", sans-serif' => 'Linux Stack',
                    'sans-serif' => 'Nur Browser-Standard'
                ]
            ],
            'font-fallback-serif' => [
                'label' => 'Serif Fallback',
                'type' => 'select', 
                'default' => '"Times New Roman", Times, serif',
                'options' => [
                    '"Times New Roman", Times, serif' => 'Times Stack',
                    'Georgia, "Times New Roman", Times, serif' => 'Georgia Stack',
                    '"Book Antiqua", Palatino, "Palatino Linotype", serif' => 'Palatino Stack',
                    '"Baskerville", "Libre Baskerville", serif' => 'Baskerville Stack',
                    'serif' => 'Nur Browser-Standard'
                ]
            ],
            'font-fallback-monospace' => [
                'label' => 'Monospace Fallback',
                'type' => 'select',
                'default' => '"SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, "Courier New", monospace',
                'options' => [
                    '"SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, "Courier New", monospace' => 'Modern Monospace Stack',
                    '"Consolas", "Monaco", "Lucida Console", monospace' => 'Classic Stack',
                    '"Courier New", Courier, monospace' => 'Courier Stack',
                    'monospace' => 'Nur Browser-Standard'
                ]
            ],
            'font-fallback-cursive' => [
                'label' => 'Cursive Fallback',
                'type' => 'select',
                'default' => 'cursive',
                'options' => [
                    '"Apple Chancery", "Bradley Hand", "Brush Script MT", cursive' => 'Handschrift Stack',
                    '"Brush Script MT", "Lucida Handwriting", cursive' => 'Script Stack',
                    'cursive' => 'Nur Browser-Standard'
                ]
            ]
        ];
    }

    public function renderForm(array $values = []): string
    {
        $fields = $this->getFields();
        
        // Felder in Kategorien gruppieren
        $categories = [
            'basics' => [
                'label' => 'Basis-Einstellungen',
                'fields' => ['global-font-family', 'global-font-weight', 'global-font-size', 'global-line-height']
            ],
            'sizes' => [
                'label' => 'Schriftgrößen',
                'fields' => [
                    'global-small-font-size', 'global-medium-font-size', 'global-large-font-size', 
                    'global-xlarge-font-size', 'global-2xlarge-font-size',
                    'heading-small-font-size-m', 'heading-medium-font-size-l', 'heading-large-font-size-l', 
                    'heading-xlarge-font-size-l', 'heading-2xlarge-font-size-l'
                ]
            ],
            'headings' => [
                'label' => 'Überschriften',
                'fields' => [
                    'base-heading-font-family', 'base-heading-font-weight', 'base-heading-text-transform',
                    'base-heading-color', 
                    'base-h1-font-size-m', 'base-h1-color', 
                    'base-h2-font-size-m', 'base-h2-color', 
                    'base-h3-font-size', 'base-h3-color', 
                    'base-h4-font-size', 'base-h4-color', 
                    'base-h5-font-size', 'base-h5-color', 
                    'base-h6-font-size', 'base-h6-color'
                ]
            ],
            'heading-variants' => [
                'label' => 'Heading-Stile',
                'fields' => [
                    'heading-divider-border', 'heading-divider-border-width',
                    'heading-bullet-border', 'heading-bullet-border-width',
                    'heading-line-border', 'heading-line-border-width'
                ]
            ],
            'blockquote' => [
                'label' => 'Blockquote',
                'fields' => [
                    'base-blockquote-font-size', 'base-blockquote-font-style',
                    'base-blockquote-font-family', 'base-blockquote-color'
                ]
            ],
            'text-utilities' => [
                'label' => 'Text-Klassen',
                'fields' => [
                    'text-lead-font-size', 'text-lead-color', 'text-meta-font-size',
                    'text-small-font-size', 'text-large-font-size',
                    'dropcap-font-size', 'dropcap-margin-right', 'dropcap-color'
                ]
            ],
            'divider' => [
                'label' => 'Divider',
                'fields' => [
                    'divider-icon-color'
                ]
            ],
            'fallbacks' => [
                'label' => 'Font-Fallbacks',
                'fields' => [
                    'font-fallback-sans-serif', 'font-fallback-serif', 
                    'font-fallback-monospace', 'font-fallback-cursive'
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
                        // Font-Familie Felder bekommen eine Preview unterhalb
                        $isFontField = str_contains($fieldKey, 'font-family');
                        $input = $this->renderSelectInput($fieldKey, $field['options'], $value);
                        
                        // Preview-Element hinzufügen für Font-Felder
                        if ($isFontField) {
                            $previewId = 'font-preview-' . str_replace(['-', '_'], '', $fieldKey);
                            $input .= '<div id="' . $previewId . '" class="uk-margin-small-top uk-padding-small uk-background-muted uk-border-rounded" style="min-height: 60px; display: flex; align-items: center; justify-content: center;">';
                            $input .= '<span class="font-preview-text" style="font-size: 18px; transition: font-family 0.3s ease;">The quick brown fox jumps over the lazy dog</span>';
                            $input .= '</div>';
                            $input .= '<script>';
                            $input .= '(function() {';
                            $input .= '  const select = document.querySelector(\'select[name="' . $fieldKey . '"]\');';
                            $input .= '  const preview = document.getElementById(\'' . $previewId . '\').querySelector(\'.font-preview-text\');';
                            $input .= '  const systemFonts = [';
                            $input .= '    \'Arial\', \'Verdana\', \'Helvetica\', \'Tahoma\', \'Trebuchet MS\',';
                            $input .= '    \'Times New Roman\', \'Times\', \'Georgia\', \'Garamond\',';
                            $input .= '    \'Courier New\', \'Courier\', \'Monaco\', \'Consolas\',';
                            $input .= '    \'system-ui\', \'BlinkMacSystemFont\', \'-apple-system\', \'Segoe UI\',';
                            $input .= '    \'Roboto\', \'Ubuntu\', \'Cantarell\', \'Helvetica Neue\',';
                            $input .= '    \'Palatino\', \'Bookman\', \'Comic Sans MS\', \'Arial Black\', \'Impact\',';
                            $input .= '    \'sans-serif\', \'serif\', \'monospace\', \'cursive\', \'fantasy\'';
                            $input .= '  ];';
                            $input .= '  function extractFirstFont(fontFamily) {';
                            $input .= '    if (!fontFamily) return \'\';';
                            $input .= '    const cleaned = fontFamily.replace(/[\'\"]/g, \'\').trim();';
                            $input .= '    return cleaned.split(\',\')[0].trim();';
                            $input .= '  }';
                            $input .= '  function isSystemFont(fontName) {';
                            $input .= '    return systemFonts.some(sf => sf.toLowerCase() === fontName.toLowerCase());';
                            $input .= '  }';
                            $input .= '  function updatePreview() {';
                            $input .= '    const fullFont = select.value;';
                            $input .= '    const firstFont = extractFirstFont(fullFont);';
                            $input .= '    if (firstFont && firstFont !== \'inherit\') {';
                            $input .= '      preview.style.fontFamily = fullFont;';
                            $input .= '      if (!isSystemFont(firstFont)) {';
                            // NIE live fonts.googleapis.com kontaktieren - nur lokal bereits
                            // heruntergeladene Fonts (siehe GoogleFontsManager). Fehlt die lokale
                            // Datei, bleibt es beim Fallback-Stack.
                            $input .= '        const link = document.createElement(\'link\');';
                            $input .= '        link.rel = \'stylesheet\';';
                            $input .= '        link.href = ' . json_encode(\rex_url::addonAssets('uikit_theme_builder', 'fonts/')) . ' + firstFont.replace(/[^a-zA-Z0-9_-]/g, \'_\') + \'.css\';';
                            $input .= '        document.head.appendChild(link);';
                            $input .= '      }';
                            $input .= '    } else {';
                            $input .= '      preview.style.fontFamily = \'\';';
                            $input .= '    }';
                            $input .= '  }';
                            $input .= '  if (select) {';
                            $input .= '    select.addEventListener(\'change\', updatePreview);';
                            $input .= '    updatePreview();';
                            $input .= '  }';
                            $input .= '})();';
                            $input .= '</script>';
                        }
                        break;
                    case 'icon':
                        $input = $this->renderIconPicker($fieldKey, $value);
                        break;
                    case 'text':
                    default:
                        $attrs = [];
                        if (isset($field['placeholder'])) $attrs['placeholder'] = $field['placeholder'];
                        $input = $this->renderTextInput($fieldKey, $value, $attrs);
                        break;
                }
                
                $label = $field['label'];
                $help = $field['help'] ?? '';
                
                $output .= $this->renderFormRow($label, $input, $help);
            }
            
            $output .= '</li>';
        }
        
        $output .= '</ul>';

        return $output;
    }

    public function processFormData(array $formData): array
    {
        $processed = [];
        $defaults = $this->getDefaultValues();
        
        foreach ($this->getFields() as $key => $field) {
            // Verwende den Wert aus dem Formular, oder den Default-Wert
            if (array_key_exists($key, $formData)) {
                // Wert ist im Formular vorhanden (kann auch leer sein)
                $value = $formData[$key];
                
                // Für Font-Size: px-Einheit hinzufügen wenn nur Zahl
                if ($key === 'global-font-size' && is_numeric($value)) {
                    $value = $value . 'px';
                }
                
                $processed[$key] = $value;
            } elseif (isset($defaults[$key])) {
                // Wenn nicht im Formular, nutze Default
                $processed[$key] = $defaults[$key];
            }
        }
        
        // Debugging: Font-Size prüfen
        if (isset($processed['global-font-size']) && !str_contains($processed['global-font-size'], 'px')) {
            $processed['global-font-size'] = $processed['global-font-size'] . 'px';
        }
        
        return $processed;
    }

    public function validateFormData(array $data): array
    {
        $errors = [];
        // Basic validation - kann erweitert werden
        return $errors;
    }

    public function generateLessVariables(array $data): array
    {
        $variables = [];
        
        // Fallback-Konfiguration extrahieren
        $fallbacks = [
            'sans-serif' => $data['font-fallback-sans-serif'] ?? '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
            'serif' => $data['font-fallback-serif'] ?? '"Times New Roman", Times, serif',
            'monospace' => $data['font-fallback-monospace'] ?? '"SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, "Courier New", monospace',
            'cursive' => $data['font-fallback-cursive'] ?? 'cursive'
        ];
        
        foreach ($data as $key => $value) {
            // Font-Fallback Variablen überspringen - diese sind nur für interne Verwendung
            if (str_starts_with($key, 'font-fallback-')) {
                continue;
            }
            
            // Leere Werte überspringen, ABER 'inherit' durchlassen
            if (empty($value) && $value !== 'inherit' && $value !== '0') {
                continue;
            }
            
            // Keys bereits mit Hyphens - direkt verwenden
            $lessVar = $key;
            
            // Font-Family spezielle Behandlung
            if (str_contains($key, 'font-family')) {
                // Wenn der Wert Leerzeichen enthält aber keine Anführungszeichen, müssen wir sie hinzufügen
                // Beispiel: "Times New Roman" muss zu '"Times New Roman"' werden
                if (str_contains($value, ' ') && !str_contains($value, '"') && !str_contains($value, ',')) {
                    // Einzelner Font-Name mit Leerzeichen ohne Quotes
                    $value = '"' . $value . '"';
                }
                
                // Google Fonts mit konfigurierten Fallbacks erweitern (nur wenn keine Kommas vorhanden)
                if (str_contains($value, '"') && !str_contains($value, ',')) {
                    // Dies ist wahrscheinlich eine Google Font ohne Fallback
                    $value = $this->applyFallback($value, $fallbacks);
                }
                
                // Fallback für font-family ohne Quotes aber mit Komma (bereits vollständiger Stack)
                // z.B. "Times New Roman, serif" muss zu '"Times New Roman", serif' werden
                if (str_contains($value, ',') && !str_contains($value, '"')) {
                    $parts = array_map('trim', explode(',', $value));
                    $quotedParts = [];
                    foreach ($parts as $part) {
                        // Nur Parts mit Leerzeichen in Quotes setzen
                        if (str_contains($part, ' ')) {
                            $quotedParts[] = '"' . $part . '"';
                        } else {
                            $quotedParts[] = $part;
                        }
                    }
                    $value = implode(', ', $quotedParts);
                }
            }
            
            // WICHTIG: Werte die mit @ beginnen sind LESS-Variablen-Referenzen und müssen bleiben
            // "inherit" Werte NICHT überspringen - sie werden später via Hooks gesetzt
            
            $variables[$lessVar] = $value;
        }
        
        return $variables;
    }
    
    /**
     * Generiert Custom LESS Code für Heading-Farben und Styles
     */
    public function generateLess(array $data): string
    {
        $less = '';
        
        // WICHTIG: Direkte CSS-Regeln statt Hooks, da UIKit die Hooks am Ende überschreibt
        // Wir müssen die Selektoren direkt ansprechen
        
        // Font-Family und Text-Transform für ALLE Überschriften
        // IMMER generieren, auch wenn Wert 'inherit' ist - LESS-Variablen werden separat definiert
        $headingStyles = [];
        
        // Font-Family: Immer anwenden wenn im Theme definiert (auch 'inherit')
        if (array_key_exists('base-heading-font-family', $data) && $data['base-heading-font-family'] !== 'inherit') {
            $headingStyles[] = "font-family: @base-heading-font-family !important;";
        }
        
        // Font-Weight: Immer anwenden wenn im Theme definiert (auch 'inherit')
        if (array_key_exists('base-heading-font-weight', $data) && $data['base-heading-font-weight'] !== 'inherit') {
            $headingStyles[] = "font-weight: @base-heading-font-weight;";
        }
        
        // Text-Transform: Immer anwenden wenn im Theme definiert (auch 'inherit')
        if (array_key_exists('base-heading-text-transform', $data) && $data['base-heading-text-transform'] !== 'inherit') {
            $headingStyles[] = "text-transform: @base-heading-text-transform;";
        }
        
        // Color: Immer anwenden wenn im Theme definiert (auch 'inherit')
        if (array_key_exists('base-heading-color', $data) && $data['base-heading-color'] !== 'inherit') {
            $headingStyles[] = "color: @base-heading-color;";
        }
        
        if (!empty($headingStyles)) {
            $less .= "// Typography Widget - Custom LESS\n";
            $less .= "// Typography - Apply heading styles (override UIKit)\n";
            $less .= "h1, .uk-h1,\n";
            $less .= "h2, .uk-h2,\n";
            $less .= "h3, .uk-h3,\n";
            $less .= "h4, .uk-h4,\n";
            $less .= "h5, .uk-h5,\n";
            $less .= "h6, .uk-h6,\n";
            $less .= ".uk-heading-small,\n";
            $less .= ".uk-heading-medium,\n";
            $less .= ".uk-heading-large,\n";
            $less .= ".uk-heading-xlarge,\n";
            $less .= ".uk-heading-2xlarge,\n";
            $less .= ".uk-heading-3xlarge {\n";
            $less .= "    " . implode("\n    ", $headingStyles) . "\n";
            $less .= "}\n\n";
        }
        
        // Individual Heading Colors
        $headingColors = [];
        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $heading) {
            $key = 'base-' . $heading . '-color';
            if (array_key_exists($key, $data) && $data[$key] !== 'inherit' && !empty($data[$key])) {
                $headingColors[$heading] = $data[$key];
            }
        }
        
        if (!empty($headingColors)) {
            $less .= "// Typography - Individual Heading Colors\n";
            foreach ($headingColors as $heading => $color) {
                $less .= "{$heading}, .uk-{$heading} { color: {$color}; }\n";
            }
            $less .= "\n";
        }
        
        // Dropcap Farbe via Hook - IMMER generieren wenn gesetzt
        if (array_key_exists('dropcap-color', $data) && !empty($data['dropcap-color'])) {
            $less .= "// Typography - Dropcap Color via Hook\n";
            $less .= ".hook-dropcap() { color: {$data['dropcap-color']}; }\n";
            $less .= "\n";
        }
        
        // Blockquote Styling via Hook
        $blockquoteHook = [];
        if (array_key_exists('base-blockquote-font-family', $data) && $data['base-blockquote-font-family'] !== 'inherit' && !empty($data['base-blockquote-font-family'])) {
            $blockquoteHook[] = "font-family: {$data['base-blockquote-font-family']};";
        }
        if (array_key_exists('base-blockquote-color', $data) && $data['base-blockquote-color'] !== 'inherit' && !empty($data['base-blockquote-color'])) {
            $blockquoteHook[] = "color: {$data['base-blockquote-color']};";
        }
        
        if (!empty($blockquoteHook)) {
            $less .= "// Typography - Blockquote Styling via Hook\n";
            $less .= ".hook-base-blockquote() {\n";
            $less .= "    " . implode("\n    ", $blockquoteHook) . "\n";
            $less .= "}\n\n";
        }
        
        
        return $less;
    }
    
    /**
     * Holt das SVG für ein Icon (UIkit oder Custom)
     */
    private function getIconSvgForDivider(string $iconName): ?string
    {
        // Versuche zuerst aus der Extended Icons Datei zu laden
        $extendedIconsFile = \rex_addon::get('uikit_theme_builder')->getAssetsPath('js/uikit-icons-extended.js');
        
        if (file_exists($extendedIconsFile)) {
            $content = file_get_contents($extendedIconsFile);
            
            // Icons extrahieren via Regex
            // Format: "icon-name": "<svg...>",
            // Das Pattern muss auch escaped Quotes (\") im String matchen
            // Wir matchen alles zwischen den Quotes, auch über Zeilen hinweg
            $pattern = '/"' . preg_quote($iconName, '/') . '":\s*"((?:[^"\\\\]|\\\\.)*)"/s';
            if (preg_match($pattern, $content, $match)) {
                // Decode escaped characters (wie \", \\, etc.)
                $svg = stripcslashes($match[1]);
                return $svg;
            }
        }
        
        // Fallback: Custom Icon Manager
        $customManager = new \UikitThemeBuilder\CustomIconManager();
        $customIcons = $customManager->getCustomIcons();
        
        foreach ($customIcons as $icon) {
            if ($icon['full_name'] === $iconName) {
                return $icon['svg_content'];
            }
        }
        
        return null;
    }
    
    /**
     * Gibt verfügbare Farboptionen für Headlines zurück
     */
    private function getColorOptions(): array
    {
        $colorOptions = [
            'inherit' => 'Erben (Standardfarbe verwenden)'
        ];

        // UIKit Standardfarben als LESS-Variablen-Referenzen
        $colorOptions['@global-primary-background'] = 'Primary';
        $colorOptions['@global-secondary-background'] = 'Secondary';
        $colorOptions['@global-emphasis-color'] = 'Emphasis (Standard)';
        $colorOptions['@global-color'] = 'Text';
        $colorOptions['@global-muted-color'] = 'Muted';
        $colorOptions['@global-danger-background'] = 'Danger/Error';
        $colorOptions['@global-success-background'] = 'Success';
        $colorOptions['@global-warning-background'] = 'Warning';

        return $colorOptions;
    }

    /**
     * Fallback zu Font-Familie hinzufügen basierend auf Kategorie
     */
    private function applyFallback(string $fontFamily, array $fallbacks): string
    {
        // Bereits vollständige Font-Stack? Dann nicht ändern
        if (str_contains($fontFamily, ',')) {
            return $fontFamily;
        }
        
        // Google Font Category bestimmen
        $availableFonts = $this->fontManager->getAllAvailableFonts();
        $cleanFontName = trim($fontFamily, '"');
        
        foreach ($availableFonts as $font) {
            if ($font['family'] === $cleanFontName && $font['source'] === 'google') {
                $category = $font['category'] ?? 'sans-serif';
                $fallback = $fallbacks[$category] ?? $fallbacks['sans-serif'];
                return $fontFamily . ', ' . $fallback;
            }
        }
        
        // Fallback für unbekannte Fonts
        return $fontFamily . ', ' . $fallbacks['sans-serif'];
    }
}