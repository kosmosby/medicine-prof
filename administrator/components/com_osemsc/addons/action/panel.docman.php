<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelDocman extends oseMscAddon
{
	public static function save($params = array())
	{
		$result = array();
		$post = JRequest::get('post');
		if (oseMscAddon::quickSavePanel('docman_',$post))
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
	
		
	public static function getGroups($params = array())
	{
		$db = oseDB::instance();
		
		$query = " SELECT groups_id as id,groups_name as title FROM `#__docman_groups` ORDER BY `groups_name` ASC ";
        $db->setQuery($query);
        $items = oseDB::loadList();
		
		$result = array();
		
		if(count($items) < 1)
		{
			$result['total'] = 0;
			$result['results'] = '';
		}
		else
		{
			$result['total'] = count($items);
			$result['results'] = $items;
		}
		
		return $result;
	}
	

}
?>