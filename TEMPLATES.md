# UIKit Theme Builder - Templates Dokumentation

## Überblick

Der UIKit Theme Builder bietet nun ein flexibles Template-System mit mitgelieferten, professionellen Templates. Das **uikit_default.php** Template enthält alle wichtigen Komponenten und lässt sich über den Template Manager vollständig konfigurieren.

## Template Manager Integration

Das Template wird über die DOMAIN_SETTINGS konfiguriert. Es unterstützt:

### Navigation & Struktur
- **tm_navbar_sticky** (Checkbox): Sticky-Navigation beim Scrollen aktivieren
- **tm_nav_style** (Select): Navigationsstil wählen:
  - `default`: Standard-Layout (Links)
  - `centered`: Zentrierte Navigation
  - `right`: Rechtsbündige Navigation

### Logo & Branding
- **tm_logo** (Media): Logo-Datei
- **tm_logo_text** (Text): Alt-Text für das Logo
- **tm_slogan** (Text): Slogan im Footer
- **tm_firma** (Text): Firmenname für Copyright

### Search It Integration (Optional)
- **tm_search_enabled** (Checkbox): Suchfeld in der Navbar anzeigen
- **tm_search_article** (Article): Artikel-ID der Search-It Ergebnisseite
- **tm_search_placeholder** (Text): Platzhaltertext im Suchfeld

### Call-to-Action Button
- **tm_cta_enabled** (Checkbox): CTA-Button anzeigen
- **tm_cta_text** (Text): Button-Text
- **tm_cta_link** (Text): Button-Ziel
- **tm_cta_icon** (Text): UIKit Icon-Name
- **tm_cta_button_style** (Select): Button-Stil:
  - `uk-button-primary`: Primärfarbe (empfohlen)
  - `uk-button-secondary`: Sekundärfarbe
  - `uk-button-default`: Standard
  - `uk-button-text`: Nur Text

### Navigation Toggle
- **tm_mobile_nav_icon** (Text): Mobile Navigation Toggle Icon

### Breadcrumb
- **tm_breadcrumb_enabled** (Checkbox): Breadcrumb Navigation anzeigen

### Footer
- **tm_footer_color** (Select): Hintergrundfarbe
- **tm_footer_section1_title** bis **tm_footer_section3_title**: Spalten-Überschriften
- **tm_footer_section1_links** bis **tm_footer_section3_links**: Komma-getrennte Artikel-IDs
- **tm_footer_section3_text** (CKE5): Kontakt-Informationen

## Features

### Responsive Design
- Desktop-Navigation mit Dropdown-Unterstützung
- Mobile Offcanvas-Navigation (Slide-In)
- Automatisches Responsive-Verhalten via UIKit

### Accessibility (A11y)
- Semantisches HTML mit ARIA-Labeln
- Skip-to-Main-Content Link
- Keyboard Navigation Support
- Focus-Visible Styles
- Breadcrumb Navigation

### Mobile-First Approach
- Mobile Suchfeld optional
- CTA Button auf Mobile vollbreit
- Optimierte Touch-Targets

### Suchfeld Integration
Das Suchfeld ist vollständig optional und integriert sich nahtlos:
- Desktop: Expandierendes Suchfeld in der Navbar
- Mobile: Separater Suchbereich im Offcanvas
- Search It kompatibel
- Konfigurierbare Zielartikel

## Installation

### Via Backend (Template Manager)
1. Gehe zu *UIKit Theme Builder → Template Verwaltung*
2. Wähle das gewünschte Template
3. Klicke "Installieren"
4. Das Template wird nach `redaxo/templates/` kopiert

### Manuell
```bash
cp uikit_default.php /redaxo/templates/
```

## Verwendung

1. Erstelle einen neuen Artikel und nutze das Template
2. Konfiguriere die Einstellungen im Template Manager (Reiter: Domains)
3. Die Navbar passt sich automatisch an die Einstellungen an

## Navbar-Stile

### Beispiel 1: Standard-Layout (Logo + Menü + CTA)
```
Logo [Links] [Menü] [Suchfeld] [CTA-Button]
```

### Beispiel 2: Zentriert
```
Logo | [Menü zentriert] | [CTA-Button]
```

### Beispiel 3: Kompakt mit Suchfeld
```
Logo | [Kompaktes Menü] [Suchfeld] [CTA]
```

## CSS Klassen

Alle UIKit-Standard-Klassen werden unterstützt:

```php
// Navbar Container
.uk-navbar-container
.uk-navbar-sticky

// Navigation
.uk-navbar-left
.uk-navbar-center
.uk-navbar-right
.uk-navbar-nav

// Button Stile
.uk-button-primary
.uk-button-secondary
.uk-button-default
.uk-button-text

// Suchfeld
.uk-search-navbar
.uk-search-default
```

## Customization

### Navbar-Hintergrund ändern
Im Template Manager oder via CSS:
```css
.uk-navbar-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### Button-Farben
```css
.uk-button-primary {
    background-color: #your-color;
}
```

### Suchfeld Style
```css
.uk-search-navbar .uk-search-input {
    border-radius: 20px;
}
```

## Best Practices

1. **Logo-Größe**: Verwende SVG-Dateien für bessere Skalierung
2. **Navigation**: Nicht mehr als 5-7 Hauptmenü-Punkte für Desktop
3. **CTA-Button**: Beschriftung sollte kurz und prägnant sein (max. 3 Worte)
4. **Suchfeld**: Nur aktivieren, wenn Search It installiert ist
5. **Sticky-Navigation**: Auf großen Screens testen, um Performance-Probleme zu vermeiden

## Fehlerbehandlung

### Suchfeld funktioniert nicht
- Search It Addon installiert? (`rex_addon::exists('searchit')`)
- Gültige Artikel-ID für Suchergebnisse hinterlegt?
- Checkbox in Template Manager aktiviert?

### Navigation wird nicht angezeigt
- Navigation Array über BuildArray erstellt?
- Genügend veröffentlichte Kategorien?
- YRewrite aktiviert?

### Sticky-Navigation flackert
- Cache leeren
- Performance-Test mit Browser DevTools durchführen

## Performance-Tipps

1. **CSS Komprimierung**: Templates.css ist bereits optimiert
2. **Icon-Rendering**: UIKit Icons werden via Sprite geladen (effizient)
3. **Search-Integration**: Lazy-Loading des Suchfeldes möglich
4. **Fonts**: Google Fonts werden via DomainContext gecacht

## Support & Weitere Templates

Zusätzliche Templates können im `templates/` Ordner abgelegt und über den Template Manager installiert werden. Jedes Template sollte die DOMAIN_SETTINGS dokumentieren.

---

**Letzte Aktualisierung**: Januar 2026
