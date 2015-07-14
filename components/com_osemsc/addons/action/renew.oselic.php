<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewOSELIC
{
	public static function renew($params)
	{
		$result = array();

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
		
		//oseExit($params);
		$db = oseDB::instance();
		$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		$order_id = $params['order_id'];
		$order_item_id = $params['order_item_id'];
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Renew Msc: No Msc ID");
			return $result;
		}
		
		$query = " SELECT * FROM `#__osemsc_order`"
				." WHERE `order_id` = '{$order_id}'"
				;
		$db->setQuery($query);
		$order = oseDB::loadItem('obj');
		$oParams = oseJson::decode($order->params);
		//$msc_option = oseObject::getValue($oParams,'msc_option');
		
		$query = " SELECT * FROM `#__osemsc_order_item`"
				." WHERE `order_id` = '{$order_id}'"
				;
		$db->setQuery($query);
		$order_item = oseDB::loadItem('obj');
		$iParams = oseJson::decode($order_item->params);
		$msc_option = $iParams->msc_option;
		
		if(oseGetValue($oParams,'isLicensee',0))
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_("Done");
			
			return $result;
		}
		
		$query = " SELECT * FROM `#__osemsc_ext`"
				." WHERE `id` = '{$msc_id}' AND `type` = 'oselic'"
				;
		$db->setQuery($query);
		$data = oseDB::loadItem('obj');
		$data = oseJson::decode($data->params);
		$data = oseObject::getValue($data,$msc_option,array());
		
		if( oseObject::getValue($data,'enable_license') )
		{
			oseRegistry :: register('user2', 'user2');
			
			oseRegistry :: register('lic', 'lic');
			oseRegistry :: register('email', 'email');
			oseRegistry :: register('locale', 'locale');
			$user = oseCall('user2')->instance( $member_id,'lic');
			$user->join( $data->license_id );
			
			$query = " SELECT * FROM `#__oselic_type_license`"
			." WHERE `user_id` = '{$member_id}'"
			." ORDER BY `id` DESC"
			." LIMIT 1"
			;
			$db->setQuery($query);
			$license = oseDB::loadItem('obj');
			$oParams->license_id = $license->id;

			$vals = array();
			$vals['order_id'] = $order_id;
			$vals['params'] = oseJson::encode($oParams);
			oseDB::update('#__osemsc_order','order_id',$vals);
			
			if( oseObject::getValue($data,'member_expiry_mode') == 1 )
			{
				$query = " SELECT * FROM `#__osemsc_member`"
				." WHERE `msc_id` = '{$msc_id}' AND `member_id` = '{$member_id}'"
				;
				$db->setQuery($query);
				$memInfo = oseDB::loadItem('obj');
					
				$vals = array();
				$vals['id'] = $license->id;
				$vals['expiry_date'] = $memInfo->expired_date;
				oseDB::update('#__oselic_type_license','id',$vals);
			}
			elseif( oseObject::getValue($data,'member_expiry_mode') == 2 )
			{
				$vals = array();
				$vals['id'] = $license->id;
				$vals['expiry_date'] = oseObject::getValue($data,'expiry_date');
				oseDB::update('#__oselic_type_license','id',$vals);
			}
		}
		

		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");

		return $result;

	}

public static function activate($params)
	{
		return self::renew($params);
	}
}
?>