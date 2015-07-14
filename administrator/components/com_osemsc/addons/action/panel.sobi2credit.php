<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelSobi2credit extends oseMscAddon
{
	public static function save($params = array())
	{
		$result = array();
		$post = JRequest::get('post');
		if (oseMscAddon::quickSavePanel('sobi2credit_',$post))
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SAVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}
		
		return $result;
	}

	

}
?>