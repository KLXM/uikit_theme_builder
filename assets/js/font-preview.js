/**
 * Font Preview für Typography Widget
 * Zeigt die ausgewählte Schriftart als Vorschau in den Select-Feldern
 */

(function() {
    'use strict';

    console.log('Font Preview Script geladen');

    // Google Fonts API URL
    const GOOGLE_FONTS_API = 'https://fonts.googleapis.com/css2?family=';
    
    // Cache für bereits geladene Fonts
    const loadedFonts = new Set();
    
    /**
     * Lädt eine Google Font dynamisch
     */
    function loadGoogleFont(fontFamily) {
        if (!fontFamily || fontFamily === 'inherit' || loadedFonts.has(fontFamily)) {
            return;
        }
        
        console.log('Lade Font:', fontFamily);
        
        // Prüfe ob es eine System-Font ist
        const systemFonts = [
            'Arial', 'Verdana', 'Helvetica', 'Tahoma', 'Trebuchet MS',
            'Times New Roman', 'Georgia', 'Garamond', 'Courier New', 'Brush Script MT'
        ];
        
        if (systemFonts.includes(fontFamily)) {
            loadedFonts.add(fontFamily);
            return;
        }
        
        // Google Font laden
        const fontUrl = GOOGLE_FONTS_API + encodeURIComponent(fontFamily) + ':400,700&display=swap';
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = fontUrl;
        document.head.appendChild(link);
        
        loadedFonts.add(fontFamily);
    }
    
    /**
     * Bereinigt Font-Family Namen (entfernt Quotes)
     */
    function cleanFontName(fontFamily) {
        return fontFamily.replace(/['"]/g, '').trim();
    }
    
    /**
     * Initialisiert Font-Preview für ein Select-Element
     */
    function initFontPreview(selectElement) {
        if (!selectElement || selectElement.classList.contains('font-preview-initialized')) {
            return;
        }
        
        console.log('Initialisiere Font-Preview für:', selectElement);
        
        selectElement.classList.add('font-preview-initialized');
        
        // Style für alle Optionen setzen
        Array.from(selectElement.options).forEach(option => {
            const fontFamily = cleanFontName(option.value);
            if (fontFamily && fontFamily !== 'inherit') {
                option.style.fontFamily = fontFamily;
                loadGoogleFont(fontFamily);
            }
        });
        
        // Style für das Select-Element selbst setzen
        function updateSelectFont() {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (selectedOption) {
                const fontFamily = cleanFontName(selectedOption.value);
                if (fontFamily && fontFamily !== 'inherit') {
                    selectElement.style.fontFamily = fontFamily;
                    loadGoogleFont(fontFamily);
                } else {
                    selectElement.style.fontFamily = '';
                }
            }
        }
        
        // Initial setzen
        updateSelectFont();
        
        // Bei Änderung aktualisieren
        selectElement.addEventListener('change', updateSelectFont);
    }
    
    /**
     * Initialisiert alle Font-Preview Selects
     */
    function initAllFontPreviews() {
        const fontSelects = document.querySelectorAll('.font-preview-select');
        console.log('Font-Preview Selects gefunden:', fontSelects.length, fontSelects);
        fontSelects.forEach(initFontPreview);
    }
    
    /**
     * UIKit Tab-Wechsel Event Handler
     * Triggert rex:ready für Icon Picker Initialisierung
     */
    function initTabSwitcher() {
        if (typeof UIkit === 'undefined') {
            return;
        }
        
        const tabs = document.querySelectorAll('[uk-tab]');
        tabs.forEach(function(tabElement) {
            UIkit.util.on(tabElement, 'shown', function() {
                // rex:ready Event triggern für Icon Picker Initialisierung
                if (typeof jQuery !== 'undefined') {
                    jQuery(document).trigger('rex:ready');
                }
                
                // Font-Previews neu initialisieren (falls neue Selects durch Tab-Wechsel sichtbar wurden)
                initAllFontPreviews();
            });
        });
    }
    
    // Initialisierung bei DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initAllFontPreviews();
            initTabSwitcher();
        });
    } else {
        // DOM bereits geladen
        initAllFontPreviews();
        initTabSwitcher();
    }
    
    // Bei rex:ready neu initialisieren (für dynamisch geladene Inhalte)
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('rex:ready', function() {
            initAllFontPreviews();
        });
    }
    
})();
