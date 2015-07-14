<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
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
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/

defined('_JEXEC') or die("Direct Access Not Allowed");
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
jimport('joomla.plugin.plugin');

class plgSystemOsemscaec extends JPlugin {
	var $_db= null;
	function plgSystemOsemscaec(& $subject, $config) {
		parent :: __construct($subject, $config);
	}
	function onAfterInitialise() {
		$mainframe = JFactory :: getApplication('SITE');
		//$plugin= & JPluginHelper :: getPlugin('system', 'osemscaec');
		//$pluginParams= new JParameter($plugin->params);
		$pluginParams = $this->params;
		$enablenotify = $pluginParams->get('enablenotify');
		$enableaec = $pluginParams->get('enableaec');
		$removeFreeMem = $pluginParams->get('removeFreeMem');
		$unpublishExpMemArts = $pluginParams->get('unpublishExpMemArts');
		$publishMemArts = $pluginParams->get('publishMemArts');
		$unpublishExpMemK2Arts = $pluginParams->get('unpublishExpMemK2Arts');
		$publishMemK2Arts = $pluginParams->get('publishMemK2Arts');
		$user = JFactory::getUser();
		if($mainframe->isAdmin()) {
			return; // Dont run in admin
		}
		$cronmode = $pluginParams->get('cronmode');
		$cronjob = JRequest::getVar('cronjob');
		if ($cronmode == true && $cronjob!='osemscaec')
		{
			return;
		}elseif($cronmode == true){
			$db = JFactory::getDBO();
			$query= "UPDATE `#__osemsc_member` SET `status` = 1 WHERE '{$current_date}' BETWEEN `start_date` and `expired_date` AND `status` = 0";
			$db->setQuery($query);
			$db->query();
		}
		if (file_exists(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php') && file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ose_cpu'.DS.'define.php') && !file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'installer.dummy.ini'))
		{
			require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		}
		else
		{
			return false;
		}
		$current_date= oseHTML :: getDateTime();
		if ($enablenotify == true)
		{
			self :: notifying();
			self :: inviting();
		}
		
		if ($enableaec == true)
		{
		self :: checkExpiredMember($current_date);
		}
		if($removeFreeMem)
		{
			self:: removeFreeMems();
		}
		if($unpublishExpMemArts)
		{
			self::  changeMemArtStatus(false);
		}
		if($publishMemArts)
		{
			self::  changeMemArtStatus(true);
		}
		if($unpublishExpMemK2Arts)
		{
			self::  changeMemK2ArtStatus(false);
		}
		if($publishMemK2Arts)
		{
			self::  changeMemK2ArtStatus(true);
		}
		return true;
	}
	function notifying() {
		$db= JFactory :: getDBO();
		//$plugin= & JPluginHelper :: getPlugin('system', 'osemscaec');
		$pluginParams= $this->params;//new JParameter($plugin->params);
		$intervaldays=array();
		$inttemp= $pluginParams->get('intervaldays', 0);
		if ($inttemp>0)
		{
			$intervaldays[] = $inttemp;
			$field[]='notified';
		}	
		$inttemp= $pluginParams->get('intervaldays2', 0);
		if ($inttemp>0)
		{
			$intervaldays[] = $inttemp;
			$field[]='notified2';
		}
		$inttemp= $pluginParams->get('intervaldays3', 0);
		if ($inttemp>0)
		{
			$intervaldays[] = $inttemp;
			$field[]='notified3';
		}
		$i=0;
		if (empty($intervaldays))
		{
			return;
		}	
		foreach ($intervaldays as $intervalday)
		{
			$objs= self :: searchMembers($intervalday, $field[$i]);
			
			if(!empty($objs))
			{
				foreach($objs as $obj) {
					$msc= oseRegistry :: call('msc');
					$member= oseRegistry :: call('member');
					$email= $member->getInstance('email');
					$ext= $msc->getExtInfo($obj->msc_id, 'msc', 'obj');
					$emailTempDetail= $email->getDoc($ext->notification, 'obj');
					$variables= $email->getEmailVariablesCancel($obj->member_id, $obj->msc_id);
					$emailParams= $email->buildEmailParams($emailTempDetail->type);
					$emailDetail= $email->transEmail($emailTempDetail, $variables, $emailParams);
					$query= "UPDATE `#__osemsc_member` SET `{$field[$i]}` = '1' WHERE msc_id= {$obj->msc_id} AND member_id= {$obj->member_id} AND status = 1";
					$db->setQuery($query);
					if(!$db->query()) {
						return false;
					}
					if (!empty($emailDetail->subject) && !empty($emailDetail->body))
					{	
						$email->sendEmail($emailDetail, $obj->email);
					}
				}
			}
			$i++;
		}
	}
	function inviting() {
		$db= JFactory :: getDBO();
		//$plugin= & JPluginHelper :: getPlugin('system', 'osemscaec');
		$pluginParams= $this->params;//new JParameter($plugin->params);
		$invitation = $pluginParams->def('intervaldays4', 0);
		if(!empty($invitation))
		{
			$date = oseHTML::getDateTime();
				$query= " SELECT mem.msc_id, mem.member_id, mem.params,mem.expired_date, mem.notified,muv.* FROM `#__osemsc_member` AS mem " .
				" INNER JOIN `#__osemsc_userinfo_view` AS muv ON muv.user_id = mem.member_id " .
				" WHERE DATEDIFF('{$date}', mem.expired_date ) BETWEEN {$invitation} AND {$invitation}+1 AND (mem.invitation <> 1 OR mem.invitation IS NULL) AND mem.status = 0 AND mem.eternal != 1 AND muv.primary_contact = 1";
			$db->setQuery($query);//echo $query;exit;
			$objs= $db->loadObjectList();
			if (empty($objs))
			{
				return; 
			}
			foreach ($objs as $obj)
			{
				$msc= oseRegistry :: call('msc');
				$member= oseRegistry :: call('member');
				$email= $member->getInstance('email');
				$ext= $msc->getExtInfo($obj->msc_id, 'msc', 'obj');
				if(empty($ext->invitation))
				{
					continue;
				}
				$emailTempDetail= $email->getDoc($ext->invitation, 'obj');			
				$variables= $email->getEmailVariablesCancel($obj->member_id, $obj->msc_id);
				$emailParams= $email->buildEmailParams($emailTempDetail->type);
				$emailDetail= $email->transEmail($emailTempDetail, $variables, $emailParams);
				
				$query= "UPDATE `#__osemsc_member` SET `invitation` = '1' WHERE msc_id= {$obj->msc_id} AND member_id= {$obj->member_id} AND status = 0";
				$db->setQuery($query);
				if(!$db->query()) {
					return false;
				}
				$email->sendEmail($emailDetail, $obj->email);
			}
		}
	}
	private function searchMembers($intervaldays,$field) {
		//$intervaldays++;
		$db= JFactory :: getDBO();
		$date = oseHTML::getDateTime();
				$query= " SELECT mem.msc_id, mem.member_id, mem.params,mem.expired_date, mem.notified,muv.* FROM `#__osemsc_member` AS mem " .
				" INNER JOIN `#__osemsc_userinfo_view` AS muv ON muv.user_id = mem.member_id " .
				" WHERE DATEDIFF( mem.expired_date, '{$date}' ) BETWEEN {$intervaldays} AND {$intervaldays}+1 AND (mem.{$field} <> 1 OR mem.{$field} IS NULL) AND mem.status = 1 AND muv.primary_contact = 1";
		$db->setQuery($query);
		$member_ids= $db->loadObjectList();
		if(!empty($member_ids))
		{
			foreach($member_ids as $k => $member)
			{
				$memParams = oseJson::decode($member->params);
				if(oseObject::getValue($memParams,'payment_mode','m') == 'm')
				{
					$dDiff = strtotime($member->expired_date) -  strtotime(oseHtml::getDateTime());
					if( $dDiff >= ($intervaldays * 86400) && $dDiff < ($intervaldays * 86400+86400))
					{
						continue;
					}
					else
					{
						unset($member_ids[$k]);
					}
				}
				else
				{
					$dDiff = strtotime($member->expired_date) -  strtotime(oseHtml::getDateTime());
					if( $dDiff >= ($intervaldays * 86400+86400) && $dDiff < ($intervaldays * 86400+86400*2))
					{
						continue;
					}
					else
					{
						unset($member_ids[$k]);
					}
				}
			}
		}
		
		return $member_ids;
	}
	function checkExpiredMember($current_date) {
		$msc= oseRegistry :: call('msc');
		$member= oseRegistry :: call('member');
		$pOrder = oseRegistry :: call('payment')->getInstance('Order');
		$db= JFactory :: getDBO();
		$query= " SELECT * from `#__osemsc_member` WHERE expired_date < '{$current_date}' AND eternal = 0 AND status = 1 AND expired_date!='0000-00-00 00:00:00'";
		$db->setQuery($query);
		$objs= $db->loadObjectList();

		if(count($objs) < 1) {
			return true;
		}
		foreach($objs as $obj) {
			$msc_id= $obj->msc_id;
			$member_id= $obj->member_id;
			$member->instance($member_id);
			$memMscInfo = $member->getMembership($msc_id,'obj');
			$memParams = oseJson::decode($memMscInfo->params);
			if(empty($memParams))
			{
				continue;
			}
			$orderInfo = $pOrder->getOrder(array("`order_id`='{$memParams->order_id}'"),'obj');
			if($orderInfo->payment_method == 'beanstream' && $orderInfo->payment_mode == 'a')
			{
				$tranResult = $pOrder->BeanStreamQueryTransaction($orderInfo);
				// not test mode & recurring
				if($tranResult['authCode'] != 'TEST' && $tranResult['trnType'] == 'R')
				{
					if($tranResult['trnApproved'] == 1)
					{
						$pOrder->confirmOrder($orderInfo->order_id);
						continue;
					}
				}
			}
			$info= $member->getUserInfo('obj');
			$params = $member->getAddonParams($msc_id,$member_id,0,$params = array());
			$updated= $msc->runAddonAction('member.msc.expireMsc', $params);
			if(!$updated['success']) {
				// Email to Admin
			}
		}
		return $updated['success'];
	}

	function fix_vm_user_type() {
		$db= JFactory :: getDBO();
		$query= "SELECT * FROM #__users WHERE usertype=''";
		$db->setQuery($query);
		$results= $db->loadObjectList();
		foreach($results as $result) {
			switch($result->gid) {
				case 18 :
					$usertype= "Registered";
					break;
				case 19 :
					$usertype= "Author";
					break;
				case 20 :
					$usertype= "Editor";
					break;
				case 21 :
					$usertype= "Publisher";
					break;
				case 23 :
					$usertype= "Manager";
					break;
				case 24 :
					$usertype= "Administrator";
					break;
				case 25 :
					$usertype= "Super Administrator";
					break;
			}
			$query2= "UPDATE `#__users` SET `usertype` = '{$usertype}' WHERE `#__users`.`id` = {$result->id} LIMIT 1 ;";
			$db->setQuery($query2);
			if(!$db->query())
				return '1';
		}
	}
	function removeFreeMems()
	{
		$mainframe = JFactory :: getApplication('SITE');
		$user = JFactory::getUser();
		$db= oseDB :: instance();
		if(empty($user->id)){
			return;
		}

		$member = oseRegistry::call('member');
		$msc= oseRegistry :: call('msc');
		$member->instance($user->id);
		$mscs = $member->getAllOwnedMsc(false,1,'obj');

		if(!empty($mscs))
		{
			foreach($mscs as $obj)
			{
				$Mem_mscs[] = $obj->msc_id;
			}
		}
		else
		{
			return;
		}
		$free = array();
		$paid = array();
		foreach($Mem_mscs as $msc_id)
		{
			$query = "SELECT params FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'payment'";
			$db->setQuery($query);
			$params = oseJson::decode($db->loadResult());
			foreach($params as $param)
			{
				if(!$param->isFree && $param->a3>0)
				{
					$paid[] = $msc_id;
					break;
				}else{
					$free[] = $msc_id;
				}
			}
		}
		$free = array_diff($free,$paid);
		if(!empty($free) && !empty($paid))
		{
			foreach($free as $vaule)
			{
				$params = $member->getAddonParams($vaule,$user->id,0,$params = array());
				$updated= $msc->runAddonAction('member.msc.expireMsc', $params);
			}
		}
	}
	function changeMemArtStatus($publish=null)
	{
		$db= JFactory :: getDBO();
		$query = " SELECT * FROM `#__content` AS c"
				." INNER JOIN `#__osemsc_member_view` AS m"
				." ON c.`created_by` = m.`member_id`"	
				." GROUP BY m.`member_id`";
		$db->setQuery($query);
		$objs = $db->loadObjectList();
		foreach($objs as $obj)
		{
			$query = "SELECT count(*) FROM `#__osemsc_member_view` WHERE `status` = 1 AND `member_id` = ".$obj->member_id;
			$db->setQuery($query);
			$result = $db->loadResult();
			if(empty($result) && $publish== false)
			{
				$query = "UPDATE `#__content` SET `state` = 0 WHERE `created_by` = ".$obj->member_id;
				$db->setQuery($query);
				$db->query();
			}else if(!empty($result) && $publish== true)
			{
				$query = "UPDATE `#__content` SET `state` = 1 WHERE `created_by` = ".$obj->member_id;
				$db->setQuery($query);
				$db->query();
			}
		}		
	}
	
	function changeMemK2ArtStatus($publish=null)
	{
		$db= JFactory :: getDBO();
		$query = " SELECT * FROM `#__k2_items` AS c"
				." INNER JOIN `#__osemsc_member_view` AS m"
				." ON c.`created_by` = m.`member_id`"	
				." GROUP BY m.`member_id`";
		$db->setQuery($query);
		$objs = $db->loadObjectList();
		foreach($objs as $obj)
		{
			$query = "SELECT count(*) FROM `#__osemsc_member_view` WHERE `status` = 1 AND `member_id` = ".$obj->member_id;
			$db->setQuery($query);
			$result = $db->loadResult();
			if(empty($result) && $publish== false)
			{
				$query = "UPDATE `#__k2_items` SET `published` = 0 WHERE `created_by` = ".$obj->member_id;
				$db->setQuery($query);
				$db->query();
			}else if(!empty($result) && $publish== true)
			{
				$query = "UPDATE `#__k2_items` SET `published` = 1 WHERE `created_by` = ".$obj->member_id;
				$db->setQuery($query);
				$db->query();
			}
		}		
	}
}