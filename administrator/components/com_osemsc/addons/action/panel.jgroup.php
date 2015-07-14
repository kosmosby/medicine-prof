<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelJgroup extends oseMscAddon
{
	public static function save($params = array())
	{
		$result = array();
		$post = JRequest::get('post');
		if (oseMscAddon::quickSavePanel('jgroup_',$post))
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SAVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR_IN_SAVING_JOOMLA_USER_GROUP_PARAMETERS');
		}
		
		return $result;
	}
	
		
	public static function getGroups($params = array())
	{
		$msc_id = JRequest::getInt('msc_id',0);
		$msc = oseRegistry::call('msc');

		$item = $msc->getExtInfo($msc_id,'jgroup','obj');
		
		$jgroup_id = oseObject::getValue($item,'jgroup_id',null);
		
		$gid = JHtml::_('access.usergroups', 'jgroup_jgroup_id', $jgroup_id, true);
		
    	oseExit($gid);
	}
}
?>