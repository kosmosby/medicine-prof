<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldInfoGDVersion extends JFormField
{
	protected $type = 'InfoGDVersion';
    protected function getLabel()
    {
        if ($this->label) return $this->label;
        else return JText::_("COM_BIDS_GD_VERSION");

    }

	protected function getInput()
	{
        $gd = array();
        ob_start();
        @phpinfo(INFO_MODULES);
        $output=ob_get_contents();
        ob_end_clean();

        if(preg_match("/GD Version[ \t]*(<[^>]+>[ \t]*)+([^<>]+)/s",$output,$matches)){
            return $matches[2];
        }else
            return JText::_("COM_BIDS_GD_NOT_AVAILABLE");
	}
}
