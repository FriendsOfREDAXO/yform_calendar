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
        $ical .= "CALSCALE:GREGORIAN\r\n";

        foreach ($events as $event) {
            $ical .= self::generateEvent($event);
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    private static function generateEvent($event): string
    {
        $allDay = $event->getValue('all_day');
        $dtStart = self::formatICalDateTime($event->getValue('dtstart'), $allDay);
        $dtEnd = self::formatICalDateTime($event->getValue('dtend'), $allDay);
        $summary = self::escapeString($event->getValue('summary'));
        $description = self::escapeString($event->getValue('description'));
        $location = self::escapeString($event->getValue('location'));
        $uid = $event->getId();
        $rrule = $event->getValue('rrule');

        $icalEvent = "BEGIN:VEVENT\r\n";
        $icalEvent .= "UID:$uid\r\n";
        $icalEvent .= "DTSTAMP:" . self::formatICalDateTime(new DateTime(), false) . "\r\n";
        $icalEvent .= "DTSTART" . ($allDay ? ";VALUE=DATE" : "") . ":$dtStart\r\n";
        $icalEvent .= "DTEND" . ($allDay ? ";VALUE=DATE" : "") . ":$dtEnd\r\n";
        $icalEvent .= "SUMMARY:$summary\r\n";
        $icalEvent .= "DESCRIPTION:$description\r\n";
        $icalEvent .= "LOCATION:$location\r\n";
        $icalEvent .= "LAST-MODIFIED:" . self::formatICalDateTime(new DateTime(), false) . "\r\n";
        $icalEvent .= "SEQUENCE:0\r\n";
        $icalEvent .= "TRANSP:OPAQUE\r\n";

        if ($rrule) {
            $icalEvent .= "RRULE:" . self::formatRRule($rrule) . "\r\n";
            if ($event->getValue('exdate')) {
                $exdates = self::formatICalExDates($event->getValue('exdate'), $allDay);
                foreach ($exdates as $exdate) {
                    $icalEvent .= "EXDATE" . ($allDay ? ";VALUE=DATE" : "") . ":$exdate\r\n";
                }
            }
        }

        $icalEvent .= self::generateAlarm($allDay);

        $icalEvent .= "END:VEVENT\r\n";

        return $icalEvent;
    }

    private static function formatICalDateTime($dateTime, bool $allDay = false): string
    {
        $dt = $dateTime instanceof DateTime ? $dateTime : new DateTime($dateTime);
        return $allDay ? $dt->format('Ymd') : $dt->format('Ymd\THis\Z');
    }

    private static function formatICalExDates(string $exdateString, bool $allDay = false): array
    {
        $exdates = array_map('trim', explode(',', $exdateString));
        return array_map(function ($exdate) use ($allDay) {
            return self::formatICalDateTime($exdate, $allDay);
        }, $exdates);
    }

    private static function escapeString(string $string): string
    {
        return preg_replace('/([\,;])/','\\\$1', $string);
    }

    private static function formatRRule(string $rrule): string
    {
        // Vereinfache RRULE für tägliche Wiederholungen
        if (strpos($rrule, 'FREQ=DAILY;INTERVAL=1') !== false) {
            return 'FREQ=DAILY';
        }
        return $rrule;
    }

    private static function generateAlarm(bool $allDay): string
    {
        $trigger = $allDay ? '-PT15H' : '-PT15M';
        return "BEGIN:VALARM\r\n" .
               "ACTION:DISPLAY\r\n" .
               "DESCRIPTION:Erinnerung\r\n" .
               "TRIGGER:$trigger\r\n" .
               "END:VALARM\r\n";
    }
}
