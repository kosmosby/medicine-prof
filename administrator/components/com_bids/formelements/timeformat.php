<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldTimeFormat extends JFormField
{
	protected $type = 'DateFormat';
	protected function getInput()
	{
        $time_format[] = JHTML::_("select.option", 'H:i', 'H:i');
        $time_format[] = JHTML::_("select.option", 'h:iA', 'h:iA');
        $html = JHTML::_("select.genericlist",$time_format,$this->name,"",'value', 'text',$this->value);
        $html.="&nbsp;<span id='{$this->id}_span'>&nbsp;</span>";
		return $html;
	}
}
