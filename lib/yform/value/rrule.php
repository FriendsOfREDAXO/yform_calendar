<?php
class rex_yform_value_rrule extends rex_yform_value_abstract
{
    function enterObject()
    {
        $this->setValue($this->getValue());

        if ($this->getValue() == '' && !$this->params['send']) {
            $this->setValue($this->getElement('default'));
        }

        if ($this->needsOutput()) {
            $this->params['form_output'][$this->getId()] = $this->parse('value.rrule.tpl.php');
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }

    function getDescription():string
    {
        return 'rrule|name|label|';
    }

    function getDefinitions():array
    {
        return [
            'type' => 'value',
            'name' => 'rrule',
            'values' => [
                'name'    => ['type' => 'name',   'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label'   => ['type' => 'text',   'label' => rex_i18n::msg('yform_values_defaults_label')],
                'default' => ['type' => 'text',   'label' => rex_i18n::msg('yform_values_rrule_default')],
            ],
            'description' => rex_i18n::msg('yform_values_rrule_description'),
            'dbtype' => 'text'
        ];
    }
}
