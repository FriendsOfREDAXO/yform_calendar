<?php
$name = $this->getFieldName();
$value = $this->getValue();
$id = $this->getFieldId();
?>
<div id="rrule-widget" class="rrule-widget apple-style">
<div class="form-group" id="<?= $id ?>-wrapper">
    <!-- Header mit Toggle und Label -->
    <div class="rrule-header">
        <label class="toggle-switch">
            <input type="checkbox" id="<?= $id ?>-checkbox" class="rrule-toggle">
            <span class="slider"></span>
        </label>
        <span class="toggle-label"><?= $this->getLabel() ?></span>
    </div>

    <!-- Verborgen bis Checkbox aktiviert -->
    <div id="<?= $id ?>-widget" class="rrule-content hidden">
        <!-- Frequenz -->
        <div class="rrule-section">
            <label for="<?= $id ?>-frequency" class="rrule-label"><?= rex_i18n::msg('yform_calendar_frequency') ?></label>
            <select id="<?= $id ?>-frequency" class="rrule-select">
                <option value="DAILY"><?= rex_i18n::msg('yform_calendar_daily') ?></option>
                <option value="WEEKLY"><?= rex_i18n::msg('yform_calendar_weekly') ?></option>
                <option value="MONTHLY"><?= rex_i18n::msg('yform_calendar_monthly') ?></option>
                <option value="YEARLY"><?= rex_i18n::msg('yform_calendar_yearly') ?></option>
            </select>
        </div>
        
        <!-- Intervall -->
        <div class="rrule-section">
            <div class="interval-group">
                <label for="<?= $id ?>-interval" class="interval-label"><?= rex_i18n::msg('yform_calendar_every') ?></label>
                <input type="number" id="<?= $id ?>-interval" min="1" max="99" value="1" class="rrule-input interval-input">
                <span class="interval-suffix" id="<?= $id ?>-interval-suffix">Tag</span>
            </div>
        </div>
        
        <!-- Wöchentliche Tage -->
        <div id="<?= $id ?>-weekly-group" class="rrule-section hidden">
            <label class="rrule-label"><?= rex_i18n::msg('yform_calendar_weekdays') ?></label>
            <div class="weekdays-grid">
                <?php foreach (['MO' => '1', 'TU' => '2', 'WE' => '3', 'TH' => '4', 'FR' => '5', 'SA' => '6', 'SU' => '7'] as $day => $num): ?>
                <label class="weekday-button">
                    <input type="checkbox" id="<?= $id ?>-<?= $day ?>" class="weekday-checkbox" data-day="<?= $day ?>">
                    <span class="weekday-label"><?= rex_i18n::msg('yform_calendar_' . strtolower($day)) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Monatlicher Typ -->
        <div id="<?= $id ?>-monthly-group" class="rrule-section hidden">
            <label class="rrule-label"><?= rex_i18n::msg('yform_calendar_monthly_type') ?></label>
            <div class="radio-buttons">
                <label class="radio-button">
                    <input type="radio" id="<?= $id ?>-bymonthday" name="<?= $id ?>-monthlyType" value="bymonthday">
                    <span><?= rex_i18n::msg('yform_calendar_day_of_month') ?></span>
                </label>
                <label class="radio-button">
                    <input type="radio" id="<?= $id ?>-byday" name="<?= $id ?>-monthlyType" value="byday">
                    <span><?= rex_i18n::msg('yform_calendar_day_of_week') ?></span>
                </label>
            </div>
        </div>
        
        <!-- Monatstag -->
        <div id="<?= $id ?>-bymonthday-group" class="rrule-section hidden">
            <label for="<?= $id ?>-monthday" class="rrule-label"><?= rex_i18n::msg('yform_calendar_day_of_month') ?></label>
            <input type="number" id="<?= $id ?>-monthday" min="1" max="31" value="1" class="rrule-input">
        </div>
        
        <!-- Wochentag im Monat -->
        <div id="<?= $id ?>-byday-group" class="rrule-section hidden">
            <label class="rrule-label"><?= rex_i18n::msg('yform_calendar_weekday_of_month') ?></label>
            <div class="row">
                <div class="col-md-6">
                    <select id="<?= $id ?>-weekdayorder" class="rrule-select">
                        <option value="1"><?= rex_i18n::msg('yform_calendar_first') ?></option>
                        <option value="2"><?= rex_i18n::msg('yform_calendar_second') ?></option>
                        <option value="3"><?= rex_i18n::msg('yform_calendar_third') ?></option>
                        <option value="4"><?= rex_i18n::msg('yform_calendar_fourth') ?></option>
                        <option value="-1"><?= rex_i18n::msg('yform_calendar_last') ?></option>
                    </select>
                </div>
                <div class="col-md-6">
                    <select id="<?= $id ?>-weekday" class="rrule-select">
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

        <!-- Enddatum Optionen -->
        <div class="rrule-section">
            <label for="<?= $id ?>-end-type" class="rrule-label"><?= rex_i18n::msg('yform_calendar_end_repeat') ?></label>
            <select id="<?= $id ?>-end-type" class="rrule-select">
                <option value="never"><?= rex_i18n::msg('yform_calendar_never') ?></option>
                <option value="count"><?= rex_i18n::msg('yform_calendar_after_occurrences') ?></option>
                <option value="until"><?= rex_i18n::msg('yform_calendar_on_date') ?></option>
            </select>
            
            <!-- Anzahl der Termine -->
            <div id="<?= $id ?>-count-group" class="rrule-subsection hidden">
                <label for="<?= $id ?>-count"><?= rex_i18n::msg('yform_calendar_number_of_events') ?></label>
                <input type="number" id="<?= $id ?>-count" min="1" value="1" class="rrule-input">
            </div>
            
            <!-- Enddatum -->
            <div id="<?= $id ?>-until-group" class="rrule-subsection hidden">
                <label for="<?= $id ?>-until"><?= rex_i18n::msg('yform_calendar_end_date') ?></label>
                <input type="date" id="<?= $id ?>-until" class="rrule-input">
            </div>
        </div>

        <!-- Ausgeschlossene Termine (EXDATE) -->
        <div class="rrule-section">
            <label for="<?= $id ?>-exdate" class="rrule-label"><?= rex_i18n::msg('yform_calendar_excluded_dates') ?></label>
            <input type="text" id="<?= $id ?>-exdate" class="rrule-input rrule-exdate-input" placeholder="Termine zum Ausschließen...">
            <div id="<?= $id ?>-exdate-list" class="rrule-exdate-list"></div>
        </div>

        <!-- Vorschau -->
        <div class="rrule-preview">
            <div id="<?= $id ?>-display" class="preview-text"></div>
        </div>
    </div>
    
    <input type="hidden" id="<?= $id ?>" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>">
</div>
</div>
