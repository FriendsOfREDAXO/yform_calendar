<?php
$name = $this->getFieldName();
$value = $this->getValue();
$id = $this->getFieldId();
?>
<div class="rrule-widget">
<div class="form-group rrule-wrapper">
    <label class="toggle-switch">
        <input type="checkbox" class="rrule-checkbox">
        <span class="slider"></span>
    </label>

    <span class="toggle-label"><i class="fas fa-redo icon"></i><?= $this->getLabel() ?></span>

    <div class="rrule-widget-content hidden">
        <div class="form-group">
            <label for="rrule-frequency"><i class="fas fa-clock icon"></i>Häufigkeit:</label>
            <select class="rrule-frequency form-control selectpicker">
                <option value="DAILY">Täglich</option>
                <option value="WEEKLY">Wöchentlich</option>
                <option value="MONTHLY">Monatlich</option>
                <option value="YEARLY">Jährlich</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="rrule-interval"><i class="fas fa-step-forward icon"></i>Intervall:</label>
            <input type="number" class="rrule-interval form-control" min="1" value="1">
        </div>
        
        <div class="rrule-weekly-group form-group hidden">
            <label><i class="fas fa-calendar-week icon"></i>Wochentage:</label>
            <div class="toggle-group">
                <?php foreach (['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'] as $day): ?>
                <div>
                    <label class="toggle-switch">
                        <input type="checkbox" class="rrule-weekday" data-day="<?= $day ?>">
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label"><?= substr($day, 0, 2) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="rrule-monthly-group form-group hidden">
            <label><i class="fas fa-calendar-alt icon"></i>Monatlicher Typ:</label>
            <div class="radio-group">
                <label><input type="radio" class="rrule-monthlyType" name="rrule-monthlyType" value="bymonthday">Tag des Monats</label>
                <label><input type="radio" class="rrule-monthlyType" name="rrule-monthlyType" value="byday">Tag der Woche</label>
            </div>
        </div>
        
        <div class="rrule-bymonthday-group form-group hidden">
            <label for="rrule-monthday"><i class="fas fa-calendar-day icon"></i>Tag des Monats:</label>
            <input type="number" class="rrule-monthday form-control" min="1" max="31" value="1">
        </div>
        
        <div class="rrule-byday-group form-group hidden">
            <label><i class="fas fa-calendar-check icon"></i>Wochentag im Monat:</label>
            <div class="row">
                <div class="col-md-6">
                    <select class="rrule-weekdayorder form-control selectpicker">
                        <option value="1">Erster</option>
                        <option value="2">Zweiter</option>
                        <option value="3">Dritter</option>
                        <option value="4">Vierter</option>
                        <option value="-1">Letzter</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <select class="rrule-weekday form-control selectpicker">
                        <option value="MO">Montag</option>
                        <option value="TU">Dienstag</option>
                        <option value="WE">Mittwoch</option>
                        <option value="TH">Donnerstag</option>
                        <option value="FR">Freitag</option>
                        <option value="SA">Samstag</option>
                        <option value="SU">Sonntag</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="rrule-end-options form-group">
            <label><i class="fas fa-calendar-times icon"></i>Ende der Wiederholung:</label>
            <select class="rrule-end-type form-control selectpicker">
                <option value="never">Nie</option>
                <option value="count">Nach Anzahl der Ereignisse</option>
                <option value="until">An einem bestimmten Datum</option>
            </select>
            
            <div class="rrule-count-group form-group hidden">
                <label for="rrule-count">Anzahl der Ereignisse:</label>
                <div class="row">
                    <div class="col-xs-12">
                        <input type="number" class="rrule-count form-control" min="1" value="1">
                    </div>
                </div>
            </div>
            
            <div class="rrule-until-group form-group hidden">
                <label for="rrule-until">Enddatum:</label>
                <div class="row">
                    <div class="col-xs-12">
                        <input type="date" class="rrule-until form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <input type="hidden" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>" class="rrule-value">
    <div class="rrule-display hidden"></div>
</div>
</div>
<script>
(function() {
    function initRRuleWidgets() {
        document.querySelectorAll('.rrule-widget').forEach(widget => {
            const elements = {
                recurringEventCheckbox: widget.querySelector('.rrule-checkbox'),
                rruleWidget: widget.querySelector('.rrule-widget-content'),
                rruleDisplay: widget.querySelector('.rrule-display'),
                frequency: widget.querySelector('.rrule-frequency'),
                interval: widget.querySelector('.rrule-interval'),
                weeklyGroup: widget.querySelector('.rrule-weekly-group'),
                monthlyGroup: widget.querySelector('.rrule-monthly-group'),
                bymonthdayGroup: widget.querySelector('.rrule-bymonthday-group'),
                bydayGroup: widget.querySelector('.rrule-byday-group'),
                rruleValue: widget.querySelector('.rrule-value'),
                endType: widget.querySelector('.rrule-end-type'),
                countGroup: widget.querySelector('.rrule-count-group'),
                untilGroup: widget.querySelector('.rrule-until-group'),
                count: widget.querySelector('.rrule-count'),
                until: widget.querySelector('.rrule-until')
            };

            function toggleVisibility(element, show) {
                if (element) {
                    element.classList.toggle('hidden', !show);
                }
            }

            function updateVisibility() {
                const frequency = elements.frequency.value;
                toggleVisibility(elements.weeklyGroup, frequency === 'WEEKLY');
                toggleVisibility(elements.monthlyGroup, frequency === 'MONTHLY');
                
                if (frequency === 'MONTHLY') {
                    const monthlyType = widget.querySelector('input[name="rrule-monthlyType"]:checked');
                    toggleVisibility(elements.bymonthdayGroup, monthlyType && monthlyType.value === 'bymonthday');
                    toggleVisibility(elements.bydayGroup, monthlyType && monthlyType.value === 'byday');
                } else {
                    toggleVisibility(elements.bymonthdayGroup, false);
                    toggleVisibility(elements.bydayGroup, false);
                }

                toggleVisibility(elements.countGroup, elements.endType.value === 'count');
                toggleVisibility(elements.untilGroup, elements.endType.value === 'until');
            }

            function updateRRule() {
                if (!elements.recurringEventCheckbox.checked) {
                    elements.rruleValue.value = '';
                    elements.rruleDisplay.textContent = '';
                    return;
                }

                let rrule = `FREQ=${elements.frequency.value};INTERVAL=${elements.interval.value}`;
                
                if (elements.frequency.value === 'WEEKLY') {
                    const weekdays = Array.from(widget.querySelectorAll('.rrule-weekday:checked'))
                        .map(cb => cb.dataset.day)
                        .join(',');
                    if (weekdays) rrule += `;BYDAY=${weekdays}`;
                } else if (elements.frequency.value === 'MONTHLY') {
                    const monthlyType = widget.querySelector('input[name="rrule-monthlyType"]:checked');
                    if (monthlyType) {
                        if (monthlyType.value === 'bymonthday') {
                            rrule += `;BYMONTHDAY=${widget.querySelector('.rrule-monthday').value}`;
                        } else if (monthlyType.value === 'byday') {
                            rrule += `;BYDAY=${widget.querySelector('.rrule-weekdayorder').value}${widget.querySelector('.rrule-weekday').value}`;
                        }
                    }
                }
                
                if (elements.endType.value === 'count') {
                    rrule += `;COUNT=${elements.count.value}`;
                } else if (elements.endType.value === 'until') {
                    const untilDate = new Date(elements.until.value);
                    const formattedDate = untilDate.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
                    rrule += `;UNTIL=${formattedDate}`;
                }

                elements.rruleValue.value = rrule;
                elements.rruleDisplay.textContent = rrule;
            }

            function parseRRule(rruleString) {
                const parts = rruleString.split(';');
                const rrule = {};
                parts.forEach(part => {
                    const [key, value] = part.split('=');
                    rrule[key] = value;
                });
                return rrule;
            }

            function setInitialValues() {
                const initialValue = elements.rruleValue.value;
                if (initialValue) {
                    elements.recurringEventCheckbox.checked = true;
                    toggleVisibility(elements.rruleWidget, true);
                    toggleVisibility(elements.rruleDisplay, true);

                    const rrule = parseRRule(initialValue);
                    
                    elements.frequency.value = rrule.FREQ || 'DAILY';
                    elements.interval.value = rrule.INTERVAL || '1';

                    if (rrule.BYDAY) {
                        if (rrule.FREQ === 'WEEKLY') {
                            rrule.BYDAY.split(',').forEach(day => {
                                const checkbox = widget.querySelector(`.rrule-weekday[data-day="${day}"]`);
                                if (checkbox) checkbox.checked = true;
                            });
                        } else if (rrule.FREQ === 'MONTHLY') {
                            const monthlyTypeRadio = widget.querySelector('.rrule-monthlyType[value="byday"]');
                            if (monthlyTypeRadio) monthlyTypeRadio.checked = true;
                            const match = rrule.BYDAY.match(/(-?\d+)([A-Z]+)/);
                            if (match) {
                                const weekdayorderSelect = widget.querySelector('.rrule-weekdayorder');
                                const weekdaySelect = widget.querySelector('.rrule-weekday');
                                if (weekdayorderSelect) weekdayorderSelect.value = match[1];
                                if (weekdaySelect) weekdaySelect.value = match[2];
                            }
                        }
                    }

                    if (rrule.BYMONTHDAY) {
                        const monthlyTypeRadio = widget.querySelector('.rrule-monthlyType[value="bymonthday"]');
                        if (monthlyTypeRadio) monthlyTypeRadio.checked = true;
                        const monthdayInput = widget.querySelector('.rrule-monthday');
                        if (monthdayInput) monthdayInput.value = rrule.BYMONTHDAY;
                    }

                    if (rrule.COUNT) {
                        elements.endType.value = 'count';
                        elements.count.value = rrule.COUNT;
                    } else if (rrule.UNTIL) {
                        elements.endType.value = 'until';
                        const untilDate = new Date(rrule.UNTIL.slice(0, 4) + '-' + rrule.UNTIL.slice(4, 6) + '-' + rrule.UNTIL.slice(6, 8));
                        elements.until.value = untilDate.toISOString().split('T')[0];
                    } else {
                        elements.endType.value = 'never';
                    }

                    updateVisibility();
                    elements.rruleDisplay.textContent = initialValue;
                }
            }

            elements.recurringEventCheckbox.addEventListener('change', function() {
                toggleVisibility(elements.rruleWidget, this.checked);
                toggleVisibility(elements.rruleDisplay, this.checked);
                updateRRule();
            });

            elements.frequency.addEventListener('change', () => {
                updateVisibility();
                updateRRule();
            });

            widget.querySelectorAll('.rrule-monthlyType').forEach(radio => {
                radio.addEventListener('change', () => {
                    updateVisibility();
                    updateRRule();
                });
            });

            elements.endType.addEventListener('change', () => {
                updateVisibility();
                updateRRule();
            });

            widget.addEventListener('change', updateRRule);

            setInitialValues();
            updateVisibility();
            updateRRule();
        });
    }

    function onReady(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
        if (typeof jQuery !== 'undefined') {
            jQuery(document).on('rex:ready', fn);
        }
    }

    onReady(initRRuleWidgets);
})();
</script>
