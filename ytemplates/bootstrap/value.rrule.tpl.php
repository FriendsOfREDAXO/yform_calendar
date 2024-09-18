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
                    const monthlyType = $widget.find('input[name="rrule-monthlyType"]:checked').val();
                    toggleVisibility(elements.bymonthdayGroup, monthlyType === 'bymonthday');
                    toggleVisibility(elements.bydayGroup, monthlyType === 'byday');
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
                    const monthlyType = $widget.find('input[name="rrule-monthlyType"]:checked').val();
                    if (monthlyType === 'bymonthday') {
                        rrule += `;BYMONTHDAY=${$widget.find('.rrule-monthday').val()}`;
                    } else if (monthlyType === 'byday') {
                        rrule += `;BYDAY=${$widget.find('.rrule-weekdayorder').val()}${$widget.find('.rrule-weekday-select').val()}`;
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
                updateVisibility();
            });

            elements.frequency.on('change', function() {
                updateVisibility();
                updateRRule();
            });

            $widget.find('input[name="rrule-monthlyType"]').on('change', function() {
                updateVisibility();
                updateRRule();
            });

            elements.endType.on('change', function() {
                updateVisibility();
                updateRRule();
            });

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
