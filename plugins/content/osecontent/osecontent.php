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
class plgContentOsecontent extends JPlugin {
	function plgContentOsecontent(& $subject, $params) {
		parent :: __construct($subject, $params);
	}

	function onPrepareContent(& $article, & $params, $limitstart=0) {
		// Replace Content
		$controlMethod= $this->params->def('controlMethod', 'replace');
		$allow_uncat= $this->params->def('allow_uncat', 0);
		$allow_intro= $this->params->def('allow_intro', 0);
		$runPlugin= array();
		$runPlugin['frontpage']= $this->params->def('run_frontpage', 0);
		$runPlugin['catlayout']= $this->params->def('run_catlayout', 0);
		$runPlugin['seclayout']= $this->params->def('run_seclayout', 0);
		// Redirection;
		$redmenuid= $this->params->def('redmenuid', "1");
		$redmessage= $this->params->def('redmessage', "");

		$option= JRequest :: getCmd('option');

		if ($option =='com_k2')
		{
			if($this->params->get('enable_k2Control', false))
			{
				self::checkK2($article,$controlMethod,$allow_intro,$redmenuid,$redmessage);
			}
		}
	}
	function onContentBeforeDisplay($context, &$article, &$params, $limitstart=0) {
		$db= JFactory :: getDBO();
		$user= JFactory :: getUser();
		$mainframe= JFactory :: getApplication();
		// Load Plugin Parameters;
		//$plugin= & JPluginHelper :: getPlugin('content', 'osecontent');
		//$pluginParams= new JParameter($plugin->params);

		// Replace Content
		$controlMethod= $this->params->def('controlMethod', 'replace');
		$allow_uncat= $this->params->def('allow_uncat', 0);
		$allow_intro= $this->params->def('allow_intro', 0);
		$runPlugin= array();
		$runPlugin['frontpage']= $this->params->def('run_frontpage', 0);
		$runPlugin['catlayout']= $this->params->def('run_catlayout', 0);
		$runPlugin['seclayout']= $this->params->def('run_seclayout', 0);
		// Redirection;
		$redmenuid= $this->params->def('redmenuid', "1");
		$redmessage= $this->params->def('redmessage', "");
		// Timing control;
		$timingcontrol= $this->params->def('timingcontrol', 0);
		$time_rest= $this->params->def('time_rest', 0);
		$time_rest_fixed= $this->params->def('time_rest_fixed', 0);
		//Google First Click Free
		$googlebot_free= $this->params->def('googlebot_free', 0);
		//MSN First Click Free
		$msnbot_free= $this->params->def('msnbot_free', 0);
		//Yahoo First Click Free
		$yahoobot_free= $this->params->def('yahoobot_free', 0);
		// Check if we need to run the plugin;
		$dontcontinue = self :: runPluginCheck($runPlugin, $article, $mainframe, $user);

		if ($dontcontinue==true)
		{
			return;
		}
		// load OSE Core functions;
		if (file_exists(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php') && file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ose_cpu'.DS.'define.php'))
		{
			require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		}
		else
		{
			return;
		}
		$option= JRequest :: getCmd('option');

		if ($option =='com_k2')
		{
			if($this->params->get('enable_k2Control', false))
			{
				return self::checkK2($article,$controlMethod,$allow_intro,$redmenuid,$redmessage);
			}
		}
		// Start control now;
		$oseContent = oseRegistry :: call('content');
		$allowtoRead= true;
		$resContentIDs= $oseContent->getRestrictedContent('joomla', 'article');
		$resCatIDs= $oseContent->getRestrictedContent('joomla', 'category');
		$DefMsgtoNonmembers = "Members Only";

		if(in_array($article->catid, $resCatIDs)) {
			$allowtoRead= false;
			$DefMsgtoNonmembers= stripslashes($oseContent->getDefMsgtoNonmembers ('category', $article->catid));
		}

		if($allowtoRead)
		{
			if(in_array($article->id, $resContentIDs)) {
				$allowtoRead= false;
				$DefMsgtoNonmembers= stripslashes($oseContent->getDefMsgtoNonmembers ('article', $article->id));
			}
		}

		$timeresCatObjs = $oseContent->getInstance('msc')->getGuestRestrictedContent('joomla', 'category','obj');
		$timeresConObjs = $oseContent->getInstance('msc')->getGuestRestrictedContent('joomla', 'article','obj');
		$timeres = false;
		$timeresCatIDs = array();
		if (!empty($timeresCatObjs))
		{
			foreach($timeresCatObjs as $timeresCatObj)
			{
				$timeresCatIDs[] = $timeresCatObj->content_id;
			}
		}
		$timeresConIDs = array();
		if (!empty($timeresConObjs))
		{
			foreach($timeresConObjs as $timeresConObj)
			{
				$timeresConIDs[] = $timeresConObj->content_id;
			}
		}
		if(in_array($article->id, $timeresConIDs) || in_array($article->catid, $timeresCatIDs))
		{
			$timeres = true;
		}

		if($timeres==true && $timingcontrol == true && $user->id) {
			$memberInfoList = oseRegistry :: call('member')->getMemberInfo(0, 'obj');
			if (!empty($memberInfoList))
			{	if (count($memberInfoList)==1)
				{
					$memberInfoList=array($memberInfoList);
				}
				foreach ($memberInfoList as $member_info)
				{
					if(date("Y-m-d", strtotime($member_info->start_date)) <= date("Y-m-d", strtotime($article->created)) && date("Y-m-d", strtotime($member_info->expired_date)) >= date("Y-m-d", strtotime($article->created))) {
						$allowtoRead= true;
						break;
					}
					else
					{
						$allowtoRead= false;
					}
				}
			}
		}
		$bot=array(); 
		//Google First Click Free
		if($googlebot_free==true)
		{
			$bot[]='Google'; 
		}
		//MSN First Click Free
		if($msnbot_free==true)
		{
			$bot[]='msnbot'; 
		}
		//Yahoo First Click Free
		if($yahoobot_free==true)
		{
			$bot[]='Yahoo'; 
		}
		if(!empty($bot))
		{
			$bot = implode('|', $bot);
	        if ($this->checkBot($bot, $_SERVER['HTTP_USER_AGENT']))
	        {
	           $allowtoRead = true;
	        } 
		}
		$regex = "#{osetag}(.*?){/osetag}#s";
		$matches = array();
		if($allowtoRead == false) {

			if($controlMethod == 'redirect') {;
				self :: redirect($redmenuid, $redmessage);
			} else {
				if($controlMethod == 'replaceseq' && $user->id)
				{
					$futureDate = $oseContent->getInstance('msc')->getSequentialMessage($type = 'joomla', 'article', $article->id, $user->id,'obj');

					if (empty($futureDate))
					{
						$futureDate = $oseContent->getInstance('msc')->getSequentialMessage($type = 'joomla', 'category', $article->catid, $user->id,'obj');
					}
					if (!empty($futureDate))
					{
						$seqmessage = $this->params->def('seqmessage', "");
						if (!empty($seqmessage))
						{
							$DefMsgtoNonmembers = str_replace('[FUTURETIME]',$futureDate, $seqmessage);
						}
					}
				}
				if($allow_intro == true) {
					if ((isset($article->text) && preg_match($regex, $article->text, $matches)) || (isset($article->introtext) && preg_match($regex, $article->introtext, $matches)))
					{
						$article->introtext = preg_replace($regex,$DefMsgtoNonmembers,$article->introtext);
						$article->text = preg_replace($regex,$DefMsgtoNonmembers,$article->text);
					}else{
						$article->text = JHTML::_('content.prepare', $article->introtext."".$DefMsgtoNonmembers);
						//$article->introtext."<br />".$DefMsgtoNonmembers;
					}
				} else {
					if (isset($article->text) && preg_match($regex, $article->text, $matches))
					{
						$article->text = preg_replace($regex,$DefMsgtoNonmembers,$article->text);
					}else{
						$article->introtext = $article->text = $DefMsgtoNonmembers;
					}	
				}
			}
		}else{
			if (isset($article->introtext) && preg_match_all($regex, $article->introtext, $matches))
			{
				foreach($matches[1] as $match)
				{
					$article->introtext = preg_replace($regex,$match,$article->introtext,1);
				}		
			}
			$matches = array();
			if (isset($article->text) && preg_match_all($regex, $article->text, $matches))
			{
				foreach($matches[1] as $match)
				{
					$article->text = preg_replace($regex,$match,$article->text,1);
				}			
			}				
		}
	}

	function onContentBeforeSave($context, &$article, $isNew) {
		$db= JFactory :: getDBO();
		$user= JFactory :: getUser();
		$mainframe= JFactory :: getApplication();

		if ($mainframe->isAdmin())
		{
			return;
		}

		if (strpos(JURI::current(), 'administrator')>0)
		{
			return;
		}

		// Load Plugin Parameters;
		$allowCreateMSC= $this->params->get('allowCreateMSC');
		$allowEditMSC= $this->params->get('allowEditMSC');

		if (!is_array($allowCreateMSC))
		{
			$allowCreateMSC = array($allowCreateMSC);
		}
		if (!is_array($allowEditMSC))
		{
			$allowEditMSC = array($allowEditMSC);
		}
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$catID		= $data['catid'];
		//$secID = JRequest :: getVar('sectionid', 0, '', 'int');
		// load OSE Core functions;
		require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		// Start control now;
		$allowtoCreate= true;
		$allowtoEdit= true;
		$oseContent = oseRegistry :: call('content');
		$controllingMSCs = $oseContent->getControllingMsc('category', $catID);

		if (empty($controllingMSCs))
		{
			return true;
		}
		$controllingMSCIDs= array();
		foreach ($controllingMSCs as $controllingMSC)
		{
			$controllingMSCIDs[] = $controllingMSC->id;
		}

		$member = oseRegistry::call('member');
		$member->instance($user->id);
		$mscs = $member->getAllOwnedMsc(false,1,'obj');
		if(!empty($mscs))
		{
			foreach($mscs as $msc)
			{
				$Mem_mscs[] = $msc->msc_id;
			}
		}else{
			$Mem_mscs = array();
		}

		if ($isNew==true)
		{
			$intersectMSC = array_intersect($controllingMSCIDs, $allowCreateMSC);
			$redmenuid = $this->params->def('redcreatemenuid', 0);
			$redmessage = $this->params->def('redcreatemessage', '');
		}
		else
		{
			$intersectMSC = array_intersect($controllingMSCIDs, $allowEditMSC);
			$redmenuid = $this->params->def('rededitmenuid', 0);
			$redmessage = $this->params->def('rededitmessage', '');
		}

		if (empty($intersectMSC))
		{
			self::redirect($redmenuid, $redmessage);
		}
		else
		{
			$mscs = array_intersect($intersectMSC,$Mem_mscs);
			foreach($mscs as $msc)
			{
				$status = self::getControlStatus($msc, 'category', $catID);
				if(empty($status)){
					//$status = self::getControlStatus('section', $secID);
				}
				$contorlStatus[] = $status;
			}
			//print_r($contorllStatus);exit;
			
			if(!empty($mscs))
			{
				if(!in_array('-1', $contorlStatus))return true;
				self::redirect($redmenuid, $redmessage);

			}else{
				//if(!in_array('1', $contorlStatus))return true;
				self::redirect($redmenuid, $redmessage);
			}
		}
	}
	function runPluginCheck($runPlugin, $article, $mainframe, $user) {
		$mainframe = JFactory::getApplication();
		$menu= $mainframe-> getMenu();
		$option= JRequest :: getCmd('option');
		$view= JRequest :: getCmd("view");
		jimport('joomla.version');
		$version= new JVersion();
		$version= substr($version->getShortVersion(), 0, 3);
		if($version == '1.5') {
			if($user->get('gid') == 24 || $user->get('gid') == 25) {
				return true;
			}
		}
		if($mainframe->isAdmin()) {
			return true;
		}

		if(!in_array($option, array("com_content","com_k2", "com_zoo"))) {
			return true;
		}

		if(!isset($article->id)) {
			return true;
		}
		if($runPlugin['frontpage'] == false) {
			if($menu->getActive() == $menu->getDefault()) {
				$curLink = "index.php".JURI::getInstance()->toString(array('query'));
				$default_menu= $menu->getDefault() ;
				$homeLink = $default_menu->link."&Itemid=".$default_menu->id;
				if ( $homeLink == $curLink || JURI::getInstance()->toString() == JURI::base())
				{
				 	return true;
				}
			}
		}
		if($runPlugin['seclayout'] == false) {
			if($view == "section") {
				return true;
			}
		}
		if($runPlugin['catlayout'] == false) {
			if($view == "category") {
				return true;
			}
		}
		return false;
	}

	function redirect($redmenuid, $redmessage) {
		$mainframe = JFactory::getApplication('SITE');
		$option= JRequest :: getVar('option');
		$Itemid= JRequest :: getVar('Itemid');
		//$plugin = &JPluginHelper::getPlugin('system', 'oserouter');
        $pluginParams = $this->params;
		$sefroutemethod= $pluginParams->get('sefroutemethod');

		if($option == "com_osemsc" || $Itemid == $redmenuid) {
			return;
		}
		if(!empty($redmenuid)) {

			$db= JFactory :: getDBO();
			$query= "SELECT * FROM `#__menu` WHERE `id` = ". (int)$redmenuid;
			$db->setQuery($query);
			$menu= $db->loadObject();

			switch ($sefroutemethod)
				{
					default:
					case 0:
						$redURL= JURI::root().$menu->link."&Itemid=".$menu->id;
					break;
					case 1:
						$redURL= JRoute :: _(JURI::root().$menu->link."&Itemid=".$menu->id);
					break;
					case 2:
						$redURL= JRoute :: _(JURI::root().$menu->alias);
					break;
				}
		} else {
			$redURL= JRoute :: _(JURI::root()."index.php?option=com_osemsc&view=register");
			$redURL= str_replace("&amp;", "&", $redURL);
		}
		$mainframe->redirect($redURL, $redmessage);
	}

	function getControlStatus($msc, $content_type, $content_id)
	{
		$db = oseDB::instance();
		$query = " SELECT status FROM `#__osemsc_content`"
		 		." WHERE  `content_type` = '{$content_type}' AND `content_id` = '{$content_id}' AND `entry_type` ='msc' AND `entry_id` = '{$msc}'";
		$db->setQuery($query);
		return $db->loadResult();
	}

function checkZooCat($category)
	{
		$db= JFactory :: getDBO();
		$user= JFactory :: getUser();
		$mainframe= JFactory :: getApplication();
		// Load Plugin Parameters;

		$plugin = JPluginHelper::getPlugin('content','osecontent');
		$pluginParams = oseJSON::decode($plugin->params);
		$runPlugin= array();
		$runPlugin['frontpage']= $pluginParams->run_frontpage;
		$runPlugin['catlayout']= $pluginParams->run_catlayout;
		$runPlugin['seclayout']= $pluginParams->run_seclayout;
 		$dontcontinue= self::runPluginCheck($runPlugin, $category, $mainframe, $user);
		if ($dontcontinue==true)
		{
			return $category;
		}
		// Replace Content
		$controlMethod= $pluginParams->controlMethod;
		$allow_uncat= $pluginParams->allow_uncat;
		$allow_intro= $pluginParams->allow_intro;

		// Redirection;
		$redmenuid= $pluginParams->redmenuid;
		$redmessage= $pluginParams->redmessage;

		// load OSE Core functions;
		require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');

		$oseContent = oseRegistry :: call('content');
		$allowtoRead= true;
		$resCatIDs= $oseContent->getRestrictedContent('zoo', 'category');

		$DefMsgtoNonmembers= stripslashes($oseContent->getDefMsgtoNonmembers ('category', $category->id,'zoo'));
		$DefMsgtoNonmembers = empty($DefMsgtoNonmembers)?"Members Only":$DefMsgtoNonmembers;
		$childCats = $category->getChildren(true);

		if(in_array($category->id,$resCatIDs))
		{
			$allowtoRead = false;
			$category->description = $category->description.'<br>'.$DefMsgtoNonmembers;

			if(!$allow_intro)
			{
				$category->setChildren(array());
			}
		}
		else
		{
			foreach($childCats as $key => $childCat)
			{
				if(in_array($childCat,$resCatIDs))
				{
					if(!$allow_intro)
					{
						unset($childCats[$key]);
					}
				}
			}
			$category->setChildren($childCats);
		}

		$result = array();
		if($allowtoRead == false) {
			if($controlMethod == 'redirect') {

				self :: redirect($redmenuid, $redmessage);
			} else {

				$result['DefMsgtoNonmembers'] = $DefMsgtoNonmembers;
				$result['allow_intro'] = $allow_intro;
			}
		}

		return $category;
	}


	function checkZooItem($item,$isTaskCategory = false)
	{
		$db= JFactory :: getDBO();
		$user= JFactory :: getUser();
		$mainframe= JFactory :: getApplication();
		$item->controlled =false;
		// Load Plugin Parameters;
		$plugin = JPluginHelper::getPlugin('content','osecontent');
		$pluginParams = oseJSON::decode($plugin->params);
		$runPlugin= array();
		$runPlugin['frontpage']= $pluginParams->run_frontpage;
		$runPlugin['catlayout']= $pluginParams->run_catlayout;
		$runPlugin['seclayout']= $pluginParams->run_seclayout;
 		$dontcontinue = self::runPluginCheck($runPlugin, $item, $mainframe, $user);
		if ($dontcontinue==true)
		{
			return $item;
		}
		// Replace Content
		$controlMethod= empty($controlMethod)?$pluginParams->controlMethod:$controlMethod;
		$allow_uncat= $pluginParams->allow_uncat;
		$allow_intro= $pluginParams->allow_intro;

		// Redirection;
		$redmenuid= $pluginParams->redmenuid;
		$redmessage= $pluginParams->redmessage;

		// load OSE Core functions;
		require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');


		$oseContent = oseRegistry :: call('content');
		$allowtoRead= true;

		$resCatIDs= $oseContent->getRestrictedContent('zoo', 'category');
		$relatedCats = $item->getRelatedCategoryIds(true);

		$resArtIDs= $oseContent->getRestrictedContent('zoo', 'article');
		
		$DefMsgtoNonmembers= stripslashes($oseContent->getDefMsgtoNonmembers ('category', $item->getPrimaryCategoryId(true),'zoo'));
		$DefMsgtoNonmembers = empty($DefMsgtoNonmembers)?"Members Only":$DefMsgtoNonmembers;

		if(count(array_intersect($relatedCats,$resCatIDs)) > 0 || in_array($item->id,$resArtIDs))
		{
			$allowtoRead = false;
			if($isTaskCategory)
			{
				if(!$allow_intro)
				{
					$item = null;
				}
			}
			else
			{
				// task = item
				//$item->direction = $DefMsgtoNonmembers;
				$item->getParams()->set('config.enable_comments', 0);
				$item->getParams()->set('metadata.title', 0);
				if(!$allow_intro)
				{
					//$item = null;
				}

				$item->getType()->__unset('elements');

				echo '<div class="yoo-zoo " id="yoo-zoo"><div class="item">' .
						'<div class="pos-header">' .
						'<h1 class="pos-title"> '.$item->name.' </h1>'
				;
				echo  '</div>' .
						'<div>'.$DefMsgtoNonmembers.'</div>';
				echo '</div></div>';
				$item->name = null;
			}
		}
		else
		{

		}

		if($allowtoRead == false) {
			$item->controlled = true;
			if($controlMethod == 'redirect') {
				$result['controlMethod'] = 'redirect';
				self :: redirect($redmenuid, $redmessage);
			} else {
				$result['controlMethod'] = 'replace';
				$result['DefMsgtoNonmembers'] = $DefMsgtoNonmembers;
				$result['allow_intro'] = $allow_intro;
			}
		}
		$result ['allowtoRead'] = $allowtoRead;
		return $item;
	}

	function checkK2($article,$controlMethod,$allow_intro,$redmenuid,$redmessage)
	{
		// load OSE Core functions;
		require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		// Start control now;
		$view = JRequest :: getCmd('view');
		$task = JRequest :: getCmd('task');
		if(!in_array($view,array('item','itemlist','latest'))) return $article;


		$oseContent = oseRegistry :: call('content');
		$allowtoRead= true;

		if($task == 'category')
		{
			if($article instanceof TableK2Category)
			{
				//return $article;
				$resCatIDs= $oseContent->getRestrictedContent('k2', 'category');
				if(in_array($article->id, $resCatIDs)) {
					$allowtoRead= false;
					$DefMsgtoNonmembers= stripslashes($oseContent->getDefMsgtoNonmembers ('category', $article->id, 'k2'));
				}
			}
		}
		else
		{
			$resContentIDs= $oseContent->getRestrictedContent('k2', 'article');
			if(in_array($article->id, $resContentIDs)) {
				$allowtoRead= false;
				$DefMsgtoNonmembers= stripslashes($oseContent->getDefMsgtoNonmembers ('article', $article->id, 'k2'));
			}
			$resCatIDs= $oseContent->getRestrictedContent('k2', 'category');
			if(in_array($article->catid, $resCatIDs)) {
				$allowtoRead= false;
				$DefMsgtoNonmembers= stripslashes($oseContent->getDefMsgtoNonmembers ('category', $article->catid, 'k2'));
			}
		}
		$DefMsgtoNonmembers = empty($DefMsgtoNonmembers)?"Members Only":$DefMsgtoNonmembers;
		//print_r($article);exit;
		if($allowtoRead == false) {

			if($controlMethod == 'redirect') {
				self :: redirect($redmenuid, $redmessage);
			} else {
				if(isset($article->gallery))
				{
					$article->gallery = null;
				}
				if(isset($article->video))
				{
					unset($article->video);
				}
				if($allow_intro == true) {
					if (strstr($article->introtext, $DefMsgtoNonmembers)==false)
					{
					//$article->introtext = $article->introtext."<br />".$DefMsgtoNonmembers;
					$article->fulltext = $article->introtext."<br />".$DefMsgtoNonmembers;
					$article->text = $article->introtext."<br />".$DefMsgtoNonmembers;
					}
				} else {
					$article->text = '';
					$article->introtext = $DefMsgtoNonmembers;
					$article->fulltext = '';
				}
			}
		}

		//return $article;
	}
	
	private function checkBot($crawlers, $userAgent)
    {
        $isCrawler = (preg_match("/$crawlers/", $userAgent) > 0);
        return $isCrawler;
    }  
}

?>