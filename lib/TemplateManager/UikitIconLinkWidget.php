<?php

namespace UikitThemeBuilder\TemplateManager;

use FriendsOfRedaxo\TemplateManager\Widget\RepeaterWidget;

/**
 * UIKit Icon + Link Repeater Widget
 * Für Icon-Listen mit UIKit Icon Picker
 */
class UikitIconLinkWidget extends RepeaterWidget
{
    public function getKey(): string
    {
        return 'tm_icon_links';
    }

    public function getName(): string
    {
        return 'Icon Links';
    }

    public function getDescription(): string
    {
        return 'Verwalten Sie Links mit UIKit Icons (z.B. für Navigation, Quick-Links, etc.)';
    }

    public function getCategory(): string
    {
        return 'navigation';
    }

    public function getDefaultValues(): array
    {
        return [
            'icon' => '',
            'label' => '',
            'url' => '',
            'target' => '_self',
            'description' => ''
        ];
    }

    protected function getRepeaterFields(): array
    {
        return [
            'icon' => [
                'label' => 'Icon',
                'type' => 'iconpicker'
            ],
            'label' => [
                'label' => 'Bezeichnung',
                'type' => 'text'
            ],
            'url' => [
                'label' => 'URL',
                'type' => 'text'
            ],
            'target' => [
                'label' => 'Ziel',
                'type' => 'select',
                'options' => [
                    '_self' => 'Gleiches Fenster',
                    '_blank' => 'Neues Fenster'
                ]
            ],
            'description' => [
                'label' => 'Beschreibung',
                'type' => 'textarea'
            ]
        ];
    }

    protected function renderRepeaterRow(array $values, int|string $index): string
    {
        $key = $this->getKey();
        
        $content = '<div class="row">';
        
        // Icon Picker (col-2)
        $content .= '<div class="col-sm-2">';
        $content .= '<label>Icon</label>';
        $content .= $this->renderUikitIconPicker(
            $this->getRepeaterFieldName('icon', $index),
            $values['icon'] ?? ''
        );
        $content .= '</div>';
        
        // Label (col-3)
        $content .= '<div class="col-sm-3">';
        $content .= '<label>Bezeichnung</label>';
        $content .= $this->renderTextInput(
            $this->getRepeaterFieldName('label', $index),
            $values['label'] ?? ''
        );
        $content .= '</div>';
        
        // URL (col-4)
        $content .= '<div class="col-sm-4">';
        $content .= '<label>URL</label>';
        $content .= $this->renderTextInput(
            $this->getRepeaterFieldName('url', $index),
            $values['url'] ?? '',
            ['placeholder' => 'https://... oder /intern']
        );
        $content .= '</div>';
        
        // Target (col-3)
        $content .= '<div class="col-sm-3">';
        $content .= '<label>Ziel</label>';
        $content .= $this->renderSelect(
            $this->getRepeaterFieldName('target', $index),
            ['_self' => 'Gleiches Fenster', '_blank' => 'Neues Fenster'],
            $values['target'] ?? '_self'
        );
        $content .= '</div>';
        
        $content .= '</div>'; // .row
        
        // Beschreibung (volle Breite)
        if (!empty($values['description']) || $index === '{{INDEX}}') {
            $content .= '<div class="row" style="margin-top: 10px;">';
            $content .= '<div class="col-sm-12">';
            $content .= '<label>Beschreibung (optional)</label>';
            $content .= $this->renderTextarea(
                $this->getRepeaterFieldName('description', $index),
                $values['description'] ?? '',
                2
            );
            $content .= '</div>';
            $content .= '</div>';
        }
        
        return $this->renderRepeaterItemWrapper($content, $index, $key);
    }

    /**
     * UIKit Icon Picker rendern
     * Nutzt das JavaScript aus dem uikit_theme_builder
     */
    protected function renderUikitIconPicker(string $name, string $value = ''): string
    {
        // Input ID generieren
        $inputId = 'uikit-icon-' . md5($name . microtime());
        
        $html = '<input type="text" 
                        class="uk-iconpicker" 
                        id="' . $inputId . '" 
                        name="' . htmlspecialchars($name) . '" 
                        value="' . htmlspecialchars($value) . '" 
                        placeholder="Icon wählen...">';
        
        // JavaScript für Icon Picker (einmalig laden)
        static $scriptLoaded = false;
        if (!$scriptLoaded) {
            $html .= $this->renderIconPickerScript();
            $scriptLoaded = true;
        }
        
        return $html;
    }

    /**
     * Icon Picker JavaScript laden und initialisieren
     */
    protected function renderIconPickerScript(): string
    {
        // UIKit Icon Picker JS
        $iconsJsUrl = \rex_url::addonAssets('uikit_theme_builder', 'js/uikit-icon-picker.js');
        
        return <<<HTML
<script>
// UIKit Icon Picker laden und initialisieren
(function() {
    if (typeof window.uikitIconPickerLoaded === 'undefined') {
        window.uikitIconPickerLoaded = true;
        
        // Script laden
        var script = document.createElement('script');
        script.src = '$iconsJsUrl';
        script.onload = function() {
            // Icon Pickers initialisieren nach dem Laden
            if (typeof window.UikitIconPicker !== 'undefined') {
                window.UikitIconPicker.init();
            }
            
            // Bei dynamisch hinzugefügten Elementen (Repeater)
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.addedNodes.length) {
                            setTimeout(function() {
                                if (typeof window.UikitIconPicker !== 'undefined') {
                                    window.UikitIconPicker.init();
                                }
                            }, 100);
                        }
                    });
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
        };
        document.head.appendChild(script);
    } else {
        // Script bereits geladen - nur initialisieren
        setTimeout(function() {
            if (typeof window.UikitIconPicker !== 'undefined') {
                window.UikitIconPicker.init();
            }
        }, 100);
    }
})();
</script>
HTML;
    }

    public function validate(array $data): array
    {
        $errors = [];
        
        if (!empty($data)) {
            foreach ($data as $index => $item) {
                // URL validieren wenn vorhanden
                if (!empty($item['url'])) {
                    // Interne URLs (beginnen mit /) sind OK
                    if (strpos($item['url'], '/') !== 0 && !filter_var($item['url'], FILTER_VALIDATE_URL)) {
                        $errors[$index]['url'] = 'Ungültige URL';
                    }
                }
                
                // Label ist Pflicht
                if (empty($item['label'])) {
                    $errors[$index]['label'] = 'Bezeichnung ist erforderlich';
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data
        ];
    }
}
