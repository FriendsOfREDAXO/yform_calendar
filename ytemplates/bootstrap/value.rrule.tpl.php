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
