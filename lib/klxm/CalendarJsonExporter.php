<?php
namespace klxm\YFormCalendar

use rex_yform_manager_dataset;

class CalendarJsonExporter
{
    private $linkCallback;
    private $modelClass;

    /**
     * Konstruktor, der einen Callback für Links und eine Modellklasse akzeptiert.
     *
     * @param callable $linkCallback Eine Callback-Funktion, die einen Link basierend auf einer Event-ID erzeugt.
     * @param string $modelClass Die Modellklasse, die für die Abfrage der Tabelle verwendet wird.
     */
    public function __construct(callable $linkCallback, string $modelClass)
    {
        $this->linkCallback = $linkCallback;
        $this->modelClass = $modelClass;
    }

    /**
     * Generiert ein JSON für die Kalendereinträge innerhalb des angegebenen Datumsbereichs.
     *
     * @param string|null $startDate Startdatum im Format 'Y-m-d H:i:s' oder 'Y-m-d'.
     * @param string|null $endDate Enddatum im Format 'Y-m-d H:i:s' oder 'Y-m-d'.
     * @param string $sortByStart Sortierrichtung nach Startdatum ('ASC' oder 'DESC').
     * @param string $sortByEnd Sortierrichtung nach Enddatum ('ASC' oder 'DESC').
     * @return string JSON-String, der die Kalendereinträge enthält.
     */
    public function generateJson(
        ?string $startDate = null,
        ?string $endDate = null,
        string $sortByStart = 'ASC', 
        string $sortByEnd = 'ASC'
    ): string {
        // Holen der Ereignisse mit den angegebenen Parametern
        $events = $this->modelClass::getCalendarEvents([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sortByStart' => $sortByStart,
            'sortByEnd' => $sortByEnd,
        ]);

        $calendarEvents = [];

        foreach ($events as $event) {
            $link = call_user_func($this->linkCallback, $event->getId());
            $calendarEvents[] = [
                'id' => $event->getId(),
                'title' => htmlspecialchars($event->getValue('summary')),
                'start' => htmlspecialchars($event->getValue('dtstart')),
                'end' => htmlspecialchars($event->getValue('dtend')),
                'description' => htmlspecialchars($event->getValue('description')),
                'location' => htmlspecialchars($event->getValue('location')),
                'status' => htmlspecialchars($event->getValue('status')),
                'categories' => htmlspecialchars($event->getValue('categories')),
                'allDay' => $event->getValue('all_day') ? true : false,
                'url' => $link, // Link basierend auf der ID
            ];
        }

        return json_encode($calendarEvents);
    }
}
