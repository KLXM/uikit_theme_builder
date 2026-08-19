# Extra Styles Integration - UIKit Theme Builder

Das UIKit Theme Builder AddOn ist jetzt vollständig mit dem Extra Styles AddOn integriert!

## 🎯 **Automatische Integration**

### **🔄 Compile-Zeit Integration**
- Extra Styles CSS wird **automatisch** bei jeder Theme-Kompilierung eingebunden
- Kein manueller Import nötig - alles passiert transparent
- CSS wird direkt in die finale Theme-CSS-Datei eingearbeitet

### **📱 Debug-Modus Feedback**
Im REDAXO Debug-Modus erhalten Sie Feedback über die Integration:
- ✅ **Erfolg**: "Extra Styles CSS wurde erfolgreich hinzugefügt (X Bytes)"
- ℹ️ **Info**: "Keine Extra Styles verfügbar oder AddOn nicht installiert"

## 🛠️ **Verwendung in Modulen**

### **Option 1: Direkte Extra Styles API**
```php
<?php
use ExtraStyles\ExtraStyles;

// Direkte Verwendung (wenn Extra Styles installiert)
$mform->addSelectField("$id.0.cardStyle")
    ->setLabel('Card-Style:')
    ->setOptions(ExtraStyles::getSelectOptions('card'));
?>
```

### **Option 2: UIKit Theme Builder Integration (Empfohlen)**
```php
<?php
use UikitThemeBuilder\ExtraStylesIntegration;

// Sicherer Wrapper mit Fallback
$mform->addSelectField("$id.0.cardStyle")
    ->setLabel('Card-Style:')
    ->setOptions(ExtraStylesIntegration::getSelectOptions('card'));

// Mit zusätzlichen Optionen
$mform->addSelectField("$id.0.sectionStyle")
    ->setLabel('Sektion-Style:')
    ->setOptions(ExtraStylesIntegration::getMergedSelectOptions('section', [
        'transparent uk-light' => 'Transparent mit heller Schrift'
    ]));
?>
```

## 🎨 **Verfügbare Style-Typen**

| Typ | CSS-Präfix | Beispiel-Klasse | Verwendung |
|-----|------------|-----------------|------------|
| `card` | `uk-card-` | `uk-card-custom-blue` | Karten/Cards |
| `section` | `uk-section-` | `uk-section-custom-dark` | Sektionen |
| `background` | `uk-background-` | `uk-background-custom-gradient` | Hintergründe |
| `border` | `uk-border-` | `uk-border-custom-thick` | Nur Rahmen |

## 🔧 **Erweiterte Features**

### **CSS-Klassen-Generierung**
```php
<?php
use UikitThemeBuilder\ExtraStylesIntegration;

// Automatische CSS-Klassen-Generierung
$styleValue = "custom-blue"; // Aus Auswahl
$cssClass = ExtraStylesIntegration::generateCssClass('card', $styleValue);
// Ergebnis: "uk-card-custom-blue"

// Spezielle Werte werden korrekt behandelt
$specialValue = "transparent uk-light";
$cssClass = ExtraStylesIntegration::generateCssClass('card', $specialValue);
// Ergebnis: "transparent uk-light"
?>
```

### **Fallback-System**
Das System funktioniert auch **ohne** Extra Styles AddOn:
- Automatischer Fallback auf UIKit Standard-Styles
- Keine Fehler wenn Extra Styles nicht installiert ist
- Graceful Degradation für alle Module

### **Debug-Information**
```php
<?php
use UikitThemeBuilder\ExtraStylesIntegration;

// Debug-Info nur im Debug-Modus
echo ExtraStylesIntegration::getDebugInfo();
?>
```

## 📋 **Vollständiges Modul-Beispiel**

```php
<?php
// input.php
use FriendsOfRedaxo\MForm;
use UikitThemeBuilder\ExtraStylesIntegration;

$id = 1;
$mform = MForm::factory()
    // Titel
    ->addTextField("$id.0.title", ['label' => 'Titel'])
    
    // Text
    ->addTextAreaField("$id.0.text", ['label' => 'Text'])
    
    // Card-Style mit Extra Styles Integration
    ->addSelectField("$id.0.cardStyle")
        ->setLabel('Card-Style:')
        ->setOptions(ExtraStylesIntegration::getMergedSelectOptions('card', [
            'transparent uk-light' => 'Transparent mit heller Schrift',
            'gradient-special' => 'Spezieller Gradient'
        ]))
        
    // Sektion-Style  
    ->addSelectField("$id.0.sectionStyle")
        ->setLabel('Sektion-Hintergrund:')
        ->setOptions(ExtraStylesIntegration::getSelectOptions('section'));

// Debug-Info anzeigen (nur im Debug-Modus)
echo ExtraStylesIntegration::getDebugInfo();

echo MBlock::show($id, $mform->show());
?>
```

```php
<?php
// output.php
use UikitThemeBuilder\ExtraStylesIntegration;

foreach ($rexVars as $rexVar):
    $title = $rexVar['title'];
    $text = $rexVar['text'];
    
    // CSS-Klassen automatisch generieren
    $cardClass = ExtraStylesIntegration::generateCssClass('card', $rexVar['cardStyle']);
    $sectionClass = ExtraStylesIntegration::generateCssClass('section', $rexVar['sectionStyle']);
?>

<section class="uk-section <?= $sectionClass ?>">
    <div class="uk-container">
        <div class="uk-card uk-card-body <?= $cardClass ?>">
            <?php if ($title): ?>
                <h2 class="uk-card-title"><?= htmlspecialchars($title) ?></h2>
            <?php endif; ?>
            
            <?php if ($text): ?>
                <div class="uk-text"><?= nl2br(htmlspecialchars($text)) ?></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php endforeach; ?>
```

## ⚡ **Workflow**

1. **Extra Styles erstellen** im Extra Styles AddOn Backend
2. **Theme kompilieren** - Extra Styles werden automatisch eingebunden
3. **Module verwenden** - Neue Styles stehen in Select-Feldern zur Verfügung
4. **CSS automatisch verfügbar** - Keine zusätzlichen CSS-Imports nötig

## 🎯 **Vorteile der Integration**

- **🔄 Automatisch**: Keine manuelle CSS-Einbindung nötig
- **💯 Fallback-sicher**: Funktioniert auch ohne Extra Styles
- **🎨 Konsistent**: Alles in einem Theme-CSS enthalten
- **⚡ Performance**: Ein CSS-Request statt mehrerer
- **🛠️ Debug-freundlich**: Ausführliche Debug-Informationen
- **📱 Responsive**: Extra Styles respektieren Theme-Breakpoints

## 🚀 **Migration bestehender Module**

### Vorher (nur UIKit Standard):
```php
->setOptions([
    'default' => 'Standard',
    'primary' => 'Hauptfarbe',
    'secondary' => 'Sekundär'
])
```

### Nachher (mit Extra Styles Integration):
```php
->setOptions(ExtraStylesIntegration::getSelectOptions('card'))
```

**Ergebnis**: Standard-Optionen + alle Custom-Styles aus Extra Styles!