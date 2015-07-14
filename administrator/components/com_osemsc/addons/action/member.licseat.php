<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberLicSeat
{
	function save()
	{
		$result = array();
		
		$post = JRequest::get('post');
		
		$post = oseMscAddon::getPost('licseat_',$post);
		
		if(!isset($post['contact_send']))
		{
			$post['contact_send'] = 0;
		}
		
		if(!isset($post['internal_contact_send']))
		{
			$post['internal_contact_send'] = 0;
		}
		
		$db = oseDB::instance();
		$msc_id = JRequest::getInt('msc_id',0);
		$member_id = JRequest::getInt('member_id',0);
		//$member = oseRegistry::call('member');
		//$member->instance($member_id);
		//$memInfo = $member->getMembership($msc_id,'obj');
		//$memInfoParams = oseObject::getParams($memInfo);
		
		$mscLicInfo = oseRegistry::call('msc')->getExtInfo($msc_id,'lic','obj');
		$mscLicInfoParams = oseObject::getParams($mscLicInfo);
		
		$where = array();
		$where[] = "license_user_id = '{$member_id}'";
		
		
		$post['id'] = $item->id;
		$post['license_user_id'] = $member_id;
		if(empty($post['id']))
		{
			$updated = oseDB::insert('#__oselic_license_seat',$post);
		}
		else
		{
			$updated = oseDB::update('#__oselic_license_seat','id',$post);
		}
		
		if(!$updated)
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Fail Saving OSEMSC User Info.');
			$result['member_id'] = '';
		}
		else
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Saved Joomla User Info.');
		}
		
		return $result;
	}
	
	function getItem()
	{
		$result = array();
		
		$post = JRequest::get('post');
		
		$post = oseMscAddon::getPost('licseat_',$post);
		
		if(!isset($post['contact_send']))
		{
			$post['contact_send'] = 0;
		}
		
		if(!isset($post['internal_contact_send']))
		{
			$post['internal_contact_send'] = 0;
		}
		
		$db = oseDB::instance();
		$msc_id = JRequest::getInt('msc_id',0);
		$member_id = JRequest::getInt('member_id',0);
		
		$mscLicInfo = oseRegistry::call('msc')->getExtInfo($msc_id,'lic','obj');
		$mscLicInfoParams = oseObject::getParams($mscLicInfo);
		
		$where = array();
		$where[] = "license_user_id = '{$member_id}'";
		
		if(empty($mscLicInfoParams->id))
		{
			$where[] = "license_id = '{$mscLicInfoParams->id}'";
		}
		$where = oseDB::implodeWhere();
		
		$query = " SELECT * FROM `#__oselic_license_seat`"
				. $where
				." ORDER BY id DESC"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		
		$result['success'] =  empty($item)?false:true;
		$result['total'] = empty($item)?0:1;
		$result['result'] = empty($item)?array():$item;
	
		//$result = oseJson::encode($result);
		
		return $result;
	}
}
?>
