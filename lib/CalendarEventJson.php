<?php 
class CalendarEventJson
{
    private $linkCallback;

    public function __construct(callable $linkCallback)
    {
        $this->linkCallback = $linkCallback;
    }

    public function generateJson(
        ?string $startDate = null,
        ?string $endDate = null,
        string $sortByStart = 'ASC', 
        string $sortByEnd = 'ASC'
    ): string {
        // Setzen der Filterkriterien
        YFormCalHelper::setStartDate($startDate);
        YFormCalHelper::setEndDate($endDate);
        YFormCalHelper::setSortByStart($sortByStart);
        YFormCalHelper::setSortByEnd($sortByEnd);

        // Holen der Ereignisse
        $events = YFormCalHelper::getEvents();
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
                // Fügen Sie weitere Felder hinzu, falls erforderlich
            ];
        }

        return json_encode($calendarEvents);
    }
}
