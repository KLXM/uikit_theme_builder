# UIKit LESS Sources v3.23.12

Dieses Verzeichnis enthält nur die LESS-Dateien von UIKit v3.23.12, die für die Theme-Kompilierung benötigt werden.

## Struktur

```
assets/uikit-less/
├── less/                    # Original UIKit LESS-Dateien
│   ├── components/         # UIKit Komponenten
│   ├── theme/             # UIKit Theme-LESS
│   └── uikit.theme.less   # Haupt-Theme-Datei
└── styles.less            # Custom Wellings Styles
```

## Warum nur LESS?

- **Minimaler Footprint**: Nur ~500KB statt 50MB+ vom kompletten UIKit
- **Theme-Kompilierung**: Nur LESS-Dateien werden für PHP-Kompilierung benötigt
- **Stabile Version**: UIKit v3.23.12 (LESS.php kompatibel)
- **JavaScript separat**: UIKit-JS wird aus den kompilierten Assets geladen

## Verwendung im Theme Builder

Der UikitThemeBuilderManager referenziert diese LESS-Dateien:

```php
// In createTempLessFile()
$lessContent .= '@import "' . $this->uikitLessPath . '/less/uikit.theme.less";' . "\n\n";
```

## Version Lock

Diese LESS-Dateien sind auf UIKit v3.23.12 eingefroren, da neuere Versionen moderne CSS-Selektoren verwenden, die LESS.php nicht parsen kann.