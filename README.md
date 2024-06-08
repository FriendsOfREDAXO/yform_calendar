
# YFormCalHelper

Die `YFormCalHelper`-Klasse dient zur Verwaltung und Verarbeitung von Kalenderereignissen in REDAXO. Sie ermöglicht das Abrufen, Filtern und Sortieren von Ereignissen aus einer Datenbank sowie das Generieren wiederkehrender Ereignisse.

### Funktionen

1. **Holen aller Ereignisse innerhalb eines bestimmten Datumsbereichs.**
2. **Generieren von wiederkehrenden Ereignissen basierend auf Wiederholungsregeln.**
3. **Holen der nächsten X Ereignisse ab einem festgelegten Datum und/oder Uhrzeit.**

### Installation

Kopieren Sie die `YFormCalHelper.php`-Datei in Ihr REDAXO-Projekt und stellen Sie sicher, dass sie in Ihren PHP-Dateien eingebunden ist:

```php
require_once 'path/to/YFormCalHelper.php';
```

### Methoden

#### `getChronologicalEvents`

Holt alle Ereignisse und sortiert sie nach den angegebenen Kriterien.

```php
public static function getChronologicalEvents(
    ?string $startDate = null,
    ?string $endDate = null,
    string $sortByStart = 'ASC',
    string $sortByEnd = 'ASC'
): array
```

- **Parameter:**
  - `startDate` (optional): Das Startdatum, um Ereignisse zu filtern.
  - `endDate` (optional): Das Enddatum, um Ereignisse zu filtern.
  - `sortByStart`: Sortierrichtung für das Startdatum (`ASC` oder `DESC`).
  - `sortByEnd`: Sortierrichtung für das Enddatum (`ASC` oder `DESC`).

- **Rückgabewert:** Ein Array von Ereignisobjekten.

#### `getNextEvents`

Holt die nächsten X Ereignisse ab einem festgelegten Datum und/oder Uhrzeit basierend auf der Datensatz-ID eines Termins.

```php
public static function getNextEvents(int $eventId, int $limit, ?string $startDateTime = null): array
```

- **Parameter:**
  - `eventId`: Die ID des Referenzereignisses.
  - `limit`: Die Anzahl der zu holenden Ereignisse.
  - `startDateTime` (optional): Das Startdatum und die Startzeit, ab der die Ereignisse abgerufen werden sollen.

- **Rückgabewert:** Ein Array von Ereignisobjekten.

#### `getEventsByDate`

Holt alle Ereignisse für ein spezifisches Datum oder Zeitraum.

```php
public static function getEventsByDate(string $startDate, ?string $endDate = null): array
```

- **Parameter:**
  - `startDate`: Das Startdatum, um Ereignisse zu filtern.
  - `endDate` (optional): Das Enddatum, um Ereignisse zu filtern.

- **Rückgabewert:** Ein Array von Ereignisobjekten.

### Beispiele

#### Beispiel 1: Holen aller Ereignisse innerhalb eines bestimmten Datumsbereichs

Dieses Beispiel zeigt, wie Sie alle Ereignisse innerhalb eines Datumsbereichs abrufen und als einfache Liste ausgeben.

```php
require_once 'path/to/YFormCalHelper.php';

// Festlegen des Start- und Enddatums
$startDate = '2024-06-01';
$endDate = '2024-06-30';

// Holen der Ereignisse innerhalb des Datumsbereichs
$events = YFormCalHelper::getChronologicalEvents($startDate, $endDate);

// Ausgabe der Ereignisse als einfache Liste
echo "<ul>";
foreach ($events as $event) {
    echo "<li>";
    echo "<strong>Event:</strong> " . htmlspecialchars($event->getValue('summary')) . "<br>";
    echo "<strong>Description:</strong> " . htmlspecialchars($event->getValue('description')) . "<br>";
    echo "<strong>Location:</strong> " . htmlspecialchars($event->getValue('location')) . "<br>";
    echo "<strong>Start:</strong> " . htmlspecialchars($event->getValue('dtstart')) . "<br>";
    echo "<strong>End:</strong> " . htmlspecialchars($event->getValue('dtend')) . "<br>";
    echo "</li>";
}
echo "</ul>";
?>
```

#### Beispiel 2: Holen der nächsten 5 Ereignisse ab heute und Ausgabe mit Bootstrap

Dieses Beispiel zeigt, wie Sie die nächsten 5 Ereignisse ab dem aktuellen Datum und der aktuellen Uhrzeit abrufen und mit Bootstrap formatieren.

```php
require_once 'path/to/YFormCalHelper.php';

// ID des Referenzereignisses und Limit der Ereignisse
$eventId = 1;
$limit = 5;

// Holen der nächsten Ereignisse ab dem aktuellen Datum und Uhrzeit
$events = YFormCalHelper::getNextEvents($eventId, $limit);

// Ausgabe der Ereignisse mit Bootstrap
echo "<div class='container'>";
echo "<div class='row'>";
foreach ($events as $event) {
    echo "<div class='col-md-4'>";
    echo "<div class='card'>";
    echo "<div class='card-body'>";
    echo "<h5 class='card-title'>" . htmlspecialchars($event->getValue('summary')) . "</h5>";
    echo "<p class='card-text'>" . htmlspecialchars($event->getValue('description')) . "</p>";
    echo "<p><strong>Location:</strong> " . htmlspecialchars($event->getValue('location')) . "</p>";
    echo "<p><strong>Start:</strong> " . htmlspecialchars($event->getValue('dtstart')) . "</p>";
    echo "<p><strong>End:</strong> " . htmlspecialchars($event->getValue('dtend')) . "</p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}
echo "</div>";
echo "</div>";
?>
```

#### Beispiel 3: Holen aller Ereignisse ab heute minus 3 Monate und Ausgabe mit UIkit 3

Dieses Beispiel zeigt, wie Sie alle Ereignisse ab einem Datum drei Monate vor dem heutigen Datum abrufen und mit UIkit 3 formatieren.

```php
require_once 'path/to/YFormCalHelper.php';

// Berechnen des Startdatums (heute minus 3 Monate)
$startDate = (new DateTime())->modify('-3 months')->format('Y-m-d');

// Holen der Ereignisse ab dem berechneten Startdatum
$events = YFormCalHelper::getChronologicalEvents($startDate);

// Ausgabe der Ereignisse mit UIkit 3
echo "<div class='uk-container'>";
foreach ($events as $event) {
    echo "<div class='uk-card uk-card-default uk-card-body uk-margin'>";
    echo "<h3 class='uk-card-title'>" . htmlspecialchars($event->getValue('summary')) . "</h3>";
    echo "<p>" . htmlspecialchars($event->getValue('description')) . "</p>";
    echo "<p><strong>Location:</strong> " . htmlspecialchars($event->getValue('location')) . "</p>";
    echo "<p><strong>Start:</strong> " . htmlspecialchars($event->getValue('dtstart')) . "</p>";
    echo "<p><strong>End:</strong> " . htmlspecialchars($event->getValue('dtend')) . "</p>";
    echo "</div>";
}
echo "</div>";
?>
```

### Beispiele für die Datumsübergabe

#### Beispiel: Holen aller Ereignisse ab heute

```php
require_once 'path/to/YFormCalHelper.php';

// Festlegen des Startdatums auf heute
$startDate = (new DateTime())->format('Y-m-d');

// Holen der Ereignisse ab dem heutigen Datum
$events = YFormCalHelper::getChronologicalEvents($startDate);

// Ausgabe der Ereignisse als einfache Liste
echo "<ul>";
foreach ($events as $event) {
    echo "<li>" . htmlspecialchars($event->getValue('summary')) . " (" . htmlspecialchars($event->getValue('dtstart')) . " - " . htmlspecialchars($event->getValue('dtend')) . ")</li>";
}
echo "</ul>";
?>
```

#### Beispiel: Holen aller Ereignisse ab heute minus 3 Monate

```php
require_once 'path/to/YFormCalHelper.php';

// Festlegen des Startdatums auf heute minus 3 Monate
$startDate = (new DateTime())->modify('-3 months')->format('Y-m-d');

// Holen der Ereignisse ab dem berechneten Datum
$events = YFormCalHelper::getChronologicalEvents($startDate);

// Ausgabe der Ereignisse als einfache Liste
echo "<ul>";
foreach ($events as $event) {
    echo "<li>" . htmlspecialchars($event->getValue('summary')) . " (" . htmlspecialchars($event->getValue('dtstart')) . " - " . htmlspecialchars($event->getValue('dtend')) . ")</li>";
}
echo "</ul>";
?>
```

Diese Beispiele zeigen verschiedene Anwendungsfälle und wie man die Methoden der `YFormCalHelper`-Klasse nutzt, um Ereignisse basierend auf spezifischen Start- und Enddatumsparametern abzurufen und in verschiedenen Formaten auszugeben.


## FullCalendarDemo

```php
  <?php
      // Callback-Funktion zur Linkgenerierung
        $linkCallback = function($id) {
        return rex_getUrl('', '', ['cal' => $id]);
    };

    // Beispiel für die Verwendung der Klasse, um Ereignisse abzurufen und anzuzeigen
    $calendarEventJson = new CalendarEventJson($linkCallback);

    // Generieren Sie das JSON für FullCalendar
    $startDate = (new DateTime('today'))->format('Y-m-d');
    $endDate = (new DateTime('+12 months'))->format('Y-m-d');
    $eventsJson = $calendarEventJson->generateJson($startDate, $endDate, 'ASC', 'DESC');
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>FullCalendar Beispiel</title>
        <!-- FullCalendar CSS -->
        <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
        <!-- FullCalendar JavaScript -->
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales-all.min.js'></script>
        <!-- Tippy.js CSS -->
        <link href="https://unpkg.com/tippy.js@6/dist/tippy.css" rel="stylesheet">
        <!-- Tippy.js JavaScript -->
        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>
    </head>
    <body>
        <div id='calendar'></div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'de',
                    events: <?php echo $eventsJson; ?>,
                    eventDidMount: function(info) {
                    tippy(info.el, {
                    content: info.event.extendedProps.description,
                    placement: 'top',
                    trigger: 'mouseenter',
                    theme: 'light',
                });
            }
                                      });
            calendar.render();
            });
        </script>
    </body>
</html>
```

