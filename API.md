# YForm Calendar - API Referenz

Vollständige API-Dokumentation für die CalRender-Klasse und das YForm Calendar Addon.

## 📑 Inhaltsverzeichnis

- [CalRender - Hauptklasse](#calrender---hauptklasse)
- [Öffentliche Methoden](#öffentliche-methoden)
- [YFormCalendarEvents - Alias](#yformcalendarevents---alias)
- [RFC 5545 RRULE Format](#rfc-5545-rrule-format)
- [Fehlerbehandlung](#fehlerbehandlung)
- [Performance-Tipps](#performance-tipps)

---

## CalRender - Hauptklasse

`CalRender` erweitert `rex_yform_manager_dataset` und ist die Hauptklasse für den Zugriff auf Kalenderereignisse.

### Namespace

```php
use FriendsOfRedaxo\YFormCalendar\CalRender;
```

### Registrierung

Die Klasse wird automatisch in `boot.php` registriert:

```php
rex_yform_manager_dataset::setModelClass('rex_yform_calendar', CalRender::class);
```

---

## Öffentliche Methoden

### 1. getCalendarEvents()

Gibt einen Generator für speichereffiziente Verarbeitung großer Ereignismengen zurück.

```php
public static function getCalendarEvents(
    array $params = [],
    rex_yform_manager_query $customQuery = null
): Generator
```

#### Parameter

```php
$params = [
    'startDate'   => '2026-01-01',           // string, optional - Format: Y-m-d oder Y-m-d H:i:s
    'endDate'     => '2026-12-31',           // string, optional - Format: Y-m-d oder Y-m-d H:i:s
    'limit'       => 1000,                   // int, default: PHP_INT_MAX
    'sortByStart' => 'ASC',                  // string, 'ASC' oder 'DESC', default: 'ASC'
    'sortByEnd'   => 'ASC',                  // string, 'ASC' oder 'DESC', default: 'ASC'
    'whereRaw'    => 'location IS NOT NULL', // string, optional - Zusätzliche SQL-WHERE-Klausel
];

$customQuery = null; // rex_yform_manager_query, optional - Alternative zur Standard-Query
```

#### Rückgabewert

`Generator` - Iteriert über Ereignis-Objekte (rex_yform_manager_dataset)

#### Beispiele

**Einfache Iteration:**
```php
foreach (CalRender::getCalendarEvents([
    'startDate' => '2026-01-01',
    'endDate' => '2026-12-31',
    'limit' => 100
]) as $event) {
    echo $event->getValue('title') . "\n";
}
```

**Mit SQL-Filter:**
```php
foreach (CalRender::getCalendarEvents([
    'startDate' => '2026-01-01',
    'whereRaw' => 'location IS NOT NULL AND location != ""',
    'sortByStart' => 'DESC'
]) as $event) {
    echo $event->getValue('title') . ' @ ' . $event->getValue('location') . "\n";
}
```

**Mit benutzerdefinierter Query:**
```php
$query = CalRender::query()
    ->where('summary', '!=', '')
    ->orderBy('dtstart', 'ASC');

foreach (CalRender::getCalendarEvents(
    ['startDate' => '2026-01-01', 'limit' => 50],
    $query
) as $event) {
    // Verarbeitung mit benutzerdefinierten Bedingungen
}
```

**Array konvertieren (nur bei kleinen Mengen!):**
```php
$events = iterator_to_array(
    CalRender::getCalendarEvents([
        'startDate' => '2026-01-01',
        'endDate' => '2026-01-31',
        'limit' => 100
    ])
);
```

---

### 2. getEventsByDate()

Gibt alle Ereignisse in einem Datumsbereich als Array zurück. Einfacher als Generator für kleinere Mengen.

```php
public static function getEventsByDate(
    string $startDate,
    ?string $endDate = null,
    int $limit = PHP_INT_MAX
): array
```

#### Parameter

| Parameter | Typ | Default | Beschreibung |
|-----------|-----|---------|-------------|
| `$startDate` | string | - | Startdatum, Format: Y-m-d oder Y-m-d H:i:s |
| `$endDate` | string\|null | null | Enddatum, null = unbegrenzt |
| `$limit` | int | PHP_INT_MAX | Maximale Anzahl von Ereignissen |

#### Rückgabewert

`array` - Array mit rex_yform_manager_dataset Objekten

#### Beispiele

**Nächste 10 Termine:**
```php
$events = CalRender::getEventsByDate(
    date('Y-m-d'),
    null,
    10
);

foreach ($events as $event) {
    echo $event->getValue('title');
}
```

**Termine dieser Woche:**
```php
$startDate = date('Y-m-d', strtotime('monday this week'));
$endDate = date('Y-m-d', strtotime('sunday this week'));
$weekEvents = CalRender::getEventsByDate($startDate, $endDate);
```

**Termine im Januar 2026:**
```php
$janEvents = CalRender::getEventsByDate('2026-01-01', '2026-01-31');
echo 'Ereignisse im Januar: ' . count($janEvents);
```

**Mit Zeitangaben:**
```php
$events = CalRender::getEventsByDate(
    '2026-01-15 09:00:00',
    '2026-01-15 17:00:00',
    50
);
// Nur Events am 15.01.2026 zwischen 9:00 und 17:00
```

---

### 3. getNextEvents()

Gibt die nächsten Vorkommen eines bestimmten Ereignisses zurück.

```php
public static function getNextEvents(
    int $eventId,
    int $limit = PHP_INT_MAX,
    ?string $startDateTime = null
): array
```

#### Parameter

| Parameter | Typ | Default | Beschreibung |
|-----------|-----|---------|-------------|
| `$eventId` | int | - | ID des Ereignisses |
| `$limit` | int | PHP_INT_MAX | Maximale Anzahl von Vorkommen |
| `$startDateTime` | string\|null | null | Startdatum (Format: Y-m-d H:i:s), null = heute |

#### Rückgabewert

`array` - Array mit Ereignis-Vorkommen als rex_yform_manager_dataset Objekte

#### Beispiele

**Nächste 5 Vorkommen:**
```php
$nextOccurrences = CalRender::getNextEvents(1, 5);
foreach ($nextOccurrences as $event) {
    echo $event->getValue('title') . ': ' . $event->getValue('dtstart') . "\n";
}
```

**Ab spezifischem Datum:**
```php
$futureEvents = CalRender::getNextEvents(
    1,
    10,
    '2026-06-01 00:00:00'
);
// Nächste 10 Vorkommen ab 01.06.2026
```

---

### 4. getEventDetailsByOccurrence()

Gibt die Details eines spezifischen Ereignisvorkommen zurück.

```php
public static function getEventDetailsByOccurrence(
    int $eventId,
    string $occurrenceDate
): ?rex_yform_manager_dataset
```

#### Parameter

| Parameter | Typ | Beschreibung |
|-----------|-----|-------------|
| `$eventId` | int | ID des Ereignisses |
| `$occurrenceDate` | string | Datum des Vorkommens (Format: Y-m-d) |

#### Rückgabewert

`rex_yform_manager_dataset|null` - Event-Objekt oder null, wenn nicht gefunden

#### Beispiele

**Spezifisches Vorkommen abrufen:**
```php
$event = CalRender::getEventDetailsByOccurrence(1, '2026-06-15');

if ($event) {
    echo $event->getValue('title');
    echo ' am ' . $event->getValue('dtstart');
} else {
    echo 'Keine Vorkommen an diesem Datum';
}
```

---

## YFormCalendarEvents - Alias

`YFormCalendarEvents` ist ein einfacher Alias für `CalRender` zur Rückwärtskompatibilität.

```php
use FriendsOfRedaxo\YFormCalendar\YFormCalendarEvents;

// Identisch mit CalRender:
$events = YFormCalendarEvents::getEventsByDate('2026-01-01', '2026-12-31');
```

### Empfehlung

**Neuer Code sollte direkt CalRender verwenden:**
```php
use FriendsOfRedaxo\YFormCalendar\CalRender;
$events = CalRender::getEventsByDate('2026-01-01', '2026-12-31');
```

---

## RFC 5545 RRULE Format

Das Addon nutzt **RFC 5545** für wiederkehrende Ereignisse. RRULEs werden als Textfeld gespeichert.

### Syntax

```
FREQ=<frequency>;[INTERVAL=<interval>];[BYDAY=<days>];[BYMONTHDAY=<day>];[BYMONTH=<month>];[COUNT=<count>];[UNTIL=<date>];[EXDATE=<dates>]
```

### Beispiele

#### Täglich

```
FREQ=DAILY
// Jeden Tag

FREQ=DAILY;INTERVAL=2
// Jeden 2. Tag

FREQ=DAILY;COUNT=10
// Täglich für 10 Vorkommen

FREQ=DAILY;UNTIL=20261231
// Täglich bis zum 31.12.2026
```

#### Wöchentlich

```
FREQ=WEEKLY;BYDAY=MO,WE,FR
// Jeden Montag, Mittwoch, Freitag

FREQ=WEEKLY;BYDAY=MO;INTERVAL=2
// Jeden zweiten Montag (alle 2 Wochen)

FREQ=WEEKLY;BYDAY=MO,FR;COUNT=20
// Montag und Freitag für 20 Vorkommen
```

#### Monatlich

```
FREQ=MONTHLY;BYMONTHDAY=15
// Am 15. jeden Monats

FREQ=MONTHLY;BYDAY=2MO
// Am zweiten Montag eines Monats

FREQ=MONTHLY;BYDAY=-1FR
// Am letzten Freitag eines Monats
```

#### Jährlich

```
FREQ=YEARLY;BYMONTH=12;BYMONTHDAY=25
// Weihnachten (25.12.) jeden Jahr

FREQ=YEARLY;BYMONTH=1;BYMONTHDAY=1
// Neujahr jeden Jahr
```

### Ausnahmen (EXDATE)

EXDATE ermöglicht es, einzelne Vorkommen auszuschließen:

```
FREQ=DAILY;EXDATE=2026-01-15,2026-01-20
// Täglich, außer am 15.01. und 20.01.2026

FREQ=DAILY;EXDATE=2026-01-15/2026-01-20
// Täglich, außer vom 15.01. bis 20.01.2026 (Bereichs-Ausnahme)
```

### Verfügbare Parameter

| Parameter | Werte | Beschreibung |
|-----------|-------|-------------|
| **FREQ** | DAILY, WEEKLY, MONTHLY, YEARLY | Wiederholungsfrequenz (erforderlich) |
| **INTERVAL** | Positive Integer (1, 2, 3...) | Häufigkeit (z.B. 2 = jede 2. Einheit) |
| **BYDAY** | MO, TU, WE, TH, FR, SA, SU | Wochentage (auch: 1MO = 1. Montag) |
| **BYMONTHDAY** | 1-31 | Tag des Monats |
| **BYMONTH** | 1-12 | Monat des Jahres |
| **COUNT** | Positive Integer | Maximale Anzahl von Vorkommen |
| **UNTIL** | YYYYMMDD | Enddatum (z.B. 20261231 für 31.12.2026) |
| **EXDATE** | Daten (Z.B. 2026-01-15,2026-01-20) | Ausnahmendaten |

---

## Fehlerbehandlung

### Ungültige Datumsformate

```php
try {
    $events = CalRender::getEventsByDate('invalid-date');
} catch (Exception $e) {
    echo 'Fehler: ' . $e->getMessage();
}
```

**Gültige Formate:**
- `2026-01-15` (Datum)
- `2026-01-15 14:30:00` (Datum + Zeit)

### Leere Ergebnisse

```php
$events = CalRender::getEventsByDate('2026-01-01', '2026-01-31');

if (empty($events)) {
    echo 'Keine Ereignisse gefunden';
} else {
    foreach ($events as $event) {
        // Verarbeitung
    }
}
```

### Mit Generator

```php
$count = 0;
foreach (CalRender::getCalendarEvents(['limit' => 100]) as $event) {
    $count++;
    // Verarbeitung
}
echo "Insgesamt: $count Ereignisse";
```

---

## Performance-Tipps

### 1. Generator für große Mengen verwenden

**Nicht optimal (lade alles in RAM):**
```php
$allEvents = CalRender::getEventsByDate('2020-01-01', '2030-12-31', 10000);
```

**Besser (streame mit Generator):**
```php
foreach (CalRender::getCalendarEvents([
    'startDate' => '2020-01-01',
    'endDate' => '2030-12-31',
    'limit' => 10000
]) as $event) {
    // Verarbeite Event einzeln
}
```

### 2. Limit setzen

```php
// Mit Limit
$events = CalRender::getEventsByDate('2026-01-01', '2026-12-31', 100);

// Besser als ohne Limit (nur die 100 nächsten Events)
```

### 3. SQL-Filter verwenden

```php
// Filtere auf DB-Ebene mit whereRaw
foreach (CalRender::getCalendarEvents([
    'startDate' => '2026-01-01',
    'whereRaw' => 'location IS NOT NULL'
]) as $event) {
    // Nur Events mit Ort
}

// Nicht: PHP-Filter nach dem Laden
```

### 4. Indizes setzen

Für bessere Performance Indizes auf der DB-Tabelle setzen:

```sql
CREATE INDEX idx_dtstart ON rex_yform_calendar(dtstart);
CREATE INDEX idx_dtend ON rex_yform_calendar(dtend);
CREATE INDEX idx_rrule ON rex_yform_calendar(rrule(50));
```

### 5. Query-Caching

```php
// Cache Generator-Ergebnisse falls nötig
$cacheKey = 'calendar_events_' . date('Ymd');
if ($events = rex_cache_file::get($cacheKey)) {
    return $events;
}

$events = iterator_to_array(CalRender::getCalendarEvents([
    'startDate' => date('Y-m-d'),
    'endDate' => date('Y-m-d', strtotime('+30 days')),
    'limit' => 100
]));

rex_cache_file::set($cacheKey, $events, 3600); // 1 Stunde Cache
return $events;
```

---

## Debugging

### Debug-Modus aktivieren

```php
if (rex::isDebugMode()) {
    // Zeige Debugging-Informationen
    echo '<pre>';
    var_dump($event);
    echo '</pre>';
}
```

### RRULE anschauen

```php
$event = CalRender::getEventsByDate('2026-01-01', '2026-01-31')[0] ?? null;
if ($event) {
    echo 'RRULE: ' . $event->getValue('rrule');
}
```

---

## Kontakt & Support

- GitHub: https://github.com/FriendsOfREDAXO/yform_calendar
- Issues: https://github.com/FriendsOfREDAXO/yform_calendar/issues
- Community: https://community.redaxo.org/
