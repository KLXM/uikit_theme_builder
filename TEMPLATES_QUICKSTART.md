# UIKit Theme Builder - Template System (NEU)

## 🎨 Neue Features

Der UIKit Theme Builder wurde um ein professionelles **Template Management System** erweitert. Mit diesem System können Sie:

✅ **Vorgefertigte Templates** installieren  
✅ **Flexible Navbar** mit verschiedenen Darstellungsstilen  
✅ **Search It Integration** mit optionalem Suchfeld  
✅ **CTA Button Gestaltung** mit verschiedenen Styles  
✅ **Responsive Design** für alle Geräte  
✅ **Volle Konfigurierbarkeit** über Template Manager  

## 📦 Enthaltene Templates

### uikit_default.php
Ein universelles, flexibles Template mit:
- Sticky-Navigation (optional)
- 3 verschiedene Navigations-Stile
- Integriertes Suchfeld (optional)
- Anpassbare CTA-Buttons
- Responsive Mobile Navigation
- Umfangreicher Footer
- Vollständige A11y-Unterstützung

## 🚀 Quick Start

### 1. Template Installation
Gehen Sie im Backend zu:
```
UIKit Theme Builder → Template Verwaltung
```

Klicken Sie auf "Installieren" neben dem gewünschten Template.

### 2. Template in Artikel nutzen
- Erstellen Sie einen neuen Artikel
- Wählen Sie das neue Template aus
- Speichern Sie

### 3. Konfiguration
Die Konfiguration erfolgt komplett über den **Template Manager** im Reiter **Domains**:

```
UIKit Theme Builder → Einstellungen → Domain-Einstellungen
```

Dort finden Sie alle Einstellungen wie:
- Logo & Branding
- Navigation Stil
- CTA Button
- Suchfeld Aktivierung
- Footer Inhalte

## 🎯 Navbar-Konfiguration

### Navigation Stile

#### 1. Default (Standard)
```
[Logo] ← [Navigationsmenü] ↑ [Suchfeld] [CTA-Button]
```

#### 2. Centered (Zentriert)
```
[Logo] ← [Navigationsmenü zentriert] → [CTA-Button]
```

#### 3. Right (Rechtsbündig)  
```
[Logo] ← → [Navigationsmenü] [CTA-Button]
```

### CTA Button Stile

| Stil | Verwendung |
|------|-----------|
| `uk-button-primary` | Hauptaktionen (empfohlen) |
| `uk-button-secondary` | Alternative Aktionen |
| `uk-button-default` | Sekundäre Aktionen |
| `uk-button-text` | Subtile Links |

## 🔍 Search It Integration

### Aktivierung
1. **Search It Addon** muss installiert sein
2. Im Template Manager: `tm_search_enabled` = ✓ Aktiviert
3. `tm_search_article` = Artikel-ID der Ergebnisseite
4. Optional: `tm_search_placeholder` anpassen

### Features
- **Desktop**: Expandierendes Suchfeld in der Navbar
- **Mobile**: Separater Suchbereich im Offcanvas
- **Konfigurierbar**: Zielartikel frei wählbar
- **Responsive**: Automatische Anpassung an Bildschirmgröße

## 📱 Responsive Verhalten

### Desktop (ab 768px)
- Volle Navigation angezeigt
- CTA-Button sichtbar
- Suchfeld angezeigt

### Mobile (unter 768px)
- Offcanvas-Navigation (Slide-In von rechts)
- Suchfeld im Offcanvas
- CTA-Button vollbreit

## ♿ Accessibility Features

✅ Semantisches HTML mit ARIA-Labels  
✅ Skip-to-Main-Content Link  
✅ Keyboard Navigation (Tab-Navigation)  
✅ Focus-Visible Styles  
✅ Breadcrumb Navigation  
✅ Aussagekräftige alt-Attribute  

## 🛠️ Customization

### Template Manager Einstellungen

```php
// Logo & Branding
tm_logo              // Media: Logo-Datei
tm_logo_text         // Text: Logo Alt-Text
tm_slogan            // Text: Slogan im Footer
tm_firma             // Text: Firmenname

// Navigation
tm_navbar_sticky     // Checkbox: Sticky bei Scroll?
tm_nav_style         // Select: default|centered|right
tm_mobile_nav_icon   // Text: Mobile Toggle Icon

// Search It
tm_search_enabled    // Checkbox: Suchfeld aktivieren
tm_search_article    // Article: Suchergebnis-Artikel
tm_search_placeholder // Text: Placeholder

// CTA Button
tm_cta_enabled       // Checkbox: Button anzeigen
tm_cta_text          // Text: Button-Text
tm_cta_link          // Text: Button-Ziel
tm_cta_icon          // Text: UIKit Icon-Name
tm_cta_button_style  // Select: Button-Stil

// Breadcrumb
tm_breadcrumb_enabled // Checkbox: Breadcrumb anzeigen

// Footer
tm_footer_color      // Select: Hintergrundfarbe
tm_footer_section1_title // Überschrift Spalte 1
tm_footer_section1_links // Artikel-IDs (komma-getrennt)
// ... ähnlich für Spalte 2 & 3
```

### CSS Override Beispiele

```css
/* Navbar-Hintergrund */
.uk-navbar-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Button-Farbe anpassen */
.uk-button-primary {
    background-color: #your-color !important;
}

/* Suchfeld rund machen */
.uk-search-navbar .uk-search-input {
    border-radius: 20px;
}

/* Mobile Navigation Breite */
.uk-offcanvas-bar {
    width: 70%;
}
```

## 🔧 API für Entwickler

### Template Installer Klasse

```php
use UikitThemeBuilder\TemplateInstaller;

// Verfügbare Templates auflisten
$templates = TemplateInstaller::getAvailableTemplates();

// Template Metadaten auslesen
$metadata = TemplateInstaller::getTemplateMetadata('uikit_default');

// Template installieren
$success = TemplateInstaller::installTemplate('uikit_default', 'my_template');

// Alle Templates installieren
$results = TemplateInstaller::installAllTemplates();
```

### Template Manager Integration

```php
use FriendsOfRedaxo\TemplateManager\TemplateManager;

// Konfigurationswert auslesen
$logoUrl = TemplateManager::get('tm_logo', 'logo.svg');

// Mit Default-Wert
$style = TemplateManager::get('tm_cta_button_style', 'uk-button-primary');
```

## 📋 Checkliste für neue Templates

Wenn Sie ein eigenes Template erstellen:

```php
/**
 * Mein Custom Template
 * 
 * DOMAIN_SETTINGS:
 * my_setting_1: type|Label|Default|Beschreibung
 * my_setting_2: type|Label|Default|Beschreibung
 */

use FriendsOfRedaxo\TemplateManager\TemplateManager;

$value = TemplateManager::get('my_setting_1', 'default');
```

## ⚡ Performance-Optimierung

✅ CSS wird minimal komprimiert  
✅ Icons via Sprite (effizient)  
✅ Lazy-Loading möglichkeiten  
✅ Browser-Caching optimiert  
✅ Fonts werden gecacht  

## 🐛 Häufig Gestellte Fragen

### F: Warum wird das Suchfeld nicht angezeigt?
**A:** Search It muss installiert sein und die `tm_search_enabled` Checkbox muss aktiviert sein.

### F: Kann ich das Template anpassen?
**A:** Ja, kopieren Sie das Template zu `redaxo/templates/` und bearbeiten Sie es direkt.

### F: Funktioniert das Template mit YRewrite?
**A:** Ja, es ist vollständig YRewrite-kompatibel.

### F: Kann ich mehrere CTA-Buttons haben?
**A:** Derzeit einer pro Template. Für mehrere müssen Sie das Template erweitern.

## 📚 Weitere Ressourcen

- **[TEMPLATES.md](TEMPLATES.md)** - Detaillierte Template-Dokumentation
- **[Template Manager Addon](https://github.com/FriendsOfREDAXO/template_manager)** - Konfigurationsmanagement
- **[UIKit 3 Doku](https://getuikit.com)** - UIKit Component Library
- **[REDAXO Doku](https://redaxo.org)** - REDAXO CMS Dokumentation

## 🤝 Support & Beitragen

Haben Sie Fragen oder möchten Sie ein Template beitragen?

- 📧 Issues: GitHub Issues
- 💬 Diskussionen: GitHub Discussions
- 📝 Pull Requests: Gerne angenommen!

## 📄 Lizenz

Dieses Template-System folgt der gleichen Lizenz wie das UIKit Theme Builder Addon.

---

**Version:** 1.0 | **Datum:** Januar 2026 | **Autor:** UIKit Theme Builder Team
