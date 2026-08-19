/**
 * UIKit Icon Picker
 * Simple CSS-class based icon picker with UIKit Modal
 * Usage: <input type="text" class="uk-iconpicker" />
 */

(function() {
    'use strict';

    const DEBUG_PREFIX = '[uikit-icon-picker]';

    function debugLog(level, message, payload) {
        if (typeof console === 'undefined') {
            return;
        }

        if (payload === undefined) {
            console[level](DEBUG_PREFIX + ' ' + message);
            return;
        }

        console[level](DEBUG_PREFIX + ' ' + message, payload);
    }

    function debugSnapshot(context) {
        const icons = Array.isArray(window.uikitAvailableIcons) ? window.uikitAvailableIcons : [];
        const categories = {};

        icons.forEach(icon => {
            const cat = (icon && icon.category) ? icon.category : 'other';
            categories[cat] = (categories[cat] || 0) + 1;
        });

        console.groupCollapsed(DEBUG_PREFIX + ' Snapshot: ' + context);
        debugLog('log', 'UIkit verfügbar: ' + !!window.UIkit + ', UIkitIcons verfügbar: ' + !!window.UIkitIcons);
        debugLog('log', 'uikitAvailableIcons Typ', typeof window.uikitAvailableIcons);
        debugLog('log', 'uikitAvailableIcons Länge', icons.length);
        debugLog('log', 'Erste 10 Icons', icons.slice(0, 10).map(icon => icon && icon.name ? icon.name : icon));
        debugLog('log', 'Kategorien', categories);
        console.groupEnd();
    }
    
    window.uikitAvailableIcons = window.uikitAvailableIcons || [];
    
    /**
     * Icon Picker initialisieren
     */
    function initIconPickers() {
        const pickerInputs = document.querySelectorAll('input.uk-iconpicker:not([data-iconpicker-init])');
        debugLog('log', 'Zu initialisierende Inputs', pickerInputs.length);

        pickerInputs.forEach(input => {
            input.setAttribute('data-iconpicker-init', 'true');
            createIconPicker(input);
        });
    }
    
    /**
     * Icon Picker für Input erstellen
     */
    function createIconPicker(inputElement) {
        debugLog('log', 'createIconPicker für Input', {
            id: inputElement.id || null,
            name: inputElement.name || null,
            value: inputElement.value || ''
        });

        const wrapper = document.createElement('div');
        wrapper.className = 'uk-inline uk-width-1-1';
        wrapper.style.position = 'relative';
        
        // Preview Icon (links im Input)
        const iconPreview = document.createElement('span');
        iconPreview.className = 'uk-form-icon';
        iconPreview.setAttribute('data-icon-preview', '');
        iconPreview.style.pointerEvents = 'none';
        if (inputElement.value) {
            iconPreview.setAttribute('uk-icon', 'icon: ' + inputElement.value + '; ratio: 0.8');
        }
        
        // Input vorbereiten
        inputElement.classList.remove('uk-iconpicker');
        inputElement.classList.add('uk-input');
        if (!inputElement.classList.contains('uk-form-width-medium') && 
            !inputElement.classList.contains('uk-form-width-large') &&
            !inputElement.classList.contains('uk-form-width-small')) {
            inputElement.classList.add('uk-form-width-medium');
        }
        inputElement.style.paddingLeft = '40px';
        inputElement.style.cursor = 'pointer';
        inputElement.readOnly = true;
        
        // Modal ID
        const modalId = 'iconpicker-modal-' + Math.random().toString(36).substr(2, 9);
        
        // Modal erstellen
        const modal = createModal(modalId, inputElement, iconPreview);
        
        // Event: Input öffnet Modal
        inputElement.addEventListener('click', function(e) {
            e.preventDefault();
            const scrollY = window.scrollY;
            modal.style.display = 'flex';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${scrollY}px`;
            document.body.style.width = '100%';
            modal.setAttribute('data-scroll-y', scrollY);
        });
        
        // DOM aufbauen - Input durch Wrapper ersetzen
        const parent = inputElement.parentNode;
        parent.insertBefore(wrapper, inputElement);
        wrapper.appendChild(iconPreview);
        wrapper.appendChild(inputElement);
        
        // Modal an Body anhängen
        document.body.appendChild(modal);
    }
    
    /**
     * Eigenständiges Modal erstellen
     */
    function createModal(modalId, inputElement, iconPreview) {
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.style.cssText = `
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 999999;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
        `;
        
        const allIcons = window.uikitAvailableIcons;
        const categories = getCategories();

        if (!Array.isArray(allIcons)) {
            debugLog('error', 'uikitAvailableIcons ist kein Array', allIcons);
        } else if (allIcons.length === 0) {
            debugLog('warn', 'Icon-Liste ist leer. Modal zeigt daher keine Einträge an.');
            debugSnapshot('empty-list-before-render');
        } else {
            debugLog('log', 'Icons vor Rendern', allIcons.length);
        }
        
        // Icons Grid HTML
        let iconsHtml = '';
        allIcons.forEach(icon => {
            const badge = icon.source === 'custom' ? '<span class="uk-badge uk-badge-success" style="position: absolute; top: 5px; right: 5px; font-size: 9px;">Custom</span>' : '';
            iconsHtml += `
                <div class="icon-item" data-icon="${icon.name}" data-category="${icon.category}" 
                     style="position: relative; display: inline-block; width: 90px; padding: 15px; text-align: center; cursor: pointer; border: 1px solid #e5e5e5; margin: 5px; transition: all 0.3s; background: white;">
                    ${badge}
                    <span uk-icon="icon: ${icon.name}; ratio: 2"></span>
                    <div style="font-size: 10px; margin-top: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${icon.name}</div>
                </div>
            `;
        });
        
        // Category Buttons
        let categoryBtns = '<button type="button" class="uk-button uk-button-small uk-button-primary" data-cat="all">Alle (' + allIcons.length + ')</button>';
        Object.keys(categories).sort().forEach(cat => {
            const count = categories[cat];
            const label = cat.charAt(0).toUpperCase() + cat.slice(1);
            categoryBtns += `<button type="button" class="uk-button uk-button-small uk-button-default uk-margin-small-left" data-cat="${cat}">${label} (${count})</button>`;
        });
        
        modal.innerHTML = `
            <div class="modal-dialog" style="background: white; border-radius: 8px; width: 90%; max-width: 1200px; max-height: 90vh; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.3); margin: 20px auto;">
                <div class="modal-header" style="padding: 20px; border-bottom: 1px solid #e5e5e5; display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0; font-size: 24px;">Icon auswählen</h2>
                    <button type="button" class="modal-close" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #999; line-height: 1;">&times;</button>
                </div>
                <div class="modal-body" style="padding: 20px; overflow-y: auto; max-height: calc(90vh - 140px);">
                    <div style="margin-bottom: 15px;">
                        <input type="text" class="uk-input" placeholder="Icon suchen..." data-search="icons" style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        ${categoryBtns}
                    </div>
                    <div class="icons-grid" style="min-height: 400px;">
                        ${iconsHtml}
                    </div>
                </div>
            </div>
        `;
        
        // Event Listeners
        setupModalEvents(modal, inputElement, iconPreview);

        debugLog('log', 'Modal erstellt', {
            modalId: modalId,
            iconItems: modal.querySelectorAll('.icon-item').length,
            categories: Object.keys(categories).length
        });
        
        return modal;
    }
    
    /**
     * Kategorien zählen
     */
    function getCategories() {
        const cats = {};
        window.uikitAvailableIcons.forEach(icon => {
            const cat = icon.category || 'other';
            cats[cat] = (cats[cat] || 0) + 1;
        });
        return cats;
    }
    
    /**
     * Modal Events
     */
    function setupModalEvents(modal, inputElement, iconPreview) {
        const searchInput = modal.querySelector('[data-search="icons"]');
        const categoryBtns = modal.querySelectorAll('[data-cat]');
        const iconItems = modal.querySelectorAll('.icon-item');
        const closeBtn = modal.querySelector('.modal-close');
        
        // Modal schließen
        function closeModal() {
            const scrollY = modal.getAttribute('data-scroll-y');
            modal.style.display = 'none';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            window.scrollTo(0, parseInt(scrollY || '0'));
            debugLog('log', 'Modal geschlossen', { modalId: modal.id });
        }
        
        closeBtn.addEventListener('click', closeModal);
        
        // Backdrop Click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // ESC Key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeModal();
            }
        });
        
        // Suche
        searchInput.addEventListener('input', function() {
            const search = this.value.toLowerCase();
            let visibleCount = 0;
            iconItems.forEach(item => {
                const iconName = item.getAttribute('data-icon').toLowerCase();
                const isVisible = iconName.includes(search);
                item.style.display = isVisible ? 'inline-block' : 'none';
                if (isVisible) {
                    visibleCount++;
                }
            });

            debugLog('log', 'Suche angewendet', {
                modalId: modal.id,
                search: search,
                visibleCount: visibleCount,
                total: iconItems.length
            });
        });
        
        // Kategorie Filter
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const cat = this.getAttribute('data-cat');
                
                // Button State
                categoryBtns.forEach(b => b.classList.remove('uk-button-primary'));
                categoryBtns.forEach(b => b.classList.add('uk-button-default'));
                this.classList.remove('uk-button-default');
                this.classList.add('uk-button-primary');
                
                // Filter
                iconItems.forEach(item => {
                    const itemCat = item.getAttribute('data-category');
                    item.style.display = (cat === 'all' || itemCat === cat) ? 'inline-block' : 'none';
                });

                const visibleCount = Array.from(iconItems).filter(item => item.style.display !== 'none').length;
                debugLog('log', 'Kategorie-Filter angewendet', {
                    modalId: modal.id,
                    category: cat,
                    visibleCount: visibleCount,
                    total: iconItems.length
                });
            });
        });
        
        // Icon Selection
        iconItems.forEach(item => {
            item.addEventListener('click', function() {
                const iconName = this.getAttribute('data-icon');
                
                // Input aktualisieren
                inputElement.value = iconName;
                
                // Preview aktualisieren
                iconPreview.setAttribute('uk-icon', 'icon: ' + iconName + '; ratio: 0.8');
                if (window.UIkit && window.UIkit.icon) {
                    window.UIkit.icon(iconPreview);
                }

                debugLog('log', 'Icon ausgewählt', {
                    modalId: modal.id,
                    icon: iconName
                });
                
                // Modal schließen
                closeModal();
            });
            
            // Hover Effect
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f8f8';
                this.style.transform = 'scale(1.05)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'white';
                this.style.transform = '';
            });
        });
    }
    
    /**
     * Initialisierung
     */
    function init() {
        debugSnapshot('init-start');

        // UIKit Icons initialisieren
        if (window.UIkit && (window.UIkitIcons || window.UIkitIconsExtended)) {
            const iconPlugin = window.UIkitIcons || window.UIkitIconsExtended;
            window.UIkit.use(iconPlugin);
            debugLog('log', 'window.UIkit.use(iconPlugin) ausgeführt', {
                plugin: window.UIkitIcons ? 'UIkitIcons' : 'UIkitIconsExtended'
            });
        } else {
            debugLog('warn', 'UIkit oder UIkitIcons fehlt beim Init', {
                hasUIkit: !!window.UIkit,
                hasUIkitIcons: !!window.UIkitIcons,
                hasUIkitIconsExtended: !!window.UIkitIconsExtended
            });
        }
        
        // Icon Pickers initialisieren
        initIconPickers();

        const initializedInputs = document.querySelectorAll('input[data-iconpicker-init="true"]').length;
        debugLog('log', 'Init abgeschlossen', { initializedInputs: initializedInputs });
    }
    
    // Backend: jQuery rex:ready
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('rex:ready', init);
    }
    
    // Frontend: DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Export
    window.UikitIconPicker = {
        init: initIconPickers
    };
    
})();
