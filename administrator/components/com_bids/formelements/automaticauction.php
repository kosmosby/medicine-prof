<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldAutomaticAuction extends JFormField
{
    protected $type = 'AutomaticAuction';

    protected function getInput()
    {
        $html = '<fieldset id="' . $this->name . '" class="radio">';

        $html .= JHtml::_('select.booleanlist', $this->name, '', $this->value);

        $html .= '</fieldset>';

        return $html;
    }
}