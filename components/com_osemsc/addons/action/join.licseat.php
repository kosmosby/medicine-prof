<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinLicSeat
{
	public static function save($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		
		unset($params['allow_work']);
		
		if($params['join_from'] != 'payment')
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Success");
			return $result;
		}
		
		$db = oseDB::instance();
		
		$msc_id = oseObject::getValue($params,'msc_id');
		$member_id = oseObject::getValue($params,'member_id');
		$mscLicInfo = oseRegistry::call('msc')->getExtInfo($msc_id,'lic','obj');
		$mscLicSeatInfo = oseRegistry::call('msc')->getExtInfo($msc_id,'licseat','obj');
		
		if(!oseObject::getValue($mscLicSeatInfo,'enabled',false))
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Success");
			return $result;
		}
		
		if(empty($mscLicSeatInfo) || empty($mscLicInfo->license_id))
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Success");
			return $result;
		}
		
		$lic_id = $mscLicInfo->license_id;
		$user = new JUser($member_id);
		$post = array();
		$post['license_id'] = $lic_id;
		$post['licenser_id'] = $member_id;
		$post['seat_number'] = $mscLicSeatInfo->seat_number;
		$post['contact_send'] = $mscLicSeatInfo->contact_send;
		$post['internal_contact_send'] = $mscLicSeatInfo->internal_contact_send; 
		$post['contact'] = $user->email;
		$post['internal_contact'] = $user->email;
		
		$order_id = oseObject::getValue($params,'order_id');
		$order_item_id = oseObject::getValue($params,'order_item_id');
		
		$where = array();
		$where[] = 'order_item_id = '.$db->Quote($order_item_id);
		
		$orderInfo = oseRegistry::call('payment')->getOrderItem($where,'obj');
		
		if($orderInfo->entry_type == 'license')
		{
			$updated = oseDB::insert('#__oselic_license_seat',$post);
			
			if($updated)
			{
				$result['success'] = true;
				$result['title'] = JText::_('Done');
				$result['content'] = JText::_("Success");
			}
			else
			{
				$result['success'] = false;
				$result['title'] = JText::_('Error');
				$result['content'] = JText::_("Error: License Seat Addon Fail");
			}
		}
		else
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Success");
		}
		
		
		return $result;
	}
}
?>