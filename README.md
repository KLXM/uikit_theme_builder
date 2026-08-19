# UIKit Theme Builder for REDAXO

Ein professionelles REDAXO AddOn zur visuellen Erstellung und Verwaltung von UIKit 3 Themes mit Live-Preview, Google Fonts Integration und erweiterten Anpassungsoptionen.

![UIKit Theme Builder](https://img.shields.io/badge/REDAXO-5.20+-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.4+-green.svg)
![UIKit](https://img.shields.io/badge/UIKit-3.21+-orange.svg)
![License](https://img.shields.io/badge/License-MIT-yellow.svg)

## 🎯 Features

### 🎨 Visueller Theme Editor
- **Widget-basierte Konfiguration**: Modularer Aufbau mit spezialisierten Widgets für verschiedene Design-Bereiche
- **Live-Preview**: Sofortige Vorschau aller Änderungen ohne Seiten-Reload
- **Drag & Drop Interface**: Intuitive Benutzeroberfläche für einfache Theme-Erstellung
- **Multi-Domain Support**: Verschiedene Themes für verschiedene Domains

### 🎭 Umfassende Design-Kontrolle
- **Farben**: Vollständige Kontrolle über UIKit's Farbpalette
- **Typography**: Google Fonts Integration mit lokaler DSGVO-konformer Speicherung
- **Breakpoints**: Responsive Design-Konfiguration
- **Schatten**: Box-Shadow und Drop-Shadow Anpassungen
- **Container**: Responsive Container-Größen
- **Navigation**: Vollständige Navbar-Anpassung
- **Borders**: Border-Radius und Border-Width Kontrolle

### 🌐 Google Fonts Integration
- **DSGVO-konform**: Lokale Speicherung aller Font-Dateien
- **Automatischer Download**: Fonts werden automatisch heruntergeladen und optimiert
- **Performance-optimiert**: Nur verwendete Fonts werden geladen
- **Caching-System**: Effiziente Font-Verwaltung mit JSON-Cache

### 🚀 Performance & Sicherheit
- **Compile-Time Optimierung**: LESS wird zur Build-Zeit kompiliert
- **Asset-Management**: Automatische CSS/JS-Generierung und Versionierung
- **Cache-System**: Intelligentes Caching für optimale Performance
- **Debug-Modus**: Umfassende Debug-Ausgaben für Entwicklung

## 📋 Systemanforderungen

- **REDAXO**: 5.20+
- **PHP**: 8.4+
- **UIKit**: 3.21+ (siehe UIKit-Versionslimitierung unten)
- **LESS Compiler**: wikimedia/less.php 5.4+
- **Berechtigung**: Schreibrechte in `var/data/` und `public/assets/`

## ⚠️ Wichtiger Hinweis: UIKit-Versionslimitierung

**Aktuell unterstützte UIKit-Version: 3.23.12**

Das AddOn ist derzeit auf UIKit v3.23.12 beschränkt, da neuere UIKit-Versionen moderne CSS-Selektoren wie `:is()` und `:where()` verwenden, die vom PHP LESS-Compiler (wikimedia/less.php) bis auf weiteres nicht unterstützt werden.

### Hintergrund

- **Problem**: UIKit ab v3.24.0 verwendet moderne CSS-Selektoren (`:is()`, `:where()`)
- **Ursache**: Der PHP LESS-Compiler kann diese Selektoren nicht parsen
- **Lösung**: Verwendung von UIKit v3.23.12 bis zur Lösung des Kompatibilitätsproblems

### Warum nicht Node.js?

Der Theme Builder verwendet bewusst einen PHP-basierten Workflow, da:
- Node.js oft nicht auf Produktionsservern verfügbar ist
- Der Build-Prozess zur Laufzeit in PHP erfolgen muss
- REDAXO-Integration nahtlos in PHP erfolgen soll

### Zukunftsperspektive

Sobald der PHP LESS-Compiler moderne CSS-Selektoren unterstützt oder eine alternative Lösung verfügbar ist, wird das AddOn auf neuere UIKit-Versionen aktualisiert. Bis dahin bleibt UIKit v3.23.12 die stabile, unterstützte Version.

### Aktuelle Konfiguration

```
UIKit Version: 3.23.12 (eingefroren)
LESS.php Version: 5.4.0 (neueste kompatible Version)
Status: Stabil, voll funktionsfähig
```

**Für Entwickler**: Verwende keine UIKit-Versionen > 3.23.12, bis diese Limitierung aufgehoben wird.

## 🛠️ Installation

### 1. AddOn Installation

```bash
# Via REDAXO Backend
System → AddOns → Installieren → UIKit Theme Builder

# Oder manueller Download
cd redaxo/src/addons/
git clone https://github.com/your-repo/uikit_theme_builder.git
```

### 2. AddOn Aktivierung

1. REDAXO Backend öffnen
2. `System → AddOns → UIKit Theme Builder`
3. "Installieren" und "Aktivieren" klicken

### 3. UIKit Setup

Das AddOn erwartet UIKit 3.23.12 in folgendem Pfad:
```
public/assets/uikit/    # Kompilierte UIKit Assets v3.23.12
```

**⚠️ Wichtig**: Verwende ausschließlich UIKit v3.23.12! Neuere Versionen sind aufgrund der LESS.php-Kompatibilität nicht unterstützt.

Die UIKit Assets werden **automatisch** bei der AddOn-Installation bereitgestellt. Falls du UIKit manuell aktualisieren möchtest:

```bash
# UIKit v3.23.12 manuell installieren (optional)
cd public/assets/
# Assets werden bereits vom AddOn bereitgestellt
```

### 4. Verzeichnis-Struktur

Nach der Installation wird folgende Struktur erstellt:
```
src/addons/uikit_theme_builder/
├── lib/                       # PHP-Klassen
│   ├── DomainContext.php     # Domain-Theme-Integration
│   ├── ThemeHelper.php       # Helper-Utilities (NEU)
│   ├── TemplateHelper.php    # Template-Integration
│   ├── UikitThemeBuilderManager.php  # Theme-Verwaltung
│   ├── GoogleFontsManager.php        # Fonts-Verwaltung
│   ├── CustomIconManager.php         # Icon-Upload
│   ├── CustomIconBuilder.php         # Icon-Kompilierung
│   └── Widget/               # Theme-Editor Widgets
├── pages/                     # Backend-Seiten
│   ├── editor.php            # Theme-Editor
│   ├── themes.php            # Theme-Verwaltung
│   └── domains.php           # Domain-Zuordnung (NEU)
├── sources/                   # Build-Quellen (NEU)
│   ├── uikit/                # UIKit Source v3.23.12
│   ├── extra.less            # Custom Styles
│   └── preview-template.html # Preview-Template
└── assets/                    # Kompilierte Assets
    └── dist/                 # Output

var/data/addons/uikit_theme_builder/
├── themes/
│   ├── saved/              # Gespeicherte Themes (JSON)
│   ├── compiled/           # Kompilierte Theme CSS
│   ├── temp/              # Temporäre LESS Build-Dateien
│   └── google_fonts/      # Google Fonts Cache
├── fonts/                 # Heruntergeladene Google Fonts
└── icons/                 # Custom Icons (NEU)

public/assets/uikit/
├── css/                   # UIKit Core CSS
│   └── uikit.min.css
├── js/                    # UIKit Core JavaScript
│   ├── uikit.min.js
│   └── uikit-icons.min.js
└── uikit-icons-extended.min.js  # Extended Icons (UIKit + Custom)

public/assets/addons/uikit_theme_builder/
├── themes/compiled/       # Kompilierte Theme CSS (öffentlich)
├── js/                    # JavaScript Assets
├── fonts/                 # Öffentliche Google Fonts
└── icons/custom/          # Custom Icons (NEU)
```

## 🎮 Verwendung

### Theme-Editor

1. **Backend aufrufen**: `AddOns → UIKit Theme Builder → Theme Editor`
2. **Neues Theme erstellen**: "Neues Theme" Button
3. **Widgets konfigurieren**: 
   - Farben anpassen
   - Google Fonts auswählen
   - Breakpoints definieren
   - Typography konfigurieren
4. **Live-Preview**: Änderungen werden sofort angezeigt
5. **Theme speichern**: "Speichern" Button

### Domain-Theme-Zuordnung

1. **Backend aufrufen**: `AddOns → UIKit Theme Builder → Domain-Theme-Zuordnung`
2. **YRewrite-Domains**: Alle verfügbaren Domains werden angezeigt
3. **Theme zuweisen**: Für jede Domain ein Theme auswählen
4. **Utility-Stile**: Optional Utility-Styles aktivieren (Transparent, Light/Dark, Background-Utilities)
5. **Speichern**: Zuordnungen werden gespeichert und Theme-Farben gecacht

### Integration als Theme-Provider für builder

Das Addon registriert in `boot.php` generische Extension-Points, damit `builder` keinerlei direkte Abhängigkeit auf `UikitThemeBuilder\DomainContext` benötigt.

Registrierte Hooks:

- `BUILDER_THEME_PROVIDER_AVAILABLE`
- `BUILDER_THEME_CHOICES`
- `BUILDER_THEME_CONTEXT_RESET`
- `BUILDER_THEME_CONTEXT_SET`
- `BUILDER_THEME_BACKGROUND_OPTIONS`
- `BUILDER_THEME_TEXT_COLOR_OPTIONS`
- `BUILDER_FRAMEWORK_NORMALIZE`

Damit können auch andere Theme-Addons dasselbe Muster nutzen und sich als Provider einklinken.

### Template Integration

#### Einfache Integration mit TemplateHelper (empfohlen)

```php
<?php
use UikitThemeBuilder\TemplateHelper;
?>
<!DOCTYPE html>
<html>
<head>
    <!-- UIKit CSS -->
    <?= TemplateHelper::includeUIKitCSS() ?>
    
    <!-- Theme CSS (optional) -->
    <?= TemplateHelper::includeThemeCSS('mein-theme') ?>
    
    <!-- Google Fonts (optional, falls konfiguriert) -->
    <?= TemplateHelper::includeGoogleFonts('mein-theme') ?>
</head>
<body>
    <!-- Template Content -->
    
    <!-- UIKit JavaScript + Extended Icons -->
    <?= TemplateHelper::includeAllJS() ?>
</body>
</html>
```

#### Komplette Integration (alle Styles auf einmal)

```php
<?php
use UikitThemeBuilder\TemplateHelper;
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Alle Styles (UIKit + Theme + Fonts) -->
    <?= TemplateHelper::includeAllStyles('mein-theme') ?>
</head>
<body>
    <!-- Template Content -->
    
    <!-- JavaScript -->
    <?= TemplateHelper::includeAllJS() ?>
</body>
</html>
```

#### Manuelle Integration

```php
<!DOCTYPE html>
<html>
<head>
    <!-- UIKit CSS -->
    <link rel="stylesheet" href="<?= rex_url::assets('uikit/css/uikit.min.css') ?>">
    
    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?= rex_url::addonAssets('uikit_theme_builder', 'themes/compiled/mein-theme.css') ?>">
</head>
<body>
    <!-- Template Content -->
    
    <!-- UIKit JavaScript -->
    <script src="<?= rex_url::assets('uikit/js/uikit.min.js') ?>"></script>
    <script src="<?= rex_url::assets('uikit/uikit-icons-extended.min.js') ?>"></script>
</body>
</html>
```

#### Multi-Domain Setup mit DomainContext

```php
<?php
use UikitThemeBuilder\TemplateHelper;
use UikitThemeBuilder\DomainContext;

// Theme automatisch per Domain ermitteln (YRewrite Integration)
$context = DomainContext::getContext();
$themeName = $context['theme'] ?? 'default-theme';
?>
<!DOCTYPE html>
<html>
<head>
    <!-- UIKit + Theme -->
    <?= TemplateHelper::includeAllStyles($themeName) ?>
</head>
<body>
    <!-- Content -->
    
    <!-- JavaScript -->
    <?= TemplateHelper::includeAllJS() ?>
</body>
</html>
```

## 🎛️ API Referenz

### ThemeHelper Klasse

Gemeinsame Utilities für Theme-Verwaltung und UIKit-Integration.

```php
// Automatische Textfarbe für Hintergrund berechnen
UikitThemeBuilder\ThemeHelper::getTextColorForBackground(string $bgColor): string

// Theme-Farben extrahieren
UikitThemeBuilder\ThemeHelper::getThemeColors(UikitThemeBuilderManager $manager, string $themeName): array

// Debug-Ausgabe für Backend
UikitThemeBuilder\ThemeHelper::debugOutput(mixed $data, string $label = 'Debug'): string

// Custom Divider rendern
UikitThemeBuilder\ThemeHelper::renderCustomDivider(string $style = 'dots', string $colorClass = 'uk-text-primary'): string
```

**Beispiel:**
```php
use UikitThemeBuilder\ThemeHelper;

// Kontrast-Berechnung
$textColor = ThemeHelper::getTextColorForBackground('#005d40'); // 'uk-light'

// Theme-Farben
$colors = ThemeHelper::getThemeColors($manager, 'wellings_theme');
// ['primary' => '#005d40', 'secondary' => '#b98b73', ...]
```

### TemplateHelper Klasse

Vereinfacht die Asset-Integration in REDAXO Templates.

#### Komplette Integration

```php
// Alle Styles einbinden (UIKit + Theme + Fonts)
UikitThemeBuilder\TemplateHelper::includeAllStyles(?string $themeName = null, bool $minified = true): string

// Alle JavaScript einbinden (UIKit + Icons)
UikitThemeBuilder\TemplateHelper::includeAllJS(bool $minified = true): string
```

#### Einzelne Assets

```php
// UIKit CSS einbinden
UikitThemeBuilder\TemplateHelper::includeUIKitCSS(bool $minified = true): string

// UIKit JavaScript einbinden
UikitThemeBuilder\TemplateHelper::includeUIKitJS(bool $minified = true, bool $includeIcons = true): string

// Extended Icons einbinden
UikitThemeBuilder\TemplateHelper::includeUIKitIcons(bool $minified = true): string

// Theme CSS einbinden
UikitThemeBuilder\TemplateHelper::includeThemeCSS(string $themeName, bool $minified = true): string

// Google Fonts einbinden
UikitThemeBuilder\TemplateHelper::includeGoogleFonts(string $themeName): string
```

### DomainContext Klasse (Multi-Domain Support)

```php
// Aktuellen Kontext abrufen (Domain, Theme, Einstellungen)
$context = UikitThemeBuilder\DomainContext::getContext(): array

// Aktuelles Theme für Domain abrufen
$themeName = UikitThemeBuilder\DomainContext::getCurrentTheme(): ?string

// Domain-ID abrufen
$domainId = UikitThemeBuilder\DomainContext::getCurrentDomainId(): ?int

// Card-Style-Optionen für aktuelle Domain
$options = UikitThemeBuilder\DomainContext::getCardStyleOptions(): array

// Background-Optionen für aktuelle Domain
$options = UikitThemeBuilder\DomainContext::getBackgroundOptions(): array

// Text-Color-Optionen für aktuelle Domain
$options = UikitThemeBuilder\DomainContext::getTextColorOptions(): array

// Extra Styles für aktuelle Domain
$extraStyles = UikitThemeBuilder\DomainContext::getExtraStyles(): array

// Cache zurücksetzen
UikitThemeBuilder\DomainContext::clearCache(): void
```

### UikitThemeBuilderManager Klasse

```php
// Theme kompilieren
$manager = new UikitThemeBuilder\UikitThemeBuilderManager();
$result = $manager->compileTheme(string $themeName, array $themeData): array

// Theme speichern
$manager->saveTheme(string $themeName, array $themeData): bool

// Theme laden
$themeData = $manager->loadTheme(string $themeName): ?array
```

### GoogleFontsManager Klasse

```php
// Font herunterladen
$manager = new UikitThemeBuilder\GoogleFontsManager();
$result = $manager->downloadFont(string $fontFamily, array $variants = ['400']): array

// Heruntergeladene Fonts abrufen
$fonts = $manager->getDownloadedFonts(): array

// Font löschen
$result = $manager->deleteFont(string $fontFamily): array
```

## 🎨 Widget-System

Das AddOn verwendet ein modulares Widget-System für verschiedene Design-Bereiche:

### Verfügbare Widgets

#### ColorsWidget
- **Zweck**: UIKit Farbpalette verwalten
- **Konfiguration**: Primary, Secondary, Success, Warning, Danger, etc.
- **Ausgabe**: LESS Variablen für globale Farben

#### TypographyWidget
- **Zweck**: Schriftarten und Typography-Einstellungen
- **Features**: Google Fonts Integration, Font-Stacks, Fallbacks
- **Konfiguration**: Font-Family, Größen, Zeilenhöhe, Überschriften

#### BreakpointsWidget
- **Zweck**: Responsive Breakpoints definieren
- **Konfiguration**: Small, Medium, Large, XLarge Breakpoints
- **Validierung**: Logische Reihenfolge der Breakpoints

#### ShadowWidget
- **Zweck**: Box-Shadow und Drop-Shadow Definitionen
- **Konfiguration**: Small, Medium, Large, XLarge Schatten
- **Ausgabe**: UIKit-kompatible Shadow-Variablen

#### ContainerWidget
- **Zweck**: Container-Größen für verschiedene Breakpoints
- **Konfiguration**: Max-Width für verschiedene Container-Größen
- **Responsive**: Separate Einstellungen pro Breakpoint

#### NavbarWidget
- **Zweck**: Navigation-Styling
- **Konfiguration**: Hintergrund, Höhe, Farben, Dropdown-Styling
- **Features**: Transparenz, Glasmorphism-Effekte

#### BorderWidget
- **Zweck**: Border-Radius und Border-Width
- **Konfiguration**: Globale Border-Einstellungen
- **Ausgabe**: UIKit Border-Variablen

### Eigene Widgets erstellen

```php
<?php
namespace UikitThemeBuilder\Widget;

class CustomWidget extends AbstractWidget
{
    public function getId(): string
    {
        return 'custom_widget';
    }
    
    public function getTitle(): string
    {
        return 'Custom Settings';
    }
    
    public function getFields(): array
    {
        return [
            'custom-variable' => [
                'label' => 'Custom Variable',
                'type' => 'text',
                'default' => 'default-value'
            ]
        ];
    }
    
    public function generateLessVariables(array $data): array
    {
        return [
            'custom-variable' => $data['custom-variable'] ?? 'default-value'
        ];
    }
}
```

## 🔧 Konfiguration

### AddOn-Konfiguration

Konfigurationsdatei: `var/data/addons/uikit_theme_builder/config.json`

```json
{
    "default_theme": "default",
    "debug_mode": false,
    "google_fonts": {
        "auto_download": true,
        "variants": ["300", "400", "500", "600", "700"],
        "subsets": ["latin", "latin-ext"]
    },
    "compilation": {
        "minify_css": true,
        "source_maps": false
    }
}
```

### Theme-Struktur

Theme-Datei: `var/data/addons/uikit_theme_builder/themes/saved/THEME_NAME.json`

```json
{
    "name": "my-theme",
    "created": "2025-11-09 10:30:00",
    "modified": 1699520200,
    "version": "1.0.0",
    "data": {
        "colors": {
            "global-primary-background": "#007bff",
            "global-secondary-background": "#6c757d"
        },
        "typography": {
            "global-font-family": "\"Inter\", -apple-system, sans-serif",
            "global-font-size": "16px"
        },
        "breakpoints": {
            "breakpoint-small": "640px",
            "breakpoint-medium": "960px"
        }
    }
}
```

### Google Fonts Cache

Cache-Datei: `var/data/addons/uikit_theme_builder/themes/google_fonts/THEME_NAME.json`

```json
{
    "theme": "my-theme",
    "updated": "2025-11-09 10:30:00",
    "fonts": [
        "Inter",
        "Fira Sans"
    ]
}
```

## 🐛 Troubleshooting

### Häufige Probleme

#### Theme wird nicht kompiliert

**Problem**: Theme speichert, aber CSS wird nicht generiert
**Lösung**:
1. LESS Compiler prüfen
2. Schreibrechte für `var/data/` und `public/assets/` prüfen
3. Debug-Modus aktivieren für detaillierte Fehlermeldungen

```php
// Debug-Modus aktivieren
\rex::setProperty('debug', true);
```

#### Google Fonts werden nicht geladen

**Problem**: Fonts sind heruntergeladen, aber werden nicht angezeigt
**Lösung**:
1. CSS-Pfade in Browser-Entwicklertools prüfen
2. Font-Cache neu erstellen:

```php
// Theme neu kompilieren
$manager = new UikitThemeBuilder\UikitThemeBuilderManager();
$manager->compileTheme('theme-name', $themeData);
```

#### Fehlerhafte LESS-Kompilierung

**Problem**: LESS-Syntax-Fehler oder Variable nicht gefunden
**Lösung**:
1. Theme-Daten validieren
2. LESS Debug-Output prüfen
3. UIKit-Variable Namen überprüfen (Hyphens statt Underscores)

#### Performance-Probleme

**Problem**: Lange Ladezeiten im Theme-Editor
**Lösung**:
1. Ungenutzte Google Fonts löschen
2. Theme-Cache leeren
3. CSS-Minifizierung aktivieren

### Debug-Informationen

```php
// Debug-Output aktivieren
if (\rex::isDebugMode()) {
    echo '<pre>';
    print_r($themeData);
    echo '</pre>';
}
```

### Log-Dateien

Wichtige Log-Dateien für Debugging:
- `var/log/system.log` - Allgemeine Fehler
- Debug-Output im Theme-Editor
- Browser-Entwicklertools für CSS/JS-Fehler

## 🚀 Performance

### Optimierungen

#### CSS-Komprimierung

```json
{
    "compilation": {
        "minify_css": true,
        "remove_comments": true
    }
}
```

#### Font-Loading

Das AddOn implementiert intelligentes Font-Loading:
- Nur verwendete Fonts werden geladen
- Font-Display: swap für bessere Performance
- Lokale Speicherung reduziert externe Requests

#### Caching

- Theme-Kompilierung wird gecacht
- Google Fonts Cache-System
- Asset-Versionierung für Browser-Cache

## 🔒 Sicherheit & DSGVO

### DSGVO-Konformität

- **Lokale Font-Speicherung**: Keine Verbindung zu Google Servers zur Laufzeit
- **Keine externen Requests**: Alle Fonts werden lokal gespeichert
- **Datenschutz**: Keine Benutzer-Tracking durch externe Font-Services

### Sicherheitsfeatures

- **Input-Validierung**: Alle Eingaben werden validiert und escaped
- **File-System-Schutz**: Sichere Pfad-Behandlung
- **XSS-Schutz**: HTML-Escaping in allen Ausgaben
- **CSRF-Schutz**: REDAXO's CSRF-Schutz wird verwendet

## 📚 Beispiele

### Einfaches Business-Theme

```php
use UikitThemeBuilder\ThemeHelper;

$businessTheme = [
    'colors' => [
        'global-primary-background' => '#0056b3',
        'global-secondary-background' => '#6c757d',
        'global-success-background' => '#28a745',
        'global-warning-background' => '#ffc107',
        'global-danger-background' => '#dc3545'
    ],
    'typography' => [
        'global-font-family' => '"Inter", -apple-system, sans-serif',
        'global-font-size' => '16px',
        'base-heading-font-family' => '"Playfair Display", serif'
    ],
    'breakpoints' => [
        'breakpoint-small' => '640px',
        'breakpoint-medium' => '960px',
        'breakpoint-large' => '1200px',
        'breakpoint-xlarge' => '1600px'
    ]
];
```

### Creative Agency Theme

```php
use UikitThemeBuilder\ThemeHelper;

$creativeTheme = [
    'colors' => [
        'global-primary-background' => '#ff6b35',
        'global-secondary-background' => '#2d3748',
        'global-background' => '#f7fafc'
    ],
    'typography' => [
        'global-font-family' => '"Nunito", sans-serif',
        'global-font-size' => '18px',
        'base-heading-font-family' => '"Caveat", cursive'
    ],
    'shadows' => [
        'box-shadow-small' => '0 4px 6px rgba(0, 0, 0, 0.1)',
        'box-shadow-medium' => '0 10px 25px rgba(0, 0, 0, 0.15)',
        'box-shadow-large' => '0 20px 40px rgba(0, 0, 0, 0.2)'
    ]
];

// Kontrast-Berechnung
$textColor = ThemeHelper::getTextColorForBackground('#ff6b35'); // ''
```

### Multi-Domain Setup mit Auto-Theme

```php
<?php
use UikitThemeBuilder\DomainContext;
use UikitThemeBuilder\TemplateHelper;

// Theme automatisch per Domain ermitteln
$context = DomainContext::getContext();
$themeName = $context['theme'] ?? 'default';

// Domain-spezifische Konfiguration
$siteConfig = match($themeName) {
    'wellings_theme' => [
        'logo' => 'wellings-logo.svg',
        'contact' => 'info@wellings.de'
    ],
    'linde_theme' => [
        'logo' => 'linde-logo.svg',
        'contact' => 'kontakt@hotel-zur-linde.de'
    ],
    default => [
        'logo' => 'default-logo.svg',
        'contact' => 'info@example.com'
    ]
};
?>
<!DOCTYPE html>
<html>
<head>
    <?= TemplateHelper::includeAllStyles($themeName) ?>
</head>
<body>
    <img src="<?= rex_url::media($siteConfig['logo']) ?>" alt="Logo">
    <!-- Content -->
    <?= TemplateHelper::includeAllJS() ?>
</body>
</html>
```

## 🤝 Beitragen

### Entwicklung

1. Repository forken
2. Feature-Branch erstellen: `git checkout -b feature/amazing-feature`
3. Änderungen committen: `git commit -m 'Add amazing feature'`
4. Branch pushen: `git push origin feature/amazing-feature`
5. Pull Request erstellen

### Code-Standards

- PSR-12 Coding Standard
- PHPDoc für alle öffentlichen Methoden
- Unit Tests für neue Features
- REDAXO Best Practices beachten

### Widget-Entwicklung

Neue Widgets sollten:
- Von `AbstractWidget` erben
- UIKit-kompatible Variablen generieren
- Vollständige Validierung implementieren
- Benutzerfreundliche Konfiguration bieten

## 📄 Lizenz

Dieses Projekt steht unter der MIT-Lizenz. Siehe [LICENSE](LICENSE) für Details.

## 🙏 Danksagungen

- [UIKit Team](https://getuikit.com) für das großartige CSS-Framework
- [REDAXO Community](https://redaxo.org) für das robuste CMS
- [Google Fonts](https://fonts.google.com) für die umfangreiche Font-Bibliothek

## 📞 Support

- **Dokumentation**: [GitHub Wiki](https://github.com/your-repo/uikit_theme_builder/wiki)
- **Issues**: [GitHub Issues](https://github.com/your-repo/uikit_theme_builder/issues)
- **Diskussionen**: [REDAXO Slack](https://redaxo.org/slack/)
- **E-Mail**: support@your-domain.com

---

Erstellt mit ❤️ für die REDAXO Community