<?php

namespace klxm\YFormCalendar;

use DateTime;

class ICalExporter
{
    public static function generateICalFile(string $filename, array $events): void
    {
        $icalData = self::generateICal($events);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.ics"');

        echo $icalData;
    }

    public static function generateICal(array $events): string
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Your Organization//Your Product//EN\r\n";

        $processedEvents = [];

        foreach ($events as $event) {
            $eventId = $event->getId();
            $rrule = $event->getValue('rrule');

            if (!isset($processedEvents[$eventId])) {
                $ical .= self::generateEvent($event, $rrule);
                $processedEvents[$eventId] = true;
            }
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    private static function generateEvent($event, $rrule): string
    {
        $dtStart = self::formatICalDateTime($event->getValue('dtstart'), $event->getValue('all_day'));
        $dtEnd = self::formatICalDateTime($event->getValue('dtend'), $event->getValue('all_day'));
        $summary = self::escapeString($event->getValue('summary'));
        $description = self::escapeString($event->getValue('description'));
        $location = self::escapeString($event->getValue('location'));
        $uid = self::generateUID($event);  // Eindeutige UID generieren

        $icalEvent = "BEGIN:VEVENT\r\n";
        $icalEvent .= self::foldICalLine("UID:$uid\r\n");
        $icalEvent .= self::foldICalLine("DTSTAMP:" . self::formatICalDateTime((new DateTime())->format('Y-m-d H:i:s')) . "\r\n");
        $icalEvent .= self::foldICalLine("DTSTART:$dtStart\r\n");
        $icalEvent .= self::foldICalLine("DTEND:$dtEnd\r\n");
        $icalEvent .= self::foldICalLine("SUMMARY:$summary\r\n");
        if (!empty($description)) {
            $icalEvent .= self::foldICalLine("DESCRIPTION:$description\r\n");
        }
        if (!empty($location)) {
            $icalEvent .= self::foldICalLine("LOCATION:$location\r\n");
        }

        if ($rrule) {
            $icalEvent .= self::foldICalLine("RRULE:$rrule\r\n");
        }

        if ($event->getValue('exdate')) {
            $icalEvent .= self::formatICalExDates($event->getValue('exdate'), $event->getValue('all_day'));
        }

        $icalEvent .= "END:VEVENT\r\n";

        return $icalEvent;
    }

    private static function formatICalDateTime(string $dateTime, bool $allDay = false): string
    {
        $dt = new DateTime($dateTime);
        return $allDay ? $dt->format('Ymd') : $dt->format('Ymd\THis\Z');
    }

    private static function formatICalExDates(string $exdateString, bool $allDay = false): string
    {
        $exdates = array_map('trim', explode(',', $exdateString));
        $formattedExDates = array_map(function ($exdate) use ($allDay) {
            return self::formatICalDateTime($exdate, $allDay);
        }, $exdates);

        $exdateLines = '';
        foreach ($formattedExDates as $exdate) {
            $exdateLines .= "EXDATE:$exdate\r\n";
        }

        return $exdateLines;
    }

    private static function escapeString(string $string): string
    {
        return preg_replace('/([\,;])/','\\\$1', $string);
    }

    private static function generateUID($event): string
    {
        return uniqid($event->getId() . '@yourdomain.com', true);
    }

    private static function foldICalLine(string $line): string
    {
        $output = '';
        while (strlen($line) > 75) {
            $output .= substr($line, 0, 75) . "\r\n ";
            $line = substr($line, 75);
        }
        $output .= $line;
        return $output;
    }
}
