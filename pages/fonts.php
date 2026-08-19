<?php

/**
 * Google Fonts Management Page
 * Eigenständige Seite zum Verwalten und Herunterladen von Google Fonts
 */

$func = \rex_request('func', 'string');
$message = '';

// Font Manager für Downloads
$fontManager = new \UikitThemeBuilder\GoogleFontsManager();

// Actions
if ($func === 'download_font') {
    $fontFamily = \rex_request('font_family', 'string');
    $variants = \rex_request('variants', 'array');
    
    try {
        $result = $fontManager->downloadFont($fontFamily, $variants);
        if ($result['success']) {
            $message = \rex_view::success("Font '{$fontFamily}' wurde erfolgreich heruntergeladen!");
        } else {
            $message = \rex_view::error("Fehler beim Herunterladen: " . $result['message']);
        }
    } catch (Exception $e) {
        $message = \rex_view::error("Fehler: " . \rex_escape($e->getMessage()));
    }
}

if ($func === 'delete_font') {
    $fontFamily = \rex_request('font_family', 'string');
    
    try {
        $result = $fontManager->deleteFont($fontFamily);
        if ($result['success']) {
            $message = \rex_view::success("Font '{$fontFamily}' wurde gelöscht!");
        } else {
            $message = \rex_view::error("Fehler beim Löschen: " . $result['message']);
        }
    } catch (Exception $e) {
        $message = \rex_view::error("Fehler: " . \rex_escape($e->getMessage()));
    }
}

// Daten laden
$downloadedFonts = $fontManager->getDownloadedFonts();
$systemFonts = $fontManager->getSystemFonts();

// Google Fonts API Key laden
$addon = rex_addon::get('uikit_theme_builder');
$apiKey = $addon->getConfig('google_fonts_api_key', '');
$apiKey = trim($apiKey, '"\'');

// Google Fonts Liste laden (gecacht)
$googleFonts = [];
$cacheFile = rex_path::addonCache('uikit_theme_builder', 'google-fonts.json');
$cacheTime = 7 * 24 * 60 * 60; // 7 Tage

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    // Aus Cache laden
    $cachedData = json_decode(file_get_contents($cacheFile), true);
    $googleFonts = $cachedData['items'] ?? [];
} elseif (!empty($apiKey)) {
    // Von Google API laden
    try {
        $url = 'https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=' . urlencode($apiKey);
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'user_agent' => 'REDAXO UIKit Theme Builder'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['items'])) {
                $googleFonts = array_map(function($font) {
                    return [
                        'family' => $font['family'],
                        'category' => $font['category'] ?? 'sans-serif',
                        'variants' => $font['variants'] ?? ['regular']
                    ];
                }, $data['items']);
                
                // Cache speichern
                rex_file::put($cacheFile, json_encode(['items' => $googleFonts]));
            }
        }
    } catch (Exception $e) {
        // Fehler ignorieren, Fallback wird unten geladen
    }
}

// Fallback auf statische Liste
if (empty($googleFonts)) {
    $googleFonts = [
        ['family' => 'Roboto', 'category' => 'sans-serif', 'variants' => ['100', '300', 'regular', '500', '700', '900']],
        ['family' => 'Open Sans', 'category' => 'sans-serif', 'variants' => ['300', 'regular', '500', '600', '700', '800']],
        ['family' => 'Lato', 'category' => 'sans-serif', 'variants' => ['100', '300', 'regular', '700', '900']],
        ['family' => 'Montserrat', 'category' => 'sans-serif', 'variants' => ['100', '200', '300', 'regular', '500', '600', '700', '800', '900']],
        ['family' => 'Source Sans Pro', 'category' => 'sans-serif', 'variants' => ['200', '300', 'regular', '600', '700', '900']],
        ['family' => 'Raleway', 'category' => 'sans-serif', 'variants' => ['100', '200', '300', 'regular', '500', '600', '700', '800', '900']],
        ['family' => 'Poppins', 'category' => 'sans-serif', 'variants' => ['100', '200', '300', 'regular', '500', '600', '700', '800', '900']],
        ['family' => 'Nunito', 'category' => 'sans-serif', 'variants' => ['200', '300', 'regular', '600', '700', '800', '900']],
        ['family' => 'Inter', 'category' => 'sans-serif', 'variants' => ['100', '200', '300', 'regular', '500', '600', '700', '800', '900']],
        ['family' => 'Playfair Display', 'category' => 'serif', 'variants' => ['regular', '500', '600', '700', '800', '900']],
        ['family' => 'Merriweather', 'category' => 'serif', 'variants' => ['300', 'regular', '700', '900']],
        ['family' => 'Lora', 'category' => 'serif', 'variants' => ['regular', '500', '600', '700']],
        ['family' => 'Caveat', 'category' => 'handwriting', 'variants' => ['regular', '500', '600', '700']],
        ['family' => 'Dancing Script', 'category' => 'handwriting', 'variants' => ['regular', '500', '600', '700']],
        ['family' => 'Fira Code', 'category' => 'monospace', 'variants' => ['300', 'regular', '500', '600', '700']],
        ['family' => 'Roboto Mono', 'category' => 'monospace', 'variants' => ['100', '200', '300', 'regular', '500', '600', '700']],
    ];
}

echo '<div class="uk-container uk-container-expand uk-margin-top">';

// Message
if ($message) {
    echo $message;
}

// Main Grid
echo '<div class="uk-grid-large" uk-grid>';

// Google Fonts Browser (2/3)
echo '<div class="uk-width-2-3@l">';
echo '<div class="uk-card uk-card-default uk-card-large">';
echo '<div class="uk-card-header">';
echo '<div class="uk-flex uk-flex-between uk-flex-middle">';
echo '<div>';
echo '<h3 class="uk-card-title uk-margin-remove-bottom"><i class="fab fa-google"></i> Google Fonts Browser</h3>';
echo '<p class="uk-text-meta uk-margin-remove-top">Durchsuche und lade Google Fonts für lokale DSGVO-konforme Nutzung herunter</p>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="uk-card-body">';

// Search Interface
echo '<div class="uk-margin-bottom">';
echo '<div class="uk-grid-small uk-flex-middle" uk-grid>';
echo '<div class="uk-width-expand">';
echo '<div class="uk-inline uk-width-1-1">';
echo '<span class="uk-form-icon" uk-icon="icon: search"></span>';
echo '<input class="uk-input" type="text" id="font-search" placeholder="Durchsuche ' . count($googleFonts) . ' Google Fonts..." onkeyup="searchGoogleFonts(this.value)">';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>'; // Schließt uk-margin-bottom

// Category Filter
echo '<div class="uk-margin-small-bottom">';
echo '<div class="uk-button-group">';
echo '<button class="uk-button uk-button-default uk-button-small uk-active" data-category="all" onclick="filterByCategory(\'all\', this)">Alle</button>';
echo '<button class="uk-button uk-button-default uk-button-small" data-category="sans-serif" onclick="filterByCategory(\'sans-serif\', this)">Sans-Serif</button>';
echo '<button class="uk-button uk-button-default uk-button-small" data-category="serif" onclick="filterByCategory(\'serif\', this)">Serif</button>';
echo '<button class="uk-button uk-button-default uk-button-small" data-category="display" onclick="filterByCategory(\'display\', this)">Display</button>';
echo '<button class="uk-button uk-button-default uk-button-small" data-category="handwriting" onclick="filterByCategory(\'handwriting\', this)">Handwriting</button>';
echo '<button class="uk-button uk-button-default uk-button-small" data-category="monospace" onclick="filterByCategory(\'monospace\', this)">Monospace</button>';
echo '</div>';
echo '</div>';

// Preview Text Input
echo '<div class="uk-margin-small-bottom">';
echo '<div class="uk-inline uk-width-1-1">';
echo '<span class="uk-form-icon" uk-icon="icon: pencil"></span>';
echo '<input class="uk-input uk-form-small" type="text" id="preview-text" placeholder="Eigener Vorschau-Text..." value="The quick brown fox jumps over the lazy dog" onkeyup="updatePreviewText(this.value)">';
echo '</div>';
echo '<div class="uk-text-small uk-text-muted uk-margin-small-top">Dieser Text wird in allen Font-Vorschauen angezeigt</div>';
echo '</div>';

// Font Size & Weight Controls
echo '<div class="uk-margin-small-bottom">';
echo '<div class="uk-grid-small" uk-grid>';
echo '<div class="uk-width-2-3@s">';
echo '<label class="uk-form-label uk-text-small">Schriftgröße: <span id="font-size-value">14</span>px</label>';
echo '<input class="uk-range" type="range" id="font-size-slider" min="10" max="32" value="14" step="1" oninput="updateFontSize(this.value)">';
echo '</div>';
echo '<div class="uk-width-1-3@s">';
echo '<label class="uk-form-label uk-text-small">Schriftstärke</label>';
echo '<select class="uk-select uk-form-small" id="font-weight-select" onchange="updateFontWeight(this.value)">';
echo '<option value="300">Light (300)</option>';
echo '<option value="400" selected>Regular (400)</option>';
echo '<option value="500">Medium (500)</option>';
echo '<option value="600">Semi Bold (600)</option>';
echo '<option value="700">Bold (700)</option>';
echo '</select>';
echo '</div>';
echo '</div>';
echo '</div>';

// Search Results Container
echo '<div id="google-fonts-results" class="uk-margin-top" style="max-height: 600px; overflow-y: auto;">';
echo '<div class="uk-alert uk-alert-primary">Gib mindestens 2 Zeichen ein oder wähle eine Kategorie, um ' . count($googleFonts) . ' Google Fonts zu durchsuchen</div>';
echo '</div>';

echo '</div>'; // Schließt uk-card-body
echo '</div>'; // Schließt uk-card
echo '</div>'; // Schließt uk-width-2-3

// Downloaded Fonts & System Fonts (1/3)
echo '<div class="uk-width-1-3@l">';

// Downloaded Fonts
echo '<div class="uk-card uk-card-default uk-card-small uk-margin-bottom">';
echo '<div class="uk-card-header">';
echo '<h4 class="uk-card-title uk-margin-remove-bottom"><i class="fas fa-download"></i> Heruntergeladene Fonts</h4>';
echo '<p class="uk-text-small uk-text-muted uk-margin-remove-top">' . count($downloadedFonts) . ' lokal verfügbar</p>';
echo '</div>';
echo '<div class="uk-card-body">';

if (empty($downloadedFonts)) {
    echo '<div class="uk-text-center uk-text-muted uk-padding">Keine Fonts heruntergeladen</div>';
} else {
    echo '<div class="uk-overflow-auto">';
    echo '<table class="uk-table uk-table-small uk-table-striped uk-margin-remove">';
    foreach ($downloadedFonts as $fontFamily => $fontData) {
        echo '<tr style="cursor: pointer;" onclick="searchForFont(\'' . \rex_escape($fontFamily, 'js') . '\')" uk-tooltip="Klicken um in Suche zu übernehmen">';
        echo '<td>';
        echo '<div class="uk-grid-small uk-flex-middle" uk-grid>';
        echo '<div class="uk-width-expand">';
        echo '<div style="font-family: \'' . \rex_escape($fontFamily) . '\', sans-serif; font-size: 16px; font-weight: 400;">' . \rex_escape($fontFamily) . '</div>';
        echo '<div class="uk-text-small uk-text-muted" style="font-family: \'' . \rex_escape($fontFamily) . '\', sans-serif; font-size: 12px;">The quick brown fox jumps over the lazy dog</div>';
        echo '<div class="uk-text-small uk-text-muted">' . count($fontData['variants']) . ' Varianten • ' . $fontData['category'] . '</div>';
        echo '</div>';
        echo '<div class="uk-width-auto">';
        echo '<button class="uk-button uk-button-danger uk-button-small" onclick="event.stopPropagation(); deleteFont(\'' . \rex_escape($fontFamily) . '\')" uk-tooltip="Font löschen">';
        echo '<span uk-icon="icon: trash; ratio: 0.8"></span>';
        echo '</button>';
        echo '</div>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
}

echo '</div>';
echo '</div>';

// System Fonts Info
echo '<div class="uk-card uk-card-secondary uk-card-small">';
echo '<div class="uk-card-header">';
echo '<h4 class="uk-card-title uk-margin-remove-bottom uk-light"><i class="fas fa-laptop"></i> System Fonts</h4>';
echo '<p class="uk-text-small uk-light uk-margin-remove-top">' . count($systemFonts) . ' verfügbar</p>';
echo '</div>';
echo '<div class="uk-card-body uk-light">';

echo '<div class="uk-text-small uk-light uk-margin-small-bottom">Verfügbare System-Schriftarten:</div>';

$systemCategories = [];
foreach ($systemFonts as $font) {
    $category = $font['category'] ?? 'other';
    if (!isset($systemCategories[$category])) {
        $systemCategories[$category] = 0;
    }
    $systemCategories[$category]++;
}

foreach ($systemCategories as $category => $count) {
    $categoryName = match($category) {
        'serif' => 'Serif',
        'sans-serif' => 'Sans-Serif', 
        'monospace' => 'Monospace',
        'cursive' => 'Kursiv',
        'fantasy' => 'Fantasy',
        default => 'Andere'
    };
    
    echo '<div class="uk-flex uk-flex-between uk-margin-small">';
    echo '<span>' . $categoryName . '</span>';
    echo '<span class="uk-badge uk-badge-secondary">' . $count . '</span>';
    echo '</div>';
}

echo '<div class="uk-margin-top">';
echo '<div class="uk-text-small uk-light">System-Fonts sind sofort verfügbar und benötigen keinen Download.</div>';
echo '</div>';

echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';

// JavaScript
echo '<script>
let allFontsCache = ' . json_encode($googleFonts) . ';
let previewText = "The quick brown fox jumps over the lazy dog";
let currentCategory = "all";
let currentSearchQuery = "";
let currentFontSize = 14;
let currentFontWeight = 400;

console.log("Loaded " + allFontsCache.length + " fonts from PHP");

function updatePreviewText(text) {
    previewText = text || "The quick brown fox jumps over the lazy dog";
    
    // Update alle sichtbaren Previews
    document.querySelectorAll(".font-preview-text").forEach(el => {
        el.textContent = previewText;
    });
}

function updateFontSize(size) {
    currentFontSize = size;
    document.getElementById("font-size-value").textContent = size;
    
    // Update alle Font-Previews
    document.querySelectorAll(".font-preview-text").forEach(el => {
        el.style.fontSize = size + "px";
    });
}

function updateFontWeight(weight) {
    currentFontWeight = weight;
    
    // Update alle Font-Previews
    document.querySelectorAll(".font-preview-text").forEach(el => {
        el.style.fontWeight = weight;
    });
}

function searchForFont(fontName) {
    const searchInput = document.getElementById("font-search");
    searchInput.value = fontName;
    searchInput.focus();
    searchGoogleFonts(fontName);
    
    // Scroll zu den Suchergebnissen
    document.getElementById("google-fonts-results").scrollIntoView({ behavior: "smooth", block: "start" });
}

function filterByCategory(category, button) {
    currentCategory = category;
    
    // Update Button States
    document.querySelectorAll("[data-category]").forEach(btn => {
        btn.classList.remove("uk-active");
    });
    button.classList.add("uk-active");
    
    // Trigger Search mit aktueller Query (auch leer)
    const searchInput = document.getElementById("font-search");
    searchGoogleFonts(searchInput.value);
}

// Search Google Fonts locally
function searchGoogleFonts(query) {
    currentSearchQuery = query;
    const resultsDiv = document.getElementById("google-fonts-results");
    const fontSearch = document.getElementById("font-search");
    
    if (!allFontsCache || allFontsCache.length === 0) {
        UIkit.notification("Keine Fonts verfügbar", {status: "warning"});
        return;
    }
    
    // Filter nach Category
    let filtered = allFontsCache;
    if (currentCategory !== "all") {
        filtered = filtered.filter(font => font.category === currentCategory);
    }
    
    // Filter nach Suchtext
    if (query && query.length >= 2) {
        filtered = filtered.filter(font => 
            font.family.toLowerCase().includes(query.toLowerCase())
        );
    }
    
    // Sortiere alphabetisch nach Family-Name
    filtered.sort((a, b) => a.family.localeCompare(b.family));
    
    // Nur anzeigen wenn Query >= 2 Zeichen oder Category Filter aktiv
    if ((!query || query.length < 2) && currentCategory === "all") {
        resultsDiv.innerHTML = "<div class=\"uk-alert uk-alert-primary\">Bitte mindestens 2 Zeichen eingeben oder eine Kategorie wählen</div>";
        return;
    }
    
    const categoryText = currentCategory !== "all" ? ` in "${currentCategory}"` : "";
    fontSearch.placeholder = filtered.length + " von " + allFontsCache.length + " Fonts gefunden" + categoryText;
    
    displaySearchResults(filtered);
}
// Search Google Fonts locally
function searchGoogleFonts(query) {
    currentSearchQuery = query;
    const resultsDiv = document.getElementById("google-fonts-results");
    const fontSearch = document.getElementById("font-search");
    
    if (!allFontsCache || allFontsCache.length === 0) {
        UIkit.notification("Keine Fonts verfügbar", {status: "warning"});
        return;
    }
    
    // Filter nach Category
    let filtered = allFontsCache;
    if (currentCategory !== "all") {
        filtered = filtered.filter(font => font.category === currentCategory);
    }
    
    // Filter nach Suchtext
    if (query && query.length >= 2) {
        filtered = filtered.filter(font => 
            font.family.toLowerCase().includes(query.toLowerCase())
        );
    }
    
    // Sortiere alphabetisch nach Family-Name
    filtered.sort((a, b) => a.family.localeCompare(b.family));
    
    // Zeige Ergebnisse wenn Category Filter aktiv ist ODER Query >= 2 Zeichen
    if ((!query || query.length < 2) && currentCategory === "all") {
        resultsDiv.innerHTML = "<div class=\"uk-alert uk-alert-primary\">Bitte mindestens 2 Zeichen eingeben oder eine Kategorie wählen</div>";
        return;
    }
    
    const categoryText = currentCategory !== "all" ? ` in "${currentCategory}"` : "";
    fontSearch.placeholder = filtered.length + " von " + allFontsCache.length + " Fonts gefunden" + categoryText;
    
    displaySearchResults(filtered);
}

// Display search results
function displaySearchResults(fonts) {
    const resultsDiv = document.getElementById("google-fonts-results");
    
    if (fonts.length === 0) {
        resultsDiv.innerHTML = "<div class=\"uk-alert uk-alert-warning\">Keine Ergebnisse gefunden</div>";
        return;
    }
    
    let html = "<table class=\"uk-table uk-table-striped uk-table-hover uk-table-middle\">";
    html += "<thead><tr>";
    html += "<th class=\"uk-width-expand\">Schriftart</th>";
    html += "<th class=\"uk-width-medium uk-text-center\">Kategorie</th>";
    html += "<th class=\"uk-width-small uk-text-center\">Aktion</th>";
    html += "</tr></thead><tbody>";
    
    fonts.slice(0, 1000).forEach(font => {
        const isDownloaded = ' . json_encode(array_keys($downloadedFonts)) . '.includes(font.family);
        
        html += "<tr>";
        
        // Font Name mit Vorschau
        html += "<td>";
        html += "<div class=\"uk-text-large\" style=\"font-family: \'" + font.family + "\', " + font.category + "; line-height: 1.2;\">" + font.family + "</div>";
        html += "<div class=\"uk-text-small uk-text-muted font-preview-text\" style=\"font-family: \'" + font.family + "\', " + font.category + "; font-size: " + currentFontSize + "px; font-weight: " + currentFontWeight + ";\">" + previewText + "</div>";
        html += "</td>";
        
        // Kategorie
        html += "<td class=\"uk-text-center\">";
        const categoryIcon = font.category === "serif" ? "file-text" : 
                            font.category === "sans-serif" ? "minus" :
                            font.category === "monospace" ? "code" :
                            font.category === "handwriting" ? "pencil" : "font";
        html += "<span uk-icon=\"icon: " + categoryIcon + "; ratio: 0.8\" class=\"uk-margin-small-right uk-text-muted\"></span>";
        html += font.category;
        html += "</td>";
        
        // Aktion
        html += "<td class=\"uk-text-center\">";
        if (isDownloaded) {
            html += "<button class=\"uk-button uk-button-danger uk-button-small\" disabled uk-tooltip=\"Bereits geladen\">";
            html += "<span uk-icon=\"check\"></span>";
            html += "</button>";
        } else {
            html += "<button class=\"uk-button uk-button-primary uk-button-small\" onclick=\"downloadFont(\'" + font.family + "\', \'" + font.category + "\')\" uk-tooltip=\"Font herunterladen\">";
            html += "<span uk-icon=\"download\"></span>";
            html += "</button>";
        }
        html += "</td>";
        
        html += "</tr>";
    });
    
    html += "</tbody></table>";
    
    if (fonts.length > 1000) {
        html += "<div class=\"uk-alert uk-alert-primary uk-margin-top\">Zeige 1000 von " + fonts.length + " Ergebnissen. Bitte schränke die Suche weiter ein.</div>";
    }
    
    resultsDiv.innerHTML = html;
    
    // Load fonts dynamically (nur erste 100 für Performance)
    fonts.slice(0, 100).forEach(font => {
        const link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = "https://fonts.googleapis.com/css2?family=" + font.family.replace(/ /g, "+") + ":wght@400&display=swap";
        document.head.appendChild(link);
    });
}

function downloadFont(fontFamily, category) {
    // Finde den Font in der Cache-Liste um Varianten zu bekommen
    const fontData = allFontsCache.find(f => f.family === fontFamily);
    const variants = fontData ? fontData.variants : ["regular"];
    
    // Mapping von API-Varianten zu lesbaren Namen
    const variantNames = {
        "100": "Thin (100)",
        "200": "Extra Light (200)",
        "300": "Light (300)",
        "regular": "Regular (400)",
        "400": "Regular (400)",
        "500": "Medium (500)",
        "600": "Semi Bold (600)",
        "700": "Bold (700)",
        "800": "Extra Bold (800)",
        "900": "Black (900)"
    };
    
    // Filtere nur non-italic normale Weights und normalisiere "regular" → "400"
    // (Google Fonts API liefert "regular" statt "400", was die defaultChecked-Logik bricht)
    const normalWeights = variants
        .filter(v => !v.includes("italic"))
        .map(v => v === "regular" ? "400" : v);
    
    // Erstelle Checkboxen für verfügbare Weights
    let checkboxesHtml = "";
    const defaultChecked = ["300", "400", "700"];
    
    normalWeights.forEach(variant => {
        const displayName = variantNames[variant] || variant;
        const isChecked = defaultChecked.includes(variant) ? " checked" : "";
        checkboxesHtml += "<label><input class=\"uk-checkbox\" type=\"checkbox\" value=\"" + variant + "\"" + isChecked + "> " + displayName + "</label><br>";
    });
    
    // Download-Dialog mit dynamischen Varianten
    UIkit.modal.dialog(
        "<div class=\"uk-modal-header\">" +
            "<h2 class=\"uk-modal-title\">Font herunterladen: " + fontFamily + "</h2>" +
        "</div>" +
        "<div class=\"uk-modal-body\">" +
            "<p>Wähle die Schriftschnitte aus (" + normalWeights.length + " verfügbar):</p>" +
            "<div class=\"uk-margin\">" + checkboxesHtml + "</div>" +
        "</div>" +
        "<div class=\"uk-modal-footer uk-text-right\">" +
            "<button class=\"uk-button uk-button-default uk-modal-close\" type=\"button\">Abbrechen</button>" +
            "<button class=\"uk-button uk-button-primary\" onclick=\"confirmDownload(\'" + fontFamily + "\', \'" + category + "\')\">Herunterladen</button>" +
        "</div>"
    );
}

function confirmDownload(fontFamily, category) {
    const checkboxes = document.querySelectorAll(".uk-modal input[type=checkbox]:checked");
    const variants = Array.from(checkboxes).map(cb => cb.value);
    
    if (variants.length === 0) {
        UIkit.notification({
            message: "Bitte wählen Sie mindestens eine Variante aus.",
            status: "warning",
            pos: "top-right"
        });
        return;
    }
    
    // Form erstellen und abschicken
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "' . \rex_url::currentBackendPage() . '";
    
    const funcInput = document.createElement("input");
    funcInput.type = "hidden";
    funcInput.name = "func";
    funcInput.value = "download_font";
    form.appendChild(funcInput);
    
    const familyInput = document.createElement("input");
    familyInput.type = "hidden";
    familyInput.name = "font_family";
    familyInput.value = fontFamily;
    form.appendChild(familyInput);
    
    variants.forEach(variant => {
        const variantInput = document.createElement("input");
        variantInput.type = "hidden";
        variantInput.name = "variants[]";
        variantInput.value = variant;
        form.appendChild(variantInput);
    });
    
    document.body.appendChild(form);
    form.submit();
    
    UIkit.modal.dialog().hide();
}

function deleteFont(fontFamily) {
    UIkit.modal.confirm(`Möchten Sie den Font "${fontFamily}" wirklich löschen?`).then(function() {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "' . \rex_url::currentBackendPage() . '";
        
        const funcInput = document.createElement("input");
        funcInput.type = "hidden";
        funcInput.name = "func";
        funcInput.value = "delete_font";
        form.appendChild(funcInput);
        
        const familyInput = document.createElement("input");
        familyInput.type = "hidden";
        familyInput.name = "font_family";
        familyInput.value = fontFamily;
        form.appendChild(familyInput);
        
        document.body.appendChild(form);
        form.submit();
    });
}
</script>';

echo '</div>'; // container
