<?php
defined('_JEXEC') or die(";)");


class oseContent_J15 extends oseContent
{
	function getRestrictedContent($type = 'joomla', $content_type)
	{
		$user =JFactory::getUser();

		if($user->guest)
		{
			$objs = $this->getInstance('msc')->getGuestRestrictedContent($type, $content_type,'obj');

		}
		elseif($user->get('gid') == 24 || $user->get('gid') == 25)
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
	
	function getRestrictedContentOwner($type = 'joomla', $content_type)
	{
		$user =JFactory::getUser();
		$contentMsc = $this->getInstance('msc');
		if($user->guest)
		{
			$objs = $contentMsc->getGuestRestrictedContent($type, $content_type,'obj');

		}
		elseif($user->get('gid') == 24 || $user->get('gid') == 25)
		{
			$objs =  array();
		}
		else
		{
			$objs = $contentMsc->getMemberRestrictedContent($type, $content_type, $user->id,'obj');

		}
		$db = oseDB::instance();

		//oseExit($db->_sql);

		$arr = array();
		if (!empty($objs))
		{
			foreach($objs as $obj)
			{
				$arr[$obj->content_id] = empty($arr[$obj->content_id])?array():$arr[$obj->content_id];
				array_push($arr[$obj->content_id],$obj->entry_id);
			}
		}
		
		return $arr;
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
			return (!empty($result->restrict))?$result->restrict:JText::_("This is members only. Please subscribe a membership to continue.");
		}
		else
		{
			return false;
		}
	}
	function checkAccess($type = 'joomla', $content_type, $content_id)
	{
			$content_ids= self::getRestrictedContent($type, $content_type);
			$redmessage = '';
			$mainframe = &JFactory::getApplication();

			if(in_array($content_id, $content_ids)) {
				if (file_exists(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'oserouter.php'))
				 {
					$plugin = &JPluginHelper::getPlugin('system', 'oserouter');
        			$pluginParams = new JParameter($pluginParams->params);
					$redmenuid= $pluginParams->params->def('redmenuid', '0');
					$redmessage= $pluginParams->params->def('redmessage', 'Member Only!');
					if (!empty($redmenuid))
					{
						$db= & JFactory :: getDBO();
						$query= "SELECT link FROM `#__menu` WHERE `id` = ". (int)$redmenuid;
						$db->setQuery($query);
						$result= $db->loadResult();
						$redURL = $result."&Itemid=".$redmenuid;
					}
					else
					{
						$redURL ='index.php?option=com_osemsc&view=register';
					}
				 }
				 else
				 {
						$redURL ='index.php?option=com_osemsc&view=register';
				 }

				$redmessage=(!empty($redmessage))?$redmessage:"Members only";
				$redirect= str_replace("&amp;", "&", JRoute::_($redURL));
				$mainframe->redirect($redirect, $redmessage);
			}
	}
}
?>
