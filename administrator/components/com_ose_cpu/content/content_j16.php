<?php
defined('_JEXEC') or die(";)");
require_once(dirname(__FILE__).DS.'content_j15.php');
class oseContent_J16 extends oseContent_J15
{
	function getRestrictedContent($type = 'joomla', $content_type)
	{
		$user =JFactory::getUser();

		if($user->guest)
		{
			$objs = $this->getInstance('msc')->getGuestRestrictedContent($type, $content_type,'obj');

		}
		elseif(isset($user->groups['Super Users']))
		{
			$objs =  array();
		}
		else
		{
			$objs = $this->getInstance('msc')->getMemberRestrictedContent($type, $content_type, $user->id,'obj');
			$aobjs = $this->getInstance('msc')->getMemberAccessContent($type, $content_type, $user->id,'obj');
		}
		$db = oseDB::instance();

		//oseExit($db->_sql);

		$values = array();
		if (!empty($objs))
		{
			foreach($objs as $obj)
			{
				$values[] = $obj->content_id;
			}
		}
		
		if (!empty($aobjs))
		{
			$avalues = array();
			foreach($aobjs as $obj)
			{
				$avalues[] = $obj->content_id;
			}
			$values = array_diff($values,$avalues);
		}
		return $values;
	}

	function getGuestRestrictedContent($type = 'joomla', $content_type)
	{
		$objs = $this->getInstance('msc')->getGuestRestrictedContent($type, $content_type,'obj');

		$values = array();

		foreach($objs as $obj)
		{
			$values[] = $obj->content_id;
		}

		return $values;
	}

	function getMemberRestrictedContent($type = 'joomla', $content_type, $member_id)
	{
		$objs = $this->getInstance('msc')->getMemberRestrictedContent($type, $content_type, $user->id,'obj');
		
		$values = array();

		foreach($objs as $obj)
		{
			$values[] = $obj->content_id;
		}

		return $values;
	}
	function getControllingMsc($content_type, $content_id, $com_type=null)
	{
		$db = oseDB::instance();
		$com_type = (empty($com_type))?"joomla":$com_type;
		$query =" SELECT acl.* FROM `#__osemsc_content` AS con " .
				" INNER JOIN `#__osemsc_acl` AS acl ON con.entry_id = acl.id " .
				" WHERE con.`entry_type` ='msc' AND con.`type` = '{$com_type}' AND con.`status` !=0 " .
				" AND con.`content_type` = '{$content_type}' AND con.`content_id` = '{$content_id}' "
				." ORDER BY acl.ordering ASC";
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	function getDefMsgtoNonmembers ($content_type, $content_id, $com_type=null)
	{
		$db = oseDB::instance();
		$controllingMSC = self:: getControllingMsc($content_type, $content_id, $com_type);
		if (!empty($controllingMSC))
		{
			$controllingMSC = $controllingMSC[0];
			$query = " SELECT `params` FROM `#__osemsc_ext` WHERE `type` = 'msc' AND `id` = ". (int)$controllingMSC->id;
			$db->setQuery($query);
			$result = $db->loadResult();
			$result = oseJSON::decode($result);
			return oseObject::getValue($result,'restrict');;
		}
		else
		{
			return false;
		}
	}
}