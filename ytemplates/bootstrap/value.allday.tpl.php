<?php
$name = $this->getFieldName();
$value = $this->getValue();
$id = $this->getFieldId();
$label = $this->getLabel();
?>

<div class="allday-widget">
    <div class="form-group">
        <label class="allday-label">
            <input type="hidden" name="<?= $name ?>" value="0">
            <input type="checkbox" id="<?= $id ?>" name="<?= $name ?>" value="1" <?= $value ? 'checked' : '' ?> class="allday-checkbox">
            <span class="allday-toggle">
                <span class="allday-toggle-track"></span>
                <span class="allday-toggle-thumb"></span>
            </span>
            <span class="allday-text"><?= $label ?></span>
        </label>
    </div>
</div>

