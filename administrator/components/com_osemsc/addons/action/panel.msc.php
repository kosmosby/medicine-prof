<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelMsc extends oseMscAddon
{
	public static function save($params = array())
	{

		$post = JRequest::get('post');
		$post['msc_restrict'] = JRequest::getVar('msc_restrict', null,'post','string', JREQUEST_ALLOWHTML);
		if (oseMscAddon::quickSavePanel('msc_',$post))
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SAVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR_IN_SAVING_MSC_PARAMETERS');
		}

		return $result;
	}


}
?>