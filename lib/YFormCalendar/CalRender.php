<?php

namespace FriendsOfRedaxo\YFormCalendar;

use DateTime;
use Generator;
use RRule\RSet;
use rex_yform_manager_dataset;

class CalRender extends rex_yform_manager_dataset
{
    /**
     * Get calendar events based on the provided parameters or a custom query.
     *
     * @param array $params {
     *     @type string $startDate Start date/time in 'Y-m-d H:i:s' or 'Y-m-d' format
     *     @type string $endDate End date/time in 'Y-m-d H:i:s' or 'Y-m-d' format
     *     @type string $sortByStart Sort direction for start date ('ASC' or 'DESC')
     *     @type string $sortByEnd Sort direction for end date ('ASC' or 'DESC')
     *     @type string $whereRaw Additional WHERE clause for the query
     *     @type int $limit Maximum number of events to return
     * }
     * @param rex_yform_manager_query|null $customQuery Optional custom query to use instead of generating a new one
     * @return Generator
     */
    public static function getCalendarEvents(array $params = [], rex_yform_manager_query $customQuery = null): Generator
    {
        $query = $customQuery ?? static::query();  // Verwende die übergebene Query oder erstelle eine neue

        if (isset($params['whereRaw'])) {
            $query->whereRaw($params['whereRaw']);
        }

        $limit = $params['limit'] ?? PHP_INT_MAX;
        $events = [];
        $count = 0;

        foreach ($query->find() as $event) {
            foreach (self::generateEventsForSingleEvent($event, $params['startDate'] ?? null, $params['endDate'] ?? null) as $generatedEvent) {
                $events[] = $generatedEvent;
                $count++;
                if ($count >= $limit) {
                    break 2;
                }
            }
        }

        $sortedEvents = self::sortEvents($events, $params['sortByStart'] ?? 'ASC', $params['sortByEnd'] ?? 'ASC');

        foreach ($sortedEvents as $event) {
            yield $event;
        }
    }


    private static function generateEventsForSingleEvent(rex_yform_manager_dataset $event, ?string $startDate, ?string $endDate): Generator
    {
        $start = $startDate ? self::createDateTime($startDate) : null;
        $end = $endDate ? self::createDateTime($endDate) : null;

        if ($event->getValue('rrule')) {
            yield from self::generateRruleRecurringEvents($event, $start, $end);
        } else {
            $eventStart = self::createDateTime($event->getValue('dtstart'));
            $eventEnd = self::createDateTime($event->getValue('dtend'));

            if ((!$start || $eventStart >= $start) && (!$end || $eventEnd <= $end)) {
                yield $event;
            }
        }
    }

    private static function generateRruleRecurringEvents(rex_yform_manager_dataset $event, ?DateTime $start, ?DateTime $end): Generator
    {
        $rset = new RSet();
        $rset->addRRule($event->getValue('rrule'));

        // Füge exdate-Daten hinzu und verarbeite Ranges
        $exceptions = self::parseExceptions($event->getValue('exdate'));

        $originalStart = new DateTime($event->getValue('dtstart'));
        $originalEnd = new DateTime($event->getValue('dtend'));
        $duration = $originalEnd->getTimestamp() - $originalStart->getTimestamp();

        foreach ($rset as $occurrence) {
            $occurrenceDate = $occurrence->format('Y-m-d');

            // Prüfe, ob das Vorkommnis in den exdate-Ausnahmen enthalten ist
            $isExcluded = false;
            foreach ($exceptions as $exception) {
                // Unterstützt sowohl einzelne Ausnahmedaten als auch Datumsbereiche
                if ($exception instanceof DateTime) {
                    if ($occurrenceDate === $exception->format('Y-m-d')) {
                        $isExcluded = true;
                        break;
                    }
                } elseif (is_array($exception) && isset($exception['start'], $exception['end'])) {
                    $startRange = $exception['start']->format('Y-m-d');
                    $endRange = $exception['end']->format('Y-m-d');
                    if ($occurrenceDate >= $startRange && $occurrenceDate <= $endRange) {
                        $isExcluded = true;
                        break;
                    }
                }
            }

            if ($isExcluded) {
                // Debug-Ausgabe für ausgeschlossene Termine
                #echo "Vorkommnis ausgeschlossen: " . $occurrence->format('Y-m-d H:i:s') . " Berlin Time\n";
                continue; // überspringe dieses Vorkommnis
            }

            if ((!$start || $occurrence >= $start) && (!$end || $occurrence <= $end)) {
                yield self::createRecurringEvent($event, $occurrence, $duration);
            }

            if ($end && $occurrence > $end) {
                break;
            }
        }
    }

    private static function parseExceptions(string $exdateString): array
    {
        $exceptions = [];
        $items = array_map('trim', explode(',', $exdateString));

        foreach ($items as $item) {
            if (strpos($item, '/') !== false) {
                [$start, $end] = explode('/', $item);
                $exceptions[] = [
                    'start' => new DateTime($start, new \DateTimeZone('Europe/Berlin')),
                    'end' => new DateTime($end, new \DateTimeZone('Europe/Berlin'))
                ];
            } else {
                $exceptions[] = new DateTime($item, new \DateTimeZone('Europe/Berlin'));
            }
        }

        return $exceptions;
    }



    private static function addExceptionsToRSet(RSet $rset, ?string $exdateString): void
    {
        if ($exdateString === null) {
            return;
        }

        $exceptions = self::parseExceptions($exdateString);
        foreach ($exceptions as $exception) {
            // Setze die Zeitzone explizit auf Europe/Berlin
            $exception->setTimezone(new \DateTimeZone('Europe/Berlin'));
            // Setze die Zeit auf Mitternacht in der Berliner Zeitzone
            $exception->setTime(0, 0, 0);

            // Debug: Ausgabe des hinzugefügten Ausnahme-Datums
            echo "Hinzufügen von Ausnahme-Datum: " . $exception->format('Y-m-d H:i:s') . " Berlin Time\n";

            $rset->addExDate($exception);
        }
    }




    private static function createRecurringEvent(rex_yform_manager_dataset $event, DateTime $occurrence, int $duration): rex_yform_manager_dataset
    {
        $newEvent = clone $event;
        $newEventStart = clone $occurrence;
        $newEventEnd = (clone $newEventStart)->modify("+$duration seconds");

        $newEvent->setValue('dtstart', $newEventStart->format('Y-m-d H:i:s'));
        $newEvent->setValue('dtend', $newEventEnd->format('Y-m-d H:i:s'));

        if ($newEvent->getValue('all_day')) {
            $newEvent->setValue('dtend', $newEventEnd->format('Y-m-d'));
        }

        return $newEvent;
    }

    private static function sortEvents(array $events, string $sortByStart, string $sortByEnd): array
    {
        usort($events, function (rex_yform_manager_dataset $a, rex_yform_manager_dataset $b) use ($sortByStart, $sortByEnd) {
            $startComparison = self::compareEventDates($a, $b, 'dtstart', $sortByStart);
            if ($startComparison !== 0) {
                return $startComparison;
            }
            return self::compareEventDates($a, $b, 'dtend', $sortByEnd);
        });

        return $events;
    }

    private static function compareEventDates(rex_yform_manager_dataset $a, rex_yform_manager_dataset $b, string $field, string $direction): int
    {
        $aDate = self::createDateTime($a->getValue($field));
        $bDate = self::createDateTime($b->getValue($field));
        $comparison = $aDate <=> $bDate;
        return $direction === 'DESC' ? -$comparison : $comparison;
    }

    private static function createDateTime(string $dateTimeString): DateTime
    {
        return new DateTime($dateTimeString);
    }

    /**
     * Get events for a specific date range.
     *
     * @param string $startDate Start date/time in 'Y-m-d H:i:s' or 'Y-m-d' format
     * @param string|null $endDate End date/time in 'Y-m-d H:i:s' or 'Y-m-d' format
     * @param int $limit Maximum number of events to return
     * @return array
     */
    public static function getEventsByDate(string $startDate, ?string $endDate = null, int $limit = PHP_INT_MAX): array
    {
        return iterator_to_array(self::getCalendarEvents([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'limit' => $limit
        ]));
    }

    /**
     * Get the next events for a specific event ID.
     *
     * @param int $eventId The ID of the event
     * @param int $limit The maximum number of events to return
     * @param string|null $startDateTime Start date/time in 'Y-m-d H:i:s' or 'Y-m-d' format
     * @return array
     */
    public static function getNextEvents(int $eventId, int $limit, ?string $startDateTime = null): array
    {
        $event = self::get($eventId);
        if (!$event) {
            return [];
        }

        $startDateTime = $startDateTime ?: (new DateTime())->format('Y-m-d H:i:s');
        $filteredEvents = [];

        foreach (self::getCalendarEvents(['startDate' => $startDateTime, 'limit' => $limit]) as $e) {
            if ($e->getId() == $eventId && self::createDateTime($e->getValue('dtstart')) >= self::createDateTime($startDateTime)) {
                $filteredEvents[] = $e;
                if (count($filteredEvents) >= $limit) {
                    break;
                }
            }
        }

        usort($filteredEvents, function ($a, $b) {
            return self::createDateTime($a->getValue('dtstart')) <=> self::createDateTime($b->getValue('dtstart'));
        });

        return $filteredEvents;
    }
    /**
     * Create a new event in the database.
     *
     * @param array $data An associative array of field names and their values
     * @return rex_yform_manager_dataset|null The newly created event, or null if creation failed
     */
    public static function createEvent(array $data): ?rex_yform_manager_dataset
    {
        $event = self::create();

        foreach ($data as $field => $value) {
            if ($event->hasValue($field)) {
                $event->setValue($field, $value);
            }
        }

        if ($event->save()) {
            return $event;
        }

        return null;
    }

    /**
     * Update an event in the database by its ID.
     *
     * @param int $eventId The ID of the event to update
     * @param array $data An associative array of field names and their new values
     * @return bool True if the update was successful, false otherwise
     */
    public static function updateEventById(int $eventId, array $data): bool
    {
        $event = self::get($eventId);
        if (!$event) {
            return false;
        }

        foreach ($data as $field => $value) {
            if ($event->hasValue($field)) {
                $event->setValue($field, $value);
            }
        }

        return $event->save();
    }

    /**
     * Delete an event from the database by its ID.
     *
     * @param int $eventId The ID of the event to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public static function deleteEventById(int $eventId): bool
    {
        $event = self::get($eventId);
        if (!$event) {
            return false;
        }

        return $event->delete();
    }

    /**
     * Holt den Original-Datensatz eines wiederholenden Events für eine bestimmte Event-ID und ein Datum,
     * wobei Start- und Endzeitpunkte des Termins dem angegebenen Wiederholungstermin entsprechen.
     *
     * @param int $eventId Die ID des Events
     * @param string $occurrenceDate Das Datum des spezifischen wiederholenden Termins im Format 'Y-m-d'
     * @return rex_yform_manager_dataset|null Der Datensatz des wiederkehrenden Events mit angepassten Start- und Endzeiten oder null, wenn kein passendes Event gefunden wurde
     */
    public static function getEventDetailsByOccurrence(int $eventId, string $occurrenceDate): ?rex_yform_manager_dataset
    {
        if ($eventId > 0 && $occurrenceDate) {
            $event = self::get($eventId);
            if (!$event) {
                return null;
            }

            $occurrenceDateTime = new DateTime($occurrenceDate);
            $originalStart = new DateTime($event->getValue('dtstart'));
            $originalEnd = new DateTime($event->getValue('dtend'));

            // Prüfe, ob der Termin ganztägig ist
            $isAllDay = $event->getValue('all_day');

            if ($isAllDay) {
                // Wenn der Termin ganztägig ist, setze die Uhrzeiten auf den Beginn und das Ende des Tages
                $newStart = (clone $occurrenceDateTime)->setTime(0, 0, 0);
                $newEnd = (clone $occurrenceDateTime)->setTime(23, 59, 59);
            } else {
                // Behalte die ursprünglichen Uhrzeiten bei, aber ändere das Datum
                $newStart = (clone $occurrenceDateTime)->setTime(
                    (int)$originalStart->format('H'),
                    (int)$originalStart->format('i'),
                    (int)$originalStart->format('s')
                );
                $newEnd = (clone $occurrenceDateTime)->setTime(
                    (int)$originalEnd->format('H'),
                    (int)$originalEnd->format('i'),
                    (int)$originalEnd->format('s')
                );
            }

            $newEvent = clone $event;
            $newEvent->setValue('dtstart', $newStart->format('Y-m-d H:i:s'));
            $newEvent->setValue('dtend', $newEnd->format('Y-m-d H:i:s'));

            return $newEvent;
        }

        return null;
    }
}
