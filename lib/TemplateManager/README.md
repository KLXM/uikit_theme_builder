# Template Manager ↔ UIKit Theme Builder Integration

Diese Integration verbindet den Template Manager mit dem UIKit Theme Builder und ermöglicht Domain- und Sprachspezifische Theme-Einstellungen.

## Features

### 1. Theme Selection Widget
- **Schlüssel**: `tm_uikit_theme`
- **Funktion**: Auswahl eines kompilierten UIKit Themes pro Domain/Sprache
- **Felder**:
  - Theme-Auswahl (Dropdown mit allen verfügbaren Themes)
  - Auto-Load Checkbox (Theme automatisch im Frontend laden)
- **Frontend-Nutzung**:
  ```php
  $themeSettings = TemplateManager::get('tm_uikit_theme');
  $themeName = $themeSettings['theme_name'] ?? '';
  
  if ($themeName && $themeSettings['auto_load']) {
      $cssUrl = \UikitThemeBuilder\TemplateManager\ThemeSelectionWidget::getThemeCssUrl($themeName);
      echo '<link rel="stylesheet" href="' . $cssUrl . '">';
  }
  ```

### 2. UIKit Icon + Link Repeater
- **Schlüssel**: `tm_icon_links`
- **Funktion**: Verwaltet Listen von Links mit UIKit Icons
- **Felder pro Item**:
  - Icon (UIKit Icon Picker)
  - Bezeichnung
  - URL (intern oder extern)
  - Ziel (_self oder _blank)
  - Beschreibung (optional)
- **Frontend-Nutzung**:
  ```php
  $iconLinks = TemplateManager::get('tm_icon_links', []);
  
  foreach ($iconLinks as $link) {
      echo '<a href="' . $link['url'] . '" target="' . $link['target'] . '">';
      echo '<span uk-icon="icon: ' . $link['icon'] . '"></span> ';
      echo $link['label'];
      echo '</a>';
  }
  ```

## Installation

Die Integration wird automatisch aktiviert, wenn beide AddOns installiert sind:
1. Template Manager
2. UIKit Theme Builder

Keine weitere Konfiguration erforderlich!

## Technische Details

### Auto-Registration
Die Widgets registrieren sich automatisch über den Extension Point `TEMPLATE_MANAGER_WIDGETS_REGISTERED`:

```php
// uikit_theme_builder/boot.php
if (rex_addon::get('template_manager')->isAvailable()) {
    require_once __DIR__ . '/lib/TemplateManager/boot.php';
}
```

### Widget-Klassen
- `UikitThemeBuilder\TemplateManager\ThemeSelectionWidget`
- `UikitThemeBuilder\TemplateManager\UikitIconLinkWidget`

### JavaScript Assets
Der UIKit Icon Picker wird automatisch geladen:
- Script: `assets/js/uikit-icon-picker.js`
- Icons: Aus dem Custom Icon Builder

## Verwendungsbeispiele

### Example 1: Domain-spezifische Themes
```php
// Hotel-Zur-Linde verwendet Theme "hotel-linde"
// Wellings.de verwendet Theme "wellings-corporate"
// Automatische Auswahl basierend auf aktueller Domain
```

### Example 2: Quick-Links mit Icons
```php
// Navigation
$quickLinks = TemplateManager::get('uikit_icon_links', []);

echo '<ul class="uk-nav">';
foreach ($quickLinks as $link) {
    echo '<li>';
    echo '<a href="' . $link['url'] . '">';
    echo '<span uk-icon="' . $link['icon'] . '"></span> ';
    echo $link['label'];
    echo '</a>';
    echo '</li>';
}
echo '</ul>';
```

## Best Practices

1. **Theme Auto-Load**: Nur aktivieren wenn das Theme immer geladen werden soll
2. **Icon Links**: Für Haupt-Navigation, Footer-Links, Social Media, etc.
3. **Validierung**: URLs werden automatisch validiert (intern vs. extern)
4. **Performance**: Themes werden gecacht, keine Performance-Einbußen

## Siehe auch

- [Template Manager Widget API](../template_manager/WIDGET_API.md)
- [UIKit Theme Builder Dokumentation](README.md)
