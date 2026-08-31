# Changelog

## [2.2.0] – 2026-08-20

### Added

- **Live Theme Editor** (neues Info-Center-Widget, Frontend): Grundfarben, Schriftarten,
  Schriftgrößen, Zeilenhöhen, Abstände und Container-Breiten lassen sich jetzt direkt im
  Frontend live testen (Server-Sent Events, kein Reload nötig). Vorschau ist zunächst nur für
  die eigene Session sichtbar, per "Live schalten" für alle Besucher, per "Speichern" dauerhaft
  ins Theme übernommen (bestehende Compile-Pipeline).
  - Vollständige Farbpalette (alle ColorsWidget-Farben inkl. Success/Warning/Danger/Emphasis/
    Muted/Border/Inverse/Überschriften-Farbe), wirkt auch auf `uk-background-*`, `uk-card-*`
    und `uk-section-*`
  - Primär-/Überschriften-Schriftart inkl. bereits geladener Google Fonts
  - Schriftgrößen H1–H4 (mit Barrierefreiheits-Untergrenze: Basis-Schriftgröße nie unter 16px)
  - Zeilenhöhe für Fließtext und Überschriften
  - Maße: Standard-Margin, Padding/Gutter in drei Stufen (Small/Medium/Large), Container-
    Breite in drei Stufen (Standard/Small/Large)
  - Navigation: Navbar-Hintergrund/Text-/Hover-Farbe/Höhe sowie Dropdown-Hintergrund/Text-/
    Hover-Farbe/Breite (wirkt auf `uk-navbar-*` und `uk-navbar-dropdown*`)
  - Live-Theme-Wechsel: komplettes Theme testen und per Klick dauerhaft der Domain zuweisen;
    in den Addon-Einstellungen lässt sich einschränken, welche Themes im Wechsel-Dropdown
    überhaupt zur Auswahl stehen
  - "Bearbeitbare Elemente markieren": ein-/ausblendbare Overlay-Labels, die Container- und
    Section-Typen direkt auf der Seite kennzeichnen
  - Akkordeon-Gliederung (Typografie/Farben/Maße/Navigation), initial eingeklappt, mit
    passenden UIkit-Icons je Abschnitt und Aktion
  - Eigene, feingranulare Rechte statt des bisherigen `info_center[]`-Behelfs: Editor nutzen,
    Stile bearbeiten und Theme wechseln sind separat vergebbar (serverseitig durchgesetzt,
    nicht nur in der UI ausgeblendet)

### Changed

- Lizenz von proprietär auf **MIT** umgestellt.

### Security

- Versehentlich eingecheckten Google Fonts API Key aus dem Default von `package.yml` entfernt
  (war seit November 2025 öffentlich im Repo sichtbar). Der Default ist jetzt leer, jeder
  Nutzer trägt seinen eigenen Key über die Addon-Einstellungen ein. Der betroffene Key sollte
  in der Google Cloud Console widerrufen/rotiert werden, falls noch nicht geschehen.

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
