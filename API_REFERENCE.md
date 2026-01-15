# YFormCalendar - API-Referenz

Vollständige API-Dokumentation für YFormCalendarEvents und CalRender.

## YFormCalendarEvents

Hauptklasse für den Zugriff auf Kalenderereignisse.

### getCalendarEvents()

```php
public static function getCalendarEvents(
    array $params = [],
    rex_yform_manager_query $customQuery = null
): Generator
```

**Beschreibung:**
Generator für effiziente Verarbeitung großer Ereignismengen. Unterstützt wiederkehrende Termine mit RRULE und EXDATE-Filterung.

**Parameter:**

| Name | Typ | Standard | Beschreibung |
|------|-----|---------|-------------|
| `startDate` | string | null | Startdatum im Format `Y-m-d` oder `Y-m-d H:i:s` |
| `endDate` | string | null | Enddatum im Format `Y-m-d` oder `Y-m-d H:i:s` |
| `limit` | int | PHP_INT_MAX | Maximale Anzahl von Ereignissen |
| `sortByStart` | string | ASC | Sortierung nach dtstart: `ASC` oder `DESC` |
| `sortByEnd` | string | ASC | Sortierung nach dtend: `ASC` oder `DESC` |
| `whereRaw` | string | null | Zusätzliche SQL-WHERE-Klausel |
| `customQuery` | rex_yform_manager_query | null | Benutzerdefinierte Query statt Standard |

**Rückgabe:** Generator mit Ereignis-Objekten

**Beispiel:**

```php
use FriendsOfRedaxo\YFormCalendar\YFormCalendarEvents;

// Alle Termine ab heute
foreach (YFormCalendarEvents::getCalendarEvents([
    'startDate' => date('Y-m-d'),
    'limit' => 100
]) as $event) {
    echo $event->getValue('title');
}

// Mit SQL-Filter
foreach (YFormCalendarEvents::getCalendarEvents([
    'startDate' => '2026-01-15',
    'endDate' => '2026-12-31',
    'whereRaw' => 'location IS NOT NULL',
    'sortByStart' => 'DESC'
]) as $event) {
    // ...
}
```

---

### getEventsByDate()

```php
public static function getEventsByDate(
    string $startDate,
    ?string $endDate = null,
    int $limit = PHP_INT_MAX
): array
```

**Beschreibung:**
Gibt alle Ereignisse in einem Datumsbereich als Array zurück. Einfacher als Generator für kleinere Mengen.

**Parameter:**

| Name | Typ | Standard | Beschreibung |
|------|-----|---------|-------------|
| `startDate` | string | - | Startdatum im Format `Y-m-d` oder `Y-m-d H:i:s` |
| `endDate` | string | null | Enddatum; null = unbegrenzt |
| `limit` | int | PHP_INT_MAX | Maximale Anzahl |

**Rückgabe:** Array mit Ereignis-Objekten

**Beispiel:**

```php
// Nächste 10 Termine
$events = YFormCalendarEvents::getEventsByDate(
    date('Y-m-d'),
    null,
    10
);

// Termine dieser Woche
$startDate = date('Y-m-d', strtotime('monday this week'));
$endDate = date('Y-m-d', strtotime('sunday this week'));
$weekEvents = YFormCalendarEvents::getEventsByDate($startDate, $endDate);

// Termine im Januar 2026
$janEvents = YFormCalendarEvents::getEventsByDate('2026-01-01', '2026-01-31');
```

---

## CalRender (Basis-Klasse)

Erweiterte Methoden der CalRender-Klasse für benutzerdefinierte Implementierungen.

### getNextEvents()

```php
public static function getNextEvents(
    int $eventId,
    int $limit,
    ?string $startDateTime = null
): array
```

**Beschreibung:**
Gibt die nächsten X Vorkommen eines spezifischen wiederholenden Ereignisses zurück.

**Parameter:**

| Name | Typ | Beschreibung |
|------|-----|-------------|
| `eventId` | int | ID des Ereignisses |
| `limit` | int | Maximale Anzahl von Vorkommen |
| `startDateTime` | string | Startpunkt; null = jetzt |

**Rückgabe:** Array mit Ereignis-Objekten

**Beispiel:**

```php
// Nächste 5 Vorkommen eines Termins (ID 42)
$occurrences = YFormCalendarEvents::getNextEvents(42, 5);

foreach ($occurrences as $occurrence) {
    echo rex_formatter::intlDateTime(strtotime($occurrence->getValue('dtstart')), [IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT]);
}
```

---

### getEventDetailsByOccurrence()

```php
public static function getEventDetailsByOccurrence(
    int $eventId,
    string $occurrenceDate
): ?rex_yform_manager_dataset
```

**Beschreibung:**
Gibt Details zu einem spezifischen Vorkommen eines wiederholenden Ereignisses zurück.

**Parameter:**

| Name | Typ | Beschreibung |
|------|-----|-------------|
| `eventId` | int | ID des Ereignisses |
| `occurrenceDate` | string | Datum im Format `Y-m-d` |

**Rückgabe:** Ereignis-Datensatz mit angepassten Start-/Endzeitpunkten, oder null

**Beispiel:**

```php
// Detailanzeige für 15. März 2026
$event = YFormCalendarEvents::getEventDetailsByOccurrence(42, '2026-03-15');

if ($event) {
    echo $event->getValue('dtstart');  // 2026-03-15 10:00:00
    echo $event->getValue('dtend');    // 2026-03-15 11:00:00
}
```

---

## Ereignis-Objekt (rex_yform_manager_dataset)

Jedes Ereignis ist ein YForm Manager Dataset mit folgenden Methoden:

### getValue()

```php
public function getValue(string $fieldName): mixed
```

**Beispiel:**

```php
$title = $event->getValue('title');
$start = $event->getValue('dtstart');
$isAllDay = $event->getValue('all_day');
$rrule = $event->getValue('rrule');
$location = $event->getValue('location');
```

**Verfügbare Felder:**

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| `id` | int | Eindeutige ID |
| `title` | string | Ereignistitel |
| `summary` | text | Kurzbeschreibung |
| `location` | string | Ort |
| `dtstart` | string | Startzeitpunkt (Y-m-d H:i:s) |
| `dtend` | string | Endzeitpunkt (Y-m-d H:i:s) |
| `all_day` | bool | Ganztägig (0/1) |
| `rrule` | string | Wiederholungsregel (z.B. `FREQ=DAILY;EXDATE=2026-01-15`) |

### getId()

```php
public function getId(): int
```

Gibt die ID des Ereignisses zurück.

---

## RRULE Format

Ereignisse verwenden RFC 5545 konforme RRULE-Strings mit integrierter EXDATE:

```
FREQ=DAILY;INTERVAL=2;COUNT=10;EXDATE=2026-01-15,2026-01-17
FREQ=WEEKLY;BYDAY=MO,WE,FR;UNTIL=2026-12-31
FREQ=MONTHLY;BYMONTHDAY=15;EXDATE=2026-02-15
FREQ=YEARLY;BYMONTH=12;BYMONTHDAY=25
```

**Komponenten:**

| Komponente | Beispiel | Beschreibung |
|-----------|----------|-------------|
| `FREQ` | DAILY, WEEKLY, MONTHLY, YEARLY | Wiederholungsfrequenz |
| `INTERVAL` | 1, 2, 3... | Wiederholungsintervall |
| `COUNT` | 10 | Anzahl der Vorkommen |
| `UNTIL` | 2026-12-31 | Enddatum |
| `BYDAY` | MO, TU, WE... | Wochentage |
| `BYMONTHDAY` | 1-31 | Monatstage |
| `BYMONTH` | 1-12 | Monate |
| `EXDATE` | 2026-01-15,2026-03-20 | Ausgeschlossene Daten |

---

## Erweiterte Beispiele

### 1. Alle Termine einer Kategorie filtern

```php
use rex_yform_manager_table;

$table = rex_yform_manager_table::get('rex_yform_calendar');
$query = $table->query()->whereRaw('category = "meetings"');

foreach (YFormCalendarEvents::getCalendarEvents(
    ['startDate' => date('Y-m-d')],
    $query
) as $event) {
    echo $event->getValue('title');
}
```

### 2. Termine pro Wochentag gruppieren

```php
$startDate = date('Y-m-d', strtotime('monday this week'));
$endDate = date('Y-m-d', strtotime('sunday this week'));

$byDay = array_fill(0, 7, []);
$events = YFormCalendarEvents::getEventsByDate($startDate, $endDate);

foreach ($events as $event) {
    $dow = date('w', strtotime($event->getValue('dtstart')));
    $byDay[$dow][] = $event;
}
```

### 3. Nächste 30 Tage mit Terminen

```php
$startDate = date('Y-m-d');
$endDate = date('Y-m-d', strtotime('+30 days'));
$events = YFormCalendarEvents::getEventsByDate($startDate, $endDate, 1000);

// Gruppiere nach Datum
$byDate = [];
foreach ($events as $event) {
    $date = date('Y-m-d', strtotime($event->getValue('dtstart')));
    if (!isset($byDate[$date])) {
        $byDate[$date] = [];
    }
    $byDate[$date][] = $event;
}

// Nur Daten mit Terminen
foreach ($byDate as $date => $dayEvents) {
    echo '<strong>' . date('d.m.Y', strtotime($date)) . '</strong>';
    foreach ($dayEvents as $event) {
        echo $event->getValue('title') . '<br>';
    }
}
```

### 4. Termine als JSON exportieren

```php
$events = YFormCalendarEvents::getEventsByDate(
    date('Y-m-d'),
    date('Y-m-d', strtotime('+90 days')),
    500
);

$json = [];
foreach ($events as $event) {
    $json[] = [
        'id' => $event->getId(),
        'title' => $event->getValue('title'),
        'start' => $event->getValue('dtstart'),
        'end' => $event->getValue('dtend'),
        'allDay' => (bool) $event->getValue('all_day'),
        'location' => $event->getValue('location'),
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
```

---

## Custom Model Classes

### Übersicht

Es gibt drei Wege, um mit unterschiedlichen Tabellen oder Custom-Logik zu arbeiten:

| Ansatz | Komplexität | Use Case |
|--------|------------|----------|
| **Wrapper** | Einfach | Andere Tabelle, keine Custom-Logik |
| **Extends CalRender** | Mittel | Zusätzliche Methoden & Features |
| **CombinedManager** | Komplex | Mehrere Tabellen kombinieren |

### Pattern 1: Wrapper um rex_yform_manager_table

Für **einfache Cases** ohne zusätzliche Logik:

```php
<?php
namespace MyAddon\Calendar;

use rex_yform_manager_table;
use FriendsOfRedaxo\YFormCalendar\CalRender;

class CustomTableEvents
{
    public static function getCalendarEvents(array $params = [], $query = null)
    {
        $table = rex_yform_manager_table::get('my_custom_table');
        $query = $query ?? $table->query();
        
        return CalRender::getCalendarEvents($params, $query);
    }

    public static function getEventsByDate($start, $end = null, $limit = PHP_INT_MAX)
    {
        return iterator_to_array(self::getCalendarEvents([
            'startDate' => $start,
            'endDate' => $end,
            'limit' => $limit
        ]));
    }
}
```

**Verwendung:**
```php
$events = CustomTableEvents::getEventsByDate('2026-01-15', '2026-12-31', 50);
```

### Pattern 2: Erweiterte Model-Klasse

Für **zusätzliche Business-Logik**:

```php
<?php
namespace MyAddon\Calendar;

use DateTime;
use rex_yform_manager_table;
use FriendsOfRedaxo\YFormCalendar\CalRender;

class EnhancedEventManager
{
    public static function getUpcomingEvents(int $daysAhead = 30, int $limit = 50): array
    {
        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime("+$daysAhead days"));
        return self::getEventsByDate($start, $end, $limit);
    }

    public static function getEventsByStatus(string $status = 'published'): array
    {
        $table = rex_yform_manager_table::get('my_events_table');
        $query = $table->query()->whereRaw("status = '$status'");
        
        return iterator_to_array(CalRender::getCalendarEvents(
            ['startDate' => date('Y-m-d'), 'limit' => 1000],
            $query
        ));
    }

    public static function getEventsByDate(string $start, ?string $end = null, int $limit = PHP_INT_MAX): array
    {
        $table = rex_yform_manager_table::get('my_events_table');
        
        return iterator_to_array(CalRender::getCalendarEvents([
            'startDate' => $start,
            'endDate' => $end,
            'limit' => $limit
        ], $table->query()));
    }

    public static function getEventsByVenue(string $venue): array
    {
        $table = rex_yform_manager_table::get('my_events_table');
        $query = $table->query()->whereRaw("location LIKE '%$venue%'");
        
        return iterator_to_array(CalRender::getCalendarEvents(
            ['startDate' => date('Y-m-d'), 'limit' => 1000],
            $query
        ));
    }
}
```

**Verwendung:**
```php
// Nächste 14 Tage
$events = EnhancedEventManager::getUpcomingEvents(14);

// Nur veröffentlichte
$published = EnhancedEventManager::getEventsByStatus('published');

// Nach Location
$venues = EnhancedEventManager::getEventsByVenue('Berlin');
```

### Pattern 3: Mehrere Datenquellen kombinieren

Für **aggregierte Kalender**:

```php
<?php
namespace MyAddon\Calendar;

use DateTime;
use FriendsOfRedaxo\YFormCalendar\CalRender;
use FriendsOfRedaxo\YFormCalendar\YFormCalendarEvents;
use rex_yform_manager_table;

class UnifiedCalendar
{
    /**
     * Alle Termine aus beliebig vielen Tabellen
     */
    public static function getAll(string $startDate, ?string $endDate = null, int $limit = 500): array
    {
        $allEvents = [];
        
        // Datenquellen definieren
        $tables = [
            'rex_yform_calendar',      // Haupt-Kalender
            'events_conferences',      // Konferenzen
            'events_holidays',         // Feiertage
        ];
        
        foreach ($tables as $tableName) {
            try {
                $table = rex_yform_manager_table::get($tableName);
                if (!$table) continue;
                
                $events = CalRender::getCalendarEvents([
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'limit' => $limit
                ], $table->query());
                
                foreach ($events as $event) {
                    $allEvents[] = $event;
                }
            } catch (\Exception $e) {
                // Tabelle existiert nicht, skip
                rex_logger::factory()->log(
                    'calendar',
                    "Tabelle $tableName nicht gefunden",
                    rex_logger::WARNING
                );
            }
        }
        
        // Nach Datum sortieren
        usort($allEvents, function ($a, $b) {
            $timeA = strtotime($a->getValue('dtstart'));
            $timeB = strtotime($b->getValue('dtstart'));
            return $timeA <=> $timeB;
        });
        
        return array_slice($allEvents, 0, $limit);
    }

    /**
     * Filterbare Aggregation
     */
    public static function getFiltered(
        string $startDate,
        ?string $endDate = null,
        array $tables = [],
        ?string $whereClause = null
    ): array {
        if (empty($tables)) {
            $tables = ['rex_yform_calendar', 'events_conferences'];
        }
        
        $allEvents = [];
        
        foreach ($tables as $tableName) {
            $table = rex_yform_manager_table::get($tableName);
            if (!$table) continue;
            
            $query = $table->query();
            if ($whereClause) {
                $query->whereRaw($whereClause);
            }
            
            $events = CalRender::getCalendarEvents([
                'startDate' => $startDate,
                'endDate' => $endDate,
                'limit' => 1000
            ], $query);
            
            foreach ($events as $event) {
                $allEvents[] = $event;
            }
        }
        
        usort($allEvents, function ($a, $b) {
            return strtotime($a->getValue('dtstart')) <=> strtotime($b->getValue('dtstart'));
        });
        
        return $allEvents;
    }
}
```

**Verwendung:**
```php
// Alles von überall
$all = UnifiedCalendar::getAll(date('Y-m-d'), date('Y-m-d', strtotime('+90 days')), 500);

// Mit Filter
$filtered = UnifiedCalendar::getFiltered(
    date('Y-m-d'),
    null,
    ['rex_yform_calendar', 'events_conferences'],
    "status = 'published' AND location IS NOT NULL"
);
```

---

## Performance-Tipps

### ✅ Verwende Generator für große Mengen

```php
// Speichereffizient - Generator
foreach (YFormCalendarEvents::getCalendarEvents(['limit' => 10000]) as $event) {
    // Verarbeite pro Ereignis
}
```

### ✅ Begrenzen Sie mit `limit`

```php
// Schneller - nur 50 Termine
$events = YFormCalendarEvents::getEventsByDate($start, $end, 50);
```

### ✅ Nutzen Sie `whereRaw` für Filter

```php
// SQL-Filter vor PHP-Verarbeitung
YFormCalendarEvents::getCalendarEvents([
    'whereRaw' => 'location IS NOT NULL'
]);
```

### ❌ Nicht: Alle Termine ohne Limit abrufen

```php
// Kann zu Memory-Überlauf führen
$allEvents = YFormCalendarEvents::getEventsByDate('2000-01-01', '2099-12-31');
```

---

## Fehlerbehandlung

```php
try {
    $events = YFormCalendarEvents::getEventsByDate($startDate, $endDate);
} catch (\Exception $e) {
    rex_logger::factory()->log(
        'calendar',
        'Error loading events: ' . $e->getMessage(),
        rex_logger::ERROR
    );
}
```

---

## Version

Diese Dokumentation gilt für **yform_calendar 1.1.0+**

Siehe [CHANGELOG.md](CHANGELOG.md) für Versionsgeschichte.
