<?php

namespace klxm\YFormCalhelper;

use DateTime;
use DateTimeInterface;
use Generator;
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
     *     @type int $limit Maximum number of events to return
     * }
     * @return Generator
     */
    public static function getCalendarEvents(array $params = []): Generator
    {
        $query = static::query();

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

        self::addExceptionsToRSet($rset, $event->getValue('exdate'));

        $originalStart = new DateTime($event->getValue('dtstart'));
        $originalEnd = new DateTime($event->getValue('dtend'));
        $duration = $originalEnd->getTimestamp() - $originalStart->getTimestamp();

        foreach ($rset as $occurrence) {
            if ((!$start || $occurrence >= $start) && (!$end || $occurrence <= $end)) {
                yield self::createRecurringEvent($event, $occurrence, $duration);
            }
            if ($end && $occurrence > $end) {
                break;
            }
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

    private static function addExceptionRange(RSet $rset, DateTime $start, DateTime $end): void
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

    /**
     * Sort events based on start and end dates.
     *
     * @param array $events Array of events to sort
     * @param string $sortByStart Sort direction for start date ('ASC' or 'DESC')
     * @param string $sortByEnd Sort direction for end date ('ASC' or 'DESC')
     * @return array Sorted array of events
     */
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

    /**
     * Compare event dates for sorting.
     *
     * @param rex_yform_manager_dataset $a First event to compare
     * @param rex_yform_manager_dataset $b Second event to compare
     * @param string $field Field to compare ('dtstart' or 'dtend')
     * @param string $direction Sort direction ('ASC' or 'DESC')
     * @return int Comparison result (-1, 0, or 1)
     */
    private static function compareEventDates(rex_yform_manager_dataset $a, rex_yform_manager_dataset $b, string $field, string $direction): int
    {
        $aDate = self::createDateTime($a->getValue($field));
        $bDate = self::createDateTime($b->getValue($field));
        $comparison = $aDate <=> $bDate;
        return $direction === 'DESC' ? -$comparison : $comparison;
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
     * Get upcoming events.
     *
     * @param int $limit Maximum number of events to return
     * @param string|null $startDateTime Start date/time (default: now)
     * @return array
     */
    public static function getUpcomingEvents(int $limit = 10, ?string $startDateTime = null): array
    {
        $startDateTime = $startDateTime ?: (new DateTime())->format('Y-m-d H:i:s');
        return iterator_to_array(self::getCalendarEvents([
            'startDate' => $startDateTime,
            'limit' => $limit,
            'sortByStart' => 'ASC'
        ]));
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
