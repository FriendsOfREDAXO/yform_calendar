# YFormCalendar

YFormCalendar ist ein Paket für REDAXO, das umfassende Funktionen zur Verwaltung, zum Export und zur Anzeige von Kalenderereignissen bietet. Es besteht aus mehreren Klassen, die zusammenarbeiten, um eine flexible und leistungsfähige Kalenderfunktionalität zu ermöglichen.

## Inhaltsverzeichnis

1. [Installation](#installation)
2. [CalRender-Klasse](#calrender-klasse)
3. [ICalExporter-Klasse](#icalexporter-klasse)
4. [CalendarJsonExporter-Klasse](#calendarjsonexporter-klasse)
5. [Erforderliche Tabellenfelder](#erforderliche-tabellenfelder)

## Installation

Dieses Paket muss in einem REDAXO-Projekt verwendet werden. Stellen Sie sicher, dass die YForm und YOrm AddOns installiert und aktiviert sind.

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

### Beispiele

#### Beispiel 1: Abrufen aller Ereignisse innerhalb eines Datumsbereichs

```php
use klxm\YFormCalendar\CalRender;

$startDate = '2024-06-01';
$endDate = '2024-06-30';
$limit = 10;

$events = CalRender::getEventsByDate($startDate, $endDate, $limit);

foreach ($events as $event) {
    echo "Ereignis: " . $event->getValue('summary') . "\n";
    echo "Start: " . $event->getValue('dtstart') . "\n";
    echo "Ende: " . $event->getValue('dtend') . "\n";
    echo "---\n";
}
```

#### Beispiel 2: Abrufen der nächsten 5 Ereignisse für ein bestimmtes Ereignis

```php
use klxm\YFormCalendar\CalRender;

$eventId = 1;
$limit = 5;
$startDateTime = (new DateTime())->format('Y-m-d H:i:s');

$nextEvents = CalRender::getNextEvents($eventId, $limit, $startDateTime);

foreach ($nextEvents as $event) {
    echo "Ereignis: " . $event->getValue('summary') . "\n";
    echo "Start: " . $event->getValue('dtstart') . "\n";
    echo "Ende: " . $event->getValue('dtend') . "\n";
    echo "---\n";
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

## Erforderliche Tabellenfelder

Um sicherzustellen, dass alle Funktionen des YFormCalendar-Pakets korrekt funktionieren, sollten die folgenden Felder in Ihrer YForm-Tabelle vorhanden sein:

1. **summary**: Eine kurze Zusammenfassung oder der Titel des Ereignisses.
2. **description**: Eine detaillierte Beschreibung des Ereignisses.
3. **location**: Der Ort, an dem das Ereignis stattfindet.
4. **dtstart**: Das Startdatum und die Startzeit des Ereignisses im Format `YYYY-MM-DD HH:MM:SS`.
5. **dtend**: Das Enddatum und die Endzeit des Ereignisses im Format `YYYY-MM-DD HH:MM:SS`.
6. **all_day**: Ein Boolean-Wert (0 oder 1), der angibt, ob es sich um ein ganztägiges Ereignis handelt.
7. **rrule**: Die Wiederholungsregel für wiederkehrende Ereignisse.
8. **exdate**: Eine durch Kommas getrennte Liste von Ausnahmedaten oder Datumsbereichen im Format `YYYY-MM-DD` oder `YYYY-MM-DD/YYYY-MM-DD`.

Diese README bietet einen umfassenden Überblick über die Funktionalitäten des YFormCalendar-Pakets mit Beispielen für jede Komponente. Sie zeigt, wie man die verschiedenen Klassen verwendet, um Ereignisse abzurufen, zu exportieren und anzuzeigen.
