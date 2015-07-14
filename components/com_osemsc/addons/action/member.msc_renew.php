<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberMsc_Renew
{
	public static function getItems($params = array())
	{
		$my = JFactory::getUser();
		
		$member_id = $my->id;
		
		$member = oseRegistry::call('member');
		
		$member->instance($member_id);
		
		$items = array();
		$memAllInfos = $member->getAllOwnedMsc(true,0,'obj');
		
		$db = oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_acl`"
				//." WHERE `published` = 1"
				;
		$db->setQuery($query);
		$objs = oseDB::loadList('obj');
		
		$mscIds = array();
		foreach($objs as $obj)
		{
			$mscIds[] = $obj->id;
		}
		
		foreach($memAllInfos as $key => $memAllInfo)
		{
			if(!in_array($memAllInfo->msc_id,$mscIds))
			{
				unset($memAllInfos[$key]);
				continue;
			}
			$memParams = oseJson::decode($memAllInfo->params);
			
			if( $memParams->payment_mode == 'a' )
			{
				if($memAllInfo->status == 0)
				{
					$items[] = $memAllInfo;
				}
			}
			else
			{
				$items[] = $memAllInfo;
			}
		}
			
		foreach($items as $item)
		{
			$mscInfo = oseRegistry::call('msc')->getInfo($item->msc_id,'obj');
			$item = oseObject::setValue($item,'msc_name',$mscInfo->title);
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
	
	public static function getPaymentMode()
	{
		$msc_id = JRequest::getInt('msc_id',0);
		$msc_option = JRequest::getCmd('msc_option',null);
		
		$msc = oseRegistry::call('msc');
		
		$ext = $msc->getExtInfo($msc_id,'payment');
		$extAdv = $msc->getExtInfo($msc_id,'paymentAdv');
		
		$items = array();
		
		if(oseObject::getValue($ext[$msc_option],'payment_mode','b') == 'a')
		{
			$items[] = array('id'=>1,'value'=>'a','text'=> JText::_('AUTOMATIC_RENEWING'));
		}
		elseif(oseObject::getValue($ext[$msc_option],'payment_mode','b') == 'm')
		{
			$items[] = array('id'=>1,'value'=>'m','text'=> JText::_('MANUAL_RENEWING'));
		}
		else
		{
			if(oseObject::getValue($extAdv[$msc_option],'payment_mode','b') != 'b')
			{
				$itemValue = oseObject::getValue($extAdv[$msc_option],'payment_mode');
				if($itemValue == 'm')
				{
					$itemText = JText::_('MANUAL_RENEWING');
				}
				else
				{
					$itemText = JText::_('AUTOMATIC_RENEWING');
				}
				$items[] = array('id'=>1,'value'=>$itemValue,'text'=> $itemText);
			}
			else
			{
				$items[] = array('id'=>1,'value'=>'a','text'=> JText::_('AUTOMATIC_RENEWING'));
				$items[] = array('id'=>2,'value'=>'m','text'=> JText::_('MANUAL_RENEWING'));
			}
			
		}
		
		$config = oseMscPublic::getConfig('global', 'obj');
		if($config->payment_mode == 'a') 
		{
			$items = array();
			$items[] = array('id'=>1,'value'=>'a','text'=> JText::_('AUTOMATIC_RENEWING'));
		}
		elseif($config->payment_mode == 'm') 
		{
			$items = array();
			$items[] = array('id'=>1,'value'=>'m','text'=> JText::_('MANUAL_RENEWING'));
		}

		$result = array();
		
		if(empty($items))
		{
			$result['total'] = 0;
			$result['results'] = array();
		}
		else
		{
			$result['total'] = count($items);
			$result['results'] = $items;
		}
		
		return $result;
	}
	
	public static function getMscInfo($params = array())
	{
		$result = oseRegistry::call('msc')->runAddonAction('member.msc.getMscInfo');
		
		return $result;
	}
}
?>