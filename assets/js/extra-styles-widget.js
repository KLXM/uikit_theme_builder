/**
 * Extra Styles Widget JavaScript
 */

let extraStyleIndex = 0;
let activeColorPicker = null;

/**
 * Initialisiert einen Pickr Color Picker für Extra Style Farbfelder
 * Exakt gleiche Konfiguration wie AbstractWidget::renderColorPicker()
 */
function initExtraStylePickr(pickrId, defaultValue, styleIndex) {
    if (typeof Pickr === 'undefined') {
        setTimeout(() => initExtraStylePickr(pickrId, defaultValue, styleIndex), 100);
        return;
    }
    const el = document.getElementById(pickrId);
    const hiddenInput = document.getElementById(pickrId + '-value');
    if (!el || !hiddenInput) {
        setTimeout(() => initExtraStylePickr(pickrId, defaultValue, styleIndex), 100);
        return;
    }
    if (el.dataset.pickrInitialized) return;
    el.dataset.pickrInitialized = 'true';

    const pickr = Pickr.create({
        el: el,
        theme: 'classic',
        default: defaultValue || '#ffffff',
        defaultRepresentation: 'RGBA',
        swatches: [
            'transparent',
            '#ffffff',
            '#000000',
            '#1e87f0',
            '#f0506e',
            '#32d296',
            '#faa05a',
            '#664dc6',
            '#222222',
            '#666666',
            '#999999',
            '#cccccc',
            '#e5e5e5',
            '#f8f9fa'
        ],
        components: {
            preview: true,
            opacity: true,
            hue: true,
            interaction: {
                hex: true,
                rgba: true,
                hsla: true,
                hsva: true,
                input: true,
                save: true
            }
        }
    });

    const updateColor = (color) => {
        try {
            const rgba = color.toRGBA();
            let colorString;
            if (rgba[3] >= 0.99) {
                colorString = color.toHEXA().toString().slice(0, 7);
            } else {
                colorString = color.toRGBA().toString();
            }
            hiddenInput.value = colorString;
            hiddenInput.dispatchEvent(new Event('change'));
            updatePreview(styleIndex);
        } catch(e) {
            console.warn('Fehler bei Farbkonvertierung:', e);
        }
    };

    pickr.on('change', updateColor);
    pickr.on('changestop', updateColor);
    pickr.on('save', (color) => {
        updateColor(color);
        pickr.hide();
    });
}

// Initialisierung
$(document).on("rex:ready", function() {
    initializeExtraStyles();
    
    // Fallback
    setTimeout(() => {
        if (document.querySelectorAll('.extra-style-item').length > 0) {
            initializeExtraStyles();
        }
    }, 500);
});

function initializeExtraStyles() {
    // Höchsten Index ermitteln
    const existingItems = document.querySelectorAll(".extra-style-item[data-index]");
    let realMaxIndex = -1;
    
    existingItems.forEach(item => {
        const index = parseInt(item.dataset.index);
        if (!isNaN(index) && index > realMaxIndex) {
            realMaxIndex = index;
        }
        
        // Pickr für alle Farbfelder in diesem Item initialisieren
        ['background-color', 'text-color', 'link-color', 'border-color'].forEach(fieldSlug => {
            const pickrId = `extrastyle-${index}-${fieldSlug}`;
            const hiddenInput = document.getElementById(pickrId + '-value');
            const defaultValue = hiddenInput ? (hiddenInput.value || '#ffffff') : '#ffffff';
            initExtraStylePickr(pickrId, defaultValue, index);
        });
        
        // Event-Handler für alle Input-Felder hinzufügen
        const inputs = item.querySelectorAll("input, select");
        inputs.forEach(function(input) {
            ['change'].forEach(eventType => {
                input.addEventListener(eventType, function() { 
                    updatePreview(index); 
                });
            });
        });
        
        // Preview für bestehende Items aktualisieren
        updatePreview(index);
    });
    
    if (realMaxIndex > extraStyleIndex) {
        extraStyleIndex = realMaxIndex;
    }
}

function addExtraStyle() {
    extraStyleIndex++;
    const container = document.getElementById("extra-styles-container");
    
    const newItem = document.createElement("div");
    newItem.innerHTML = generateStyleItemHTML(extraStyleIndex);
    
    const newElement = newItem.firstElementChild;
    container.appendChild(newElement);
    
    // Animation
    newElement.classList.add("new-item");
    
    // Preview, Event-Handler und Pickr initialisieren
    setTimeout(function() {
        console.log(`Initializing new style item ${extraStyleIndex}`);
        
        // Pickr für die neuen Farbfelder initialisieren
        initExtraStylePickr(`extrastyle-${extraStyleIndex}-background-color`, '#ffffff', extraStyleIndex);
        initExtraStylePickr(`extrastyle-${extraStyleIndex}-text-color`, '#333333', extraStyleIndex);
        initExtraStylePickr(`extrastyle-${extraStyleIndex}-link-color`, '#1e87f0', extraStyleIndex);
        initExtraStylePickr(`extrastyle-${extraStyleIndex}-border-color`, '#e5e5e5', extraStyleIndex);
        
        // Event-Handler für alle Input-Felder hinzufügen
        const newItemElement = container.querySelector(`[data-index="${extraStyleIndex}"]`);
        if (newItemElement) {
            const inputs = newItemElement.querySelectorAll("input, select");
            inputs.forEach(function(input) {
                ['input', 'change'].forEach(eventType => {
                    input.addEventListener(eventType, function() { 
                        updatePreview(extraStyleIndex); 
                    });
                });
            });
        }
        
        // Preview sofort aktualisieren
        updatePreview(extraStyleIndex);
        console.log("Added new extra style with index:", extraStyleIndex);
    }, 100);
}

function generateStyleItemHTML(index) {
    return `
<div class="extra-style-item uk-margin" data-index="${index}">
    <div class="uk-card uk-card-default uk-margin">
        <div class="uk-card-header">
            <div class="uk-flex uk-flex-between uk-flex-middle">
                <div class="uk-flex uk-flex-middle">
                    <label class="uk-margin-small-right" title="Aktiviert/Deaktiviert">
                        <input type="checkbox" name="extra_styles[styles][${index}][enabled]" value="1" checked class="uk-checkbox" onchange="toggleExtraStyle(${index})">
                    </label>
                    <div>
                        <h4 class="uk-margin-remove uk-text-bold">Unbenannt</h4>
                        <span class="uk-label uk-label-default uk-margin-small-left">Background</span>
                    </div>
                </div>
                <button type="button" class="uk-button uk-button-danger uk-button-small" onclick="removeExtraStyle(${index})" title="Löschen">
                    <span uk-icon="trash"></span>
                </button>
            </div>
        </div>
        
        <div class="uk-card-body">
            <div class="uk-grid uk-grid-small" uk-grid>
                <div class="uk-width-2-3@m">
                    <div class="uk-grid uk-grid-small uk-child-width-1-2@s" uk-grid>
                        <div class="uk-width-1-1">
                            <label class="uk-form-label">Name</label>
                            <input type="text" name="extra_styles[styles][${index}][name]" value="" class="uk-input uk-form-small" placeholder="z.B. Hero Section" oninput="updateStyleName(${index}, this.value); generateSlug(${index})">
                        </div>
                        <div>
                            <label class="uk-form-label">Slug</label>
                            <input type="text" name="extra_styles[styles][${index}][slug]" value="" class="uk-input uk-form-small" placeholder="hero-section" oninput="updatePreview(${index})">
                        </div>
                        <div>
                            <label class="uk-form-label">Typ</label>
                            <select name="extra_styles[styles][${index}][type]" class="uk-select uk-form-small" onchange="updateStyleType(${index}); updatePreview(${index})">
                                <option value="background">Background</option>
                                <option value="card">Card</option>
                                <option value="section">Section</option>
                                <option value="button">Button</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="uk-form-label">Hintergrundfarbe</label>
                            <div class="pickr-el" id="extrastyle-${index}-background-color"></div>
                            <input type="hidden" id="extrastyle-${index}-background-color-value" name="extra_styles[styles][${index}][background_color]" value="#ffffff">
                        </div>
                        
                        <div>
                            <label class="uk-form-label">Textfarbe <small class="uk-text-meta">(optional)</small></label>
                            <div class="pickr-el" id="extrastyle-${index}-text-color"></div>
                            <input type="hidden" id="extrastyle-${index}-text-color-value" name="extra_styles[styles][${index}][text_color]" value="#333333">
                        </div>
                        
                        <div>
                            <label class="uk-form-label">Linkfarbe <small class="uk-text-meta">(optional)</small></label>
                            <div class="pickr-el" id="extrastyle-${index}-link-color"></div>
                            <input type="hidden" id="extrastyle-${index}-link-color-value" name="extra_styles[styles][${index}][link_color]" value="#1e87f0">
                        </div>
                        
                        <div>
                            <label class="uk-form-label">Rahmenfarbe</label>
                            <div class="pickr-el" id="extrastyle-${index}-border-color"></div>
                            <input type="hidden" id="extrastyle-${index}-border-color-value" name="extra_styles[styles][${index}][border_color]" value="#e5e5e5">
                        </div>
                        
                        <div>
                            <label class="uk-form-label">Rahmenstärke</label>
                            <div class="uk-flex uk-flex-middle">
                                <input type="number" name="extra_styles[styles][${index}][border_width]" value="0" class="uk-input uk-form-small uk-width-expand" min="0" max="20" oninput="updatePreview(${index})">
                                <span class="uk-text-small uk-margin-small-left">px</span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="uk-form-label">Eckenradius</label>
                            <input type="text" name="extra_styles[styles][${index}][border_radius]" value="8px" class="uk-input uk-form-small" placeholder="8px" oninput="updatePreview(${index})">
                        </div>
                        
                        <div>
                            <label class="uk-form-label">Backdrop Blur</label>
                            <div class="uk-flex uk-flex-middle">
                                <input type="number" name="extra_styles[styles][${index}][backdrop_blur]" value="0" class="uk-input uk-form-small uk-width-expand" min="0" max="50" oninput="updatePreview(${index})">
                                <span class="uk-text-small uk-margin-small-left">px</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="uk-width-1-3@m">
                    <div class="uk-margin-bottom">
                        <h5 class="uk-text-center uk-margin-remove">Live-Vorschau</h5>
                        <div id="preview-${index}" class="extra-style-preview uk-padding uk-margin-small-top uk-text-center" style="min-height: 150px; border: 1px solid #e5e5e5; border-radius: 5px;">
                            <h6 class="uk-h6 uk-margin-small">Beispiel Titel</h6>
                            <p class="uk-text-small uk-margin-small">Live-Vorschau deines Extra Styles.</p>
                            <a href="#" class="uk-link uk-text-small">Beispiel Link</a>
                            <div class="uk-margin-small-top">
                                <span class="uk-label uk-label-success uk-text-small">Preview</span>
                            </div>
                        </div>
                    </div>
                    
                    <details>
                        <summary class="uk-text-small uk-text-meta">CSS-Code</summary>
                        <pre id="css-${index}" class="uk-background-muted uk-padding-small uk-text-small" style="font-size: 10px; max-height: 150px; overflow: auto;"></pre>
                    </details>
                </div>
            </div>
        </div>
    </div>
</div>`;
}

function removeExtraStyle(index) {
    if (confirm("Extra Style wirklich löschen?")) {
        const item = document.querySelector(`[data-index="${index}"]`);
        if (item) {
            item.remove();
        }
    }
}

function updateStyleName(index, name) {
    const item = document.querySelector(`[data-index="${index}"]`);
    const nameDisplay = item.querySelector("h4");
    if (nameDisplay) {
        nameDisplay.textContent = name || "Unbenannt";
    }
    updatePreview(index);
}

function updateStyleType(index) {
    const item = document.querySelector(`[data-index="${index}"]`);
    const select = item.querySelector('select[name*="[type]"]');
    const badge = item.querySelector(".uk-label");
    
    if (select && badge) {
        const type = select.value;
        badge.className = `uk-label uk-label-${getTypeBadgeColor(type)} uk-margin-small-left`;
        badge.textContent = getTypeLabel(type);
    }
}

function generateSlug(index) {
    const item = document.querySelector(`[data-index="${index}"]`);
    const nameInput = item.querySelector('input[name*="[name]"]');
    const slugInput = item.querySelector('input[name*="[slug]"]');
    
    if (nameInput && slugInput && nameInput.value && !slugInput.dataset.manuallyEdited) {
        const slug = nameInput.value
            .toLowerCase()
            .replace(/[äöüß]/g, c => ({ä:"ae",ö:"oe",ü:"ue",ß:"ss"}[c] || c))
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/^-+|-+$/g, "");
        
        slugInput.value = slug;
        updatePreview(index);
    }
}

function updatePreview(index) {
    const item = document.querySelector(`[data-index="${index}"]`);
    const preview = document.getElementById(`preview-${index}`);
    const cssCode = document.getElementById(`css-${index}`);
    
    if (!preview || !item) {
        console.log(`Preview elements not found for index ${index}`);
        return;
    }
    
    // Werte sammeln mit Debug-Output
    const getValue = (name) => {
        const input = item.querySelector(`[name*="[${name}]"]`);
        const value = input ? input.value : "";
        console.log(`getValue(${name}):`, value);
        return value;
    };
    
    const name = getValue("name");
    const slug = getValue("slug");
    const type = getValue("type");
    const backgroundColor = getValue("background_color") || "#ffffff";
    const textColor = getValue("text_color");
    const linkColor = getValue("link_color");
    const borderColor = getValue("border_color") || "#e5e5e5";
    const borderWidth = parseInt(getValue("border_width")) || 0;
    const borderRadius = getValue("border_radius") || "0";
    const backdropBlur = parseInt(getValue("backdrop_blur")) || 0;
    
    console.log(`Updating preview ${index}:`, {
        backgroundColor, textColor, linkColor, borderColor, 
        borderWidth, borderRadius, backdropBlur
    });
    
    // Styles anwenden mit sichtbaren Änderungen
    preview.style.backgroundColor = backgroundColor;
    preview.style.border = borderWidth > 0 ? `${borderWidth}px solid ${borderColor}` : "1px solid #e5e5e5";
    preview.style.borderRadius = borderRadius;
    
    if (backdropBlur > 0) {
        preview.style.backdropFilter = `blur(${backdropBlur}px)`;
        preview.style.webkitBackdropFilter = `blur(${backdropBlur}px)`;
    } else {
        preview.style.backdropFilter = "";
        preview.style.webkitBackdropFilter = "";
    }
    
    // Textfarbe automatisch oder manuell
    const finalTextColor = textColor || (getBrightness(backgroundColor) > 128 ? "#333333" : "#ffffff");
    preview.style.color = finalTextColor;
    
    // Linkfarbe
    const link = preview.querySelector("a");
    if (link) {
        link.style.color = linkColor || "#1e87f0";
    }
    
    // CSS Code generieren
    if (cssCode && slug) {
        cssCode.textContent = generateCSSCode({
            name, slug, type, backgroundColor, textColor,
            linkColor, borderColor, borderWidth, borderRadius, backdropBlur
        });
    }
    
    console.log(`Preview ${index} updated successfully`);
}

function toggleExtraStyle(index) {
    const item = document.querySelector(`[data-index="${index}"]`);
    const checkbox = item.querySelector('input[name*="[enabled]"]');
    const card = item.querySelector(".uk-card");
    
    if (checkbox && card) {
        card.style.opacity = checkbox.checked ? "1" : "0.5";
        card.style.filter = checkbox.checked ? "none" : "grayscale(50%)";
    }
}

function getBrightness(color) {
    if (!color) return 128;
    // Handle rgba() / rgb() format
    const rgbaMatch = color.match(/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/);
    if (rgbaMatch) {
        const r = parseInt(rgbaMatch[1]);
        const g = parseInt(rgbaMatch[2]);
        const b = parseInt(rgbaMatch[3]);
        return ((r * 299) + (g * 587) + (b * 114)) / 1000;
    }
    // Handle hex format
    const hex = color.replace("#", "");
    if (hex.length < 6) return 128;
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    return ((r * 299) + (g * 587) + (b * 114)) / 1000;
}

function getTypeLabel(type) {
    const labels = {
        background: "Background",
        card: "Card", 
        section: "Section",
        button: "Button"
    };
    return labels[type] || type;
}

function getTypeBadgeColor(type) {
    const colors = {
        background: "default",
        card: "primary",
        section: "secondary", 
        button: "success"
    };
    return colors[type] || "default";
}

function generateCSSCode(style) {
    if (!style.slug) return "/* Slug fehlt */";
    
    const className = `.uk-${style.type}-${style.slug}`;
    let css = `${className} {\n`;
    
    if (style.backgroundColor && style.backgroundColor !== "#ffffff") {
        css += `    background-color: ${style.backgroundColor};\n`;
    }
    
    if (style.textColor) {
        css += `    color: ${style.textColor};\n`;
    }
    
    if (style.borderWidth > 0) {
        css += `    border: ${style.borderWidth}px solid ${style.borderColor || "#e5e5e5"};\n`;
    }
    
    if (style.borderRadius && style.borderRadius !== "0") {
        css += `    border-radius: ${style.borderRadius};\n`;
    }
    
    if (style.backdropBlur > 0) {
        css += `    backdrop-filter: blur(${style.backdropBlur}px);\n`;
        css += `    -webkit-backdrop-filter: blur(${style.backdropBlur}px);\n`;
    }
    
    css += "}";
    
    if (style.linkColor) {
        css += `\n\n${className} a,\n${className} .uk-link {\n`;
        css += `    color: ${style.linkColor};\n`;
        css += "}";
    }
    
    return css;
}