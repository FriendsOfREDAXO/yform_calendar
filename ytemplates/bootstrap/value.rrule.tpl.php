<?php
$name = $this->getFieldName();
$value = $this->getValue();
$package = rex_addon::get('yform_calendar');
?>
<div class="rrule-widget">
    <label class="toggle-switch">
        <input type="checkbox" class="rrule-checkbox">
        <span class="slider"></span>
    </label>

    <span class="toggle-label"><i class="fas fa-redo icon"></i><?= $this->getLabel() ?></span>

    <div class="rrule-options hidden">
        <div class="form-group">
            <label for="rrule-frequency"><i class="fas fa-clock icon"></i><?= $package->i18n('frequency') ?>:</label>
            <select class="rrule-frequency form-control selectpicker">
                <option value="DAILY"><?= $package->i18n('daily') ?></option>
                <option value="WEEKLY"><?= $package->i18n('weekly') ?></option>
                <option value="MONTHLY"><?= $package->i18n('monthly') ?></option>
                <option value="YEARLY"><?= $package->i18n('yearly') ?></option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="rrule-interval"><i class="fas fa-step-forward icon"></i><?= $package->i18n('interval') ?>:</label>
            <input type="number" class="rrule-interval form-control" min="1" value="1">
        </div>
        
        <div class="rrule-weekly-group form-group hidden">
            <label><i class="fas fa-calendar-week icon"></i><?= $package->i18n('weekdays') ?>:</label>
            <div class="toggle-group">
                <?php 
                $days = ['mo', 'tu', 'we', 'th', 'fr', 'sa', 'su'];
                foreach ($days as $day): 
                ?>
                <div>
                    <label class="toggle-switch">
                        <input type="checkbox" class="rrule-weekday" data-day="<?= strtoupper($day) ?>">
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label"><?= $package->i18n($day) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="rrule-monthly-group form-group hidden">
            <label><i class="fas fa-calendar-alt icon"></i><?= $package->i18n('monthly_type') ?>:</label>
            <div class="radio-group">
                <label><input type="radio" name="rrule-monthlyType" value="bymonthday"><?= $package->i18n('day_of_month') ?></label>
                <label><input type="radio" name="rrule-monthlyType" value="byday"><?= $package->i18n('day_of_week') ?></label>
            </div>
        </div>
        
        <div class="rrule-bymonthday-group form-group hidden">
            <label for="rrule-monthday"><i class="fas fa-calendar-day icon"></i><?= $package->i18n('day_of_month') ?>:</label>
            <input type="number" class="rrule-monthday form-control" min="1" max="31" value="1">
        </div>
        
        <div class="rrule-byday-group form-group hidden">
            <label><i class="fas fa-calendar-check icon"></i><?= $package->i18n('weekday_of_month') ?>:</label>
            <div class="row">
                <div class="col-md-6">
                    <select class="rrule-weekdayorder form-control selectpicker">
                        <option value="1"><?= $package->i18n('first') ?></option>
                        <option value="2"><?= $package->i18n('second') ?></option>
                        <option value="3"><?= $package->i18n('third') ?></option>
                        <option value="4"><?= $package->i18n('fourth') ?></option>
                        <option value="-1"><?= $package->i18n('last') ?></option>
                    </select>
                </div>
                <div class="col-md-6">
                    <select class="rrule-weekday-select form-control selectpicker">
                        <option value="MO"><?= $package->i18n('monday') ?></option>
                        <option value="TU"><?= $package->i18n('tuesday') ?></option>
                        <option value="WE"><?= $package->i18n('wednesday') ?></option>
                        <option value="TH"><?= $package->i18n('thursday') ?></option>
                        <option value="FR"><?= $package->i18n('friday') ?></option>
                        <option value="SA"><?= $package->i18n('saturday') ?></option>
                        <option value="SU"><?= $package->i18n('sunday') ?></option>
                    </select>
                </div>
            </div>
        </div>

        <div class="rrule-end-options form-group">
            <label><i class="fas fa-calendar-times icon"></i><?= $package->i18n('end_repeat') ?>:</label>
            <select class="rrule-end-type form-control selectpicker">
                <option value="never"><?= $package->i18n('never') ?></option>
                <option value="count"><?= $package->i18n('after_occurrences') ?></option>
                <option value="until"><?= $package->i18n('on_date') ?></option>
            </select>
            
            <div class="rrule-count-group form-group hidden">
                <label for="rrule-count"><?= $package->i18n('number_of_events') ?>:</label>
                <input type="number" class="rrule-count form-control" min="1" value="1" style="width: auto; display: inline-block;">
            </div>
            
            <div class="rrule-until-group form-group hidden">
                <label for="rrule-until"><?= $package->i18n('end_date') ?>:</label>
                <input type="date" class="rrule-until form-control" style="width: auto; display: inline-block;">
            </div>
        </div>
    </div>
    
    <input type="hidden" class="rrule-value" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>">
    <div class="rrule-display hidden"></div>
</div>
<script>
    (function($) {
    $.fn.rruleWidget = function() {
        return this.each(function() {
            const $widget = $(this);
            
            const elements = {
                recurringEventCheckbox: $widget.find('.rrule-checkbox'),
                rruleWidget: $widget.find('.rrule-options'),
                rruleDisplay: $widget.find('.rrule-display'),
                frequency: $widget.find('.rrule-frequency'),
                interval: $widget.find('.rrule-interval'),
                weeklyGroup: $widget.find('.rrule-weekly-group'),
                monthlyGroup: $widget.find('.rrule-monthly-group'),
                bymonthdayGroup: $widget.find('.rrule-bymonthday-group'),
                bydayGroup: $widget.find('.rrule-byday-group'),
                rruleValue: $widget.find('.rrule-value'),
                endType: $widget.find('.rrule-end-type'),
                countGroup: $widget.find('.rrule-count-group'),
                untilGroup: $widget.find('.rrule-until-group'),
                count: $widget.find('.rrule-count'),
                until: $widget.find('.rrule-until')
            };

            function toggleVisibility($element, show) {
                $element.toggleClass('hidden', !show);
            }

            function updateVisibility() {
                const frequency = elements.frequency.val();
                toggleVisibility(elements.weeklyGroup, frequency === 'WEEKLY');
                toggleVisibility(elements.monthlyGroup, frequency === 'MONTHLY');
                
                if (frequency === 'MONTHLY') {
                    const monthlyType = $widget.find('input[name="rrule-monthlyType"]:checked');
                    toggleVisibility(elements.bymonthdayGroup, monthlyType.val() === 'bymonthday');
                    toggleVisibility(elements.bydayGroup, monthlyType.val() === 'byday');
                } else {
                    toggleVisibility(elements.bymonthdayGroup, false);
                    toggleVisibility(elements.bydayGroup, false);
                }

                toggleVisibility(elements.countGroup, elements.endType.val() === 'count');
                toggleVisibility(elements.untilGroup, elements.endType.val() === 'until');
            }

            function updateRRule() {
                if (!elements.recurringEventCheckbox.prop('checked')) {
                    elements.rruleValue.val('');
                    elements.rruleDisplay.text('');
                    return;
                }

                let rrule = `FREQ=${elements.frequency.val()};INTERVAL=${elements.interval.val()}`;

                if (elements.frequency.val() === 'WEEKLY') {
                    const weekdays = $widget.find('.rrule-weekday:checked')
                        .map(function() { return $(this).data('day'); })
                        .get()
                        .join(',');
                    if (weekdays) rrule += `;BYDAY=${weekdays}`;
                } else if (elements.frequency.val() === 'MONTHLY') {
                    const monthlyType = $widget.find('input[name="rrule-monthlyType"]:checked');
                    if (monthlyType.length) {
                        if (monthlyType.val() === 'bymonthday') {
                            rrule += `;BYMONTHDAY=${$widget.find('.rrule-monthday').val()}`;
                        } else if (monthlyType.val() === 'byday') {
                            rrule += `;BYDAY=${$widget.find('.rrule-weekdayorder').val()}${$widget.find('.rrule-weekday-select').val()}`;
                        }
                    }
                }
                
                if (elements.endType.val() === 'count') {
                    rrule += `;COUNT=${elements.count.val()}`;
                } else if (elements.endType.val() === 'until') {
                    const untilDate = new Date(elements.until.val());
                    const formattedDate = untilDate.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
                    rrule += `;UNTIL=${formattedDate}`;
                }

                elements.rruleValue.val(rrule);
                elements.rruleDisplay.text(rrule);
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
                const initialValue = elements.rruleValue.val();
                if (initialValue) {
                    elements.recurringEventCheckbox.prop('checked', true);
                    toggleVisibility(elements.rruleWidget, true);
                    toggleVisibility(elements.rruleDisplay, true);

                    const rrule = parseRRule(initialValue);
                    
                    elements.frequency.val(rrule.FREQ || 'DAILY');
                    elements.interval.val(rrule.INTERVAL || '1');

                    if (rrule.BYDAY) {
                        if (rrule.FREQ === 'WEEKLY') {
                            rrule.BYDAY.split(',').forEach(day => {
                                $widget.find(`.rrule-weekday[data-day="${day}"]`).prop('checked', true);
                            });
                        } else if (rrule.FREQ === 'MONTHLY') {
                            $widget.find('input[name="rrule-monthlyType"][value="byday"]').prop('checked', true);
                            const match = rrule.BYDAY.match(/(-?\d+)([A-Z]+)/);
                            if (match) {
                                $widget.find('.rrule-weekdayorder').val(match[1]);
                                $widget.find('.rrule-weekday-select').val(match[2]);
                            }
                        }
                    }

                    if (rrule.BYMONTHDAY) {
                        $widget.find('input[name="rrule-monthlyType"][value="bymonthday"]').prop('checked', true);
                        $widget.find('.rrule-monthday').val(rrule.BYMONTHDAY);
                    }

                    if (rrule.COUNT) {
                        elements.endType.val('count');
                        elements.count.val(rrule.COUNT);
                    } else if (rrule.UNTIL) {
                        elements.endType.val('until');
                        const untilDate = new Date(rrule.UNTIL.slice(0, 4) + '-' + rrule.UNTIL.slice(4, 6) + '-' + rrule.UNTIL.slice(6, 8));
                        elements.until.val(untilDate.toISOString().split('T')[0]);
                    } else {
                        elements.endType.val('never');
                    }

                    updateVisibility();
                    elements.rruleDisplay.text(initialValue);
                }
            }

            elements.recurringEventCheckbox.on('change', function() {
                toggleVisibility(elements.rruleWidget, this.checked);
                toggleVisibility(elements.rruleDisplay, this.checked);
                updateRRule();
            });

            elements.frequency.on('change', updateVisibility);
            $widget.find('input[name="rrule-monthlyType"]').on('change', updateVisibility);
            elements.endType.on('change', updateVisibility);

            $widget.on('change', 'input, select', updateRRule);

            setInitialValues();
            updateVisibility();
            updateRRule();
        });
    };

    // Funktion zur Initialisierung, die prüft, ob .rrule-widget vorhanden ist
    function initRRuleWidgets() {
        const $widgets = $('.rrule-widget');
        if ($widgets.length > 0) {
            $widgets.rruleWidget();
        }
    }

        // Verwendung von jQuery's ready-Funktion für die Initialisierung
        $(function() {
            initRRuleWidgets();
        });

        // Zusätzlich auf das rex:ready Event hören, falls es in der Umgebung verwendet wird
        $(document).on('rex:ready', initRRuleWidgets);

    })(jQuery);

</script>
