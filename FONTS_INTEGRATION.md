# Google Fonts Integration

## Verwendung in Templates

Das UIKit Theme Builder AddOn stellt verschiedene Methoden zur Verfügung, um Google Fonts in REDAXO Templates zu laden.

### 1. Automatische Integration (empfohlen)

```php
<?php
// Theme aktivieren (optional, falls noch nicht gesetzt)
UikitThemeBuilder\TemplateHelper::activateTheme('mein-theme');

// Komplette CSS-Einbindung (UIKit + Theme + Google Fonts)
echo UikitThemeBuilder\TemplateHelper::includeAllStyles();
?>
```

### 2. Spezifisches Theme ohne Aktivierung

```php
<?php
$themeName = 'hotel-theme';
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Komplette Einbindung für spezifisches Theme -->
    <?= UikitThemeBuilder\TemplateHelper::includeAllStyles($themeName) ?>
</head>
<body>
    <!-- Template Content -->
</body>
</html>
```

### 3. Separate Einbindung (für mehr Kontrolle)

```php
<!DOCTYPE html>
<html>
<head>
    <!-- UIKit CSS -->
    <link rel="stylesheet" href="<?= rex_url::assets('uikit/css/uikit.min.css') ?>">
    
    <!-- Google Fonts für spezifisches Theme -->
    <?= UikitThemeBuilder\TemplateHelper::includeGoogleFontsForTheme('mein-theme') ?>
    
    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?= UikitThemeBuilder\TemplateHelper::getThemeCssUrlForTheme('mein-theme') ?>">
</head>
<body>
    <!-- Template Content -->
</body>
</html>
```

### 4. Theme-Auswahl im Template

```php
<?php
// Verfügbare Themes anzeigen
$availableThemes = UikitThemeBuilder\TemplateHelper::getAvailableThemes();
$currentTheme = UikitThemeBuilder\TemplateHelper::getActiveTheme();

// Theme per GET-Parameter wechseln
$selectedTheme = rex_request('theme', 'string', $currentTheme);
if ($selectedTheme && in_array($selectedTheme, $availableThemes)) {
    UikitThemeBuilder\TemplateHelper::activateTheme($selectedTheme);
}
?>
<!DOCTYPE html>
<html>
<head>
    <?= UikitThemeBuilder\TemplateHelper::includeAllStyles() ?>
</head>
<body>
    <!-- Theme-Wechsler -->
    <div class="uk-margin">
        <label>Theme wählen:</label>
        <select onchange="window.location.href='?theme='+this.value">
            <?php foreach ($availableThemes as $theme): ?>
                <option value="<?= rex_escape($theme) ?>" 
                        <?= $theme === $selectedTheme ? 'selected' : '' ?>>
                    <?= rex_escape($theme) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- Template Content -->
</body>
</html>
```

### 5. Multi-Domain Setup

```php
<?php
// Theme basierend auf Domain setzen
$domain = rex_server('SERVER_NAME');
$themeMapping = [
    'wellings.de' => 'wellings-theme',
    'hotel-zur-linde.de' => 'linde-theme', 
    'wellings-parkhotel.de' => 'parkhotel-theme'
];

$themeName = $themeMapping[$domain] ?? 'default-theme';
UikitThemeBuilder\TemplateHelper::activateTheme($themeName);
?>
<!DOCTYPE html>
<html>
<head>
    <?= UikitThemeBuilder\TemplateHelper::includeAllStyles() ?>
</head>
<body>
    <!-- Content für <?= rex_escape($domain) ?> mit Theme: <?= rex_escape($themeName) ?> -->
</body>
</html>
```

## Verfügbare Methoden

### Theme-Verwaltung

#### TemplateHelper::activateTheme(string $themeName): bool
Setzt ein Theme als aktiv für die weitere Verwendung.

#### TemplateHelper::getActiveTheme(): ?string
Gibt das aktuell aktive Theme zurück.

#### TemplateHelper::getAvailableThemes(): array
Gibt alle verfügbaren Themes zurück.

### CSS-Einbindung

#### TemplateHelper::includeAllStyles(?string $themeName = null): string
Lädt UIKit CSS, Google Fonts und Theme CSS in der richtigen Reihenfolge.
- Ohne Parameter: Verwendet das aktive Theme
- Mit Parameter: Verwendet das angegebene Theme

#### TemplateHelper::includeGoogleFonts(): string
Lädt nur die Google Fonts für das aktive Theme.

#### TemplateHelper::includeGoogleFontsForTheme(string $themeName): string
Lädt Google Fonts für ein bestimmtes Theme.

### URL-Generierung

#### TemplateHelper::getThemeCssUrl(): ?string
Gibt die CSS URL für das aktive Theme zurück.

#### TemplateHelper::getThemeCssUrlForTheme(string $themeName): string
Gibt die CSS URL für ein bestimmtes Theme zurück.

### Prüfmethoden

#### TemplateHelper::hasGoogleFonts(): bool
Prüft ob Google Fonts für das aktive Theme verfügbar sind.

## Funktionsweise

1. **Download**: Google Fonts werden lokal heruntergeladen (DSGVO-konform)
2. **Speicherung**: Fonts werden in `public/assets/addons/uikit_theme_builder/fonts/` gespeichert
3. **CSS-Generierung**: Für jede Font wird eine eigene CSS-Datei erstellt
4. **Automatische Erkennung**: Das System erkennt automatisch, welche Fonts vom Theme verwendet werden
5. **Template-Integration**: Fonts werden nur geladen, wenn sie auch verwendet werden

## Performance

- Fonts werden nur geladen, wenn sie im Theme tatsächlich verwendet werden
- Lokale Speicherung reduziert externe Requests
- Separate CSS-Dateien ermöglichen selektives Caching