<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelJcontent extends oseMscAddon
{
	public static function save($params = array())
	{
		$result = array();
		$post = JRequest::get('post');
		$post['jcontent_view_control'] = empty($post['jcontent_view_control'])?0:1;
		$post['jcontent_create_control'] = empty($post['jcontent_create_control'])?0:1;
		$post['jcontent_edit_control'] = empty($post['jcontent_edit_control'])?0:1;
		if (oseMscAddon::quickSavePanel('jcontent_',$post))
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