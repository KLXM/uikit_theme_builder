<?php

namespace UikitThemeBuilder\Widget;

/**
 * ComponentSelectionWidget – UIkit-Komponenten abwählen, die das Theme
 * nicht braucht. Abgewählte Komponenten werden beim Kompilieren nicht
 * importiert (weder aus components/ noch aus theme/), das CSS wird
 * entsprechend kleiner. Basis-Bausteine (variables, mixin, base, utility,
 * flex, margin, padding, width, visibility ...) sind immer enthalten.
 */
class ComponentSelectionWidget extends AbstractWidget
{
    /** Immer importiert (Grundlage fuer alles andere). */
    public const REQUIRED = ['variables', 'mixin', 'base', 'utility', 'flex', 'margin', 'padding', 'width', 'height', 'visibility', 'text', 'position', 'transition', 'svg', 'print'];

    /**
     * Abwaehlbare Komponenten: Name => [Label, Gruppe, Hinweis].
     * Reihenfolge und Abhaengigkeiten stammen aus components/_import.less.
     */
    public const OPTIONAL = [
        'link' => ['Link', 'Basis', 'uk-link-*'],
        'heading' => ['Heading', 'Basis', 'uk-heading-*, uk-h1 … uk-h6'],
        'divider' => ['Divider', 'Basis', 'uk-divider-*'],
        'list' => ['List', 'Basis', 'uk-list'],
        'description-list' => ['Description List', 'Basis', 'uk-description-list'],
        'table' => ['Table', 'Basis', 'uk-table'],
        'icon' => ['Icon', 'Basis', 'uk-icon, uk-icon-button'],
        'form-range' => ['Form Range', 'Formulare', 'uk-range'],
        'form' => ['Form', 'Formulare', 'uk-input, uk-select, uk-checkbox …'],
        'button' => ['Button', 'Elemente', 'uk-button'],
        'progress' => ['Progress', 'Elemente', 'uk-progress'],
        'section' => ['Section', 'Layout', 'uk-section'],
        'container' => ['Container', 'Layout', 'uk-container'],
        'tile' => ['Tile', 'Layout', 'uk-tile'],
        'card' => ['Card', 'Elemente', 'uk-card'],
        'close' => ['Close', 'Elemente', 'uk-close'],
        'spinner' => ['Spinner', 'Elemente', 'uk-spinner'],
        'totop' => ['Totop', 'Elemente', 'uk-totop'],
        'marker' => ['Marker', 'Elemente', 'uk-marker'],
        'alert' => ['Alert', 'Elemente', 'uk-alert'],
        'placeholder' => ['Placeholder', 'Elemente', 'uk-placeholder'],
        'badge' => ['Badge', 'Elemente', 'uk-badge'],
        'label' => ['Label', 'Elemente', 'uk-label'],
        'overlay' => ['Overlay', 'Elemente', 'uk-overlay'],
        'article' => ['Article', 'Elemente', 'uk-article'],
        'comment' => ['Comment', 'Elemente', 'uk-comment'],
        'search' => ['Search', 'Formulare', 'uk-search'],
        'accordion' => ['Accordion', 'Interaktion', 'uk-accordion'],
        'drop' => ['Drop', 'Interaktion', 'uk-drop (Basis fuer Dropdown)'],
        'dropbar' => ['Dropbar', 'Interaktion', 'uk-dropbar'],
        'dropnav' => ['Dropnav', 'Interaktion', 'uk-dropnav'],
        'modal' => ['Modal', 'Interaktion', 'uk-modal'],
        'slideshow' => ['Slideshow', 'Interaktion', 'uk-slideshow'],
        'slider' => ['Slider', 'Interaktion', 'uk-slider'],
        'sticky' => ['Sticky', 'Interaktion', 'uk-sticky'],
        'offcanvas' => ['Offcanvas', 'Interaktion', 'uk-offcanvas'],
        'switcher' => ['Switcher', 'Interaktion', 'uk-switcher'],
        'leader' => ['Leader', 'Elemente', 'uk-leader'],
        'notification' => ['Notification', 'Interaktion', 'uk-notification'],
        'tooltip' => ['Tooltip', 'Interaktion', 'uk-tooltip'],
        'sortable' => ['Sortable', 'Interaktion', 'uk-sortable'],
        'countdown' => ['Countdown', 'Elemente', 'uk-countdown'],
        'thumbnav' => ['Thumbnav', 'Navigation', 'uk-thumbnav'],
        'iconnav' => ['Iconnav', 'Navigation', 'uk-iconnav'],
        'grid' => ['Grid', 'Layout', 'uk-grid, uk-child-width-*'],
        'nav' => ['Nav', 'Navigation', 'uk-nav'],
        'navbar' => ['Navbar', 'Navigation', 'uk-navbar'],
        'subnav' => ['Subnav', 'Navigation', 'uk-subnav'],
        'breadcrumb' => ['Breadcrumb', 'Navigation', 'uk-breadcrumb'],
        'pagination' => ['Pagination', 'Navigation', 'uk-pagination'],
        'tab' => ['Tab', 'Navigation', 'uk-tab'],
        'slidenav' => ['Slidenav', 'Navigation', 'uk-slidenav'],
        'dotnav' => ['Dotnav', 'Navigation', 'uk-dotnav'],
        'dropdown' => ['Dropdown', 'Interaktion', 'uk-dropdown'],
        'lightbox' => ['Lightbox', 'Interaktion', 'uk-lightbox'],
        'animation' => ['Animation', 'Utilities', 'uk-animation-*'],
        'column' => ['Column', 'Utilities', 'uk-column-*'],
        'cover' => ['Cover', 'Utilities', 'uk-cover'],
        'background' => ['Background', 'Utilities', 'uk-background-*'],
        'align' => ['Align', 'Utilities', 'uk-align-*'],
        'inverse' => ['Inverse', 'Utilities', 'uk-light / uk-dark (helle Schrift auf dunklem Grund)'],
    ];

    public function getName(): string
    {
        return 'Komponenten-Auswahl';
    }

    public function getKey(): string
    {
        return 'component_selection';
    }

    public function getDescription(): string
    {
        return 'Nicht benötigte UIkit-Komponenten abwählen – sie werden nicht ins CSS kompiliert (kleinere Datei).';
    }

    public function getDefaultValues(): array
    {
        return ['disabled' => []];
    }

    public function getFields(): array
    {
        return ['disabled' => ['label' => 'Abgewählte Komponenten', 'type' => 'checkbox', 'default' => []]];
    }

    /** @return list<string> */
    public static function disabledFrom(array $themeData): array
    {
        $disabled = $themeData['component_selection']['disabled'] ?? [];
        if (!is_array($disabled)) {
            return [];
        }
        return array_values(array_filter(array_map('strval', $disabled), static fn (string $n): bool => isset(self::OPTIONAL[$n])));
    }

    public function renderForm(array $values = []): string
    {
        $disabled = array_flip(self::disabledFrom(['component_selection' => $values]));
        $groups = [];
        foreach (self::OPTIONAL as $name => [$label, $group, $hint]) {
            $groups[$group][$name] = [$label, $hint];
        }
        $html = '<div class="uk-alert uk-alert-primary uk-margin-small-bottom" style="padding:10px 14px"><p class="uk-margin-remove">Haken weg = Komponente wird nicht kompiliert. Vorher prüfen, welche <code>uk-*</code>-Klassen und Attribute Templates, Module und eigene Skripte verwenden (auch Klassen, die UIkit-JS zur Laufzeit setzt, z.&nbsp;B. <code>uk-open</code>, <code>uk-active</code>, <code>uk-animation-*</code>). Grundbausteine (variables, base, utility, flex, margin, padding, width, height, visibility, text, position, transition) bleiben immer enthalten.</p></div>';
        $html .= '<p class="uk-text-meta"><a href="#" data-component-select="all">Alle aktivieren</a> · <a href="#" data-component-select="none">Alle abwählen</a> · <span data-component-count></span></p>';
        foreach ($groups as $group => $items) {
            $html .= '<h5 class="uk-margin-small-top uk-margin-small-bottom">' . rex_escape($group) . '</h5><div class="uk-grid-small uk-child-width-1-2@s uk-child-width-1-3@m uk-child-width-1-4@l" uk-grid>';
            foreach ($items as $name => [$label, $hint]) {
                $checked = isset($disabled[$name]) ? '' : ' checked';
                $html .= '<div><label class="uk-text-small"><input type="checkbox" class="uk-checkbox uk-margin-small-right" name="component_selection[enabled][]" value="' . rex_escape($name) . '"' . $checked . '> <strong>' . rex_escape($label) . '</strong> <span class="uk-text-muted">' . rex_escape($hint) . '</span></label></div>';
            }
            $html .= '</div>';
        }
        $html .= '<input type="hidden" name="component_selection[submitted]" value="1">';
        $html .= '<script>(function(){var boxes=function(){return document.querySelectorAll(\'input[name="component_selection[enabled][]"]\');};var count=function(){var all=boxes(),on=0;all.forEach(function(b){if(b.checked)on++;});var c=document.querySelector("[data-component-count]");if(c){c.textContent=on+" von "+all.length+" Komponenten aktiv";}};document.querySelectorAll("[data-component-select]").forEach(function(a){a.addEventListener("click",function(e){e.preventDefault();var on=a.getAttribute("data-component-select")==="all";boxes().forEach(function(b){b.checked=on;});count();});});boxes().forEach(function(b){b.addEventListener("change",count);});count();})();</script>';
        return $html;
    }

    public function processFormData(array $formData): array
    {
        $data = $formData['component_selection'] ?? [];
        if (!is_array($data) || empty($data['submitted'])) {
            return $this->getDefaultValues();
        }
        $enabled = isset($data['enabled']) && is_array($data['enabled']) ? array_map('strval', $data['enabled']) : [];
        $disabled = [];
        foreach (array_keys(self::OPTIONAL) as $name) {
            if (!in_array($name, $enabled, true)) {
                $disabled[] = $name;
            }
        }
        return ['disabled' => $disabled];
    }

    public function validateFormData(array $data): array
    {
        return [];
    }

    public function generateLessVariables(array $data): array
    {
        return [];
    }

    /**
     * Import-Zeilen fuer die LESS-Datei: alle Komponenten aus
     * components/_import.less in Originalreihenfolge ohne die abgewaehlten,
     * jeweils mit passender theme/-Datei.
     *
     * @param list<string> $disabled
     */
    public static function buildImports(string $uikitLessPath, array $disabled): string
    {
        $importFile = $uikitLessPath . '/components/_import.less';
        $content = (string) @file_get_contents($importFile);
        preg_match_all('/@import\s+"([a-z0-9-]+)\.less"/i', $content, $m);
        $names = $m[1];
        if ($names === []) {
            return '@import "' . $uikitLessPath . '/components/_import.less";' . "\n" . '@import "' . $uikitLessPath . '/theme/_import.less";' . "\n";
        }
        $skip = array_flip(array_diff($disabled, self::REQUIRED));
        $out = "// Komponenten-Auswahl: " . count($skip) . " Komponenten abgewählt\n";
        $themeOut = '';
        foreach ($names as $name) {
            if (isset($skip[$name])) {
                continue;
            }
            $out .= '@import "' . $uikitLessPath . '/components/' . $name . '.less";' . "\n";
            if (is_file($uikitLessPath . '/theme/' . $name . '.less')) {
                $themeOut .= '@import "' . $uikitLessPath . '/theme/' . $name . '.less";' . "\n";
            }
        }
        return $out . $themeOut;
    }
}
