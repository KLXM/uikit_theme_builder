# Copilot Instructions for UIkit Theme Builder Refactoring

## Status: ✅ ABGESCHLOSSEN

Die Refactoring-Arbeiten wurden erfolgreich durchgeführt.

## Durchgeführte Änderungen

### 1. TypographyWidget.php
- **Font-Fallback Variablen** umbenannt von `font_fallback_*` zu `font-fallback-*`
- Diese Variablen werden **NICHT** als LESS-Variablen generiert (nur intern verwendet)
- Alle anderen Typografie-Variablen verwenden bereits die korrekten UIkit-Namen
- **Hook für `base-heading`** hinzugefügt, um sicherzustellen, dass Überschriften-Schrift UND Text-Transform angewendet werden
- **`inherit` Werte** werden nun korrekt als LESS-Variablen generiert (nicht mehr gefiltert)

### 2. ShadowWidget.php
- **Alle Shadow-Keys** umbenannt zu korrekten UIkit-Namen:
  - `box_shadow_small` → `global-small-box-shadow`
  - `box_shadow_medium` → `global-medium-box-shadow`
  - `box_shadow_large` → `global-large-box-shadow`
  - `box_shadow_xlarge` → `global-xlarge-box-shadow`
  - `drop_shadow_small` → `global-small-drop-shadow`
  - `drop_shadow_medium` → `global-medium-drop-shadow`
  - `drop_shadow_large` → `global-large-drop-shadow`
  - `drop_shadow_xlarge` → `global-xlarge-drop-shadow`
- Variable Mapping in `generateLessVariables()` entfernt (nicht mehr nötig)
- Alle String-Checks aktualisiert (`strpos` für `box-shadow` statt `box_shadow`)

### 3. UikitThemeBuilderManager.php
- **`flattenArray()` Methode** überarbeitet:
  - Entfernt: `str_replace('_', '-', $key)` - keine automatische Konvertierung mehr
  - Stattdessen: Präfixe werden direkt mit Hyphens kombiniert (`$prefix . '-' . $key`)
  - Dokumentation hinzugefügt, dass alle Widget-Keys bereits im korrekten Format sein müssen

### 4. Debug-Seite erstellt
- **`pages/debug.php`** neu erstellt
- Zeigt Theme-Datenstruktur, flattened Data und generierte LESS-Variablen
- Automatische Validierung und Problemerkennung:
  - Warnt vor Variablen mit Unterstrichen
  - Prüft ob font-fallback Variablen fälschlicherweise als LESS-Variablen generiert werden
  - Vergleicht mit UIkit Standard-Variablen
- Theme-Auswahl per Dropdown

## Breaking Changes

⚠️ **Wichtig:** Diese Änderungen sind Breaking Changes für bestehende Themes!

Bestehende Themes müssen aktualisiert werden:
- Shadow-Variablen in gespeicherten Theme-JSON-Dateien müssen manuell umbenannt werden
- Font-Fallback Variablen verwenden nun Hyphens statt Underscores

## Testing

Um die Änderungen zu testen:

1. Öffne die Debug-Seite: `?page=uikit_theme_builder/debug`
2. Wähle ein Theme aus
3. Überprüfe die "Analyse & Validierung" Sektion
4. Alle Variablen sollten nun das korrekte Format haben (Hyphens, keine Underscores)
5. Font-fallback Variablen sollten NICHT in den generierten LESS-Variablen erscheinen

## Vorteile der Änderungen

1. **Direkte UIkit-Kompatibilität**: Variablennamen entsprechen exakt den UIkit LESS-Variablen
2. **Keine versteckte Konvertierung**: Transparentes Verhalten, was in LESS landet
3. **Bessere Wartbarkeit**: Widget-Code ist klarer und selbsterklärender
4. **Korrekte Typografie**: Alle Typografie-Stile werden nun korrekt angewendet
5. **Korrekte Schatten**: Shadow-Variablen nutzen die offiziellen UIkit-Namen

## Nächste Schritte

- [ ] Bestehende Theme-Dateien migrieren (manuell oder via Migration-Script)
- [ ] Debug-Seite kann im Produktivbetrieb entfernt oder über Konfiguration deaktiviert werden
- [ ] Dokumentation für Benutzer aktualisieren
