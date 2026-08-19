# Custom Widgets für REDAXO Backend

Diese Widgets erweitern das REDAXO Backend um moderne UI-Komponenten mit UIKit-Integration.

## 🎨 Pickr Color Picker Widget

Ein eleganter Color Picker basierend auf Pickr.js mit Button-Group Layout.

### Features
- ✅ Sauberes Button-Group Design (Input + Color Button)
- ✅ UIKit-kompatibles Styling
- ✅ HTML5 Color Input Fallback
- ✅ Hex, RGB, HSL Farbformate
- ✅ Globale Asset-Verwaltung (über boot.php)

### Verwendung

#### Basic Usage
```php
echo PickrColorPickerWidget::render('primary_color', '#1e87f0');
```

#### Mit Optionen
```php
echo PickrColorPickerWidget::render('accent_color', '#f0506e', [
    'format' => 'rgb',        // hex, rgb, hsl
    'alpha' => true,          // Alpha-Channel aktivieren
    'placeholder' => 'Wähle eine Farbe...'
]);
```

#### In MForm/YForm
```php
// MForm
$mform->addText('background_color', ['label' => 'Hintergrundfarbe'])
      ->setNote(PickrColorPickerWidget::render('background_color', '#ffffff'));

// YForm
$yform->addField('text', 'brand_color', 'Brand-Farbe');
// Zusätzlich Widget-HTML in Template einbinden
```

---

## 🎯 UIKit Icon Picker

Ein intelligenter Icon-Picker für UIKit Icons mit Live-Preview, Suchfunktion und Kategoriefilter.

### Features

- **Live-Preview**: Sofortige Anzeige des ausgewählten Icons
- **Automatisches Laden**: Alle UIKit Standard + Custom Icons verfügbar
- **Suchfunktion**: Schnelles Finden von Icons über Namen oder Kategorien
- **Kategoriefilter**: Navigation, Interface, Media, Social, Editing, Devices, Objects, Custom
- **Responsive Design**: Optimiert für das REDAXO Backend
- **Touch-optimiert**: Funktioniert perfekt auf mobilen Geräten
- **Keyboard Navigation**: Vollständige Tastatursteuerung
- **CSS-Klassen basiert**: Kein PHP nötig!

### Verwendung

#### Simple CSS-Klasse (empfohlen)

```html
<!-- Einfach die CSS-Klasse hinzufügen -->
<input type="text" class="uk-iconpicker uk-input" name="icon" value="heart" />

<!-- Mit Placeholder -->
<input type="text" class="uk-iconpicker uk-input" name="icon" placeholder="Icon wählen..." />

<!-- Custom Icons funktionieren automatisch -->
<input type="text" class="uk-iconpicker uk-input" name="icon" value="custom-person" />
```

#### In MForm

```php
$mform = MForm::factory()
    ->addTextField('icon', ['label' => 'Icon'])
    ->setAttributes(['class' => 'uk-iconpicker']);
```

#### In MBlock

```php
$id = 'REX_INPUT_VALUE[1]';

$mform = MForm::factory()
    ->addTabElement('<i class="fas fa-icon"></i> Icon-Auswahl', MForm::factory()
        ->addTextField("$id.0.icon", ['label' => 'Icon Name'])
        ->setAttributes(['class' => 'uk-iconpicker'])
    , true);
```

### Automatische Initialisierung

Der Icon Picker wird automatisch initialisiert für:
- Alle Inputs mit der Klasse `uk-iconpicker`
- Backend: Bei `rex:ready` Event
- Frontend: Bei `DOMContentLoaded`

Keine weiteren Konfigurationen oder Scripts nötig!
        return UikitIconPickerWidget::render(
            $params['name'], 
            $params['value'], 
            ['placeholder' => 'Icon auswählen...']
        );
    }, 'icon', '', ['label' => 'Icon']);
```

#### Frontend-Ausgabe

```php
// Icon im Frontend ausgeben
$iconName = "REX_VALUE[1]";
if ($iconName) {
    echo '<span uk-icon="icon: ' . htmlspecialchars($iconName) . '"></span>';
}

// Alternative: SVG-basierte Ausgabe
if ($iconName) {
    if (!class_exists('UikitIconLoader')) {
        require_once rex_addon::get('uikit_theme_builder')->getPath('lib/CustomWidgets/UikitIconLoader.php');
    }
    echo UikitIconLoader::getIconSVG($iconName, 24);
}
```

## UIKit Icon Loader

Intelligente Klasse zum dynamischen Laden von UIKit Icons aus der Installation.

### Features

- **Automatische Erkennung**: Lädt Icons aus SVG-Dateien oder CSS
- **Kategorisierung**: Automatische Zuordnung zu Kategorien
- **Caching**: Optimierte Performance durch Zwischenspeicherung
- **Fallback**: Hardcoded Icon-Liste als Fallback
- **Flexible Ausgabe**: SVG mit konfigurierbarer Größe

### Verwendung

```php
// Icon Loader laden
if (!class_exists('UikitIconLoader')) {
    require_once rex_addon::get('uikit_theme_builder')->getPath('lib/CustomWidgets/UikitIconLoader.php');
}

// Alle verfügbaren Icons abrufen
$icons = UikitIconLoader::loadAvailableIcons();

// SVG für spezifisches Icon generieren
$svg = UikitIconLoader::getIconSVG('home', 24);

// Prüfen ob Icons verfügbar sind
if (UikitIconLoader::areIconsAvailable()) {
    // Icons sind verfügbar
}

// Cache leeren (für Development)
UikitIconLoader::clearCache();
```

## Geplante Erweiterungen

### Zukünftige Widgets

1. **UIKit Color Picker**
   - Auswahl von UIKit Theme-Farben
   - Live-Preview mit Kontrast-Check
   - Accessibility-Features

2. **UIKit Component Builder**
   - Visueller Builder für UIKit-Komponenten
   - Drag & Drop Interface
   - Code-Export

3. **UIKit Animation Picker**
   - Auswahl von UIKit-Animationen
   - Live-Preview der Animationen
   - Timing-Konfiguration

4. **UIKit Grid Builder**
   - Visueller Grid-Designer
   - Responsive Breakpoint-Konfiguration
   - Code-Export

### Technische Verbesserungen

- **Performance**: Lazy Loading für große Widget-Sets
- **Accessibility**: WCAG 2.1 AA Konformität
- **Mobile**: Touch-optimierte Bedienung
- **Keyboard**: Vollständige Tastatursteuerung
- **Testing**: Automatisierte Tests für alle Widgets

## Installation

Die Custom Widgets sind Teil des UIKit Theme Builder AddOns und werden automatisch mit installiert.

## Beitragen

Neue Custom Widgets können einfach hinzugefügt werden:

1. Neue Widget-Klasse in `lib/CustomWidgets/` erstellen
2. Dem Namensschema `UikitXxxWidget` folgen
3. Statische `render()` Methode implementieren
4. Dokumentation hinzufügen
5. Demo auf der Custom Widgets Seite erstellen

## Support

Bei Problemen oder Fragen:

- GitHub Issues im UIKit Theme Builder Repository
- REDAXO Community Forum
- Dokumentation auf der Custom Widgets Seite im Backend