# YFormCalHelper

Die `YFormCalHelper`-Klasse dient zur Verwaltung und Verarbeitung von Kalenderereignissen in REDAXO. Sie ermöglicht das Abrufen, Filtern und Sortieren von Ereignissen aus einer Datenbank sowie das Generieren wiederkehrender Ereignisse.

### Funktionen

1. **Abrufen aller Ereignisse innerhalb eines bestimmten Datumsbereichs.**
2. **Generieren von wiederkehrenden Ereignissen basierend auf Wiederholungsregeln.**
3. **Abrufen der nächsten X Ereignisse ab einem festgelegten Datum und/oder Uhrzeit.**

### Installation

Diese Klasse muss in einem REDAXO-Projekt verwendet werden. Stellen Sie sicher, dass die YForm und YOrm AddOns installiert und aktiviert sind.

### Methoden

#### `getEvents`

Holt alle Ereignisse und sortiert sie nach den angegebenen Kriterien.

```php
public static function getEvents(): array
```

- **Parameter:** Keine direkten Parameter, aber durch Setter-Methoden konfigurierbar:
  - `setStartDate(?string $startDate)`: Setzt das Startdatum, um Ereignisse zu filtern.
  - `setEndDate(?string $endDate)`: Setzt das Enddatum, um Ereignisse zu filtern.
  - `setSortByStart(string $sortByStart)`: Sortierrichtung für das Startdatum (`ASC` oder `DESC`).
  - `setSortByEnd(string $sortByEnd)`: Sortierrichtung für das Enddatum (`ASC` oder `DESC`).
  - `setWhereRaw(?string $whereRaw)`: Setzt eine rohe WHERE-Bedingung für die Ereignisabfrage.

- **Rückgabewert:** Ein Array von Ereignisobjekten.

### Beispiele

#### Beispiel 1: Holen aller Ereignisse innerhalb eines bestimmten Datumsbereichs

Dieses Beispiel zeigt, wie Sie alle Ereignisse innerhalb eines Datumsbereichs abrufen und als einfache Liste ausgeben.

```php
// Festlegen des Start- und Enddatums
YFormCalHelper::setStartDate('2024-06-01');
YFormCalHelper::setEndDate('2024-06-30');

// Holen der Ereignisse innerhalb des Datumsbereichs
$events = YFormCalHelper::getEvents();

// Ausgabe der Ereignisse als einfache Liste
echo "<ul>";
foreach ($events as $event) {
    echo "<li>";
    echo "<strong>Event:</strong> " . rex_escape($event->getValue('summary')) . "<br>";
    echo "<strong>Location:</strong> " . rex_escape($event->getValue('location')) . "<br>";
    echo "<strong>Start:</strong> " . rex_escape($event->getValue('dtstart')) . "<br>";
    echo "<strong>End:</strong> " . rex_escape($event->getValue('dtend')) . "<br>";
    echo "</li>";
}
echo "</ul>";
```

#### Beispiel 2: Holen der nächsten 5 Ereignisse ab heute und Ausgabe mit Bootstrap

Dieses Beispiel zeigt, wie Sie die nächsten 5 Ereignisse ab dem aktuellen Datum und der aktuellen Uhrzeit abrufen und mit Bootstrap formatieren.

```php
// ID des Referenzereignisses und Limit der Ereignisse
$eventId = 1;
$limit = 5;

// Holen der nächsten Ereignisse ab dem aktuellen Datum und Uhrzeit
YFormCalHelper::setStartDate((new DateTime())->format('Y-m-d'));
$events = YFormCalHelper::getEvents();

$nextEvents = array_slice($events, 0, $limit);

// Ausgabe der Ereignisse mit Bootstrap
echo "<div class='container'>";
echo "<div class='row'>";
foreach ($nextEvents as $event) {
    echo "<div class='col-md-4'>";
    echo "<div class='card'>";
    echo "<div class='card-body'>";
    echo "<h5 class='card-title'>" . rex_escape($event->getValue('summary')) . "</h5>";
    echo "<p class='card-text'>" . rex_escape($event->getValue('description')) . "</p>";
    echo "<p><strong>Location:</strong> " . rex_escape($event->getValue('location')) . "</p>";
    echo "<p><strong>Start:</strong> " . rex_escape($event->getValue('dtstart')) . "</p>";
    echo "<p><strong>End:</strong> " . rex_escape($event->getValue('dtend')) . "</p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}
echo "</div>";
echo "</div>";
```

#### Beispiel 3: Holen aller Ereignisse ab heute minus 3 Monate und Ausgabe mit UIkit 3

Dieses Beispiel zeigt, wie Sie alle Ereignisse ab einem Datum drei Monate vor dem heutigen Datum abrufen und mit UIkit 3 formatieren.

```php
// Berechnen des Startdatums (heute minus 3 Monate)
$startDate = (new DateTime())->modify('-3 months')->format('Y-m-d');

// Holen der Ereignisse ab dem berechneten Startdatum
YFormCalHelper::setStartDate($startDate);
$events = YFormCalHelper::getEvents();

// Ausgabe der Ereignisse mit UIkit 3
echo "<div class='uk-container'>";
foreach ($events as $event) {
    echo "<div class='uk-card uk-card-default uk-card-body uk-margin'>";
    echo "<h3 class='uk-card-title'>" . rex_escape($event->getValue('summary')) . "</h3>";
    echo "<p>" . rex_escape($event->getValue('description')) . "</p>";
    echo "<p><strong>Location:</strong> " . rex_escape($event->getValue('location')) . "</p>";
    echo "<p><strong>Start:</strong> " . rex_escape($event->getValue('dtstart')) . "</p>";
    echo "<p><strong>End:</strong> " . rex_escape($event->getValue('dtend')) . "</p>";
    echo "</div>";
}
echo "</div>";
```

### Beispiele für die Datumsübergabe

#### Beispiel: Holen aller Ereignisse ab jetzt

```php
<?php
// Aktuelles Datum und Uhrzeit in der Zeitzone Berlin
$berlinTimezone = new DateTimeZone('Europe/Berlin');
$startDateTime = (new DateTime('now', $berlinTimezone))->format('Y-m-d H:i:s');

// Abrufen der zukünftigen Ereignisse
$events = YFormCalHelper::getChronologicalEvents($startDateTime);

// Ausgabe der Ereignisse
foreach ($events as $event) {
    echo 'Event: ' . $event->getValue('title') . ' - Start: ' . $event->getValue('dtstart') . ' - End: ' . $event->getValue('dtend') . '<br>';
}
?>
```


#### Beispiel: Holen aller Ereignisse ab heute

```php
// Festlegen des Startdatums auf heute
$startDate = (new DateTime())->format('Y-m-d');

// Holen der Ereignisse ab dem heutigen Datum
YFormCalHelper::setStartDate($startDate);
$events = YFormCalHelper::getEvents();

// Ausgabe der Ereignisse als einfache Liste
echo "<ul>";
foreach ($events as $event) {
    echo "<li>" . rex_escape($event->getValue('summary')) . " (" . rex_escape($event->getValue('dtstart')) . " - " . rex_escape($event->getValue('dtend')) . ")</li>";
}
echo "</ul>";
```

#### Beispiel: Holen aller Ereignisse ab heute minus 3 Monate

```php
// Festlegen des Startdatums auf heute minus 3 Monate
$startDate = (new DateTime())->modify('-3 months')->format('Y-m-d');

// Holen der Ereignisse ab dem berechneten Datum
YFormCalHelper::setStartDate($startDate);
$events = YFormCalHelper::getEvents();

// Ausgabe der Ereignisse als einfache Liste
echo "<ul>";
foreach ($events as $event) {
    echo "<li>" . rex_escape($event->getValue('summary')) . " (" . rex_escape($event->getValue('dtstart')) . " - " . rex_escape($event->getValue('dtend')) . ")</li>";
}
echo "</ul>";
```

Diese Beispiele zeigen verschiedene Anwendungsfälle und wie man die Methoden der `YFormCalHelper`-Klasse nutzt, um Ereignisse basierend auf spezifischen Start- und Enddatumsparametern abzurufen und in verschiedenen Formaten auszugeben.

### Sortierung und Filterung

Sie können die Ereignisse nach Start- und Enddatum sortieren. Die Standardwerte sind aufsteigend (`ASC`). Sie können diese jedoch auf absteigend (`DESC`) ändern, indem Sie die entsprechenden Setter-Methoden `setSortByStart` und `setSortByEnd` verwenden. Beispielsweise:

```php
// Holen der Ereignisse in absteigender Reihenfolge nach Startdatum
YFormCalHelper::setSortByStart('DESC');
YFormCalHelper::setSortByEnd('DESC');
$events = YFormCalHelper::getEvents();
```

### Erforderliche Tabellenfelder

Um sicherzustellen, dass alle Funktionen der `YFormCalHelper`-Klasse korrekt funktionieren, sollten die folgenden Felder in Ihrer Tabelle vorhanden sein:

1. **summary**: Eine kurze Zusammenfassung oder der Titel des Ereignisses.
2. **description**: Eine detaillierte Beschreibung des Ereignisses.
3. **location**: Der Ort, an dem das Ereignis stattfindet.
4. **dtstart**: Das Startdatum und die Startzeit des Ereignisses im Format `YYYY-MM-DD HH:MM:SS`.
5. **dtend**: Das Enddatum und die Endzeit des Ereignisses im Format `YYYY-MM-DD HH:MM:SS`.
6. **all_day**: Ein Boolean-Wert (0 oder 1), der angibt, ob es sich um ein ganztägiges Ereignis handelt.
7. **repeat**: Ein Boolean-Wert (0 oder 1), der angibt, ob das Ereignis wiederholt wird.
8. **freq**: Die Häufigkeit der Wiederholung (z.B. DAILY, WEEKLY, MONTHLY, YEARLY).
9. **interval**: Das Intervall der Wiederholung.
10. **until**: Das Enddatum der Wiederholungen im Format `YYYY-MM-DD HH:MM:SS`.
11. **exdate:** Eine durch Kommas getrennte Liste von Ausnahme-Daten im Format YYYY-MM-DD.


## CalendarEventJson

Die `CalendarEventJson`-Klasse dient dazu, Kalenderereignisse aus der Tabelle in ein JSON-Format zu konvertieren, das von FullCalendar verwendet werden kann. Sie ermöglicht das Abrufen und Formatieren von Ereignissen basierend auf bestimmten Filter- und Sortierkriterien.

### Konstruktor

```php
public function __construct(callable $linkCallback)
```

- **Parameter:**
  - `linkCallback`: Eine Callback-Funktion, die für jedes Ereignis einen Link generiert.

### Methoden

#### `generateJson`

Generiert ein JSON-Format für FullCalendar basierend auf den angegebenen Kriterien.

```php
public function generateJson(
    ?string $startDate = null,
    ?string $endDate = null,
    string $sortByStart = 'ASC',
    string $sortByEnd = 'ASC'
): string
```

- **Parameter:**
  - `startDate` (optional): Das Startdatum, um Ereignisse zu filtern.
  - `endDate` (optional): Das Enddatum, um Ereignisse zu filtern.
  - `sortByStart`: Sortierrichtung für das Startdatum (`ASC` oder `DESC`).
  - `sortByEnd`: Sortierrichtung für das Enddatum (`ASC` oder `DESC`).

- **Rückgabewert:** Ein JSON-String, der die Ereignisse im FullCalendar-Format enthält.

### Beispiele

#### Beispiel: Generieren von JSON-Daten für FullCalendar

```php
// Url2 links generieren
$linkCallback = function($id) {
    return rex_getUrl('', '', ['event_id' => $id]);
};

// Erstellen einer Instanz der CalendarEventJson-Klasse
$calendarEventJson = new CalendarEventJson($linkCallback);

// Generieren der JSON-Daten für FullCalendar
$startDate = '2024-01-01';
$endDate = '2024-12-31';
$eventsJson = $calendarEventJson->generateJson($startDate, $endDate);

// Ausgabe der JSON-Daten
echo $eventsJson;
```

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
$eventsJson = $calendarEventJson->generateJson($startDate, $endDate, 'ASC', 'DESC','id=2');
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
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/list.min.js'></script>
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
                views: {
                    listMonth: { buttonText: 'Liste' }, // Listenansicht
                    timeGridWeek: { buttonText: 'Woche' } // Wochenansicht
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,dayGridMonth,listMonth' // Hinzufügen der Wochenansicht zur Toolbar
                },
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


## Erklärung der Klasse `CalendarEventICal`

Die `CalendarEventICal`-Klasse dient dazu, Kalenderereignisse in das iCalendar (iCal)-Format zu konvertieren. Diese Klasse verwendet die `YFormCalHelper`-Klasse, um Ereignisse abzurufen und diese in ein iCal-kompatibles Format zu transformieren.

### Methoden

#### `generateICal`

Generiert den gesamten iCal-Kalender für einen bestimmten Zeitraum.

```php
public function generateICal(
    ?string $startDate = null,
    ?string $endDate = null,
    string $sortByStart = 'ASC', 
    string $sortByEnd = 'ASC'
): string
```

- **Parameter:**
  - `startDate` (optional): Das Startdatum, um Ereignisse zu filtern.
  - `endDate` (optional): Das Enddatum, um Ereignisse zu filtern.
  - `sortByStart`: Sortierrichtung für das Startdatum (`ASC` oder `DESC`).
  - `sortByEnd`: Sortierrichtung für das Enddatum (`ASC` oder `DESC`).

- **Rückgabewert:** 
  - Ein String, der den iCal-Kalender darstellt.

- **Beschreibung:**
  - Diese Methode ruft alle Ereignisse innerhalb des angegebenen Zeitraums ab, konvertiert jedes Ereignis in das iCal-Format und fügt sie zu einem iCal-Kalender zusammen.

#### `generateICalEvent`

Generiert ein einzelnes iCal-Ereignis.

```php
private function generateICalEvent($event): string
```

- **Parameter:**
  - `event`: Das Ereignisobjekt, das in das iCal-Format konvertiert werden soll.

- **Rückgabewert:** 
  - Ein String, der das iCal-Ereignis darstellt.

- **Beschreibung:**
  - Diese Methode konvertiert die Daten eines Ereignisses in das iCal-Format und behandelt ganztägige Ereignisse und wiederkehrende Ereignisse.

#### `escapeString`

Hilfsfunktion zum Escapen von Zeichen in iCal.

```php
private function escapeString(string $string): string
```

- **Parameter:**
  - `string`: Der zu escapende String.

- **Rückgabewert:** 
  - Ein String, in dem die speziellen iCal-Zeichen escapet wurden.

- **Beschreibung:**
  - Diese Methode sorgt dafür, dass spezielle Zeichen im iCal-Format korrekt dargestellt werden.

### Beispiele

#### Beispiel 1: Generieren eines iCal-Kalenders für den aktuellen Monat

```php
require_once 'path/to/CalendarEventICal.php';

// Erstellen einer Instanz der CalendarEventICal-Klasse
$calendar = new CalendarEventICal();

// Festlegen des Start- und Enddatums
$startDate = (new DateTime())->format('Y-m-01'); // Erster Tag des aktuellen Monats
$endDate = (new DateTime())->format('Y-m-t'); // Letzter Tag des aktuellen Monats

// Generieren des iCal-Kalenders
$ical = $calendar->generateICal($startDate, $endDate);

// Ausgabe des iCal-Kalenders
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="calendar.ics"');
echo $ical;
```

#### Beispiel 2: Generieren eines iCal-Kalenders für die nächsten 3 Monate

```php
require_once 'path/to/CalendarEventICal.php';

// Erstellen einer Instanz der CalendarEventICal-Klasse
$calendar = new CalendarEventICal();

// Festlegen des Start- und Enddatums
$startDate = (new DateTime())->format('Y-m-d');
$endDate = (new DateTime())->modify('+3 months')->format('Y-m-d');

// Generieren des iCal-Kalenders
$ical = $calendar->generateICal($startDate, $endDate);

// Ausgabe des iCal-Kalenders
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="calendar.ics"');
echo $ical;
```

Um sicherzustellen, dass die Kategorien in der Datenbank richtig gespeichert werden, damit sie im iCal-Format korrekt verarbeitet werden können, sollten die Kategorien als durch Kommas getrennte Liste von Kategorienamen gespeichert werden. Dies entspricht dem iCal-Standard für das `CATEGORIES`-Feld, das mehrere Kategorien als durch Kommas getrennte Werte unterstützt.

### Beispiel für die Speicherung von Kategorien in der Datenbank

Angenommen, Sie haben ein Ereignis, das zu den Kategorien "Meeting", "Work" und "Urgent" gehört, sollte das entsprechende Feld in der Datenbank so aussehen:

```plaintext
Meeting,Work,Urgent
```

### iCal-Spezifikation für das `CATEGORIES`-Feld

Das `CATEGORIES`-Feld im iCal-Format wird verwendet, um eine oder mehrere Kategorien zu definieren, zu denen das Ereignis gehört. Mehrere Kategorien sollten durch Kommas getrennt werden. Hier ist ein Beispiel für ein `CATEGORIES`-Feld im iCal-Format:

```plaintext
CATEGORIES:Meeting,Work,Urgent
```

### Anpassung der `CalendarEventICal`-Klasse

Die `generateICalEvent`-Methode in der `CalendarEventICal`-Klasse setzt das `CATEGORIES`-Feld im iCal-Format korrekt, indem es den Wert aus der Datenbank direkt verwendet. Die Methode nimmt an, dass die Kategorien in der Datenbank als durch Kommas getrennte Liste gespeichert sind.

### Beispiel für die Speicherung und Verwendung

#### 1. Beispiel: Einfügen eines Ereignisses in die Datenbank

Angenommen, wir fügen ein Ereignis in die Datenbank ein, das die Kategorien "Meeting", "Work" und "Urgent" hat:

```sql
INSERT INTO rex_yform_table (summary, description, location, `status`, categories, dtstart, dtend, all_day, `repeat`, freq, `interval`, repeat_by, exdate)
VALUES ('Team Meeting', 'Discuss project updates', 'Conference Room', 'CONFIRMED', 'Meeting,Work,Urgent', '2024-06-15 10:00:00', '2024-06-15 11:00:00', 0, 0, '', 0, '', '');
```

#### 2. Beispiel: Generieren eines iCal-Kalenders mit Kategorien

Das folgende Beispiel zeigt, wie Sie die `CalendarEventICal`-Klasse verwenden, um einen iCal-Kalender zu generieren, der Ereignisse mit Kategorien enthält:

```php
require_once 'path/to/CalendarEventICal.php';

// Erstellen einer Instanz der CalendarEventICal-Klasse
$calendar = new CalendarEventICal();

// Festlegen des Start- und Enddatums
$startDate = (new DateTime())->format('Y-m-d');
$endDate = (new DateTime())->modify('+1 month')->format('Y-m-d');

// Generieren des iCal-Kalenders
$ical = $calendar->generateICal($startDate, $endDate);

// Ausgabe des iCal-Kalenders
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="calendar.ics"');
echo $ical;
```

### Beispiel für die Ausgabe eines Ereignisses mit Kategorien im iCal-Format

Angenommen, das Ereignis aus der Datenbank hat die folgenden Kategorien: "Meeting,Work,Urgent", dann wird die `generateICalEvent`-Methode dieses Ereignis wie folgt in das iCal-Format konvertieren:

```plaintext
BEGIN:VEVENT
UID:60d21b96c292f@yourdomain.com
SUMMARY:Team Meeting
DESCRIPTION:Discuss project updates
LOCATION:Conference Room
STATUS:CONFIRMED
CATEGORIES:Meeting,Work,Urgent
DTSTART:20240615T100000Z
DTEND:20240615T110000Z
END:VEVENT
```
