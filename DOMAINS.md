# Domain-Theme-Integration 🌐

Die erste **allumfassende domain-übergreifende Theme-Lösung** für REDAXO! Ordne jedem YRewrite-Domain ein eigenes Theme zu und nutze automatisch die passenden Farben und Stile in deinen Modulen.

## 🎯 Übersicht

Das Domain-Theme-System ermöglicht es, verschiedene Themes für unterschiedliche Domains zu verwenden und diese nahtlos in Module, Templates und Addons zu integrieren.

**Vorteile:**
- ✅ Automatische Theme-Zuweisung pro Domain
- ✅ MForm-kompatible Farboptionen
- ✅ Zentrale Verwaltung
- ✅ Keine Code-Duplizierung
- ✅ Extra Styles Support
- ✅ Template & Modul Integration

---

## 📋 Einrichtung

### 1. Domain-Theme-Zuordnung

Gehe zu **UIKit Theme Builder → Domain-Theme-Zuordnung**

Hier siehst du alle YRewrite-Domains und kannst jedem ein Theme zuweisen:

| Domain | URL | Theme |
|--------|-----|-------|
| wellings.de | https://wellings.de | wellings_theme |
| hotel-zur-linde.de | https://hotel-zur-linde.de | linde_theme |
| wellings-parkhotel.de | https://wellings-parkhotel.de | parkhotel_theme |

**Speichern** - Fertig! 🎉

### 2. Theme-Farben werden automatisch gecacht

Beim Speichern eines Themes werden die wichtigsten Farben in der Datenbank gecacht:
- Primary
- Secondary
- Default
- Muted
- Background
- Inverse

Dies ermöglicht schnellen Zugriff ohne Theme-JSON zu parsen.

---

## 🔧 Verwendung in Modulen

### MForm Integration (empfohlen)

**Beispiel: Content Cards Modul**

```php
<?php
use UikitThemeBuilder\DomainContext;

// MBlock ID
$id = 'REX_VALUE[1]';

// Domain-basierte Optionen holen
$cardOptions = DomainContext::getCardStyleOptions();
$backgroundOptions = DomainContext::getBackgroundOptions();
$textColorOptions = DomainContext::getTextColorOptions();

// MForm mit automatischen Domain-Farben
$mform = MForm::factory()
    ->addRadioColorField("$id.0.card_style", $cardOptions, [
        'label' => 'Card-Stil',
        'default_value' => array_key_first($cardOptions)
    ])
    ->addRadioColorField("$id.0.background", $backgroundOptions, [
        'label' => 'Hintergrund'
    ])
    ->addRadioColorField("$id.0.text_color", $textColorOptions, [
        'label' => 'Textfarbe'
    ]);

echo MBlock::show(1, $mform->show());
```

**Output:**
```php
<?php
use UikitThemeBuilder\DomainContext;

$cards = rex_var::toArray('REX_VALUE[1]');

foreach ($cards as $card) {
    $cardStyle = $card['card_style'] ?? 'uk-card-default';
    
    // Extra Styles (extra-*) werden als CSS-Klassen verwendet
    echo '<div class="uk-card ' . $cardStyle . '">';
    // ...
    echo '</div>';
}
```

---

## 🎨 Verfügbare Methoden

### DomainContext::getContext()

Gibt alle Kontext-Informationen zurück:

```php
$context = DomainContext::getContext();
// Array:
// [
//     'domain_id' => 1,
//     'domain_name' => 'wellings.de',
//     'theme' => 'wellings_theme',
//     'theme_css_url' => '../assets/addons/uikit_theme_builder/themes/compiled/wellings_theme.css',
//     'is_backend' => false
// ]
```

### DomainContext::getCurrentTheme()

Gibt nur den Theme-Namen zurück:

```php
$theme = DomainContext::getCurrentTheme();
// String: 'wellings_theme'
```

### DomainContext::getCardStyleOptions()

MForm-kompatibles Array für RadioColorField:

```php
$options = DomainContext::getCardStyleOptions();
// Array:
// [
//     'uk-card-primary' => [
//         'color' => '#005d40',
//         'label' => 'Primary (Brand-Grün)'
//     ],
//     'uk-card-secondary' => [
//         'color' => '#b98b73',
//         'label' => 'Secondary'
//     ],
//     'extra-fff' => [
//         'color' => '#ffb43f',
//         'label' => 'fff (Extra Style)'
//     ]
// ]
```

### DomainContext::getBackgroundOptions()

Hintergrund-Utilities:

```php
$options = DomainContext::getBackgroundOptions();
// Array:
// [
//     'uk-background-default' => ['color' => '#ffffff', 'label' => 'Standard'],
//     'uk-background-muted' => ['color' => '#f5f0e8', 'label' => 'Gedämpft'],
//     // ...
// ]
```

### DomainContext::getTextColorOptions()

Textfarben-Utilities:

```php
$options = DomainContext::getTextColorOptions();
// Array:
// [
//     '' => ['color' => '#666666', 'label' => 'Standard'],
//     'uk-text-primary' => ['color' => '#005d40', 'label' => 'Primary'],
//     // ...
// ]
```

### DomainContext::getExtraStyles()

Alle aktiven Style-Sets:

```php
$extraStyles = DomainContext::getExtraStyles();
// Array mit Style-Set-Objekten inkl. styles_data
```

---

## 📄 Verwendung in Templates

### Theme CSS einbinden

**Automatisch das richtige Theme-CSS laden:**

```php
<?php
use UikitThemeBuilder\DomainContext;
use UikitThemeBuilder\TemplateHelper;

$context = DomainContext::getContext();
$themeName = $context['theme'] ?? null;

// Alle Styles einbinden (UIKit + Theme + Fonts)
echo TemplateHelper::includeAllStyles($themeName);
?>
```

**Oder manuell:**

```php
<?php
use UikitThemeBuilder\DomainContext;

$context = DomainContext::getContext();
$themeCssUrl = $context['theme_css_url'] ?? '';

if ($themeCssUrl) {
    echo '<link rel="stylesheet" href="' . $themeCssUrl . '">';
}
?>
```

### Domain-spezifische Inhalte

```php
<?php
use UikitThemeBuilder\DomainContext;

$currentTheme = DomainContext::getCurrentTheme();

// Theme-spezifische Logik
switch ($currentTheme) {
    case 'wellings_theme':
        echo '<h1>Willkommen bei Wellings</h1>';
        break;
    case 'linde_theme':
        echo '<h1>Hotel zur Linde</h1>';
        break;
    default:
        echo '<h1>Willkommen</h1>';
}
?>
```

### Domain-Erkennung

```php
<?php
use UikitThemeBuilder\DomainContext;

$context = DomainContext::getContext();

// Domain-ID
$domainId = $context['domain_id']; // int oder null

// Domain-Name
$domainName = $context['domain_name']; // String oder null

// Backend-Check
if ($context['is_backend']) {
    // Im Backend wird das erste zugeordnete Theme verwendet
    echo '<!-- Backend-Modus: ' . $context['theme'] . ' -->';
}
?>
```

---

## 🔌 Verwendung in Addons

### In eigenen Addons integrieren

```php
<?php
namespace MeinAddon;

use UikitThemeBuilder\DomainContext;

class MeineKlasse
{
    public function getThemeConfig(): array
    {
        $theme = DomainContext::getCurrentTheme();
        
        // Theme-spezifische Konfiguration laden
        $configFile = \rex_path::addonData('mein_addon', "config_{$theme}.json");
        
        if (file_exists($configFile)) {
            return json_decode(file_get_contents($configFile), true);
        }
        
        return $this->getDefaultConfig();
    }
    
    public function renderWidget(): string
    {
        $cardOptions = DomainContext::getCardStyleOptions();
        
        // Widget mit Theme-Farben rendern
        $html = '<div class="uk-card ' . array_key_first($cardOptions) . '">';
        // ...
        $html .= '</div>';
        
        return $html;
    }
}
```

### YForm-Integration

```php
<?php
// In YForm-Action oder Value
use UikitThemeBuilder\DomainContext;

$context = DomainContext::getContext();
$theme = $context['theme'];

// Theme-abhängige E-Mail-Templates
$templateFile = "email_template_{$theme}.php";

if (file_exists($templateFile)) {
    include $templateFile;
}
```

---

## 🎭 Extra Styles Integration

Extra Styles werden automatisch in die Card-Optionen integriert:

**Im Theme-Editor:**
1. Gehe zu **Extra Styles**
2. Erstelle Style-Set mit Typ "card"
3. Speichere Theme

**In Modulen:**
```php
$cardOptions = DomainContext::getCardStyleOptions();
// Enthält automatisch alle Extra Styles mit Prefix "extra-*"
// Beispiel: 'extra-fff' => ['color' => '#ffb43f', 'label' => 'fff (Extra Style)']
```

**Im Output:**
```html
<!-- CSS-Klasse wird verwendet, kein Inline-CSS -->
<div class="uk-card extra-fff">
    <!-- Extra Style CSS wurde beim Theme-Kompilieren generiert -->
</div>
```

---

## 🐛 Debugging

### Debug-Modul verwenden

Es gibt ein fertiges Debug-Modul: **debug_domain_context**

Zeigt:
- ✅ Aktuellen Context (Domain, Theme)
- ✅ Card-Style-Optionen
- ✅ Background-Optionen
- ✅ Text-Color-Optionen
- ✅ Extra Styles
- ✅ Code-Beispiele

### Manuelles Debugging

```php
<?php
use UikitThemeBuilder\DomainContext;

// Context ausgeben
dump(DomainContext::getContext());

// Theme-Name
dump(DomainContext::getCurrentTheme());

// Verfügbare Optionen
dump(DomainContext::getCardStyleOptions());
```

---

## ⚙️ Technische Details

### Datenbank-Struktur

**rex_uikit_theme_domains:**
- `domain_id` - YRewrite Domain ID
- `theme_name` - Zugeordnetes Theme
- `created_date`, `updated_date`

**rex_uikit_theme_colors:**
- `theme_name` - Theme-Referenz
- `color_type` - z.B. 'global-primary-background'
- `color_value` - Hex-Code
- `color_label` - Anzeigename
- `ui_class` - CSS-Klasse (z.B. 'uk-card-primary')

### Caching

Farben werden beim Theme-Speichern automatisch gecacht:
- Schneller Zugriff ohne JSON-Parsing
- Automatische Aktualisierung bei Theme-Änderungen
- Cache-Clearing via `DomainContext::clearCache()`

### Backend vs. Frontend

**Backend:**
- Keine spezifische Domain aktiv
- Verwendet das erste zugeordnete Theme als Fallback
- `is_backend => true` im Context

**Frontend:**
- YRewrite-Domain wird erkannt
- Zugeordnetes Theme wird geladen
- Volle Domain-Theme-Integration

---

## 🚀 Best Practices

### 1. Zentrale Farbverwaltung

❌ **Schlecht:**
```php
// Farben hardcoded
->addRadioColorField("$id.0.color", [
    'primary' => ['color' => '#005d40', 'label' => 'Grün'],
    'secondary' => ['color' => '#b98b73', 'label' => 'Braun']
]);
```

✅ **Gut:**
```php
// Domain-Theme-basiert
$cardOptions = DomainContext::getCardStyleOptions();
->addRadioColorField("$id.0.color", $cardOptions);
```

### 2. Theme-CSS im Template

❌ **Schlecht:**
```php
// Statisches Theme
<link rel="stylesheet" href="/assets/uikit/wellings.css">
```

✅ **Gut:**
```php
// Dynamisches Theme
<?php
$context = DomainContext::getContext();
if ($context['theme_css_url']) {
    echo '<link rel="stylesheet" href="' . $context['theme_css_url'] . '">';
}
?>
```

### 3. Domain-Check

❌ **Schlecht:**
```php
// Hostname parsen
$host = $_SERVER['HTTP_HOST'];
if ($host === 'wellings.de') { /* ... */ }
```

✅ **Gut:**
```php
// DomainContext verwenden
$context = DomainContext::getContext();
if ($context['theme'] === 'wellings_theme') { /* ... */ }
```

---

## 📚 Zusammenfassung

**Das Domain-Theme-System bietet:**

1. **Zentrale Verwaltung** - Eine Stelle für alle Domain-Theme-Zuordnungen
2. **Automatische Integration** - Module passen sich automatisch an
3. **MForm-Kompatibilität** - Perfekt für RadioColorField
4. **Extra Styles Support** - Erweiterte Styling-Optionen
5. **Performance** - Gecachte Farben für schnellen Zugriff
6. **Flexibilität** - Verwendbar in Modulen, Templates, Addons

**Die erste und einzige allumfassende domain-übergreifende Theme-Lösung für REDAXO!** 🎉

---

## 💡 Weitere Beispiele

### Beispiel: Kontaktformular mit Domain-Farben

```php
<?php
use UikitThemeBuilder\DomainContext;

$context = DomainContext::getContext();
$theme = $context['theme'];
$cardOptions = DomainContext::getCardStyleOptions();

// Domain-spezifische Absender-Adresse
$fromEmail = match($theme) {
    'wellings_theme' => 'info@wellings.de',
    'linde_theme' => 'kontakt@hotel-zur-linde.de',
    'parkhotel_theme' => 'reservierung@wellings-parkhotel.de',
    default => 'info@example.com'
};

// Formular mit Theme-Farben
$yform = new rex_yform();
$yform->setValueField('text', ['name', 'Name']);
$yform->setValueField('email', ['email', 'E-Mail']);
$yform->setActionField('tpl2email', [
    'from' => $fromEmail,
    'template' => "contact_{$theme}.html"
]);
```

### Beispiel: Navigation mit Theme-Klassen

```php
<?php
use UikitThemeBuilder\DomainContext;

$context = DomainContext::getContext();
$theme = $context['theme'];

// Theme-spezifische Navigation-Klasse
$navClass = match($theme) {
    'wellings_theme' => 'uk-navbar-primary',
    'linde_theme' => 'uk-navbar-secondary',
    default => 'uk-navbar-default'
};
?>

<nav class="uk-navbar <?= $navClass ?>" uk-navbar>
    <!-- Navigation -->
</nav>
```

---

**Viel Erfolg mit der Domain-Theme-Integration!** 🚀
