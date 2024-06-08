<?php 
class CalendarEventICal
{
    // Generiert den iCal-Kalender
    public function generateICal(
        ?string $startDate = null,
        ?string $endDate = null,
        string $sortByStart = 'ASC', 
        string $sortByEnd = 'ASC'
    ): string {
        $events = YFormCalHelper::getChronologicalEvents($startDate, $endDate, $sortByStart, $sortByEnd);
        $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Your Organization//Your Product//EN\r\n";

        foreach ($events as $event) {
            $ical .= $this->generateICalEvent($event);
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    // Generiert ein iCal-Ereignis
    private function generateICalEvent($event): string {
        $start = new DateTime($event->getValue('dtstart'));
        $end = new DateTime($event->getValue('dtend'));

        $icalEvent = "BEGIN:VEVENT\r\n";
        $icalEvent .= "UID:" . uniqid() . "@yourdomain.com\r\n";
        $icalEvent .= "SUMMARY:" . $this->escapeString($event->getValue('summary')) . "\r\n";
        $icalEvent .= "DESCRIPTION:" . $this->escapeString($event->getValue('description')) . "\r\n";
        $icalEvent .= "LOCATION:" . $this->escapeString($event->getValue('location')) . "\r\n";
        $icalEvent .= "STATUS:" . $this->escapeString($event->getValue('status')) . "\r\n";
        $icalEvent .= "CATEGORIES:" . $this->escapeString($event->getValue('categories')) . "\r\n";
        $icalEvent .= "DTSTART:" . $start->format('Ymd\THis\Z') . "\r\n";
        $icalEvent .= "DTEND:" . $end->format('Ymd\THis\Z') . "\r\n";

        if ($event->getValue('all_day')) {
            $icalEvent .= "X-MICROSOFT-CDO-ALLDAYEVENT:TRUE\r\n";
            $icalEvent .= "DTSTART;VALUE=DATE:" . $start->format('Ymd') . "\r\n";
            // Für ganztägige Ereignisse muss das Enddatum um einen Tag erhöht werden
            $end->modify('+1 day');
            $icalEvent .= "DTEND;VALUE=DATE:" . $end->format('Ymd') . "\r\n";
        }

        $icalEvent .= "END:VEVENT\r\n";

        return $icalEvent;
    }

    // Hilfsfunktion zum Escapen von Zeichen in iCal
    private function escapeString(string $string): string {
        return addcslashes($string, "\n,;");
    }
}
