<?php
class rex_yform_value_rrule extends rex_yform_value_abstract
{
    function enterObject()
    {
        $this->setValue($this->getValue());

        if ($this->getValue() == '' && !$this->params['send']) {
            $this->setValue($this->getElement('default'));
        }

        if ($this->needsOutput()) {
            $this->params['form_output'][$this->getId()] = $this->parse('value.rrule.tpl.php');
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }

    function getDescription():string
    {
        return 'rrule|name|label|';
    }

    function getDefinitions():array
    {
        return [
            'type' => 'value',
            'name' => 'rrule',
            'values' => [
                'name'    => ['type' => 'name',   'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label'   => ['type' => 'text',   'label' => rex_i18n::msg('yform_values_defaults_label')],
                'default' => ['type' => 'text',   'label' => rex_i18n::msg('yform_values_rrule_default')],
            ],
            'description' => rex_i18n::msg('yform_values_rrule_description'),
            'dbtype' => 'text'
        ];
    }

    public static function getListValue($params)
    {
        $value = (string) $params['subject'];
        
        if (empty($value)) {
            return '<span><em>-- Keine Wiederholung --</em></span>';
        }

        // Extrahiere EXDATE aus dem RRule-Wert
        $exdateValue = self::extractExdateFromRRule($value);

        $output = self::formatRRule($value);
        $modalId = 'rrule-modal-' . uniqid();
        $buttonId = 'btn-' . uniqid();

        // Button mit Modal
        $html = '<span>';
        $html .= $output;
        $html .= ' <button type="button" class="btn btn-xs btn-default btn-show-occurrences-list" id="' . $buttonId . '" data-rrule="' . rex_escape($value) . '" data-exdate="' . rex_escape($exdateValue) . '" data-toggle="modal" data-target="#' . $modalId . '" style="margin-left: 8px;">';
        $html .= '<i class="fa fa-calendar"></i> Termine';
        $html .= '</button>';
        
        // Modal
        $html .= '<div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog">';
        $html .= '<div class="modal-dialog modal-lg" role="document">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header">';
        $html .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
        $html .= '<h4 class="modal-title"><i class="fa fa-calendar"></i> Nächste Termine</h4>';
        $html .= '</div>';
        $html .= '<div class="modal-body">';
        $html .= '<div class="rrule-occurrences-list-' . $buttonId . '" style="min-height: 50px;">';
        $html .= '<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Wird geladen...</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</span>';

        // Im Debug-Mode zusätzlich RRule anzeigen
        if (rex::isDebugMode()) {
            $html .= '<br><details style="margin-top:5px;"><summary style="cursor:pointer; font-size:11px;">RRule (Debug)</summary>';
            $html .= '<pre style="background:#f5f5f5; padding:10px; border-radius:3px; font-size:11px; overflow:auto; max-height:200px; margin-top:5px;">';
            $html .= rex_escape($value);
            if ($exdateValue) {
                $html .= "\nEXDATE: " . rex_escape($exdateValue);
            }
            $html .= '</pre></details>';
        }

        return $html;
    }

    private static function extractExdateFromRRule($rruleString)
    {
        $parts = explode(';', $rruleString);
        foreach ($parts as $part) {
            if (strpos($part, 'EXDATE=') === 0) {
                return substr($part, 7); // Länge von 'EXDATE='
            }
        }
        return '';
    }

    private static function formatRRule($rruleString)
    {
        $parts = explode(';', $rruleString);
        $rule = [];
        
        foreach ($parts as $part) {
            $kv = explode('=', $part);
            if (count($kv) === 2) {
                $rule[trim($kv[0])] = trim($kv[1]);
            }
        }

        if (!isset($rule['FREQ'])) {
            return '<em>-- Ungültige RRule --</em>';
        }

        $output = '';
        $interval = isset($rule['INTERVAL']) ? (int)$rule['INTERVAL'] : 1;
        $freq = $rule['FREQ'];

        // Häufigkeit formatieren
        $freqLabels = [
            'DAILY' => 'Täglich',
            'WEEKLY' => 'Wöchentlich',
            'MONTHLY' => 'Monatlich',
            'YEARLY' => 'Jährlich',
        ];

        $freqLabel = $freqLabels[$freq] ?? $freq;
        
        if ($interval > 1) {
            $output .= 'Alle ' . $interval . ' ';
            $intervalLabels = [
                'DAILY' => 'Tage',
                'WEEKLY' => 'Wochen',
                'MONTHLY' => 'Monate',
                'YEARLY' => 'Jahre',
            ];
            $output .= ($intervalLabels[$freq] ?? strtolower($freq));
        } else {
            $output .= $freqLabel;
        }

        // Wochentage bei WEEKLY
        if ($freq === 'WEEKLY' && isset($rule['BYDAY'])) {
            $days = explode(',', $rule['BYDAY']);
            $dayNames = [
                'MO' => 'Mo', 'TU' => 'Di', 'WE' => 'Mi', 'TH' => 'Do',
                'FR' => 'Fr', 'SA' => 'Sa', 'SU' => 'So'
            ];
            $output .= ' auf ' . implode(', ', array_map(function ($d) use ($dayNames) {
                return $dayNames[trim($d)] ?? $d;
            }, $days));
        }

        // Endbedingung
        if (isset($rule['COUNT'])) {
            $output .= ', ' . $rule['COUNT'] . 'x';
        } elseif (isset($rule['UNTIL'])) {
            $untilStr = $rule['UNTIL'];
            // Format: 20260115T000000Z -> 15.01.2026
            if (strlen($untilStr) >= 8) {
                $year = substr($untilStr, 0, 4);
                $month = substr($untilStr, 4, 2);
                $day = substr($untilStr, 6, 2);
                $output .= ' bis ' . $day . '.' . $month . '.' . $year;
            }
        }

        // EXDATE Info
        if (isset($rule['EXDATE'])) {
            $exdates = explode(',', $rule['EXDATE']);
            $count = count($exdates);
            $output .= ' (' . $count . ' ausgeschlossen)';
        }

        return '<strong>' . $output . '</strong>';
    }
}
