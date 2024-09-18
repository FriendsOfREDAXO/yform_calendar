<?php

namespace klxm\YFormCalendar;

use DateTime;
use DateTimeZone;

class ICalExporter
{
    // Generiert und sendet die iCal-Datei an den Browser
    public static function generateICalFile(string $filename, array $events): void
    {
        $icalData = self::generateICal($events);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.ics"');

        echo $icalData;
    }

    // Generiert den gesamten iCal-Inhalt aus Events
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

    // Generiert ein einzelnes Event
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

    // Formatiert Datum und Uhrzeit für iCal
    private static function formatICalDateTime(string $dateTime, bool $allDay = false): string
    {
        $dt = new DateTime($dateTime);
        if ($allDay) {
            // Ganztägige Events nur als Ymd formatieren (ohne Uhrzeit)
            return $dt->format('Ymd');
        } else {
            // Events mit Uhrzeit in UTC (Z) formatieren
            $dt->setTimezone(new DateTimeZone('UTC'));
            return $dt->format('Ymd\THis\Z');
        }
    }

    // Formatiert EXDATE-Werte korrekt
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

    // Hilfsfunktion zum Escapen von Strings (z.B. für Kommas)
    private static function escapeString(string $string): string
    {
        return preg_replace('/([\,;])/','\\\$1', $string);
    }

    // Generiert eine eindeutige UID für jedes Event
    private static function generateUID($event): string
    {
        return uniqid($event->getId() . '@yourdomain.com', true);
    }

    // Faltet eine Zeile, wenn sie länger als 75 Zeichen ist, wie es von RFC 5545 verlangt wird
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
