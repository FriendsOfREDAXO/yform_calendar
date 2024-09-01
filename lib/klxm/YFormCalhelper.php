<?php

namespace klxm\YFormCalhelper;

use DateTime;
use DateTimeInterface;
use Generator;
use RRule\RRule;
use RRule\RSet;
use rex_yform_manager_dataset;

class CalRender extends rex_yform_manager_dataset
{
    /**
     * Get calendar events based on the provided parameters.
     *
     * @param array $params {
     *     @type string $startDate Start date/time in 'Y-m-d H:i:s' or 'Y-m-d' format
     *     @type string $endDate End date/time in 'Y-m-d H:i:s' or 'Y-m-d' format
     *     @type string $sortByStart Sort direction for start date ('ASC' or 'DESC')
     *     @type string $sortByEnd Sort direction for end date ('ASC' or 'DESC')
     *     @type string $whereRaw Additional WHERE clause for the query
     * }
     * @return Generator
     */
    public static function getCalendarEvents(array $params = []): Generator
    {
        $query = static::query();

        if (isset($params['whereRaw'])) {
            $query->whereRaw($params['whereRaw']);
        }

        $events = $query->find();
        $allEvents = self::generateAllEvents($events);

        foreach (self::filterEventsByDate(iterator_to_array($allEvents), $params['startDate'] ?? null, $params['endDate'] ?? null) as $event) {
            yield $event;
        }
    }

    private static function generateAllEvents(array $events): Generator
    {
        foreach ($events as $event) {
            if ($event->getValue('rrule')) {
                yield from self::generateRruleRecurringEvents($event);
            } else {
                yield $event;
            }
        }
    }

    private static function generateRruleRecurringEvents(rex_yform_manager_dataset $event): Generator
    {
        $rset = new RSet();
        $rset->addRRule($event->getValue('rrule'));
        
        self::addExceptionsToRSet($rset, $event->getValue('exdate'));

        $originalStart = new DateTime($event->getValue('dtstart'));
        $originalEnd = new DateTime($event->getValue('dtend'));
        $duration = $originalEnd->getTimestamp() - $originalStart->getTimestamp();

        foreach ($rset as $occurrence) {
            yield self::createRecurringEvent($event, $occurrence, $duration);
        }
    }

    private static function addExceptionsToRSet(RSet $rset, ?string $exdateString): void
    {
        if ($exdateString === null) {
            return;
        }

        $exceptions = self::parseExceptions($exdateString);
        foreach ($exceptions as $exception) {
            if ($exception instanceof DateTime) {
                $rset->addExDate($exception);
            } elseif (is_array($exception)) {
                self::addExceptionRange($rset, $exception['start'], $exception['end']);
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
                    'start' => new DateTime($start),
                    'end' => new DateTime($end)
                ];
            } else {
                $exceptions[] = new DateTime($item);
            }
        }
        
        return $exceptions;
    }

    private static void addExceptionRange(RSet $rset, DateTime $start, DateTime $end): void
    {
        $current = clone $start;
        while ($current <= $end) {
            $rset->addExDate($current);
            $current->modify('+1 day');
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

    private static function filterEventsByDate(array $events, ?string $startDate, ?string $endDate): array
    {
        if (!$startDate && !$endDate) {
            return $events;
        }

        $start = $startDate ? self::createDateTime($startDate) : null;
        $end = $endDate ? self::createDateTime($endDate) : null;

        return array_filter($events, function ($event) use ($start, $end) {
            $eventStart = self::createDateTime($event->getValue('dtstart'));
            $eventEnd = self::createDateTime($event->getValue('dtend'));

            return (!$start || $eventStart >= $start) && (!$end || $eventEnd <= $end);
        });
    }

    private static function sortEvents(array $events, string $sortByStart, string $sortByEnd): array
    {
        usort($events, function ($a, $b) use ($sortByStart, $sortByEnd) {
            $startComparison = self::compareEventDates($a, $b, 'dtstart', $sortByStart);
            return $startComparison ?: self::compareEventDates($a, $b, 'dtend', $sortByEnd);
        });

        return $events;
    }

    private static function compareEventDates($a, $b, string $field, string $sortDirection): int
    {
        $aDate = self::createDateTime($a->getValue($field));
        $bDate = self::createDateTime($b->getValue($field));
        $comparison = $aDate <=> $bDate;
        return $sortDirection === 'DESC' ? -$comparison : $comparison;
    }

    /**
     * Get events for a specific date range.
     *
     * @param string $startDate Start date/time in 'Y-m-d H:i:s' or 'Y-m-d' format
     * @param string|null $endDate End date/time in 'Y-m-d H:i:s' or 'Y-m-d' format
     * @return array
     */
    public static function getEventsByDate(string $startDate, ?string $endDate = null): array
    {
        return iterator_to_array(self::getCalendarEvents([
            'startDate' => $startDate,
            'endDate' => $endDate
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
        $events = iterator_to_array(self::getCalendarEvents(['startDate' => $startDateTime]));

        $filteredEvents = array_filter($events, function($e) use ($eventId, $startDateTime) {
            return $e->getId() == $eventId && self::createDateTime($e->getValue('dtstart')) >= self::createDateTime($startDateTime);
        });

        usort($filteredEvents, function ($a, $b) {
            return self::createDateTime($a->getValue('dtstart')) <=> self::createDateTime($b->getValue('dtstart'));
        });

        return array_slice($filteredEvents, 0, $limit);
    }

    /**
     * Create a DateTime object from a string.
     *
     * @param string $dateTimeString Date/time string in 'Y-m-d H:i:s' or 'Y-m-d' format
     * @return DateTime
     */
    private static function createDateTime(string $dateTimeString): DateTime
    {
        return new DateTime($dateTimeString);
    }
}
