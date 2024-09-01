<?php
$name = $this->getFieldName();
$value = $this->getValue();
$id = $this->getFieldId();
?>

<div class="form-group" id="<?= $id ?>-wrapper">
    <label class="toggle-switch">
        <input type="checkbox" id="<?= $id ?>-checkbox">
        <span class="slider"></span>
    </label>

    <span class="toggle-label"><i class="fas fa-redo icon"></i><?= $this->getLabel() ?></span>

    <div id="<?= $id ?>-widget" class="hidden">
        <div id="rrule-widget">
            <div class="form-group">
                <label for="<?= $id ?>-frequency"><i class="fas fa-clock icon"></i>Häufigkeit:</label>
                <select id="<?= $id ?>-frequency">
                    <option value="DAILY">Täglich</option>
                    <option value="WEEKLY">Wöchentlich</option>
                    <option value="MONTHLY">Monatlich</option>
                    <option value="YEARLY">Jährlich</option>
                </select>
            </div>

            <div class="form-group">
                <label for="<?= $id ?>-interval"><i class="fas fa-step-forward icon"></i>Intervall:</label>
                <input type="number" id="<?= $id ?>-interval" min="1" value="1">
            </div>

            <div id="<?= $id ?>-weekly-group" class="form-group hidden">
                <label><i class="fas fa-calendar-week icon"></i>Wochentage:</label>
                <div class="toggle-group">
                    <?php foreach (['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'] as $day): ?>
                        <div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="<?= $id ?>-<?= $day ?>">
                                <span class="slider"></span>
                            </label>
                            <span class="toggle-label"><?= substr($day, 0, 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="<?= $id ?>-monthly-group" class="form-group hidden">
                <label><i class="fas fa-calendar-alt icon"></i>Monatlicher Typ:</label>
                <div class="radio-group">
                    <label><input type="radio" id="<?= $id ?>-bymonthday" name="<?= $id ?>-monthlyType" value="bymonthday">Tag des Monats</label>
                    <label><input type="radio" id="<?= $id ?>-byday" name="<?= $id ?>-monthlyType" value="byday">Tag der Woche</label>
                </div>
            </div>

            <div id="<?= $id ?>-bymonthday-group" class="form-group hidden">
                <label for="<?= $id ?>-monthday"><i class="fas fa-calendar-day icon"></i>Tag des Monats:</label>
                <input type="number" id="<?= $id ?>-monthday" min="1" max="31" value="1">
            </div>

            <div id="<?= $id ?>-byday-group" class="form-group hidden">
                <label><i class="fas fa-calendar-check icon"></i>Wochentag im Monat:</label>
                <select id="<?= $id ?>-weekdayorder">
                    <option value="1">Erster</option>
                    <option value="2">Zweiter</option>
                    <option value="3">Dritter</option>
                    <option value="4">Vierter</option>
                    <option value="-1">Letzter</option>
                </select>
                <select id="<?= $id ?>-weekday">
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

        <input type="hidden" id="<?= $id ?>" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>">
        <div id="<?= $id ?>-display" class="hidden"></div>
    </div>
</div>
<script>
    (function() {
        function initRRuleWidget() {
            const id = '<?= $id ?>';
            const elements = {
                recurringEventCheckbox: document.getElementById(id + '-checkbox'),
                rruleWidget: document.getElementById(id + '-widget'),
                rruleDisplay: document.getElementById(id + '-display'),
                frequency: document.getElementById(id + '-frequency'),
                interval: document.getElementById(id + '-interval'),
                weeklyGroup: document.getElementById(id + '-weekly-group'),
                monthlyGroup: document.getElementById(id + '-monthly-group'),
                bymonthdayGroup: document.getElementById(id + '-bymonthday-group'),
                bydayGroup: document.getElementById(id + '-byday-group'),
                rruleValue: document.getElementById(id)
            };

            function toggleVisibility(element, show) {
                element.classList.toggle('hidden', !show);
            }

            function updateVisibility() {
                const frequency = elements.frequency.value;
                toggleVisibility(elements.weeklyGroup, frequency === 'WEEKLY');
                toggleVisibility(elements.monthlyGroup, frequency === 'MONTHLY');

                if (frequency === 'MONTHLY') {
                    const monthlyType = document.querySelector('input[name="' + id + '-monthlyType"]:checked');
                    toggleVisibility(elements.bymonthdayGroup, monthlyType && monthlyType.value === 'bymonthday');
                    toggleVisibility(elements.bydayGroup, monthlyType && monthlyType.value === 'byday');
                } else {
                    toggleVisibility(elements.bymonthdayGroup, false);
                    toggleVisibility(elements.bydayGroup, false);
                }
            }

            function updateRRule() {
                if (!elements.recurringEventCheckbox.checked) {
                    elements.rruleValue.value = '';
                    elements.rruleDisplay.textContent = '';
                    return;
                }

                let rrule = `FREQ=${elements.frequency.value};INTERVAL=${elements.interval.value}`;

                if (elements.frequency.value === 'WEEKLY') {
                    const weekdays = Array.from(document.querySelectorAll('#' + id + '-weekly-group input[type="checkbox"]:checked'))
                        .map(cb => cb.id.split('-').pop())
                        .join(',');
                    if (weekdays) rrule += `;BYDAY=${weekdays}`;
                } else if (elements.frequency.value === 'MONTHLY') {
                    const monthlyType = document.querySelector('input[name="' + id + '-monthlyType"]:checked');
                    if (monthlyType) {
                        if (monthlyType.value === 'bymonthday') {
                            rrule += `;BYMONTHDAY=${document.getElementById(id + '-monthday').value}`;
                        } else if (monthlyType.value === 'byday') {
                            rrule += `;BYDAY=${document.getElementById(id + '-weekdayorder').value}${document.getElementById(id + '-weekday').value}`;
                        }
                    }
                }

                elements.rruleValue.value = rrule;
                elements.rruleDisplay.textContent = rrule;
            }

            elements.recurringEventCheckbox.addEventListener('change', function() {
                toggleVisibility(elements.rruleWidget, this.checked);
                toggleVisibility(elements.rruleDisplay, this.checked);
                updateRRule();
            });

            elements.frequency.addEventListener('change', updateVisibility);
            document.getElementById(id + '-bymonthday').addEventListener('change', updateVisibility);
            document.getElementById(id + '-byday').addEventListener('change', updateVisibility);

            document.getElementById(id + '-wrapper').addEventListener('change', updateRRule);

            updateVisibility();
            updateRRule();
        }

        // Funktion sowohl für DOMContentLoaded als auch für rex:ready registrieren
        function onReady(fn) {
            if (document.readyState !== 'loading') {
                fn();
            } else {
                document.addEventListener('DOMContentLoaded', fn);
            }
            if (typeof rex !== 'undefined') {
                rex.ready(fn);
            }
        }

        onReady(initRRuleWidget);
    })();
</script>
