<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionBridgeLicSeat extends oseMscAddon
{
	public static function save($params = array())
	{
		
		$post = JRequest::get('post');
		
		if(!isset($post['licseat_contact_send']))
		{
			$post['licseat_contact_send'] = 0;
		}
		
		if(!isset($post['licseat_internal_contact_send']))
		{
			$post['licseat_internal_contact_send'] = 0;
		}
		
		if(!isset($post['licseat_enabled']))
		{
			$post['licseat_enabled'] = 0;
		}
		
		if (oseMscAddon::quickSavePanel('licseat_',$post))
		{
			$result['success'] = true;
			$result['title'] = JText::_('Finished');
			$result['content'] = JText::_('Save Successfully!');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Error in Saving License Parameters');
		}
		
		return $result;
	}
	
}
?>