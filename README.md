# YForm Calendar 🗓️

Ein umfassendes REDAXO-Addon für die Verwaltung von Kalenderereignissen mit **RFC 5545-konformen Wiederholungsregeln (RRULE)** und erweiterten Filteroptionen.

## ✨ Features

- 📅 **Volle RFC 5545 (iCalendar) Unterstützung** - Standard für Kalenderdaten
- 🔁 **Wiederholung (RRULE)** - Tägliche, wöchentliche, monatliche und jährliche Serien mit Ausnahmen (EXDATE)
- 🎨 **Modernes YForm Value Plugin** - Intuitiver RRULE-Editor für Listview und Formulare
- ⚡ **Generator-basiert** - Speichereffiziente Verarbeitung großer Ereignismengen
- 🔍 **Flexible Abfragen** - Zeiträume, SQL-Filter, benutzerdefinierte Queries
- 📦 **Model Class (CalRender)** - Erweiterbarer rex_yform_manager_dataset
- 🌍 **Multi-Table Support** - Der RRULE-Editor funktioniert mit **allen** YForm-Tabellen
- 🔗 **Alias-Klasse (YFormCalendarEvents)** - Für Rückwärtskompatibilität
- 📱 **Responsive UI** - Bootstrap 4 und 5 kompatibel
- 🌙 **Dark Mode Support** - CSS mit Dark Mode Varianten

## 📋 Anforderungen

| Paket | Version | Info |
|-------|---------|------|
| REDAXO | `^5.17` | CMS Core |
| YForm | `^5.0.0` | Formular-Addon erforderlich |
| PHP | `>8.2,<9` | Strict Types aktiviert |

## 🚀 Installation

### Schritt 1: Addon installieren

Im REDAXO Backend:
1. **Addons** → **Installer**
2. Nach "yform_calendar" suchen
3. **Installieren** klicken

Das war's! Das Addon wird automatisch aktiviert.

### Schritt 2: YForm-Feld hinzufügen (optional)

Für neue Tabellen mit RRULE-Support:
1. **YForm** → Deine Tabelle → **Struktur bearbeiten**
2. Ein neues Feld hinzufügen
3. Typ: **rrule**
4. Speichern

Fertig! Der RRULE-Editor und der "Termine"-Button sind nun verfügbar.

## 🌍 Multiple Kalender in REDAXO

Das wichtigste Feature: **Du kannst aus jeder YForm-Tabelle einen Kalender machen!** 📅

### Konzept: Ein Kalender pro Tabelle

Statt nur eine zentrale `rex_yform_calendar` Tabelle zu haben, kannst du beliebig viele **unabhängige Kalender** pflegen:

```
REDAXO Projekt
├── 📅 Kalender: Firmenevents
├── 📅 Kalender: Schulferien
├── 📅 Kalender: Feiertage
├── 📅 Kalender: Teamtreffen
└── 📅 Kalender: Kundenveranstaltungen
```

### Wie funktioniert's?

1. **Neue YForm-Tabelle erstellen** (z.B. `rex_yform_schulferien`)
2. **RRULE-Feld hinzufügen** (Typ: `rrule`)
3. **Weitere Felder** wie `dtstart`, `dtend`, `title` hinzufügen
4. **Custom Model Class erstellen** (optional, aber empfohlen)
5. **In Modulen/Plugins verwenden** wie gewohnt

### Praktische Use-Cases

| Kalender | Beispiel | Nutzen |
|----------|----------|--------|
| **Unternehmens-Events** | Konferenzen, Messen, Teamtreffen | Zentrale Planung |
| **Schulferien** | Bundesländer-spezifische Ferien | Bildungsportale |
| **Feiertage** | Nationale & regionale Feiertage | Geschlossene Zeiten |
| **Kurs-Termine** | Schulungskurse, Workshops | E-Learning Plattformen |
| **Ressourcen-Auslastung** | Buchungen, Reservierungen | Raum- und Geräte-Verwaltung |
| **Ausgabe-Kalender** | Müllabfuhr, Straßenreinigung | Bürger-Services |
| **Sport-Events** | Spieltage, Trainingszeiten | Sport-Verbände |

### Beispiel: Multiple Kalender

```php
<?php
use FriendsOfRedaxo\YFormCalendar\CalRender;

// Kalender 1: Firmenevent
$events1 = CalRender::getEventsByDate('2026-01-01', '2026-12-31');

// Kalender 2: Schulferien (eigene Tabelle, aber gleiche Struktur)
class SchulferienCalendar extends CalRender
{
    protected static string $table = 'rex_yform_schulferien';
}
$schoolHolidays = SchulferienCalendar::getEventsByDate('2026-01-01', '2026-12-31');

// Kalender 3: Feiertage
class HolidayCalendar extends CalRender
{
    protected static string $table = 'rex_yform_holidays';
}
$holidays = HolidayCalendar::getEventsByDate('2026-01-01', '2026-12-31');

// Alle zusammen anzeigen:
$allEvents = array_merge($events1, $schoolHolidays, $holidays);
usort($allEvents, fn($a, $b) => 
    strtotime($a->getValue('dtstart')) <=> strtotime($b->getValue('dtstart'))
);

foreach ($allEvents as $event) {
    echo $event->getValue('title') . ': ' . $event->getValue('dtstart');
}
?>
```

## 📊 Datenbankstruktur

Das Addon arbeitet standardmäßig mit der Tabelle `rex_yform_calendar`:

```sql
CREATE TABLE `rex_yform_calendar` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `summary` TEXT,
  `location` VARCHAR(255),
  `dtstart` DATETIME NOT NULL,
  `dtend` DATETIME NOT NULL,
  `all_day` TINYINT(1) DEFAULT 0,
  `rrule` TEXT,
  PRIMARY KEY (`id`)
);
```

### Feldtypen:

| Feld | Typ | Beschreibung | Erforderlich |
|------|-----|-------------|-------------|
| `title` | varchar(255) | Ereignistitel | Ja |
| `summary` | text | Kurzbeschreibung | Nein |
| `location` | varchar(255) | Veranstaltungsort | Nein |
| `dtstart` | datetime | Startzeitpunkt | Ja |
| `dtend` | datetime | Endzeitpunkt | Ja |
| `all_day` | tinyint(1) | Ganztägiges Ereignis (0/1) | Nein |
| `rrule` | text | RFC 5545 Wiederholungsregel | Nein |

## �️ Warum eine Model Class?

Eine **Model Class** wie `CalRender` ist die REDAXO-Standard-Methode für typsichere Datenbankabfragen und Code-Wiederverwendung:

### ✅ Vorteile

| Vorteil | Erklärung |
|---------|-----------|
| **Typsicherheit** | IDE-Autocompletion, Fehler-Früherkennung |
| **Wiederverwendbarkeit** | Business-Logik an einer Stelle, nicht verstreut |
| **Wartbarkeit** | Änderungen am Datenmodell nur in der Model Class |
| **Skalierbarkeit** | Custom Methods für spezielle Abfragen |
| **Testing** | Model Class ist leicht zu testen |
| **Standards** | Folgt REDAXO Best Practices (rex_yform_manager_dataset) |

### ❌ Ohne Model Class (Probleme)

```php
// ❌ Schlecht: Jedes Modul/Plugin macht eigene Abfragen
$events = rex_yform_manager_dataset::query('rex_yform_calendar')
    ->where('dtstart', '>=', date('Y-m-d'))
    ->find();

// → Code ist verstreut
// → RFC 5545 RRULE-Logik muss überall wiederholt werden
// → Bei Feldnamen-Änderung: Überall updaten
```

### ✅ Mit Model Class (Sauberer Code)

```php
// ✅ Gut: Zentrale Model Class
use FriendsOfRedaxo\YFormCalendar\CalRender;

$events = CalRender::getEventsByDate(date('Y-m-d'));

// → RFC 5545 RRULE-Expansion ist integriert
// → Konsistente API überall
// → RRULE-Logik wird automatisch angewendet
// → Änderungen nur in CalRender nötig
```

### 📦 CalRender - Spezial-Features der Model Class

Die `CalRender` Model Class bietet **vordefinierte Logik** für Kalender-Funktionen:

1. **RFC 5545 RRULE-Expansion** - Automatische Berechnung wiederkehrender Termine
2. **EXDATE-Filterung** - Ausnahmen bei wiederkehrenden Terminen
3. **Speicherer-effiziente Generatoren** - Für große Datenmengen
4. **Vordefinierte Filter-Methoden** - `getEventsByDate()`, `getNextEvents()`, etc.

Ohne Model Class müsstest du diese Logik **selbst implementieren** - kompliziert und fehleranfällig!

## �🎯 Quick Start

### 1. Alle Ereignisse abrufen

```php
<?php
use FriendsOfRedaxo\YFormCalendar\CalRender;

// Einfache Datumsbereichsabfrage
$today = date('Y-m-d');
$endDate = date('Y-m-d', strtotime('+90 days'));
$events = CalRender::getEventsByDate($today, $endDate, 100);

foreach ($events as $event) {
    echo $event->getValue('title') . ' - ';
    echo date('d.m.Y H:i', strtotime($event->getValue('dtstart')));
}
?>
```

### 2. Generator für große Datenmengen

```php
<?php
use FriendsOfRedaxo\YFormCalendar\CalRender;

// Memory-effizient
$startDate = '2026-01-01';
$endDate = '2026-12-31';

foreach (CalRender::getCalendarEvents([
    'startDate' => $startDate,
    'endDate' => $endDate,
    'limit' => 1000,
    'sortByStart' => 'ASC'
]) as $event) {
    // Verarbeitung...
    echo $event->getValue('title') . "\n";
}
?>
```

### 3. Nächste 10 Ereignisse

```php
<?php
use FriendsOfRedaxo\YFormCalendar\CalRender;

$nextEvents = CalRender::getNextEvents($eventId = 1, $limit = 10);

foreach ($nextEvents as $event) {
    echo $event->getValue('title');
}
?>
```

### 4. Ereignisse mit SQL-Filter

```php
<?php
use FriendsOfRedaxo\YFormCalendar\CalRender;

// Mit whereRaw:
foreach (CalRender::getCalendarEvents([
    'startDate' => '2026-01-01',
    'endDate' => '2026-12-31',
    'whereRaw' => 'location IS NOT NULL AND location != ""'
]) as $event) {
    echo $event->getValue('title') . ' @ ' . $event->getValue('location');
}
?>
```

### 5. Ganztägige Ereignisse filtern

```php
<?php
use FriendsOfRedaxo\YFormCalendar\CalRender;

$allDayEvents = CalRender::getEventsByDate('2026-01-01', '2026-12-31');

foreach ($allDayEvents as $event) {
    if ($event->getValue('all_day')) {
        echo '<strong>' . $event->getValue('title') . '</strong> (Ganztägig)';
    }
}
?>
```

## 📖 Modul-Beispiel (Arbeitsfertig)

Erstelle ein neues Modul im Backend mit folgendem Code:

```php
<?php
namespace FriendsOfRedaxo\YFormCalendar;

use FriendsOfRedaxo\YFormCalendar\CalRender;

$startDate = date('Y-m-d');
$endDate = date('Y-m-d', strtotime('+1000 days'));
$eventId = 1;
$limit = 10;
?>
<div class="calrender-container">
    <h2>Nächste Termine</h2>
    
    <?php
    $nextEvents = CalRender::getNextEvents($eventId, $limit);
    
    if (empty($nextEvents)):
        echo '<div class="alert alert-info">Keine zukünftigen Termine gefunden.</div>';
    else:
    ?>
    <div class="events-list">
        <?php foreach ($nextEvents as $event): ?>
        <div class="event-item">
            <div class="event-header">
                <h3><?= rex_escape($event->getValue('title')) ?></h3>
                <?php if ($event->getValue('location')): ?>
                <span class="event-location">
                    <i class="fa fa-map-marker"></i> 
                    <?= rex_escape($event->getValue('location')) ?>
                </span>
                <?php endif; ?>
            </div>
            
            <div class="event-datetime">
                <?php
                $start = strtotime($event->getValue('dtstart'));
                $end = strtotime($event->getValue('dtend'));
                $isAllDay = $event->getValue('all_day');
                ?>
                <i class="fa fa-calendar"></i>
                <?php if ($isAllDay): ?>
                    <?= date('d.m.Y', $start) ?>
                <?php else: ?>
                    <?= date('d.m.Y H:i', $start) ?> - <?= date('H:i', $end) ?>
                <?php endif; ?>
            </div>
            
            <?php if ($event->getValue('summary')): ?>
            <div class="event-summary">
                <?= nl2br(rex_escape($event->getValue('summary'))) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($event->getValue('rrule')): ?>
            <div class="event-recurring">
                <span class="badge badge-info">Wiederkehrend</span>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.calrender-container {
    max-width: 800px;
    margin: 20px 0;
}

.events-list {
    list-style: none;
    padding: 0;
}

.event-item {
    border-left: 4px solid #007bff;
    padding: 15px;
    margin-bottom: 15px;
    background: #f9f9f9;
    border-radius: 4px;
    transition: all 0.3s;
}

.event-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateX(2px);
}

.event-header {
    margin-bottom: 10px;
}

.event-header h3 {
    margin: 0 0 5px 0;
    color: #333;
}

.event-location {
    font-size: 12px;
    color: #666;
    display: inline-block;
}

.event-datetime {
    font-size: 13px;
    color: #666;
    margin-bottom: 10px;
}

.event-summary {
    font-size: 13px;
    color: #555;
    line-height: 1.5;
    margin-bottom: 10px;
}

.event-recurring {
    margin-top: 10px;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .event-item {
        background: #2a2a2a;
    }
    
    .event-header h3 {
        color: #e0e0e0;
    }
    
    .event-datetime,
    .event-location,
    .event-summary {
        color: #b0b0b0;
    }
}
</style>
```

## 🔧 Custom Model Classes

### Muster 1: Wrapper für Alias

```php
<?php
namespace MyProject\Calendar;

use FriendsOfRedaxo\YFormCalendar\CalRender;

class CustomCalendar extends CalRender
{
    // Alle Methoden von CalRender erben
}

// Verwendung:
$events = CustomCalendar::getEventsByDate('2026-01-01', '2026-12-31');
```

### Muster 2: Erweiterung mit Custom Methods

```php
<?php
namespace MyProject\Calendar;

use FriendsOfRedaxo\YFormCalendar\CalRender;

class EnhancedCalendar extends CalRender
{
    /**
     * Nur Events der nächsten 7 Tage
     */
    public static function getWeeklyEvents(int $limit = 50): array
    {
        $today = date('Y-m-d');
        $nextWeek = date('Y-m-d', strtotime('+7 days'));
        return self::getEventsByDate($today, $nextWeek, $limit);
    }
    
    /**
     * Nur ganztägige Events
     */
    public static function getAllDayEvents(string $startDate, ?string $endDate = null): array
    {
        $events = self::getEventsByDate($startDate, $endDate);
        return array_filter($events, fn($e) => $e->getValue('all_day'));
    }
}

// Verwendung:
$weeklyEvents = EnhancedCalendar::getWeeklyEvents();
$allDayEvents = EnhancedCalendar::getAllDayEvents('2026-01-01', '2026-12-31');
```

### Muster 3: Multi-Table Aggregation

```php
<?php
namespace MyProject\Calendar;

use FriendsOfRedaxo\YFormCalendar\CalRender;
use rex_yform_manager_table;

class UnifiedCalendar
{
    /**
     * Termine aus mehreren Tabellen zusammenführen
     */
    public static function getUnifiedEvents(
        string $startDate, 
        ?string $endDate = null, 
        int $limit = 500
    ): array {
        $allEvents = [];
        
        // Tabellen definieren
        $tables = [
            ['class' => CalRender::class, 'table' => 'rex_yform_calendar'],
            ['class' => OtherEventClass::class, 'table' => 'rex_yform_events'],
        ];
        
        // Alle Tabellen abfragen
        foreach ($tables as $tableConfig) {
            $events = $tableConfig['class']::getEventsByDate($startDate, $endDate, $limit);
            $allEvents = array_merge($allEvents, $events);
        }
        
        // Sortieren nach Startdatum
        usort($allEvents, function($a, $b) {
            return strtotime($a->getValue('dtstart')) <=> strtotime($b->getValue('dtend'));
        });
        
        return array_slice($allEvents, 0, $limit);
    }
}

// Verwendung:
$unifiedEvents = UnifiedCalendar::getUnifiedEvents('2026-01-01', '2026-12-31', 100);
```

## 📤 Export-Beispiele

### iCalendar (iCal) Format

```php
<?php
use FriendsOfRedaxo\YFormCalendar\CalRender;

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="events.ics"');

$events = CalRender::getEventsByDate('2026-01-01', '2026-12-31');

echo "BEGIN:VCALENDAR\n";
echo "VERSION:2.0\n";
echo "PRODID:-//MyProject//REDAXO//EN\n";
echo "CALSCALE:GREGORIAN\n";

foreach ($events as $event) {
    echo "BEGIN:VEVENT\n";
    echo "DTSTART:" . date('Ymd\THis', strtotime($event->getValue('dtstart'))) . "\n";
    echo "DTEND:" . date('Ymd\THis', strtotime($event->getValue('dtend'))) . "\n";
    echo "SUMMARY:" . $event->getValue('title') . "\n";
    
    if ($event->getValue('summary')) {
        echo "DESCRIPTION:" . $event->getValue('summary') . "\n";
    }
    
    if ($event->getValue('location')) {
        echo "LOCATION:" . $event->getValue('location') . "\n";
    }
    
    if ($event->getValue('rrule')) {
        echo "RRULE:" . $event->getValue('rrule') . "\n";
    }
    
    echo "END:VEVENT\n";
}

echo "END:VCALENDAR\n";
exit;
?>
```

### JSON Format

```php
<?php
use FriendsOfRedaxo\YFormCalendar\CalRender;

header('Content-Type: application/json; charset=utf-8');

$events = CalRender::getEventsByDate('2026-01-01', '2026-12-31');

$jsonEvents = [];
foreach ($events as $event) {
    $jsonEvents[] = [
        'id' => $event->getId(),
        'title' => $event->getValue('title'),
        'summary' => $event->getValue('summary'),
        'location' => $event->getValue('location'),
        'start' => $event->getValue('dtstart'),
        'end' => $event->getValue('dtend'),
        'allDay' => (bool)$event->getValue('all_day'),
        'rrule' => $event->getValue('rrule'),
    ];
}

echo json_encode($jsonEvents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
?>
```

## 🌐 RRULE Format Referenz

Das Addon unterstützt **RFC 5545 Recurrence Rules (RRULE)**:

```
FREQ=DAILY;INTERVAL=1;EXDATE=2026-01-15,2026-01-20
FREQ=WEEKLY;BYDAY=MO,WE,FR;UNTIL=2026-12-31
FREQ=MONTHLY;BYMONTHDAY=15;COUNT=24
FREQ=YEARLY;BYMONTH=12;BYMONTHDAY=25
```

### Unterstützte Parameter:

| Parameter | Werte | Beispiel |
|-----------|-------|----------|
| `FREQ` | DAILY, WEEKLY, MONTHLY, YEARLY | `FREQ=DAILY` |
| `INTERVAL` | Positive Integer | `INTERVAL=2` (jeden 2. Tag) |
| `BYDAY` | MO, TU, WE, TH, FR, SA, SU | `BYDAY=MO,WE,FR` |
| `BYMONTHDAY` | 1-31 | `BYMONTHDAY=15` |
| `BYMONTH` | 1-12 | `BYMONTH=12` |
| `COUNT` | Positive Integer | `COUNT=10` (10 Vorkommen) |
| `UNTIL` | YYYYMMDD | `UNTIL=20261231` |
| `EXDATE` | Komma-getrennte Daten | `EXDATE=2026-01-15,2026-01-20` |

### EXDATE für Ausnahmen:

```php
// Einzelne Ausnahmendaten:
FREQ=DAILY;EXDATE=2026-01-15,2026-01-20

// Datumsbereiche (Custom):
FREQ=DAILY;EXDATE=2026-01-15/2026-01-20
```

## 🔗 API-Referenz

Siehe [API.md](API.md) für die vollständige API-Dokumentation mit allen Methoden, Parametern und Advanced Use Cases.

## 📝 Changelog

Siehe [CHANGELOG.md](CHANGELOG.md) für alle Versionsänderungen.

## 📄 Lizenz

MIT - Siehe [LICENSE](LICENSE) für Details.

## 🤝 Beitragen

Beiträge sind willkommen! Bitte erstelle einen Pull Request oder öffne ein Issue.

## 💬 Support

- GitHub Issues: https://github.com/FriendsOfREDAXO/yform_calendar/issues
- REDAXO Community: https://community.redaxo.org/

---

**YForm Calendar** entwickelt mit ❤️ von Friends of REDAXO
