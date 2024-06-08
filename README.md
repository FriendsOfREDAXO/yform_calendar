# YFormCalHelper


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

