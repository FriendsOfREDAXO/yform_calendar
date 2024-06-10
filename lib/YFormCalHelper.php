<?php
use RRule\RRule;
class YFormCalHelper extends \rex_yform_manager_dataset
{
    private static ?string $startDate = null;
    private static ?string $endDate = null;
    private static string $sortByStart = 'ASC';
    private static string $sortByEnd = 'ASC';
    private static ?string $whereRaw = null;

    // Setter-Methoden
    public static function setStartDate(?string $startDate): void
    {
        self::$startDate = $startDate;
    }

    public static function setEndDate(?string $endDate): void
    {
        self::$endDate = $endDate;
    }

    public static function setSortByStart(string $sortByStart): void
    {
        self::$sortByStart = $sortByStart;
    }

    public static function setSortByEnd(string $sortByEnd): void
    {
        self::$sortByEnd = $sortByEnd;
    }

    public static function setWhereRaw(?string $whereRaw): void
    {
        self::$whereRaw = $whereRaw;
    }

    // Wrapper-Methode für Kompatibilität
    public static function getChronologicalEvents(
        ?string $startDate = null,
        ?string $endDate = null,
        string $sortByStart = 'ASC',
        string $sortByEnd = 'ASC',
        ?string $whereRaw = null
    ): array {
        self::setStartDate($startDate);
        self::setEndDate($endDate);
        self::setSortByStart($sortByStart);
        self::setSortByEnd($sortByEnd);
        self::setWhereRaw($whereRaw);
        return self::getEvents();
    }

    // Holt alle Ereignisse und sortiert sie nach den angegebenen Kriterien
    public static function getEvents(): array
    {
        $query = self::query();

        if (self::$whereRaw) {
            $query->whereRaw(self::$whereRaw);
        }

        $events = $query->find();
        $allEvents = [];

          foreach ($events as $event) {
            if ($event->getValue('rrule')) {
                // Wenn rrule vorhanden ist, benutze generateRruleRecurringEvents
                $allEvents = array_merge($allEvents, self::generateRruleRecurringEvents($event));
            } elseif ($event->getValue('repeat')) {
                // Wenn repeat vorhanden ist, benutze generateRecurringEvents
                $allEvents = array_merge($allEvents, self::generateRecurringEvents($event));
            } else {
                $allEvents[] = $event;
            }
        }

        if (self::$startDate || self::$endDate) {
            $filteredEvents = [];
            $start = self::$startDate ? new DateTime(self::$startDate) : null;
            $end = self::$endDate ? new DateTime(self::$endDate) : null;

            foreach ($allEvents as $event) {
                $eventStart = new DateTime($event->getValue('dtstart'));
                $eventEnd = new DateTime($event->getValue('dtend'));

                if ((!$start || $eventStart >= $start) && (!$end || $eventEnd <= $end)) {
                    $filteredEvents[] = $event;
                }
            }

            $allEvents = $filteredEvents;
        }

        usort($allEvents, function ($a, $b) {
            $startComparison = strtotime($a->getValue('dtstart')) <=> strtotime($b->getValue('dtstart'));
            $endComparison = strtotime($a->getValue('dtend')) <=> strtotime($b->getValue('dtend'));

            if (self::$sortByStart === 'DESC') {
                $startComparison *= -1;
            }

            if (self::$sortByEnd === 'DESC') {
                $endComparison *= -1;
            }

            return $startComparison ?: $endComparison;
        });

        return $allEvents;
    }

    // Generiert wiederkehrende Ereignisse basierend auf den Wiederholungsregeln
    private static function generateRecurringEvents($event): array
    {
        $recurringEvents = [];
        $currentDate = new DateTime($event->getValue('dtstart')); // Startdatum des Ereignisses
        $originalEndDate = new DateTime($event->getValue('dtend')); // Enddatum des ursprünglichen Ereignisses
        $endRecurrence = $event->getValue('until') ? new DateTime($event->getValue('until')) : new DateTime('+1 year'); // Enddatum der Wiederholung
        $repeatBy = $event->getValue('repeat_by'); // 'date' oder 'day'

        // Berechne den Wochentag und die Woche im Monat des ursprünglichen Ereignisses
        $originalDayOfWeek = (int)$currentDate->format('N'); // 1 (für Montag) bis 7 (für Sonntag)
        $originalWeekOfMonth = (int)ceil($currentDate->format('j') / 7); // Woche im Monat

        // Formatieren der Ausnahmedaten
        $exceptions = array_map(fn ($date) => (new DateTime($date))->format('Ymd'), explode(',', $event->getValue('exdate'))); // Exceptions als Array und im richtigen Format

        // Generiere die wiederkehrenden Ereignisse bis zum Enddatum
        while ($currentDate <= $endRecurrence) {
            $formattedCurrentDate = $currentDate->format('Ymd');

            // Prüfe, ob das aktuelle Datum in den Ausnahmen enthalten ist
            if (in_array($formattedCurrentDate, $exceptions)) {
                self::nextOccurrence($currentDate, $event->getValue('freq'), (int)$event->getValue('interval'), $repeatBy, $originalDayOfWeek, $originalWeekOfMonth);
                continue;
            }

            $newEvent = clone $event;
            $newEventStart = clone $currentDate;

            // Berechne die Dauer des ursprünglichen Ereignisses
            $duration = $originalEndDate->getTimestamp() - (new DateTime($event->getValue('dtstart')))->getTimestamp();
            $newEventEnd = (clone $newEventStart)->modify("+$duration seconds");

            $newEvent->setValue('dtstart', $newEventStart->format('Y-m-d H:i:s'));
            $newEvent->setValue('dtend', $newEventEnd->format('Y-m-d H:i:s'));

            // Anpassen der Endzeit für ganztägige Ereignisse
            if ($newEvent->getValue('all_day')) {
                $newEvent->setValue('dtend', (clone $newEventEnd)->format('Y-m-d'));
            }

            $recurringEvents[] = $newEvent;

            // Berechne das nächste Wiederholungsdatum
            self::nextOccurrence($currentDate, $event->getValue('freq'), (int)$event->getValue('interval'), $repeatBy, $originalDayOfWeek, $originalWeekOfMonth);
        }

        return $recurringEvents;
    }


   // Generiert wiederkehrende Ereignisse basierend auf RRULE
    private static function generateRruleRecurringEvents($event): array
    {
        $recurringEvents = [];
        $rrule = new RRule($event->getValue('rrule'));

        foreach ($rrule as $occurrence) {
            $newEvent = clone $event;
            $newEventStart = $occurrence->format('Y-m-d H:i:s');
            $newEventEnd = (new DateTime($newEventStart))->modify('+1 hour')->format('Y-m-d H:i:s'); // Annahme: Ereignis dauert eine Stunde

            $newEvent->setValue('dtstart', $newEventStart);
            $newEvent->setValue('dtend', $newEventEnd);

            $recurringEvents[] = $newEvent;
        }

        return $recurringEvents;
    }


    // Berechnet das nächste Wiederholungsdatum basierend auf freq und repeat_by
    private static function nextOccurrence(DateTime &$currentDate, string $freq, int $interval, string $repeatBy, int $originalDayOfWeek, int $originalWeekOfMonth): void
    {
        $currentDate = match ($freq) {
            'DAILY' => $currentDate->modify('+' . $interval . ' day'),
            'WEEKLY' => $currentDate->modify('+' . $interval . ' week'),
            'MONTHLY' => $repeatBy == 'day' ? self::getNextWeekdayInMonth($currentDate, $originalDayOfWeek, $originalWeekOfMonth, $interval) : $currentDate->modify('+' . $interval . ' month'),
            'YEARLY' => $repeatBy == 'day' ? self::getNextWeekdayOfYear($currentDate, $originalDayOfWeek, $interval) : $currentDate->modify('+' . $interval . ' year'),
            default => throw new InvalidArgumentException("Invalid frequency: $freq"),
        };
    }

    // Berechnet das nächste Wiederholungsdatum basierend auf dem Wochentag im Monat
    private static function getNextWeekdayInMonth(DateTime $currentDate, int $originalDayOfWeek, int $originalWeekOfMonth, int $interval): DateTime
    {
        $nextDate = clone $currentDate;
        $nextDate->modify('first day of this month');
        $nextDate->modify('+' . $interval . ' month');

        $counter = 0;
        while ($counter < $originalWeekOfMonth) {
            if ((int)$nextDate->format('N') === $originalDayOfWeek) {
                $counter++;
            }
            if ($counter < $originalWeekOfMonth) {
                $nextDate->modify('+1 day');
            }
        }

        // Falls der Monat nicht genügend Wochen hat, setze das Datum auf den Anfang des nächsten Monats
        if ((int)ceil($nextDate->format('j') / 7) < $originalWeekOfMonth) {
            $nextDate->modify('first day of next month');
        }

        return $nextDate;
    }

    // Berechnet das nächste Wiederholungsdatum basierend auf dem Wochentag im Jahr
    private static function getNextWeekdayOfYear(DateTime $currentDate, int $dayOfWeek, int $interval): DateTime
    {
        $nextDate = clone $currentDate;
        $nextDate->modify('+' . $interval . ' year');

        // Suche den nächsten Wochentag im Zieljahr
        while ((int)$nextDate->format('N') !== $dayOfWeek) {
            $nextDate->modify('+1 day');
        }

        return $nextDate;
    }

    // Holt alle Ereignisse für ein spezifisches Datum oder Zeitraum
    public static function getEventsByDate(string $startDate, ?string $endDate = null): array
    {
        $events = self::getChronologicalEvents($startDate, $endDate);
        $specificEvents = [];

        $start = new DateTime($startDate);
        $end = $endDate ? new DateTime($endDate) : $start;

        // Filtere Ereignisse basierend auf dem angegebenen Zeitraum
        foreach ($events as $event) {
            $eventStart = new DateTime($event->getValue('dtstart'));
            $eventEnd = new DateTime($event->getValue('dtend'));

            if ($eventStart >= $start && $eventEnd <= $end) {
                $specificEvents[] = $event;
            }
        }

        return $specificEvents;
    }

    // Holt die nächsten X Ereignisse ab einem festgelegten Datum und/oder Uhrzeit basierend auf der Datensatz-ID eines Termins
    public static function getNextEvents(int $eventId, int $limit, ?string $startDateTime = null): array
    {
        $event = self::get($eventId);
        if (!$event) {
            return [];
        }

        $startDateTime = $startDateTime ?: (new DateTime())->format('Y-m-d H:i:s');
        $startDateTimeObj = new DateTime($startDateTime);
        $events = self::getChronologicalEvents();

        $filteredEvents = [];
        foreach ($events as $e) {
            $eventStart = new DateTime($e->getValue('dtstart'));
            if ($eventStart >= $startDateTimeObj && $e->getId() == $eventId) {
                $filteredEvents[] = $e;
            }
        }

        usort($filteredEvents, function ($a, $b) {
            return strtotime($a->getValue('dtstart')) <=> strtotime($b->getValue('dtstart'));
        });

        return array_slice($filteredEvents, 0, $limit);
    }
}

