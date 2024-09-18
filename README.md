# YFormCalendar

YFormCalendar ist ein umfassendes Paket für REDAXO YForm, das erweiterte Funktionen zur Verwaltung, zum Export und zur Anzeige von Kalenderereignissen bietet. Es besteht aus mehreren Klassen und einem speziellen RRULE-Widget für die Verwaltung wiederkehrender Ereignisse.

## Inhaltsverzeichnis

1. [Installation](#installation)
2. [CalRender-Klasse](#calrender-klasse)
3. [ICalExporter-Klasse](#icalexporter-klasse)
4. [CalendarJsonExporter-Klasse](#calendarjsonexporter-klasse)
5. [RRULE-Widget](#rrule-widget)
6. [Erforderliche Tabellenfelder](#erforderliche-tabellenfelder)

## Installation

Dieses Paket muss in einem REDAXO-Projekt verwendet werden. Es ist sicherzustellen, dass die YForm und YOrm AddOns installiert und aktiviert sind.

## CalRender-Klasse

Die `CalRender`-Klasse ist das Herzstück des YFormCalendar-Pakets. Sie ermöglicht das Abrufen, Filtern und Sortieren von Ereignissen aus der Datenbank sowie das Generieren von wiederkehrenden Ereignissen.

### Hauptmethoden

#### `getCalendarEvents`

```php
public static function getCalendarEvents(array $params = [], rex_yform_manager_query $customQuery = null): Generator
```

#### `getEventsByDate`

```php
public static function getEventsByDate(string $startDate, ?string $endDate = null, int $limit = PHP_INT_MAX): array
```

#### `getNextEvents`

```php
public static function getNextEvents(int $eventId, int $limit, ?string $startDateTime = null): array
```

### Beispiele für die Verwendung der CalRender-Klasse

#### Beispiel 1: Abrufen aller Ereignisse innerhalb eines Datumsbereichs

```php
use klxm\YFormCalendar\CalRender;

$startDate = '2024-06-01';
$endDate = '2024-06-30';
$limit = 10;

$events = CalRender::getEventsByDate($startDate, $endDate, $limit);

foreach ($events as $event) {
    echo "Ereignis: " . $event->getValue('summary') . "<br>";
    echo "Start: " . $event->getValue('dtstart') . "<br>";
    echo "Ende: " . $event->getValue('dtend') . "<br>";
    echo "---<br>";
}
```

#### Beispiel 2: Abrufen der nächsten Ereignisse für ein bestimmtes Ereignis

```php
use klxm\YFormCalendar\CalRender;

$eventId = 1;
$limit = 5;
$startDateTime = (new DateTime())->format('Y-m-d H:i:s');

$nextEvents = CalRender::getNextEvents($eventId, $limit, $startDateTime);

foreach ($nextEvents as $event) {
    echo "Ereignis: " . $event->getValue('summary') . "<br>";
    echo "Start: " . $event->getValue('dtstart') . "<br>";
    echo "Ende: " . $event->getValue('dtend') . "<br>";
    echo "---<br>";
}
```

#### Beispiel 3: Verwendung eines benutzerdefinierten Queries

```php
use klxm\YFormCalendar\CalRender;

$customQuery = rex_yform_manager_table::get('rex_calendar_events')->query()
    ->where('status', 'CONFIRMED')
    ->orderBy('dtstart', 'ASC');

$params = [
    'startDate' => '2024-01-01',
    'endDate' => '2024-12-31',
    'limit' => 50
];

$events = CalRender::getCalendarEvents($params, $customQuery);

foreach ($events as $event) {
    echo "Ereignis: " . $event->getValue('summary') . "<br>";
    echo "Status: " . $event->getValue('status') . "<br>";
    echo "Start: " . $event->getValue('dtstart') . "<br>";
    echo "---<br>";
}
```

#### Beispiel 4: Filtern von Ereignissen nach Kategorie

```php
use klxm\YFormCalendar\CalRender;

$params = [
    'startDate' => '2024-01-01',
    'endDate' => '2024-12-31',
    'whereRaw' => 'FIND_IN_SET("Konferenz", categories) > 0'
];

$events = CalRender::getCalendarEvents($params);

foreach ($events as $event) {
    echo "Ereignis: " . $event->getValue('summary') . "<br>";
    echo "Kategorien: " . $event->getValue('categories') . "<br>";
    echo "Start: " . $event->getValue('dtstart') . "<br>";
    echo "---<br>";
}
```

## ICalExporter-Klasse

Die `ICalExporter`-Klasse ermöglicht den Export von Kalenderereignissen im iCal-Format.

### Hauptmethoden

#### `generateICalFile`

```php
public static function generateICalFile(string $filename, array $events): void
```

#### `generateICal`

```php
public static function generateICal(array $events): string
```

### Beispiel: Generieren und Herunterladen einer iCal-Datei

```php
use klxm\YFormCalendar\CalRender;
use klxm\YFormCalendar\ICalExporter;

$events = CalRender::getEventsByDate('2024-01-01', '2024-12-31');
ICalExporter::generateICalFile('kalender_2024', $events);
```

## CalendarJsonExporter-Klasse

Die `CalendarJsonExporter`-Klasse dient zum Exportieren von Kalenderereignissen im JSON-Format, das für FullCalendar kompatibel ist.

### Konstruktor

```php
public function __construct(callable $linkCallback, string $modelClass)
```

### Hauptmethode

```php
public function generateJson(?string $startDate = null, ?string $endDate = null, string $sortByStart = 'ASC', string $sortByEnd = 'ASC'): string
```

### Beispiel: Generieren von JSON-Daten für FullCalendar

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

Das RRULE-Widget ist eine benutzerfreundliche Oberfläche zur Erstellung und Bearbeitung von Wiederholungsregeln für Ereignisse. Es generiert einen RRULE-String, der dem iCalendar-Standard entspricht.

### RRULE-Wert Erklärung

Der RRULE-Wert ist ein String, der die Wiederholungsregel für ein Ereignis definiert. Er besteht aus mehreren Komponenten, die durch Semikolons getrennt sind. Hier sind die wichtigsten Komponenten:

- `FREQ`: Gibt die Häufigkeit der Wiederholung an (z.B. DAILY, WEEKLY, MONTHLY, YEARLY).
- `INTERVAL`: Definiert das Intervall zwischen den Wiederholungen.
- `BYDAY`: Spezifiziert die Wochentage für wöchentliche oder monatliche Wiederholungen.
- `BYMONTHDAY`: Gibt den Tag des Monats für monatliche Wiederholungen an.
- `COUNT`: Begrenzt die Anzahl der Wiederholungen.
- `UNTIL`: Setzt ein Enddatum für die Wiederholungen.

Beispiel:
```
FREQ=WEEKLY;INTERVAL=2;BYDAY=MO,WE,FR;UNTIL=20240630T235959Z
```
Dies bedeutet: Alle 2 Wochen am Montag, Mittwoch und Freitag bis zum 30. Juni 2024.

### Verwendung des RRULE-Widgets

Das RRULE-Widget wird automatisch in einem YForm-Formular angezeigt, wenn ein Feld vom Typ `rrule` hinzugefügt wird. Es bietet eine intuitive Benutzeroberfläche zum Erstellen und Bearbeiten von Wiederholungsregeln.

## Erforderliche Tabellenfelder

Um sicherzustellen, dass alle Funktionen des YFormCalendar-Pakets korrekt funktionieren, sollten die folgenden Felder in der YForm-Tabelle vorhanden sein:

1. **summary**: Eine kurze Zusammenfassung oder der Titel des Ereignisses.
2. **description**: Eine detaillierte Beschreibung des Ereignisses.
3. **location**: Der Ort, an dem das Ereignis stattfindet.
4. **dtstart**: Das Startdatum und die Startzeit des Ereignisses im Format `YYYY-MM-DD HH:MM:SS`.
5. **dtend**: Das Enddatum und die Endzeit des Ereignisses im Format `YYYY-MM-DD HH:MM:SS`.
6. **all_day**: Ein Boolean-Wert (0 oder 1), der angibt, ob es sich um ein ganztägiges Ereignis handelt.
7. **rrule**: Die Wiederholungsregel für wiederkehrende Ereignisse im RRULE-Format.
8. **exdate**: Eine durch Kommas getrennte Liste von Ausnahmedaten oder Datumsbereichen im Format `YYYY-MM-DD` oder `YYYY-MM-DD/YYYY-MM-DD`.
