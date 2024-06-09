


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


## Erforderliche Tabellenfelder

Um sicherzustellen, dass alle Funktionen der `YFormCalHelper`- und `CalendarEventICal`-Klassen korrekt funktionieren, sollten die folgenden Felder in Ihrer Tabelle vorhanden sein:

1. **summary**: Eine kurze Zusammenfassung oder der Titel des Ereignisses.
2. **description**: Eine detaillierte Beschreibung des Ereignisses.
3. **location**: Der Ort, an dem das Ereignis stattfindet.
4. **status**: Der Status des Ereignisses (z.B. CONFIRMED, TENTATIVE).
5. **categories**: Kategorien des Ereignisses als durch Kommas getrennte Liste.
6. **dtstart**: Das Startdatum und die Startzeit des Ereignisses im Format `YYYY-MM-DD HH:MM:SS`.
7. **dtend**: Das Enddatum und die Endzeit des Ereignisses im Format `YYYY-MM-DD HH:MM:SS`.
8. **all_day**: Ein Boolean-Wert (0 oder 1), der angibt, ob es sich um ein ganztägiges Ereignis handelt.
9. **repeat**: Ein Boolean-Wert (0 oder 1), der angibt, ob das Ereignis wiederholt wird.
10. **freq**: Die Häufigkeit der Wiederholung (z.B. DAILY, WEEKLY, MONTHLY, YEARLY).
11. **interval**: Das Intervall der Wiederholung.
12. **repeat_by**: Die Regel, nach der das Ereignis wiederholt wird (z.B. day oder date).
13. **exdate**: Eine durch Kommas getrennte Liste von Ausnahme-Daten im Format `YYYY-MM-DD`.

Ein Tableset zum Import und Aufbau einer eigenen Tabelle liegt bei. 
