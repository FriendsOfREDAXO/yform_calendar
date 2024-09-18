    (function() {
        function initRRuleWidgets() {
            document.querySelectorAll('.rrule-widget').forEach(widget => {
                const id = widget.querySelector('[id$="-wrapper"]').id.replace('-wrapper', '');
                const elements = {
                    recurringEventCheckbox: widget.querySelector('#' + id + '-checkbox'),
                    rruleWidget: widget.querySelector('#' + id + '-widget'),
                    rruleDisplay: widget.querySelector('#' + id + '-display'),
                    frequency: widget.querySelector('#' + id + '-frequency'),
                    interval: widget.querySelector('#' + id + '-interval'),
                    weeklyGroup: widget.querySelector('#' + id + '-weekly-group'),
                    monthlyGroup: widget.querySelector('#' + id + '-monthly-group'),
                    bymonthdayGroup: widget.querySelector('#' + id + '-bymonthday-group'),
                    bydayGroup: widget.querySelector('#' + id + '-byday-group'),
                    rruleValue: widget.querySelector('#' + id),
                    endType: widget.querySelector('#' + id + '-end-type'),
                    countGroup: widget.querySelector('#' + id + '-count-group'),
                    untilGroup: widget.querySelector('#' + id + '-until-group'),
                    count: widget.querySelector('#' + id + '-count'),
                    until: widget.querySelector('#' + id + '-until')
                };

                function toggleVisibility(element, show) {
                    if (element) element.classList.toggle('hidden', !show);
                }

                function updateVisibility() {
                    const frequency = elements.frequency.value;
                    toggleVisibility(elements.weeklyGroup, frequency === 'WEEKLY');
                    toggleVisibility(elements.monthlyGroup, frequency === 'MONTHLY');

                    if (frequency === 'MONTHLY') {
                        const monthlyType = widget.querySelector('input[name="' + id + '-monthlyType"]:checked');
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
                        const weekdays = Array.from(widget.querySelectorAll('#' + id + '-weekly-group input[type="checkbox"]:checked'))
                        .map(cb => cb.id.split('-').pop())
                        .join(',');
                        if (weekdays) rrule += `;BYDAY=${weekdays}`;
                    } else if (elements.frequency.value === 'MONTHLY') {
                        const monthlyType = widget.querySelector('input[name="' + id + '-monthlyType"]:checked');
                        if (monthlyType) {
                            if (monthlyType.value === 'bymonthday') {
                                rrule += `;BYMONTHDAY=${widget.querySelector('#' + id + '-monthday').value}`;
                            } else if (monthlyType.value === 'byday') {
                                rrule += `;BYDAY=${widget.querySelector('#' + id + '-weekdayorder').value}${widget.querySelector('#' + id + '-weekday').value}`;
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
                                    const checkbox = widget.querySelector(`#${id}-${day}`);
                                    if (checkbox) checkbox.checked = true;
                                });
                            } else if (rrule.FREQ === 'MONTHLY') {
                                const bydayRadio = widget.querySelector(`#${id}-byday`);
                                if (bydayRadio) bydayRadio.checked = true;
                                const match = rrule.BYDAY.match(/(-?\d+)([A-Z]+)/);
                                if (match) {
                                    const weekdayOrder = widget.querySelector(`#${id}-weekdayorder`);
                                    const weekday = widget.querySelector(`#${id}-weekday`);
                                    if (weekdayOrder) weekdayOrder.value = match[1];
                                    if (weekday) weekday.value = match[2];
                                }
                            }
                        }

                        if (rrule.BYMONTHDAY) {
                            const bymonthdayRadio = widget.querySelector(`#${id}-bymonthday`);
                            const monthday = widget.querySelector(`#${id}-monthday`);
                            if (bymonthdayRadio) bymonthdayRadio.checked = true;
                            if (monthday) monthday.value = rrule.BYMONTHDAY;
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
                const bymonthdayRadio = widget.querySelector('#' + id + '-bymonthday');
                const bydayRadio = widget.querySelector('#' + id + '-byday');
                if (bymonthdayRadio) bymonthdayRadio.addEventListener('change', updateVisibility);
                if (bydayRadio) bydayRadio.addEventListener('change', updateVisibility);
                elements.endType.addEventListener('change', updateVisibility);

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
