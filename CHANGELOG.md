# Changelog

Alle bemerkenswerten Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

## [1.1.0] - 2026-01-15

### Neue Features
- **EXDATE-Integration ins Widget**: Ausgeschlossene Termine werden jetzt direkt im RRULE-Widget verwaltet, nicht in einem separaten Datenbankfeld
- **Apple Calendar Design**: Komplett überarbeitete Benutzeroberfläche mit Apple Calendar-ähnlichen Komponenten
- **Flatpickr-Integration**: Datum-Picker für UNTIL und EXDATE Felder
- **Live-Preview**: Die Vorschau zeigt sofort lesbare Zusammenfassung der Wiederholungsregel
- **Modal mit nächsten Terminen**: In der Table Manager Liste können die nächsten 10 Vorkommen betrachtet werden
- **Dark Mode Support**: Widget passt sich an REDAXO Dark Mode an
- **CSS-Namespacing**: Alle Widget-Styles mit `.rrule-` Präfix um Bootstrap-Konflikte zu vermeiden

### Breaking Changes
- **Datenbank-Schema**: Das `exdate`-Feld sollte aus Tabellen entfernt werden (nicht automatisch). EXDATE wird jetzt im `rrule`-Feld gespeichert
- **EXDATE-Format**: EXDATE ist jetzt Teil des RRule-Strings (z.B. `FREQ=DAILY;EXDATE=2026-01-15,2026-03-15`)

### API-Änderungen
- **rex_api_rrule**: Neue API-Klasse für AJAX-Requests
  - `extractExdateFromRRule()`: Extrahiert EXDATE aus RRule-String
  - `removeExdateFromRRule()`: Entfernt EXDATE aus RRule-String für Verarbeitung
  - Verbesserte EXDATE-Filterung in `calculateOccurrences()` mit Sicherheit vor Endlosschleife

### Bugfixes
- **Timezone-Problem**: Korrekte Konvertierung lokaler Daten ohne UTC-Versatz (führte zu Tagwechseln bei Flatpickr)
- **EXDATE-Filterung**: Schleife bricht nicht mehr zu früh ab wenn Termine ausgeschlossen sind
- **RRule-Parsing**: Robustere Unterstützung für RFC-konforme RRule-Strings mit php-rrule Library

### Template-Änderungen
- `ytemplates/bootstrap/value.rrule.tpl.php`: Komplett überarbeitete Struktur mit Flatpickr-Integration

### CSS
- `assets/rrule.css`: Neues Design mit `--rrule-` CSS-Variablen, Dark Mode Support

### JavaScript
- `assets/rrule.js`: Erweiterte Funktionalität für EXDATE-Management
  - `addExdate()`: Fügt Termin zur Ausschlussliste hinzu
  - `renderExdateList()`: Rendert die Liste ausgeschlossener Termine
  - `formatDateLocalToIso()`: Konvertiert lokale Daten ohne Timezone-Versatz

### Dokumentation
- README.de.md und README.md aktualisiert mit neuen Features
- Beispiele für EXDATE-Nutzung hinzugefügt
- Hinweis auf Integration von EXDATE ins RRule-Widget

### Dependencies
- rlanvin/php-rrule: ^2.5 (für RFC-konforme RRule-Verarbeitung)

### Migration von älteren Versionen

Wenn Sie von 1.0.x upgraden:

1. **Tableset aktualisieren**: Das `exdate`-Feld wird automatisch ausgeblendet
2. **Bestehende EXDATE-Daten**: Werden weiterhin aus separaten `exdate`-Spalten gelesen (Fallback-Logik)
3. **Neue Einträge**: Speichern EXDATE direkt im `rrule`-Feld

Optional können Sie das alte `exdate`-Feld nach Migration löschen:

```sql
ALTER TABLE rex_yform_calendar DROP COLUMN exdate;
```

## [1.0.2] - vorherige Version
