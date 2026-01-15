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
                    intervalSuffix: widget.querySelector('#' + id + '-interval-suffix'),
                    weeklyGroup: widget.querySelector('#' + id + '-weekly-group'),
                    monthlyGroup: widget.querySelector('#' + id + '-monthly-group'),
                    bymonthdayGroup: widget.querySelector('#' + id + '-bymonthday-group'),
                    bydayGroup: widget.querySelector('#' + id + '-byday-group'),
                    rruleValue: widget.querySelector('#' + id),
                    endType: widget.querySelector('#' + id + '-end-type'),
                    countGroup: widget.querySelector('#' + id + '-count-group'),
                    untilGroup: widget.querySelector('#' + id + '-until-group'),
                    count: widget.querySelector('#' + id + '-count'),
                    until: widget.querySelector('#' + id + '-until'),
                    exdateInput: widget.querySelector('#' + id + '-exdate'),
                    exdateList: widget.querySelector('#' + id + '-exdate-list')
                };

                // Speicher für EXDATE Termine
                let exdates = [];

                const frequencyLabels = {
                    'DAILY': 'Tag',
                    'WEEKLY': 'Woche',
                    'MONTHLY': 'Monat',
                    'YEARLY': 'Jahr'
                };

                function toggleVisibility(element, show) {
                    if (element) element.classList.toggle('hidden', !show);
                }

                function updateIntervalSuffix() {
                    const freq = elements.frequency.value;
                    const interval = parseInt(elements.interval.value) || 1;
                    const label = frequencyLabels[freq] || 'Tagen';
                    
                    if (elements.intervalSuffix) {
                        elements.intervalSuffix.textContent = interval === 1 ? label : label + 'n';
                    }
                }

                function updateVisibility() {
                    const frequency = elements.frequency.value;
                    toggleVisibility(elements.weeklyGroup, frequency === 'WEEKLY');
                    toggleVisibility(elements.monthlyGroup, frequency === 'MONTHLY');
                    updateIntervalSuffix();

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

                function generatePreview(rrule) {
                    if (!rrule) return '';

                    const parts = rrule.split(';');
                    const rule = {};
                    parts.forEach(part => {
                        const [key, value] = part.split('=');
                        rule[key] = value;
                    });

                    let preview = 'Wiederholt sich: ';
                    
                    const interval = rule.INTERVAL ? parseInt(rule.INTERVAL) : 1;
                    const freq = rule.FREQ;
                    
                    if (interval > 1) {
                        preview += 'Alle ' + interval + ' ';
                    }
                    
                    const freqLabels = {
                        'DAILY': 'Tag' + (interval > 1 ? 'e' : ''),
                        'WEEKLY': 'Woche' + (interval > 1 ? 'n' : ''),
                        'MONTHLY': 'Monat' + (interval > 1 ? 'e' : ''),
                        'YEARLY': 'Jahr' + (interval > 1 ? 'e' : '')
                    };
                    
                    preview += freqLabels[freq] || freq;

                    // Wochentage
                    if (rule.BYDAY && freq === 'WEEKLY') {
                        const days = rule.BYDAY.split(',');
                        const dayNames = {
                            'MO': 'Mo', 'TU': 'Di', 'WE': 'Mi', 'TH': 'Do',
                            'FR': 'Fr', 'SA': 'Sa', 'SU': 'So'
                        };
                        preview += ' auf ' + days.map(d => dayNames[d] || d).join(', ');
                    }

                    // Enddatum
                    if (rule.COUNT) {
                        preview += ', ' + rule.COUNT + 'x';
                    } else if (rule.UNTIL) {
                        const date = new Date(rule.UNTIL.slice(0, 4) + '-' + rule.UNTIL.slice(4, 6) + '-' + rule.UNTIL.slice(6, 8));
                        const options = { year: 'numeric', month: 'long', day: 'numeric' };
                        preview += ' bis ' + date.toLocaleDateString('de-DE', options);
                    }

                    // EXDATE Info
                    if (exdates.length > 0) {
                        preview += ' (' + exdates.length + ' ausgeschlossen)';
                    }

                    return preview;
                }

                function renderExdateList() {
                    elements.exdateList.innerHTML = '';
                    exdates.forEach(date => {
                        const item = document.createElement('div');
                        item.className = 'rrule-exdate-item';
                        item.innerHTML = `
                            <span>${formatDateDisplay(date)}</span>
                            <button type="button" class="rrule-exdate-remove" data-date="${date}" title="Entfernen">×</button>
                        `;
                        elements.exdateList.appendChild(item);
                    });

                    // Event-Listener für Remove-Buttons
                    elements.exdateList.querySelectorAll('.rrule-exdate-remove').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            const date = btn.getAttribute('data-date');
                            exdates = exdates.filter(d => d !== date);
                            renderExdateList();
                            updateRRule();
                        });
                    });
                }

                function formatDateDisplay(dateStr) {
                    const date = new Date(dateStr);
                    const options = { weekday: 'short', year: 'numeric', month: '2-digit', day: '2-digit' };
                    return date.toLocaleDateString('de-DE', options);
                }

                function addExdate(dateStr) {
                    if (dateStr && !exdates.includes(dateStr)) {
                        exdates.push(dateStr);
                        exdates.sort();
                        renderExdateList();
                        updateRRule();
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
                        const untilDate = new Date(elements.until.value + 'T23:59:59');
                        const year = untilDate.getFullYear();
                        const month = String(untilDate.getMonth() + 1).padStart(2, '0');
                        const day = String(untilDate.getDate()).padStart(2, '0');
                        const formattedDate = year + month + day + 'T235959Z';
                        rrule += `;UNTIL=${formattedDate}`;
                    }

                    // EXDATE hinzufügen
                    if (exdates.length > 0) {
                        rrule += `;EXDATE=${exdates.join(',')}`;
                    }

                    elements.rruleValue.value = rrule;
                    elements.rruleDisplay.textContent = generatePreview(rrule);
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

                        const rrule = parseRRule(initialValue);

                        elements.frequency.value = rrule.FREQ || 'DAILY';
                        elements.interval.value = rrule.INTERVAL || '1';

                        // EXDATE laden
                        if (rrule.EXDATE) {
                            exdates = rrule.EXDATE.split(',');
                            renderExdateList();
                        }

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
                            // Konvertiere zu lokalen Datum ohne Zeitzone-Versatz
                            const year = untilDate.getFullYear();
                            const month = String(untilDate.getMonth() + 1).padStart(2, '0');
                            const day = String(untilDate.getDate()).padStart(2, '0');
                            elements.until.value = year + '-' + month + '-' + day;
                        } else {
                            elements.endType.value = 'never';
                        }

                        updateVisibility();
                        elements.rruleDisplay.textContent = generatePreview(initialValue);
                    }
                }

                // EXDATE Date Picker mit Flatpickr
                if (elements.exdateInput && typeof flatpickr !== 'undefined') {
                    flatpickr(elements.exdateInput, {
                        mode: 'single',
                        dateFormat: 'Y-m-d',
                        locale: 'de',
                        time_24hr: true,
                        placeholder: 'Termin zum Ausschließen...',
                        onChange: function(selectedDates) {
                            if (selectedDates.length > 0) {
                                // Konvertiere zu Y-m-d ohne Zeitzone-Versatz
                                const date = selectedDates[0];
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const day = String(date.getDate()).padStart(2, '0');
                                const dateStr = year + '-' + month + '-' + day;
                                
                                addExdate(dateStr);
                                // Input leeren für nächste Auswahl
                                elements.exdateInput.value = '';
                                // Flatpickr-Instanz leeren
                                if (elements.exdateInput._flatpickr) {
                                    elements.exdateInput._flatpickr.clear();
                                }
                            }
                        }
                    });
                }

                elements.recurringEventCheckbox.addEventListener('change', function() {
                    toggleVisibility(elements.rruleWidget, this.checked);
                    updateRRule();
                });

                elements.frequency.addEventListener('change', function() {
                    updateVisibility();
                    updateRRule();
                });
                
                elements.interval.addEventListener('change', function() {
                    updateIntervalSuffix();
                    updateRRule();
                });

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


(function() {
    function initExDatePickers() {
        // Alle Felder mit der Klasse 'exdate' finden
        var exdate_elements = document.querySelectorAll('input.exdate');
        
        exdate_elements.forEach(function (element) {
            // Flatpickr initialisieren
            flatpickr(element, {
                mode: 'multiple',
                dateFormat: 'Y-m-d',
                locale: 'de',
                conjunction: ',',
                time_24hr: true,
                placeholder: 'Termine auswählen...',
                onChange: function(selectedDates, dateStr) {
                    element.value = dateStr;
                    var event = new Event('change', { bubbles: true });
                    element.dispatchEvent(event);
                }
            });
        });
        
        // Alternative: Falls die Auswahl nach Namen zuverlässiger ist
        var exdate_by_name = document.querySelectorAll('input[name*="[exdate]"]');
        
        exdate_by_name.forEach(function (element) {
            // Nur verarbeiten, wenn es noch nicht durch den obigen Selektor erfasst wurde
            if (!element._flatpickr && !element.classList.contains('exdate')) {
                element.classList.add('exdate');
                
                flatpickr(element, {
                    mode: 'multiple',
                    dateFormat: 'Y-m-d',
                    locale: 'de',
                    conjunction: ',',
                    time_24hr: true,
                    placeholder: 'Termine auswählen...',
                    onChange: function(selectedDates, dateStr) {
                        element.value = dateStr;
                        var event = new Event('change', { bubbles: true });
                        element.dispatchEvent(event);
                    }
                });
            }
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

    onReady(initExDatePickers);
})();
