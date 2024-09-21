<?php
namespace FriendsOfRedaxo\YFormCalendar;

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
            $calendarEvent = [
                'id' => $event->getId(),
                'title' => $this->safeHtmlspecialchars($event->getValue('summary')),
                'start' => $this->safeHtmlspecialchars($event->getValue('dtstart')),
                'end' => $this->safeHtmlspecialchars($event->getValue('dtend')),
                'description' => $this->safeHtmlspecialchars($event->getValue('description')),
                'location' => $this->safeHtmlspecialchars($event->getValue('location')),
                'categories' => $this->safeHtmlspecialchars($event->getValue('categories')),
                'allDay' => $event->getValue('all_day') ? true : false,
                'url' => $link, // Link basierend auf der ID
            ];

            // Füge 'status' nur hinzu, wenn es verfügbar ist
            if ($event->hasValue('status')) {
                $calendarEvent['status'] = $this->safeHtmlspecialchars($event->getValue('status'));
            }

            $calendarEvents[] = $calendarEvent;
        }

        return json_encode($calendarEvents);
    }

    /**
     * Eine sichere Version von htmlspecialchars, die null-Werte handhabt.
     *
     * @param mixed $value Der zu verarbeitende Wert.
     * @return string Der verarbeitete String oder ein leerer String, wenn der Eingabewert null ist.
     */
    private function safeHtmlspecialchars($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
