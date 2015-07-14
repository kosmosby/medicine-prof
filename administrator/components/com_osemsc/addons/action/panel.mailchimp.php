<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelMailchimp extends oseMscAddon
{
	public static function save($params = array())
	{
		$result = array();
		$post = JRequest::get('post');
		if (oseMscAddon::quickSavePanel('mailchimp_',$post))
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
	
		
	public static function getList($params = array())
	{
		require_once(OSEMSC_B_LIB.DS.'MCAPI.class.php');
		$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
		$APIKey = $oseMscConfig->mailchimp_api_key;
		$api = new MCAPI($APIKey);

		$lists = $api->lists();	
		$data = $lists['data'];
		$items = array();
		$item = array();
		foreach($data as $value)
		{
			$item['list_id'] = $value['id'];
			$item['name'] = $value['name'];
			$items[] = $item; 			
		}
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