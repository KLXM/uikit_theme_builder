<?php

namespace UikitThemeBuilder\Widget;

/**
 * Google Fonts Widget
 * Ermöglicht das Hinzufügen und Verwalten von Google Fonts
 */
class GoogleFontsWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Google Fonts';
    }

    public function getKey(): string
    {
        return 'google_fonts';
    }

    public function getDescription(): string
    {
        return 'Verwalte und integriere Google Fonts in dein Theme.';
    }

    public function getFields(): array
    {
        return [
            'selected_fonts' => 'Ausgewählte Google Fonts',
            'font_variables' => 'Font-Variable Zuordnungen'
        ];
    }

    public function getTitle(): string
    {
        return '<i class="fas fa-font"></i> Google Fonts';
    }

    public function renderForm(array $data = []): string
    {
        $selectedFonts = $data['selected_fonts'] ?? [];
        $fontVariables = $data['font_variables'] ?? [];
        
        $html = '<div class="uk-grid-small" uk-grid>';
        
        // Google Fonts Browser
        $html .= '<div class="uk-width-2-3@m">';
        $html .= '<div class="uk-card uk-card-default uk-card-small">';
        $html .= '<div class="uk-card-header">';
        $html .= '<h4 class="uk-card-title uk-margin-remove-bottom"><i class="fab fa-google"></i> Google Fonts Browser</h4>';
        $html .= '</div>';
        $html .= '<div class="uk-card-body">';
        
        // Search Input
        $html .= '<div class="uk-margin-bottom">';
        $html .= '<div class="uk-grid-small" uk-grid>';
        $html .= '<div class="uk-width-expand">';
        $html .= '<div class="uk-inline uk-width-1-1">';
        $html .= '<span class="uk-form-icon" uk-icon="icon: search"></span>';
        $html .= '<input class="uk-input" type="text" id="google-fonts-search" placeholder="Nach Google Fonts suchen...">';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="uk-width-auto">';
        $html .= '<button class="uk-button uk-button-secondary" id="load-all-fonts-btn" onclick="googleFontsWidget.loadAllFonts()" title="Lädt alle ~1900 Google Fonts von der API">';
        $html .= '<span uk-icon="cloud-download"></span> Alle Fonts laden';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Loading Indicator
        $html .= '<div id="fonts-loading" class="uk-text-center uk-margin" style="display: none;">';
        $html .= '<div uk-spinner></div>';
        $html .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">Lade Google Fonts...</div>';
        $html .= '</div>';
        
        // Popular Fonts
        $html .= '<div class="uk-margin-bottom">';
        $html .= '<h5 class="uk-margin-small-bottom">Beliebte Schriftarten</h5>';
        $html .= '<div id="popular-fonts" class="font-list">';
        $html .= $this->renderPopularFonts();
        $html .= '</div>';
        $html .= '</div>';
        
        // Search Results
        $html .= '<div id="search-results" class="font-list" style="display: none;">';
        $html .= '</div>';
        
        // Load More Button
        $html .= '<div class="uk-text-center uk-margin-top">';
        $html .= '<button class="uk-button uk-button-default" id="load-more-fonts" onclick="googleFontsWidget.loadMoreFonts()">Mehr Schriftarten laden</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Selected Fonts & Variables Panel
        $html .= '<div class="uk-width-1-3@m">';
        $html .= '<div class="uk-card uk-card-primary uk-card-small">';
        $html .= '<div class="uk-card-header">';
        $html .= '<h4 class="uk-card-title uk-margin-remove-bottom uk-light"><i class="fas fa-check-circle"></i> Ausgewählte Fonts</h4>';
        $html .= '</div>';
        $html .= '<div class="uk-card-body uk-light">';
        
        // Selected Fonts List
        $html .= '<div id="selected-fonts-list" class="uk-margin-bottom">';
        if (empty($selectedFonts)) {
            $html .= '<div class="uk-text-center uk-text-muted">Keine Fonts ausgewählt</div>';
        } else {
            foreach ($selectedFonts as $font) {
                $html .= $this->renderSelectedFont($font);
            }
        }
        $html .= '</div>';
        
        // Font Variables Mapping
        $html .= '<hr class="uk-divider-small">';
        $html .= '<h5 class="uk-margin-small-bottom uk-light">LESS-Variablen Zuordnung</h5>';
        $html .= '<div class="uk-margin-small uk-text-small uk-light">Ordne Fonts den UIKit-Variablen zu:</div>';
        
        $commonVariables = [
            'global-font-family' => 'Standard Schriftart',
            'heading-primary-font-family' => 'Hauptüberschriften',
            'heading-secondary-font-family' => 'Unterüberschriften',
            'navbar-nav-item-font-family' => 'Navigation',
            'button-font-family' => 'Buttons'
        ];
        
        foreach ($commonVariables as $varKey => $label) {
            $currentValue = $fontVariables[$varKey] ?? '';
            $html .= '<div class="uk-margin-small">';
            $html .= '<label class="uk-form-label uk-light uk-text-small">' . rex_escape($label) . '</label>';
            $html .= '<select class="uk-select uk-form-small" name="google_fonts[font_variables][' . $varKey . ']">';
            $html .= '<option value="">Standard verwenden</option>';
            
            foreach ($selectedFonts as $font) {
                $selected = $currentValue === $font['family'] ? ' selected' : '';
                $html .= '<option value="' . rex_escape($font['family']) . '"' . $selected . '>' . rex_escape($font['family']) . '</option>';
            }
            $html .= '</select>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Hidden Input für Selected Fonts
        $html .= '<input type="hidden" id="selected-fonts-data" name="google_fonts[selected_fonts]" value="' . rex_escape(json_encode($selectedFonts)) . '">';
        
        // JavaScript
        $html .= $this->renderJavaScript();
        
        return $html;
    }

    private function renderPopularFonts(): string
    {
        $popularFonts = [
            ['family' => 'Roboto', 'category' => 'sans-serif'],
            ['family' => 'Open Sans', 'category' => 'sans-serif'],
            ['family' => 'Lato', 'category' => 'sans-serif'],
            ['family' => 'Montserrat', 'category' => 'sans-serif'],
            ['family' => 'Source Sans Pro', 'category' => 'sans-serif'],
            ['family' => 'Raleway', 'category' => 'sans-serif'],
            ['family' => 'Poppins', 'category' => 'sans-serif'],
            ['family' => 'Nunito', 'category' => 'sans-serif'],
            ['family' => 'Playfair Display', 'category' => 'serif'],
            ['family' => 'Merriweather', 'category' => 'serif'],
            ['family' => 'Lora', 'category' => 'serif'],
            ['family' => 'PT Serif', 'category' => 'serif']
        ];
        
        $html = '';
        foreach ($popularFonts as $font) {
            $html .= $this->renderFontItem($font);
        }
        
        return $html;
    }

    private function renderFontItem(array $font): string
    {
        $fontFamily = rex_escape($font['family']);
        $category = rex_escape($font['category'] ?? 'sans-serif');
        
        return '<div class="font-item uk-margin-small" data-font="' . $fontFamily . '" data-category="' . $category . '">
            <div class="uk-flex uk-flex-between uk-flex-middle uk-padding-small" style="border: 1px solid #e5e5e5; border-radius: 4px;">
                <div class="uk-width-expand">
                    <div class="font-preview" style="font-family: \'' . $fontFamily . '\', ' . $category . '; font-size: 16px; font-weight: 400;">' . $fontFamily . '</div>
                    <div class="uk-text-small uk-text-muted">' . ucfirst($category) . '</div>
                </div>
                <button class="uk-button uk-button-primary uk-button-small add-font-btn" onclick="googleFontsWidget.addFont(\'' . $fontFamily . '\', \'' . $category . '\')">
                    <span uk-icon="plus"></span>
                </button>
            </div>
        </div>';
    }

    private function renderSelectedFont(array $font): string
    {
        $fontFamily = rex_escape($font['family']);
        $category = rex_escape($font['category'] ?? 'sans-serif');
        
        return '<div class="selected-font-item uk-margin-small" data-font="' . $fontFamily . '">
            <div class="uk-flex uk-flex-between uk-flex-middle uk-padding-small" style="background: rgba(255,255,255,0.1); border-radius: 4px;">
                <div class="uk-width-expand">
                    <div style="font-family: \'' . $fontFamily . '\', ' . $category . '; font-size: 14px; color: white;">' . $fontFamily . '</div>
                    <div class="uk-text-small" style="color: rgba(255,255,255,0.7);">' . ucfirst($category) . '</div>
                </div>
                <button class="uk-button uk-button-danger uk-button-small" onclick="googleFontsWidget.removeFont(\'' . $fontFamily . '\')">
                    <span uk-icon="trash"></span>
                </button>
            </div>
        </div>';
    }

    private function renderJavaScript(): string
    {
        // API Endpoint URL für rex_api_function
        $apiUrl = \rex_url::frontendController([
            'rex-api-call' => 'uikit_theme_builder_fonts'
        ]);
        
        return '
        <script>
        class GoogleFontsWidget {
            constructor() {
                this.selectedFonts = [];
                this.apiUrl = "' . $apiUrl . '";
                this.allFontsCache = null; // Cache für alle Fonts
                this.loadSelectedFonts();
                this.setupSearch();
                this.loadGoogleFontsCSS();
            }
            
            async loadAllFonts() {
                const loadingIndicator = document.getElementById("fonts-loading");
                const loadBtn = document.getElementById("load-all-fonts-btn");
                const searchInput = document.getElementById("google-fonts-search");
                
                try {
                    loadBtn.disabled = true;
                    loadingIndicator.style.display = "block";
                    
                    const response = await fetch(this.apiUrl);
                    
                    if (!response.ok) {
                        throw new Error("API unavailable");
                    }
                    
                    const data = await response.json();
                    this.allFontsCache = data.items || [];
                    
                    loadingIndicator.style.display = "none";
                    
                    UIkit.notification({
                        message: `${this.allFontsCache.length} Google Fonts erfolgreich geladen!`,
                        status: "success",
                        pos: "top-right",
                        timeout: 3000
                    });
                    
                    // Aktiviere Suchfeld
                    searchInput.placeholder = `${this.allFontsCache.length} Fonts durchsuchen...`;
                    searchInput.focus();
                    
                    // Button Text ändern
                    loadBtn.innerHTML = `<span uk-icon="refresh"></span> Neu laden`;
                    loadBtn.disabled = false;
                    
                } catch (error) {
                    loadingIndicator.style.display = "none";
                    loadBtn.disabled = false;
                    
                    UIkit.notification({
                        message: "Fehler beim Laden der Fonts: " + error.message,
                        status: "danger",
                        pos: "top-right",
                        timeout: 5000
                    });
                }
            }
            
            loadSelectedFonts() {
                const data = document.getElementById("selected-fonts-data").value;
                if (data) {
                    this.selectedFonts = JSON.parse(data);
                }
            }
            
            setupSearch() {
                const searchInput = document.getElementById("google-fonts-search");
                if (searchInput) {
                    let timeout;
                    searchInput.addEventListener("input", (e) => {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => {
                            this.searchFonts(e.target.value);
                        }, 300);
                    });
                }
            }
            
            loadGoogleFontsCSS() {
                // Load CSS for selected fonts
                this.selectedFonts.forEach(font => {
                    // Extrahiere nur den ersten Font-Namen (ohne Fallbacks)
                    const fontName = this.extractFirstFont(font.family);
                    this.loadFontCSS(fontName);
                });
            }
            
            searchFonts(query) {
                const searchResults = document.getElementById("search-results");
                const popularFonts = document.getElementById("popular-fonts");
                
                if (!query.trim()) {
                    searchResults.style.display = "none";
                    popularFonts.style.display = "block";
                    return;
                }
                
                popularFonts.style.display = "none";
                searchResults.style.display = "block";
                
                // Prüfe ob alle Fonts geladen wurden
                if (this.allFontsCache) {
                    // Suche im Cache
                    const matches = this.allFontsCache
                        .filter(font => font.family.toLowerCase().includes(query.toLowerCase()))
                        .slice(0, 20);
                    
                    this.displaySearchResults(matches, query);
                } else {
                    // Zeige Hinweis, dass Fonts erst geladen werden müssen
                    searchResults.innerHTML = `
                        <div class="uk-text-center uk-padding">
                            <p class="uk-text-muted">Bitte lade zuerst alle Google Fonts.</p>
                            <button class="uk-button uk-button-primary uk-margin-small-top" onclick="googleFontsWidget.loadAllFonts()">
                                <span uk-icon="cloud-download"></span> Jetzt laden
                            </button>
                        </div>
                    `;
                }
            }
            
            displaySearchResults(matches, query) {
                const searchResults = document.getElementById("search-results");
                
                if (matches.length === 0) {
                    searchResults.innerHTML = `<div class=\"uk-text-center uk-text-muted uk-padding\">
                        Keine Ergebnisse für "${query}" gefunden
                    </div>`;
                    return;
                }
                
                let html = "";
                matches.forEach(font => {
                    const category = font.category || "sans-serif";
                    html += this.createFontItemHTML(font.family, category);
                });
                
                searchResults.innerHTML = html;
            }
            
            createFontItemHTML(family, category) {
                return `<div class="font-item uk-margin-small" data-font="${family}" data-category="${category}">
                    <div class="uk-flex uk-flex-between uk-flex-middle uk-padding-small" style="border: 1px solid #e5e5e5; border-radius: 4px;">
                        <div class="uk-width-expand">
                            <div class="font-preview" style="font-family: \'${family}\', ${category}; font-size: 16px; font-weight: 400;">${family}</div>
                            <div class="uk-text-small uk-text-muted">${category}</div>
                        </div>
                        <button class="uk-button uk-button-primary uk-button-small add-font-btn" onclick="googleFontsWidget.addFont(\'${family}\', \'${category}\')">
                            <span uk-icon="plus"></span>
                        </button>
                    </div>
                </div>`;
            }
            
            addFont(family, category) {
                if (this.selectedFonts.some(f => f.family === family)) {
                    UIkit.notification({
                        message: `${family} ist bereits hinzugefügt!`,
                        status: "warning",
                        pos: "top-right"
                    });
                    return;
                }
                
                const font = { family, category };
                this.selectedFonts.push(font);
                this.updateSelectedFonts();
                this.loadFontCSS(family);
                
                UIkit.notification({
                    message: `${family} hinzugefügt!`,
                    status: "success",
                    pos: "top-right"
                });
            }
            
            removeFont(family) {
                this.selectedFonts = this.selectedFonts.filter(f => f.family !== family);
                this.updateSelectedFonts();
                this.removeFontCSS(family);
                
                UIkit.notification({
                    message: `${family} entfernt!`,
                    status: "success",
                    pos: "top-right"
                });
            }
            
            updateSelectedFonts() {
                const container = document.getElementById("selected-fonts-list");
                const hiddenInput = document.getElementById("selected-fonts-data");
                
                if (this.selectedFonts.length === 0) {
                    container.innerHTML = "<div class=\"uk-text-center uk-text-muted\">Keine Fonts ausgewählt</div>";
                } else {
                    container.innerHTML = this.selectedFonts.map(font => 
                        this.createSelectedFontHTML(font)
                    ).join("");
                }
                
                hiddenInput.value = JSON.stringify(this.selectedFonts);
                
                // Update variable dropdowns
                this.updateVariableDropdowns();
            }
            
            createSelectedFontHTML(font) {
                return `<div class="selected-font-item uk-margin-small" data-font="${font.family}">
                    <div class="uk-flex uk-flex-between uk-flex-middle uk-padding-small" style="background: rgba(255,255,255,0.1); border-radius: 4px;">
                        <div class="uk-width-expand">
                            <div style="font-family: \'${font.family}\', ${font.category}; font-size: 14px; color: white;">${font.family}</div>
                            <div class="uk-text-small" style="color: rgba(255,255,255,0.7);">${font.category}</div>
                        </div>
                        <button class="uk-button uk-button-danger uk-button-small" onclick="googleFontsWidget.removeFont(\'${font.family}\')">
                            <span uk-icon="trash"></span>
                        </button>
                    </div>
                </div>`;
            }
            
            updateVariableDropdowns() {
                const selects = document.querySelectorAll("select[name^=\"google_fonts[font_variables]\"]");
                selects.forEach(select => {
                    const currentValue = select.value;
                    const options = Array.from(select.options).slice(1); // Keep first "Standard" option
                    
                    // Remove old font options
                    options.forEach(option => option.remove());
                    
                    // Add current font options
                    this.selectedFonts.forEach(font => {
                        const option = document.createElement("option");
                        option.value = font.family;
                        option.textContent = font.family;
                        if (currentValue === font.family) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                });
            }
            
            loadFontCSS(family) {
                // Extrahiere nur den ersten Font-Namen (ohne Fallbacks)
                const fontName = this.extractFirstFont(family);
                const fontLink = `https://fonts.googleapis.com/css2?family=${fontName.replace(/\\s+/g, "+")}:wght@300;400;500;600;700&display=swap`;
                
                if (!document.querySelector(`link[href="${fontLink}"]`)) {
                    const link = document.createElement("link");
                    link.rel = "stylesheet";
                    link.href = fontLink;
                    link.id = `font-${fontName.replace(/\\s+/g, "-").toLowerCase()}`;
                    document.head.appendChild(link);
                }
            }
            
            removeFontCSS(family) {
                // Extrahiere nur den ersten Font-Namen (ohne Fallbacks)
                const fontName = this.extractFirstFont(family);
                const fontId = `font-${fontName.replace(/\\s+/g, "-").toLowerCase()}`;
                const link = document.getElementById(fontId);
                if (link) {
                    link.remove();
                }
            }
            
            extractFirstFont(fontFamily) {
                // Entferne Anführungszeichen und extrahiere ersten Font
                // z.B. "Abel", -apple-system, BlinkMacSystemFont -> Abel
                // oder Abel, sans-serif -> Abel
                if (!fontFamily) return "";
                
                const cleaned = fontFamily.replaceAll(\'"\', "").replaceAll("\'", "").trim();
                const firstFont = cleaned.split(",")[0].trim();
                return firstFont;
            }
            
            loadMoreFonts() {
                UIkit.notification({
                    message: "Mehr Fonts werden geladen...",
                    status: "primary",
                    pos: "top-right"
                });
                
                // In real implementation, this would load more fonts from Google Fonts API
            }
        }
        
        // Initialize when DOM is ready
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof googleFontsWidget === "undefined") {
                window.googleFontsWidget = new GoogleFontsWidget();
            }
        });
        </script>';
    }

    public function processFormData(array $formData): array
    {
        $data = [];
        
        if (isset($formData['google_fonts'])) {
            $googleFontsData = $formData['google_fonts'];
            
            $data['selected_fonts'] = json_decode($googleFontsData['selected_fonts'] ?? '[]', true) ?: [];
            $data['font_variables'] = $googleFontsData['font_variables'] ?? [];
        }
        
        return $data;
    }

    public function getDefaultValues(): array
    {
        return [
            'selected_fonts' => [],
            'font_variables' => []
        ];
    }

    public function validateFormData(array $data): array
    {
        $errors = [];
        
        // Validierung für Selected Fonts
        if (isset($data['selected_fonts']) && !is_array($data['selected_fonts'])) {
            $errors[] = 'Selected fonts must be an array';
        }
        
        // Validierung für Font Variables
        if (isset($data['font_variables']) && !is_array($data['font_variables'])) {
            $errors[] = 'Font variables must be an array';
        }
        
        return $errors;
    }

    public function generateLessVariables(array $data): array
    {
        $variables = [];
        
        if (!empty($data['font_variables'])) {
            foreach ($data['font_variables'] as $varKey => $fontFamily) {
                if (!empty($fontFamily)) {
                    // Font family mit Fallbacks
                    $fontValue = "'{$fontFamily}', sans-serif";
                    
                    // Bestimme Fallback basierend auf ausgewählten Fonts
                    if (!empty($data['selected_fonts'])) {
                        $selectedFont = array_filter($data['selected_fonts'], function($font) use ($fontFamily) {
                            return $font['family'] === $fontFamily;
                        });
                        
                        if (!empty($selectedFont)) {
                            $font = array_values($selectedFont)[0];
                            $fallback = $font['category'] === 'serif' ? 'serif' : 'sans-serif';
                            $fontValue = "'{$fontFamily}', {$fallback}";
                        }
                    }
                    
                    $variables[$varKey] = $fontValue;
                }
            }
        }
        
        return $variables;
    }

    public function generateLess(array $data): string
    {
        $less = '';

        // Google Fonts Import hinzufügen - NUR lokal gehostete Dateien (siehe GoogleFontsManager/
        // TemplateHelper::includeGoogleFonts()). Früher stand hier ein direkter @import auf
        // fonts.googleapis.com - das hätte bei jedem Seitenaufruf für JEDEN Besucher eine externe
        // Verbindung ausgelöst, sobald diese Auswahl mal befüllt wird. Fonts, die (noch) nicht
        // über die Fontverwaltung heruntergeladen wurden, werden hier bewusst übersprungen statt
        // extern nachgeladen - dann greift nur der Fallback-Stack.
        if (!empty($data['selected_fonts'])) {
            $imports = [];
            foreach ($data['selected_fonts'] as $font) {
                if (empty($font['family'])) {
                    continue;
                }
                $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $font['family']);
                $localPath = \rex_path::addonAssets('uikit_theme_builder', 'fonts/' . $sanitized . '.css');
                if (!file_exists($localPath)) {
                    continue;
                }
                $localUrl = \rex_url::addonAssets('uikit_theme_builder', 'fonts/' . $sanitized . '.css');
                $imports[] = "@import url('{$localUrl}');";
            }

            if (!empty($imports)) {
                $less .= "// Google Fonts Import (lokal gehostet)\n";
                $less .= implode("\n", $imports) . "\n\n";
            }
        }

        return $less;
    }
}