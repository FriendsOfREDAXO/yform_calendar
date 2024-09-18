# YFormCalendar

YFormCalendar ist ein umfassendes Paket für REDAXO, das erweiterte Funktionen zur Verwaltung, zum Export und zur Anzeige von Kalenderereignissen bietet.

## Inhaltsverzeichnis

1. [Installation](#installation)
2. [CalRender-Klasse](#calrender-klasse)
3. [ICalExporter-Klasse](#icalexporter-klasse)
4. [CalendarJsonExporter-Klasse](#calendarjsonexporter-klasse)
5. [RRULE-Widget](#rrule-widget)
6. [Erforderliche Tabellenfelder](#erforderliche-tabellenfelder)

## Installation

AddOn über den Installer installieren. 

Dieses Paket muss in einem REDAXO-Projekt-AddOn oder einem eigenen AddOn als abhängiges AddOn verwendet werden. Es ist sicherzustellen, dass die YForm und der YForm Manager installiert und aktiviert sind.

### Demo-Tableset für den Start verwenden

#### 1. install.php im Projekt-AddOn anlegen. 

```php 
<?php
if (rex_addon::get('yform') && rex_addon::get('yform')->isAvailable()) {
    rex_yform_manager_table_api::importTablesets(rex_file::get(rex_path::addon('yform_calendar', 'tableset/tableset.json')));
}
```
**Projekt-AddOn reinstallieren**. Danach sollte eine Tabelle erscheinen: YFormCalender
Diese kann nach Belieben erweitert werden. 

#### 2. boot.php des Projekt-AddOn erweitern

```php 
// Am Anfang einsetzen 
use klxm\YFormCalendar\CalRender;
// Einsetzen wo es Sinn ergibt

rex_yform_manager_dataset::setModelClass(
            'rex_yformcalendar',CalRender::class
);
```


## Verwendung

```php
<?php
// Im Template oder Modul

// Alle Ereignisse im Juni 2024
$events = MeineCal::getEventsByDate('2024-06-01', '2024-06-30');

// Die nächsten 5 Ereignisse ab jetzt
$nextEvents = MeineCal::getNextEvents(1, 5, date('Y-m-d H:i:s'));

foreach ($events as $event) {
    // $event ist nun eine Instanz von MeineCal
    echo $event->getStartDate();
    // Verwenden Sie hier Ihre spezifischen Methoden
}
```

## CalRender-Klasse

Die `CalRender`-Klasse ist das Herzstück des YFormCalendar-Pakets. Sie ermöglicht das Abrufen, Filtern und Sortieren von Ereignissen.

### Methoden

#### `getCalendarEvents`

```php
public static function getCalendarEvents(array $params = [], rex_yform_manager_query $customQuery = null): Generator
```

Parameter:
- `$params` (optional): Ein Array mit folgenden möglichen Schlüsseln:
  - `startDate`: (string) Start-Datum/Zeit im Format 'Y-m-d' oder 'Y-m-d H:i:s'
  - `endDate`: (string) End-Datum/Zeit im Format 'Y-m-d' oder 'Y-m-d H:i:s'
  - `sortByStart`: (string) Sortierrichtung für Startdatum ('ASC' oder 'DESC')
  - `sortByEnd`: (string) Sortierrichtung für Enddatum ('ASC' oder 'DESC')
  - `whereRaw`: (string) Zusätzliche WHERE-Bedingung für die Abfrage
  - `limit`: (int) Maximale Anzahl der zurückzugebenden Ereignisse
- `$customQuery` (optional): Eine benutzerdefinierte YForm-Query

Rückgabewert: Ein Generator, der Objekte vom Typ `rex_yform_manager_dataset` liefert.

#### `getEventsByDate`

```php
public static function getEventsByDate(string $startDate, ?string $endDate = null, int $limit = PHP_INT_MAX): array
```

Parameter:
- `$startDate`: (string) Start-Datum im Format 'Y-m-d' oder 'Y-m-d H:i:s'
- `$endDate`: (string, optional) End-Datum im Format 'Y-m-d' oder 'Y-m-d H:i:s'
- `$limit`: (int, optional) Maximale Anzahl der zurückzugebenden Ereignisse

Rückgabewert: Ein Array von Objekten vom Typ `rex_yform_manager_dataset`.

#### `getNextEvents`

```php
public static function getNextEvents(int $eventId, int $limit, ?string $startDateTime = null): array
```

Parameter:
- `$eventId`: (int) Die ID des Referenzereignisses
- `$limit`: (int) Maximale Anzahl der zurückzugebenden Ereignisse
- `$startDateTime`: (string, optional) Start-Datum/Zeit im Format 'Y-m-d H:i:s'

Rückgabewert: Ein Array von Objekten vom Typ `rex_yform_manager_dataset`.

### Beispiele

```php
use klxm\YFormCalendar\CalRender;

// Beispiel 1: Alle Ereignisse im Juni 2024
$events = CalRender::getEventsByDate('2024-06-01', '2024-06-30');

// Beispiel 2: Die nächsten 5 Ereignisse ab jetzt
$nextEvents = CalRender::getNextEvents(1, 5, date('Y-m-d H:i:s'));

// Beispiel 3: Benutzerdefinierte Abfrage
$customQuery = rex_yform_manager_table::get('rex_calendar_events')->query()
    ->where('status', 'CONFIRMED');

$params = [
    'startDate' => '2024-01-01',
    'endDate' => '2024-12-31',
    'limit' => 50
];

$events = CalRender::getCalendarEvents($params, $customQuery);
foreach ($events as $event) {
    // $event ist ein rex_yform_manager_dataset Objekt
    echo $event->getValue('summary');
}
```

## ICalExporter-Klasse

Die `ICalExporter`-Klasse ermöglicht den Export von Kalenderereignissen im iCal-Format.

### Methoden

#### `generateICalFile`

```php
public static function generateICalFile(string $filename, array $events): void
```

Parameter:
- `$filename`: (string) Der Name der zu generierenden Datei (ohne .ics Erweiterung)
- `$events`: (array) Ein Array von `rex_yform_manager_dataset` Objekten

Rückgabewert: Void. Diese Methode generiert eine Datei zum Download.

#### `generateICal`

```php
public static function generateICal(array $events): string
```

Parameter:
- `$events`: (array) Ein Array von `rex_yform_manager_dataset` Objekten

Rückgabewert: Ein String im iCal-Format.

### Beispiel

```php
use klxm\YFormCalendar\CalRender;
use klxm\YFormCalendar\ICalExporter;

$events = CalRender::getEventsByDate('2024-01-01', '2024-12-31');
$icalString = ICalExporter::generateICal($events);
echo $icalString; // Gibt den iCal-String aus

// Oder zum Herunterladen einer Datei:
ICalExporter::generateICalFile('kalender_2024', $events);
```

## CalendarJsonExporter-Klasse

Die `CalendarJsonExporter`-Klasse dient zum Exportieren von Kalenderereignissen im JSON-Format für FullCalendar.

### Konstruktor

```php
public function __construct(callable $linkCallback, string $modelClass)
```

Parameter:
- `$linkCallback`: (callable) Eine Funktion, die einen Link für jedes Ereignis generiert
- `$modelClass`: (string) Der Name der Modellklasse für die Ereignisse

### Methode

#### `generateJson`

```php
public function generateJson(?string $startDate = null, ?string $endDate = null, string $sortByStart = 'ASC', string $sortByEnd = 'ASC'): string
```

Parameter:
- `$startDate`: (string, optional) Start-Datum im Format 'Y-m-d' oder 'Y-m-d H:i:s'
- `$endDate`: (string, optional) End-Datum im Format 'Y-m-d' oder 'Y-m-d H:i:s'
- `$sortByStart`: (string, optional) Sortierrichtung für Startdatum ('ASC' oder 'DESC')
- `$sortByEnd`: (string, optional) Sortierrichtung für Enddatum ('ASC' oder 'DESC')

Rückgabewert: Ein JSON-String mit den Ereignisdaten.

### Beispiel

```php
use klxm\YFormCalendar\CalendarJsonExporter;
use klxm\YFormCalendar\CalRender;

$linkCallback = function($id) {
    return rex_getUrl('', '', ['event_id' => $id]);
};

$exporter = new CalendarJsonExporter($linkCallback, CalRender::class);
$json = $exporter->generateJson('2024-01-01', '2024-12-31');

echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: " . $json . "
        });
        calendar.render();
    });
</script>";

echo "<div id='calendar'></div>";
```

## RRULE-Widget

Das RRULE-Widget ist eine Benutzeroberfläche zur Erstellung und Bearbeitung von Wiederholungsregeln für Ereignisse.

### RRULE-Wert Erklärung

Der RRULE-Wert ist ein String, der die Wiederholungsregel für ein Ereignis definiert. Komponenten:

- `FREQ`: Häufigkeit (DAILY, WEEKLY, MONTHLY, YEARLY)
- `INTERVAL`: Intervall zwischen Wiederholungen
- `BYDAY`: Wochentage für wöchentliche/monatliche Wiederholungen
- `BYMONTHDAY`: Tag des Monats für monatliche Wiederholungen
- `COUNT`: Anzahl der Wiederholungen
- `UNTIL`: Enddatum für Wiederholungen

Beispiel:
```
FREQ=WEEKLY;INTERVAL=2;BYDAY=MO,WE,FR;UNTIL=20240630T235959Z
```

### Verwendung des RRULE-Widgets

Das RRULE-Widget wird automatisch in YForm-Formularen für Felder vom Typ `rrule` angezeigt. Es generiert einen RRULE-String, der in der Datenbank gespeichert wird.

## Erforderliche Tabellenfelder

Für die korrekte Funktion des YFormCalendar-Pakets sind folgende Felder erforderlich:

1. **summary**: Titel des Ereignisses (Text)
2. **description**: Beschreibung des Ereignisses (Text)
3. **location**: Ort des Ereignisses (Text)
4. **dtstart**: Startdatum/-zeit (DateTime, Format: YYYY-MM-DD HH:MM:SS)
5. **dtend**: Enddatum/-zeit (DateTime, Format: YYYY-MM-DD HH:MM:SS)
6. **all_day**: Ganztägiges Ereignis (Boolean, 0 oder 1)
7. **rrule**: Wiederholungsregel (Text, RRULE-Format)
8. **exdate**: Ausnahmedaten (Text, Format: YYYY-MM-DD oder YYYY-MM-DD/YYYY-MM-DD, kommagetrennt)
