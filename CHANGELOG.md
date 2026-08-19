# Changelog

## [1.4.0] – 2026-04-20

### Changed

- **UIKit auf 3.25.15 aktualisiert** – Die bisher gültige Beschränkung auf 3.23.x war durch Inkompatibilitäten mit `wikimedia/less.php` bedingt. Nach Prüfung der aktuellen less.php-Version (v5.5.1) kompiliert UIKit 3.25.15 nun fehlerfrei.
- **`update-uikit.sh` – Default-Version** von `3.23.12` → `3.25.15`
- **`update-uikit.sh` – Bug gefixt**: `git clone` holte bisher immer den neuesten `main`-Branch, unabhängig von der angegebenen Version. Der Clone nutzt nun `--branch "v$TARGET_VERSION"`.
- **`update-uikit.sh` – Kompatibilitäts-Warnung** angepasst: Warnung für `>= 3.24` entfernt (da nun kompatibel); Warnung gilt nur noch bei Major-Version `> 3`.
- **`install.php` – Tabelle `rex_uikit_style_sets`** an den `StyleSetManager` angeglichen:
  - Spalte `slug VARCHAR(255)` hinzugefügt (fehlte komplett, verursachte SQL-Fehler beim Erstellen von Style-Sets)
  - `created_date` → `created` (Manager-Konvention)
  - `updated_date` → `updated` (Manager-Konvention)
  - `created_by` / `updated_by` entfernt (werden vom Manager nicht genutzt)
  - Unique-Index von `name` auf `slug` geändert (Manager sucht per Slug)

## [1.0.0] – 2025-xx-xx

- Initial Release
