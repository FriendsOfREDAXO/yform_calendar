<?php

class rex_yform_value_allday extends rex_yform_value_abstract
{
    public function enterObject()
    {
        $this->setValue($this->getValue());

        if ($this->getValue() == '' && !$this->params['send']) {
            $this->setValue($this->getElement('default'));
        }

        if ($this->needsOutput()) {
            $this->params['form_output'][$this->getId()] = $this->parse('value.allday.tpl.php');
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue() ? rex_i18n::msg('yform_calendar_yes') : rex_i18n::msg('yform_calendar_no');
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }

    public function getDescription(): string
    {
        return 'allday|name|label|';
    }

    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'allday',
            'values' => [
                'name'    => ['type' => 'name',   'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label'   => ['type' => 'text',   'label' => rex_i18n::msg('yform_values_defaults_label')],
                'default' => ['type' => 'text',   'label' => rex_i18n::msg('yform_values_defaults_default')],
            ],
            'description' => 'Toggle für ganztägige Termine',
            'dbtype' => 'tinyint(1)'
        ];
    }

    public static function getListValue($params)
    {
        $value = (int) $params['subject'];
        
        if ($value) {
            return '<i class="fa fa-circle-check" style="color: #34C759; font-size: 16px;" title="Ganztägig"></i>';
        } else {
            return '<i class="fa fa-circle-xmark" style="color: #999; font-size: 16px;" title="Mit Zeit"></i>';
        }
    }
}

