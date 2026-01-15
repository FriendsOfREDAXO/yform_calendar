<?php
$name = $this->getFieldName();
$value = $this->getValue();
$id = $this->getFieldId();

// Parse aktuelle Flags
$currentFlags = [];
if (!empty($value)) {
    $currentFlags = array_map('trim', explode(',', $value));
}

// Parse verfügbare Flag-Optionen aus values (Textarea wird als String gespeichert)
$flagOptions = [];
$valuesRaw = $this->getElement('values') ?? '';

// Wenn String, nach Newlines splitten; wenn Array, direkt verwenden
if (is_string($valuesRaw)) {
    $valuesRaw = array_filter(array_map('trim', explode("\n", $valuesRaw)));
} elseif (!is_array($valuesRaw)) {
    $valuesRaw = [];
}

foreach ($valuesRaw as $val) {
    if (empty($val)) {
        continue;
    }
    // Format: "flag_id|Label|fa-icon|color"
    $parts = array_map('trim', explode('|', $val));

    if (count($parts) >= 2) {
        $flagId = $parts[0];
        $label = $parts[1];
        $icon = $parts[2] ?? 'fa-tag';
        $color = $parts[3] ?? 'secondary';

        $flagOptions[$flagId] = [
            'label' => $label,
            'icon' => $icon,
            'color' => $color,
        ];
    }
}
?>

<div id="<?= $id ?>-wrapper" class="flags-widget apple-style">
    <div class="form-group">
        <label><?= $this->getLabel() ?></label>

        <div id="<?= $id ?>-toggles" class="flags-toggle-group">
            <?php foreach ($flagOptions as $flagId => $flagDef): ?>
            <label class="flag-toggle">
                <input 
                    type="checkbox" 
                    class="flag-checkbox" 
                    value="<?= $flagId ?>"
                    <?= in_array($flagId, $currentFlags) ? 'checked' : '' ?>
                    data-flag="<?= $flagId ?>"
                >
                <span class="toggle-switch"></span>
                <span class="toggle-label">
                    <i class="fa <?= rex_escape($flagDef['icon']) ?>" style="width: 20px; text-align: center;"></i>
                    <?= rex_escape($flagDef['label']) ?>
                </span>
            </label>
            <?php endforeach; ?>
        </div>

        <input 
            type="hidden" 
            name="<?= $name ?>" 
            id="<?= $id ?>-input" 
            value="<?= rex_escape($value) ?>"
        >
    </div>
</div>

<style>
.flags-widget {
    margin: 20px 0;
}

.flags-toggle-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.flag-toggle {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
    position: relative;
}

.flag-toggle input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.toggle-switch {
    display: inline-block;
    width: 48px;
    height: 28px;
    background-color: #ccc;
    border-radius: 14px;
    position: relative;
    margin-right: 12px;
    transition: background-color 0.3s ease;
}

.toggle-switch::after {
    content: '';
    position: absolute;
    width: 24px;
    height: 24px;
    background-color: white;
    border-radius: 50%;
    top: 2px;
    left: 2px;
    transition: left 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.flag-toggle input[type="checkbox"]:checked ~ .toggle-switch {
    background-color: #007bff;
}

.flag-toggle input[type="checkbox"]:checked ~ .toggle-switch::after {
    left: 22px;
}

.toggle-label {
    display: flex;
    align-items: center;
    font-weight: 500;
    color: #333;
    flex: 1;
}

.flag-toggle input[type="checkbox"]:checked ~ .toggle-label {
    color: #007bff;
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .flags-toggle-group {
        background: #2a2a2a;
        border-color: #444;
    }

    .toggle-label {
        color: #e0e0e0;
    }

    .flag-toggle input[type="checkbox"]:checked ~ .toggle-label {
        color: #5eb3ff;
    }

    .flag-toggle input[type="checkbox"]:checked ~ .toggle-switch {
        background-color: #0056b3;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('<?= $id ?>-wrapper');
    const input = document.getElementById('<?= $id ?>-input');
    const checkboxes = wrapper.querySelectorAll('.flag-checkbox');

    // Update hidden input wenn Checkboxen sich ändern
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checked = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            input.value = checked.join(',');
        });
    });
});
</script>
