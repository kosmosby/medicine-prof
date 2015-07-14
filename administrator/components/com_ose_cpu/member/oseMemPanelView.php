<?php
/**
  * @version     4.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence {@link
http://www.opensource-excellence.co.uk}
  * @author        EasyJoomla {@link http://www.easy-joomla.org
Easy-Joomla.org}
  * @author        SSRRN {@link http://www.ssrrn.com}
  * @author        Created on 15-Sep-2008
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- ... Open Source Excellence
*/

defined('_JEXEC') or die("Direct Access Not Allowed");

class oseMemPanelView
{
	function displayMember($addon_type = 'member')
	{
		$db = oseDB::instance();

		$com = OSECPU_PATH_JS.'/com_ose_cpu/extjs';

		oseHTML::script($com."/fileupload/field.js",'1.5');
		oseHTML::stylesheet($com.'/fileupload/field.css','1.5');

		$result = array();
		$result['layout'] = 'member';

		$user = JFactory::getUser();

		$member = oseRegistry::call('member');
		$member->instance($user->id);

		if($addon_type == 'member')
		{
			$where = array();
			$where[] = "type LIKE 'member_%'";
			$where[] = '`frontend` = 1';
			$where[] = '`frontend_enabled` = 1';
			$where[] = '`action` = 1 ';

			$where = oseDB::implodeWhere($where);
			$query = " SELECT * FROM `#__osemsc_addon`"
					. $where
					." ORDER BY ordering ASC "
					;
			$db->setQuery($query);

			$objs = oseDB::loadList('obj','name');
		}
		else
		{
			$objs = oseMscAddon::getAddonList($addon_type,false,null,'obj','name');
		}

		//$objs = oseMscAddon::getAddonList($addon_type,false,null,'obj','name');

		$memInfos = $member->getAllOwnedMsc(false,1,'obj');


		if(count($memInfos) > 0)
		{
			$memberships = $member->getMemberOwnedMscInfo(false,1,'obj');
			$payment = oseRegistry::call('payment');
			$order_ids = array();
			foreach($memberships as $membership)
			{
				$memParams = oseJson::decode($membership->params);
				if($memParams->payment_mode == 'a')
				{
					$orderInfo = $payment->getOrder(array("`order_id`=".$db->Quote($memParams->order_id).""));

					if(oseObject::GetValue($orderInfo,'order_status') == 'confirmed')
					{
						$order_ids[$memParams->order_id] = $db->Quote($memParams->order_id);
					}

				}

			}

			if(count($order_ids) < 1 )
			{
				unset($objs['msc_cancel']);
				unset($objs['creditcardupdate']);
			}

			$memAllInfos = $member->getAllOwnedMsc(true,0,'obj');
			$renew = false;
			foreach($memAllInfos as $memAllInfo)
			{
				$memParams = oseJson::decode($memAllInfo->params);

				if( $memParams->payment_mode == 'a' )
				{
					if($memAllInfo->status == 0)
					{
						$renew = true;
					}
				}
				else
				{
					$renew = true;
				}
			}

			if(!$renew)
			{
				unset($objs['msc_renew']);
			}


			//oseExit($objs['direcotry']);
			if(isset($objs['directory']))
			{
				oseHTML::script(oseMscConfig::generateGmapScript(),'1.5');

				oseHTML::script($com."/gmap/panel.js",'1.5');
			}

			$result['addons'] = $objs;
			$result['tpl'] = 'master';
		}
		else
		{
			$query = " SELECT * FROM `#__osemsc_member` AS mem"
					." WHERE mem.member_id = {$user->id}"
					." LIMIT 1"
					;
			$db->setQuery($query);

			$item = oseDB::loadItem();
			if(empty($item))
			{
				$result['layout'] = 'default';

				if($addon_type == 'member_user')
				{
					$result['addons'] = array('juser' => $objs['juser']);
				}
				else
				{
					$result['addons'] = array();
				}

				$result['tpl'] = '';
			}
			else
			{
				unset($objs['msc']);
				unset($objs['directory']);
				unset($objs['company']);
				unset($objs['licuser']);
				unset($objs['msc_cancel']);
				unset($objs['creditcardupdate']);
				$result['addons'] = $objs;
				$result['tpl'] = 'expired';
				//if($addon_type == 'member_user')	oseExit($result);
			}
		}

		return $result;
	}

	function displayPayment()
	{
		return true;
	}

	function displayMemberAuthor()
	{
		$db = oseDB::instance();

		///$com = OSECPU_PATH_JS.'/com_ose_cpu/extjs';

		//oseHTML::script($com."/fileupload/field.js",'1.5');
		//oseHTML::stylesheet($com.'/fileupload/field.css','1.5');

		$result = array();
		$result['layout'] = 'member';

		$user = JFactory::getUser();

		$member = oseRegistry::call('member');
		$member->instance($user->id);

		$status = $member->getUserInfo('obj');

		/*
		$query = " SELECT muv.*,muv.block AS user_status,luv.* "
				." FROM `#__osemsc_userinfo_view` AS muv "
				." LEFT JOIN `#__oselic_userinfo_view` AS luv ON luv.user_id = muv.user_id"
				." WHERE luv.user_id = {$user->id}"
				;

		$db->setQuery($query);

		$item = oseDB::loadItem('obj');
		*/

		//$objs = oseMscAddon::getAddonList($addon_type,false,null,'obj','name');
		$where = array();
		$where[] = "type LIKE 'member_%'";
		$where[] = 'frontend = 1';
		$where[] = 'frontend_enabled = 1';
		$where[] = ' action != "0" ';

		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `#__osemsc_addon`"
				. $where
				." ORDER BY ordering ASC "
				;
		$db->setQuery($query);

		$objs = oseDB::loadList('obj','name');
		//oseExit($db->getQuery());
		$memInfos = $member->getAllOwnedMsc(false,1,'obj');

		if(count($memInfos) > 0)
		{
			$memberships = $member->getMemberOwnedMscInfo(false,1,'obj');

			$payment = oseRegistry::call('payment');
			$order_ids = array();
			foreach($memberships as $membership)
			{
				$memParams = oseJson::decode($membership->params);
				if($memParams->payment_mode == 'a')
				{
					$orderInfo = $payment->getOrder(array("`order_id`=".$db->Quote($memParams->order_id).""));
					if(oseObject::GetValue($orderInfo,'order_status') == 'confirmed')
					{
						$order_ids[$memParams->order_id] = $db->Quote($memParams->order_id);
					}

				}

			}

			if(count($order_ids) < 1 )
			{
				unset($objs['msc_cancel']);
			}

			$memAllInfos = $member->getAllOwnedMsc(true,0,'obj');
			$renew = false;
			foreach($memAllInfos as $memAllInfo)
			{
				$memParams = oseJson::decode($memAllInfo->params);

				if( $memParams->payment_mode == 'a' )
				{
					if($memAllInfo->status == 0)
					{
						$renew = true;
					}
				}
				else
				{
					$renew = true;
				}
			}

			if(!$renew)
			{
				unset($objs['msc_renew']);
			}

			//$objs['msc'] = 'msc';
			$result['addons'] = $objs;
			$result['tpl'] = 'master';
		}
		else
		{
			$query = " SELECT * FROM `#__osemsc_member` AS mem"
					." WHERE mem.member_id = {$user->id}"
					." LIMIT 1"
					;
			$db->setQuery($query);

			$item = oseDB::loadItem();
			if(empty($item))
			{
				$result['layout'] = 'default';

				$result['addons'] = array(
					'juser' => $objs['juser']
					,'billinginfo' => $objs['billinginfo']
				);

			}
			else
			{
				$result['addons'] = array();
				$result['addons']['juser'] = $objs['juser'];
				$result['addons']['billinginfo'] = $objs['billinginfo'];
				$result['addons']['msc_renew'] = $objs['msc_renew'];
				$result['addons']['join_history'] = $objs['join_history'];
				$result['tpl'] = 'expired';
				//if($addon_type == 'member_user')	oseExit($result);
			}
		}
		//oseExit($result);
		return $result;
	}
}
?>
