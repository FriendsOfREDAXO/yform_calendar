<?php
$name = $this->getFieldName();
$value = $this->getValue();
$id = $this->getFieldId();
?>
<div id="rrule-widget" class="rrule-widget">
<div class="form-group" id="<?= $id ?>-wrapper">
    <label class="toggle-switch">
        <input type="checkbox" id="<?= $id ?>-checkbox">
        <span class="slider"></span>
    </label>

    <span class="toggle-label"><i class="fas fa-redo icon"></i><?= $this->getLabel() ?></span>

    <div id="<?= $id ?>-widget" class="hidden">
        <div class="form-group">
            <label for="<?= $id ?>-frequency"><i class="fas fa-clock icon"></i><?= rex_i18n::msg('yform_calendar_frequency') ?>:</label>
            <select id="<?= $id ?>-frequency" class="form-control selectpicker">
                <option value="DAILY"><?= rex_i18n::msg('yform_calendar_daily') ?></option>
                <option value="WEEKLY"><?= rex_i18n::msg('yform_calendar_weekly') ?></option>
                <option value="MONTHLY"><?= rex_i18n::msg('yform_calendar_monthly') ?></option>
                <option value="YEARLY"><?= rex_i18n::msg('yform_calendar_yearly') ?></option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="<?= $id ?>-interval"><i class="fas fa-step-forward icon"></i><?= rex_i18n::msg('yform_calendar_interval') ?>:</label>
            <input type="number" id="<?= $id ?>-interval" min="1" value="1" class="form-control">
        </div>
        
        <div id="<?= $id ?>-weekly-group" class="form-group hidden">
            <label><i class="fas fa-calendar-week icon"></i><?= rex_i18n::msg('yform_calendar_weekdays') ?>:</label>
            <div class="toggle-group">
                <?php foreach (['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'] as $day): ?>
                <div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="<?= $id ?>-<?= $day ?>">
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label"><?= rex_i18n::msg('yform_calendar_' . strtolower($day)) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div id="<?= $id ?>-monthly-group" class="form-group hidden">
            <label><i class="fas fa-calendar-alt icon"></i><?= rex_i18n::msg('yform_calendar_monthly_type') ?>:</label>
            <div class="radio-group">
                <label><input type="radio" id="<?= $id ?>-bymonthday" name="<?= $id ?>-monthlyType" value="bymonthday"><?= rex_i18n::msg('yform_calendar_day_of_month') ?></label>
                <label><input type="radio" id="<?= $id ?>-byday" name="<?= $id ?>-monthlyType" value="byday"><?= rex_i18n::msg('yform_calendar_day_of_week') ?></label>
            </div>
        </div>
        
        <div id="<?= $id ?>-bymonthday-group" class="form-group hidden">
            <label for="<?= $id ?>-monthday"><i class="fas fa-calendar-day icon"></i><?= rex_i18n::msg('yform_calendar_day_of_month') ?>:</label>
            <input type="number" id="<?= $id ?>-monthday" min="1" max="31" value="1" class="form-control">
        </div>
        
        <div id="<?= $id ?>-byday-group" class="form-group hidden">
            <label><i class="fas fa-calendar-check icon"></i><?= rex_i18n::msg('yform_calendar_weekday_of_month') ?>:</label>
            <div class="row">
                <div class="col-md-6">
                    <select id="<?= $id ?>-weekdayorder" class="form-control selectpicker">
                        <option value="1"><?= rex_i18n::msg('yform_calendar_first') ?></option>
                        <option value="2"><?= rex_i18n::msg('yform_calendar_second') ?></option>
                        <option value="3"><?= rex_i18n::msg('yform_calendar_third') ?></option>
                        <option value="4"><?= rex_i18n::msg('yform_calendar_fourth') ?></option>
                        <option value="-1"><?= rex_i18n::msg('yform_calendar_last') ?></option>
                    </select>
                </div>
                <div class="col-md-6">
                    <select id="<?= $id ?>-weekday" class="form-control selectpicker">
                        <option value="MO"><?= rex_i18n::msg('yform_calendar_monday') ?></option>
                        <option value="TU"><?= rex_i18n::msg('yform_calendar_tuesday') ?></option>
                        <option value="WE"><?= rex_i18n::msg('yform_calendar_wednesday') ?></option>
                        <option value="TH"><?= rex_i18n::msg('yform_calendar_thursday') ?></option>
                        <option value="FR"><?= rex_i18n::msg('yform_calendar_friday') ?></option>
                        <option value="SA"><?= rex_i18n::msg('yform_calendar_saturday') ?></option>
                        <option value="SU"><?= rex_i18n::msg('yform_calendar_sunday') ?></option>
                    </select>
                </div>
            </div>
        </div>

        <div id="<?= $id ?>-end-options" class="form-group">
            <label><i class="fas fa-calendar-times icon"></i><?= rex_i18n::msg('yform_calendar_end_repeat') ?>:</label>
            <select id="<?= $id ?>-end-type" class="form-control selectpicker">
                <option value="never"><?= rex_i18n::msg('yform_calendar_never') ?></option>
                <option value="count"><?= rex_i18n::msg('yform_calendar_after_occurrences') ?></option>
                <option value="until"><?= rex_i18n::msg('yform_calendar_on_date') ?></option>
            </select>
            
            <div id="<?= $id ?>-count-group" class="form-group hidden">
                <label for="<?= $id ?>-count"><?= rex_i18n::msg('yform_calendar_number_of_events') ?>:</label>
                <div class="row">
                    <div class="col-xs-12">
                        <input type="number" id="<?= $id ?>-count" min="1" value="1" class="form-control">
                    </div>
                </div>
            </div>
            
            <div id="<?= $id ?>-until-group" class="form-group hidden">
                <label for="<?= $id ?>-until"><?= rex_i18n::msg('yform_calendar_end_date') ?>:</label>
                <div class="row">
                    <div class="col-xs-12">
                        <input type="date" id="<?= $id ?>-until" class="form-control">
                    </div>
                </div>
            </div>
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
            rruleValue: document.getElementById(id),
            endType: document.getElementById(id + '-end-type'),
            countGroup: document.getElementById(id + '-count-group'),
            untilGroup: document.getElementById(id + '-until-group'),
            count: document.getElementById(id + '-count'),
            until: document.getElementById(id + '-until')
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
                            document.getElementById(`${id}-${day}`).checked = true;
                        });
                    } else if (rrule.FREQ === 'MONTHLY') {
                        document.getElementById(`${id}-byday`).checked = true;
                        const match = rrule.BYDAY.match(/(-?\d+)([A-Z]+)/);
                        if (match) {
                            document.getElementById(`${id}-weekdayorder`).value = match[1];
                            document.getElementById(`${id}-weekday`).value = match[2];
                        }
                    }
                }

                if (rrule.BYMONTHDAY) {
                    document.getElementById(`${id}-bymonthday`).checked = true;
                    document.getElementById(`${id}-monthday`).value = rrule.BYMONTHDAY;
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

        elements.frequency.addEventListener('change', updateVisibility);
        document.getElementById(id + '-bymonthday').addEventListener('change', updateVisibility);
        document.getElementById(id + '-byday').addEventListener('change', updateVisibility);
        elements.endType.addEventListener('change', updateVisibility);

        document.getElementById(id + '-wrapper').addEventListener('change', updateRRule);

        setInitialValues();
        updateVisibility();
        updateRRule();
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

    onReady(initRRuleWidget);
})();
</script>
