<?php

use rex;
use rex_api_function;
use rex_api_result;
use rex_request;
use rex_response;
use RRule\RRule;

/**
 * API Handler für RRule Widget
 * 
 * Stellt Endpunkte für AJAX-Requests bereit:
 * - get_next_occurrences: Berechnet die nächsten Termine aus einer RRule
 * 
 * Aufruf: /redaxo/index.php?rex-api-call=rrule&action=get_next_occurrences
 */
class rex_api_rrule extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        // Nur im Backend und für eingeloggte Benutzer
        if (!rex::isBackend() || !rex::getUser()) {
            return new rex_api_result(false, 'Zugriff verweigert');
        }

        rex_response::cleanOutputBuffers();

        $action = rex_request::request('action', 'string', '');

        switch ($action) {
            case 'get_next_occurrences':
                $this->getNextOccurrences();
                break;

            default:
                rex_response::sendJson(['error' => 'Unbekannte Aktion: ' . $action]);
                exit;
        }

        exit;
    }

    /**
     * Berechnet die nächsten Termine aus einer RRule
     */
    protected function getNextOccurrences(): void
    {
        $rruleString = rex_request::post('rrule', 'string', '');
        $exdateString = rex_request::post('exdate', 'string', ''); // Optional: separates EXDATE-Feld (Fallback)
        $startDate = rex_request::post('start_date', 'string', date('Y-m-d'));
        $limit = min((int)rex_request::post('limit', 'int', 10), 20); // Max 20

        if (empty($rruleString)) {
            rex_response::sendJson(['error' => 'RRule-String erforderlich']);
            return;
        }

        try {
            $occurrences = $this->calculateOccurrences($rruleString, $exdateString, $startDate, $limit);
            rex_response::sendJson(['success' => true, 'occurrences' => $occurrences]);
        } catch (Exception $e) {
            rex_response::sendJson(['error' => $e->getMessage()]);
        }
    }

    /**
     * Berechnet die nächsten Termine mit php-rrule Library
     */
    protected function calculateOccurrences(string $rruleString, string $exdateString, string $startDate, int $limit): array
    {
        try {
            // Erstelle Start-Datum
            $dtStart = new DateTime($startDate);
            
            // Extrahiere EXDATE aus RRule-String wenn vorhanden
            $extractedExdate = $this->extractExdateFromRRule($rruleString);
            if (!empty($extractedExdate) && empty($exdateString)) {
                $exdateString = $extractedExdate;
            }
            
            // Entferne EXDATE aus RRule-String für die Verarbeitung
            $rruleForProcessing = $this->removeExdateFromRRule($rruleString);
            
            // Parse EXDATE String (z.B. "2026-01-20,2026-01-27")
            $exdates = [];
            if (!empty($exdateString)) {
                $dates = explode(',', $exdateString);
                foreach ($dates as $date) {
                    $date = trim($date);
                    try {
                        $exdates[] = new DateTime($date);
                    } catch (Exception $e) {
                        // Ignoriere ungültige Daten
                    }
                }
            }
            
            // Parse RRule String
            $rrule = RRule::createFromRfcString('RRULE:' . $rruleForProcessing, false, $dtStart);
            
            // Berechne Vorkommen
            $occurrences = [];
            $count = 0;
            $iterations = 0;
            $maxIterations = 1000; // Sicherheit vor Endlosschleife
            
            foreach ($rrule as $date) {
                $iterations++;
                if ($iterations > $maxIterations || $count >= $limit) {
                    break;
                }
                
                // Prüfe ob Termin ausgeschlossen ist
                $isExcluded = false;
                $dateFormatted = $date->format('Y-m-d');
                
                foreach ($exdates as $exdate) {
                    if ($dateFormatted === $exdate->format('Y-m-d')) {
                        $isExcluded = true;
                        break;
                    }
                }
                
                if (!$isExcluded) {
                    $occurrences[] = [
                        'date' => $dateFormatted,
                        'formatted' => $this->formatDate($date),
                    ];
                    $count++;
                }
            }
            
            return $occurrences;
        } catch (Exception $e) {
            throw new Exception('RRule konnte nicht geparst werden: ' . $e->getMessage());
        }
    }

    /**
     * Parst ein RRule-Datum (kann verschiedene Formate haben)
     */
    protected function parseRRuleDate(string $dateStr): string
    {
        $dateStr = trim($dateStr);
        
        // Format: 20260115T000000Z -> 2026-01-15
        if (strlen($dateStr) === 16 && strpos($dateStr, 'T') !== false) {
            $year = substr($dateStr, 0, 4);
            $month = substr($dateStr, 4, 2);
            $day = substr($dateStr, 6, 2);
            return $year . '-' . $month . '-' . $day;
        }
        
        // Format: 20260115 -> 2026-01-15
        if (strlen($dateStr) === 8 && is_numeric($dateStr)) {
            $year = substr($dateStr, 0, 4);
            $month = substr($dateStr, 4, 2);
            $day = substr($dateStr, 6, 2);
            return $year . '-' . $month . '-' . $day;
        }
        
        return $dateStr;
    }

    /**
     * Formatiert ein Datum für die Ausgabe
     */
    protected function formatDate(DateTime $date): string
    {
        $germanMonths = [
            'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
            'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'
        ];
        
        $germanDays = [
            'Sonntag', 'Montag', 'Dienstag', 'Mittwoch',
            'Donnerstag', 'Freitag', 'Samstag'
        ];
        
        $dayOfWeek = $germanDays[(int)$date->format('w')];
        $day = (int)$date->format('d');
        $month = $germanMonths[(int)$date->format('m') - 1];
        $year = $date->format('Y');
        
        return $dayOfWeek . ', ' . $day . '. ' . $month . ' ' . $year;
    }

    /**
     * Extrahiert EXDATE aus einem RRule-String
     * z.B. "FREQ=DAILY;EXDATE=2026-01-20,2026-01-27" -> "2026-01-20,2026-01-27"
     */
    protected function extractExdateFromRRule(string $rruleString): string
    {
        $parts = explode(';', $rruleString);
        foreach ($parts as $part) {
            if (strpos($part, 'EXDATE=') === 0) {
                return substr($part, 7); // Länge von 'EXDATE='
            }
        }
        return '';
    }

    /**
     * Entfernt EXDATE aus einem RRule-String
     * z.B. "FREQ=DAILY;EXDATE=2026-01-20" -> "FREQ=DAILY"
     */
    protected function removeExdateFromRRule(string $rruleString): string
    {
        $parts = explode(';', $rruleString);
        $filtered = [];
        foreach ($parts as $part) {
            if (strpos($part, 'EXDATE=') !== 0) {
                $filtered[] = $part;
            }
        }
        return implode(';', $filtered);
    }
}
