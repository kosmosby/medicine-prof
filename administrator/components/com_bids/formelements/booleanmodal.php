<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldBooleanModal extends JFormField
{
    protected $type = 'BooleanModal';

    protected function getInput()
    {
        $html = '<fieldset id="'.$this->name.'" class="radio">';

        $setupURLid = $this->name . '_setupURL';

        $arr = array( JHtml::_('select.option', '1', JText::_('JYES')), JHtml::_('select.option', '0', JText::_('JNO')) );
        $html .= JHtml::_('select.radiolist', $arr, $this->name, 'onchange="$(\'' . $setupURLid . '\').toggle()"', 'value', 'text', (int) $this->value);

        $attribs = array();
        $attribs['class'] = 'modal';
        $attribs['style'] = 'float: right; display:'.($this->value ? 'inline' : 'none');
        $attribs['rel'] = "{handler: 'iframe', size: {x: 640, y: 480}}";
        $attribs['id'] = $setupURLid;

        $html .= JHtml::link($this->element->getAttribute('setupURL'),'<input style="color: #F00; font-weight: bold; border-color: #F00;" type="button" value="Setup" />',$attribs);

        $html .= '</fieldset>';

        return $html;
    }
}