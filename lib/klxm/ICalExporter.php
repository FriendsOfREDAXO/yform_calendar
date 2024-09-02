<?php

namespace klxm\YFormCalendar;

use DateTime;

class ICalExporter
{
    /**
     * Generate an iCal file from calendar events.
     *
     * @param string $filename The name of the file to generate
     * @param array $events An array of rex_yform_manager_dataset objects representing the events
     * @return void
     */
    public static function generateICalFile(string $filename, array $events): void
    {
        $icalData = self::generateICal($events);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.ics"');

        echo $icalData;
    }

    /**
     * Generate iCal data from an array of events.
     *
     * @param array $events An array of rex_yform_manager_dataset objects
     * @return string The generated iCal data
     */
    public static function generateICal(array $events): string
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Your Organization//Your Product//EN\r\n";

        foreach ($events as $event) {
            $ical .= self::generateEvent($event);
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Generate an iCal event string from a single event.
     *
     * @param rex_yform_manager_dataset $event The event object
     * @return string The generated iCal event
     */
    private static function generateEvent(rex_yform_manager_dataset $event): string
    {
        $dtStart = self::formatICalDateTime($event->getValue('dtstart'), $event->getValue('all_day'));
        $dtEnd = self::formatICalDateTime($event->getValue('dtend'), $event->getValue('all_day'));
        $summary = self::escapeString($event->getValue('summary'));
        $description = self::escapeString($event->getValue('description'));
        $location = self::escapeString($event->getValue('location'));
        $uid = uniqid();

        $icalEvent = "BEGIN:VEVENT\r\n";
        $icalEvent .= "UID:$uid\r\n";
        $icalEvent .= "DTSTAMP:" . self::formatICalDateTime((new DateTime())->format('Y-m-d H:i:s')) . "\r\n";
        $icalEvent .= "DTSTART:$dtStart\r\n";
        $icalEvent .= "DTEND:$dtEnd\r\n";
        $icalEvent .= "SUMMARY:$summary\r\n";
        if (!empty($description)) {
            $icalEvent .= "DESCRIPTION:$description\r\n";
        }
        if (!empty($location)) {
            $icalEvent .= "LOCATION:$location\r\n";
        }

        // Optional: Add RRULE if event is recurring
        if ($event->getValue('rrule')) {
            $icalEvent .= "RRULE:" . $event->getValue('rrule') . "\r\n";
        }

        // Optional: Add EXDATE if event has exceptions
        if ($event->getValue('exdate')) {
            $exdates = self::formatICalExDates($event->getValue('exdate'), $event->getValue('all_day'));
            $icalEvent .= "EXDATE:$exdates\r\n";
        }

        $icalEvent .= "END:VEVENT\r\n";

        return $icalEvent;
    }

    /**
     * Format a date-time string into iCal format.
     *
     * @param string $dateTime The date-time string
     * @param bool $allDay Whether the event is an all-day event
     * @return string The formatted iCal date-time string
     */
    private static function formatICalDateTime(string $dateTime, bool $allDay = false): string
    {
        $dt = new DateTime($dateTime);
        return $allDay ? $dt->format('Ymd') : $dt->format('Ymd\THis\Z');
    }

    /**
     * Format exceptions dates into iCal format.
     *
     * @param string $exdateString The exdate string
     * @param bool $allDay Whether the event is an all-day event
     * @return string The formatted iCal exdates
     */
    private static function formatICalExDates(string $exdateString, bool $allDay = false): string
    {
        $exdates = array_map('trim', explode(',', $exdateString));
        $formattedExDates = array_map(function ($exdate) use ($allDay) {
            return self::formatICalDateTime($exdate, $allDay);
        }, $exdates);

        return implode(',', $formattedExDates);
    }

    /**
     * Escape special characters in a string for iCal.
     *
     * @param string $string The string to escape
     * @return string The escaped string
     */
    private static function escapeString(string $string): string
    {
        return preg_replace('/([\,;])/','\\\$1', $string);
    }
}
