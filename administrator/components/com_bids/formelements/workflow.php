<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldWorkFlow extends JFormField
{
	protected $type = 'WorkFlow';
	protected function getInput()
	{
        $quick_checked='';
        $catpage_checked='';
        if ((string) $this->value=='quick') $quick_checked="checked='checked'";
        if ((string) $this->value=='catpage') $catpage_checked="checked='checked'";
	    $html="
            <table width='100%'>
            <tr>
                <td><input type='radio' value='quick' name='{$this->name}' $quick_checked></td>
                <td>".JText::_('COM_BIDS_ONE_STEP_POSTING')."</td>
            </tr>
            <tr>
                <td><input type='radio' value='catpage' name='{$this->name}' $catpage_checked></td>
                <td>".JText::_('COM_BIDS_TWO_STEPS_POSTING')."</td>
            </tr>
            </table>
	    ";
		return $html;
	}
}
