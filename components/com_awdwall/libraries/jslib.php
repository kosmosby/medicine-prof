<?php
/**
 * @ version 2.4
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
class JsLib
{
	public function getAboutUs($userId)
	{
		$jspath = JPATH_ROOT .DS . 'components' . DS . 'com_community';
		require_once($jspath . DS . 'libraries' . DS . 'core.php');
		require_once($jspath . DS . 'helpers' . DS . 'friends.php');
		require_once($jspath . DS . 'libraries' . DS .'template.php');
		require_once($jspath . DS . 'models' . DS .'profile.php');
		
		$tmpl	= new CTemplate();
		$data	= new stdClass();
		$model 	= new CommunityModelProfile();
		$data->profile	= $model->getViewableProfile($userId);
		$profileField	= $data->profile['fields'];
		CFactory::load( 'helpers' , 'linkgenerator' );
		CFactory::load( 'helpers' , 'phone' );
		$my				= CFactory::getUser();
		$config			=& CFactory::getConfig();
		
		$userid	=  JRequest::getVar('userid', $my->id);
		$user	= CFactory::getUser($userid);
		// Allow search only on profile with type text and not empty
		foreach($profileField as $key => $val)
		{

			foreach($profileField[$key] as $pKey => $pVal)
			{
				$field	=& $profileField[$key][$pKey];								

				// Remove this info if we don't want empty field displayed
				if( !$config->get('showemptyfield') && ( empty($field['value']) && $field['value']!="0") )
				{
					unset( $profileField[$key][$pKey] );
					
				}
				else
				{
					if(!empty($field['value']) || $field['value']=="0" )
					{
						switch($field['type'])
						{
							case 'text':
								if(cValidateEmails($field['value']))
								{
									$profileField[$key][$pKey]['value'] = cGenerateEmailLink($field['value']);
								}
								else if (cValidateURL($field['value']))
								{
									$profileField[$key][$pKey]['value'] = cGenerateHyperLink($field['value']);
								}
								else if(!cValidatePhone($field['value']) && !empty($field['fieldcode']))
								{
									$profileField[$key][$pKey]['searchLink'] = CRoute::_('index.php?option=com_community&view=search&task=field&'.$field['fieldcode'].'='. urlencode( $field['value'] ) );					
								}
								break;
							case 'select':
							case 'singleselect':
							case 'radio':
							case 'country':
								$profileField[$key][$pKey]['searchLink'] = CRoute::_('index.php?option=com_community&view=search&task=field&'.$field['fieldcode'].'='. urlencode( $field['value'] ) );
								break;
							default:
								break;
						}
					}
				}
			}
		}
		
		CFactory::load( 'libraries' , 'profile' );		
		$tmpl->set( 'profile' , $data->profile );
	//	$tmpl->set( 'isMine' , isMine($my->id, $user->id));
		$tmpl->set( 'isMine' , false);
		return $tmpl->fetch( 'profile.about' );
	}
	
	public function getUserBasicInfo($userId,$fields)
	{
		$db = &JFactory::getDBO();
		
		if($fields != ''){
		$fields=implode(',',$fields);
		$query 	= 'SELECT * FROM #__comprofiler_fields WHERE published = 1 AND pluginid IN (SELECT id FROM #__comprofiler_plugin WHERE published = 1) AND fieldid IN (' . $fields . ')';
		
		$db->setQuery($query);
		$fields = $db->loadObjectList();
		$lines = file_get_contents('components/com_comprofiler/plugin/language/default_language/default_language.php');
		$i = 0;
		if(isset($fields[0])){
		foreach($fields as $o){
			$n = strpos($lines, $o->title);
			if($n != ''){
				$t = stripos($lines, ',', $n);
				$d = stripos($lines,"')",($t+2));
				$fields[$i]->title = substr($lines, $t+2,($d-($t+2)));
			}
			$i++;			
		}		
		$data = array();
		// get final user
		foreach($fields as $fd){
			$x = $fd->tablecolumns;
			if($x != null){
				$query = "SELECT $fd->tablecolumns FROM $fd->table WHERE id= $userId";
				$db->setQuery($query);
				$obj = $db->loadResult();
				$arrTmp = array();
				$arrTmp[] = $obj;
				$arrTmp[] = $fd->title;
				$data[$x] = $arrTmp;
			}else{ 
				if($fd->name == "onlinestatus"){
					$query = "SELECT count(*) FROM #__session WHERE userid= $userId";
					$db->setQuery($query);
					$obj = $db->loadResult();					
					$s = $fd->name;
					$arrTmp = array();
					if($obj)
						$arrTmp[]  = "online";
					else
						$arrTmp[] = "offline";
										
					$arrTmp[] = $fd->title;
					$data[$s] = $arrTmp;
				}
				
				if($fd->name == "connections"){
					$countPendingsToo = true;
					$query = "SELECT COUNT(*)"
							. "\n FROM #__comprofiler_members AS m"
							. "\n LEFT JOIN #__comprofiler AS c ON m.memberid = c.id"
							. "\n LEFT JOIN #__users AS u ON m.memberid = u.id"
							. "\n WHERE m.referenceid = " . (int) $userId
							. "\n AND c.approved = 1 AND c.confirmed = 1 AND c.banned = 0 AND u.block = 0"
							. ( $countPendingsToo ? '' : "\n AND m.pending = 0" )
							. " AND m.accepted = 1"
							;
					$db->setQuery($query);
					$conn = $db->loadResult();
					$arrTmp = array();
					$arrTmp[] = $conn;
					$arrTmp[] = $fd->title;
					$data['connections'] = $arrTmp;					
				}
			}				
		}
		}}
		return $data;
	}
	
	public function isFriend($userFrom, $userTo)
	{
		$db = &JFactory::getDBO();
		// first way
		$query = 'SELECT connection_id FROM #__awd_connection '
				.'WHERE connect_from = ' . (int)$userFrom . ' AND connect_to = ' . (int)$userTo . ' '
				.'AND status = 1 AND pending = 0'
				;
		$db->setQuery($query);
		$result = $db->loadResult();
		if((int)$result > 0){
			return true;
		}else{
			return false;
		}
	}
	
	public function isFriendOfFriend($userFrom, $userTo)
	{
		$db = &JFactory::getDBO();
		// first way
		$query = 'SELECT connect_from, connect_to FROM #__awd_connection '
				.'WHERE (connect_from = ' . (int)$userFrom . ' Or connect_to = ' . (int)$userFrom . ') '
				.'AND (connect_from <> ' . (int)$userTo . ' Or connect_to <> ' . (int)$userTo . ') '
				.'AND status = 1 AND pending = 0'
				;
		$db->setQuery($query);
		$result = $db->loadObjectList();
		//print_r($result);
		//	exit;
		
		if($result){
			return true;
		}else{
			return false;
		}
	}
	
	public function getFriendStatus($userFrom, $userTo)
	{
		$db = &JFactory::getDBO();
		
		// check second way
		$query = 'SELECT pending FROM #__awd_connection inner join #__users ON #__awd_connection.connect_to=#__users.id '
			.'WHERE connect_from = ' . (int)$userFrom . ' AND connect_to = ' . (int)$userTo . ' '			
			;
			$db->setQuery($query);			
		$result = $db->loadResult();
		if((int)$result > 0){
			return true;
		}else{
			return false;
		}
		
	}
	
	public static function countFriends($userId)
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT COUNT(*) FROM #__awd_connection inner join #__users ON #__awd_connection.connect_to=#__users.id '
				.'WHERE connect_from = ' . (int)$userId . ' '
				.'AND status = 1 AND pending = 0'
				;
		$db->setQuery($query);
		return $db->loadResult();		
	}
	
	public static function getAllFriends($userId, $limit)
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT connect_to FROM #__awd_connection inner join #__users ON #__awd_connection.connect_to=#__users.id '
				.'WHERE connect_from = ' . (int)$userId . ' '
				.'AND status = 1 AND pending = 0 LIMIT 0, ' . (int)$limit
				;
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public static function getPendingFriends($userId)
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT COUNT(*) FROM #__awd_connection  inner join #__users ON #__awd_connection.connect_to=#__users.id '
				.'WHERE connect_from = ' . (int)$userId . ' '
				.'AND status = 0 AND pending = 0 '
				;
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	public static function getPendingGroups($userId)
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT COUNT(*) FROM #__awd_groups_members '
				.'WHERE user_id = ' . (int)$userId . ' '
				.'AND status = 2'
				;
		$db->setQuery($query);
		return $db->loadResult();
	}
}