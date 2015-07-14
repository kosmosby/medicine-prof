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
defined('_JEXEC') or die('Restricted access');
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
jimport('joomla.plugin.plugin');
/**
 * Example User Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.5
 */
class plgSystemOserouter extends JPlugin {
	var $_db= null;
	var $_active= null;
	function plgSystemOserouter(& $subject, $config) {
		jimport('joomla.environment.uri');
		jimport('joomla.application.router');
		$version = new JVersion();
		$version = substr($version->getShortVersion(), 0, 3);
		if ($version<'3.0.0')
		{
			jimport('cms.router.site');
		}	
		else
		{
			require_once(JPATH_SITE.DS."includes".DS."router.php");
		}	
		$user= JFactory :: getUser();
		$uri= JURI :: current();
		parent :: __construct($subject, $config);
	}
	function onAfterInitialise() {}
	function onAfterRoute() {
		$mainframe= JFactory :: getApplication();
		if($mainframe->isAdmin()) {
			return; // Dont run in admin
		}
		if (file_exists(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php') && file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ose_cpu'.DS.'define.php') && !file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'installer.dummy.ini'))
		{
			require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		}
		else
		{
			return false;
		}

		$db= oseDB :: instance();
		$current_date= oseHTML :: getDateTime();
		$pluginParams= $this->params;
		if($pluginParams->get('registrationRedirect', false) == true) {
			self :: registrationRedirect($mainframe);
		}
		if($pluginParams->get('use2columnLayout', false) == true) {
			self :: twocolumnRedirect($mainframe);
		}
		if($pluginParams->get('enable_componentControl', false) == true) {
			$isComponentControlled= self :: isComponentControlled();
			if($isComponentControlled == true) {
				$com_redmenuid= $pluginParams->def('com_redmenuid', null);
				self :: redirect(null,null,$com_redmenuid);
			}
		}
		if($pluginParams->get('enable_menuControl', false) == true) {
			$isMenuControlled= self :: isMenuControlled();
			$user = JFactory::getUser();
			$menu= & JSite :: getMenu();
			$item= $menu->getActive();
			if (empty($item))
			{
				return;
			}
			$oseContent = oseRegistry :: call('content');
			$futureDate = $oseContent->getInstance('msc')->getSequentialMessage($type = 'joomla', 'menu', $item->id, $user->id,'obj');
			$redmessage= $this->params->def('redmessage',null);
			if (!empty($futureDate))
			{
				$DefMsgtoNonmembers = str_replace('[FUTURETIME]',$futureDate, $redmessage);
			}else{
				$DefMsgtoNonmembers = str_replace('[FUTURETIME]','', $redmessage);
			}
			if($isMenuControlled == true) {
				$menu_redmenuid= $pluginParams->def('menu_redmenuid', null);
				self :: redirect(null,$DefMsgtoNonmembers,$menu_redmenuid);
			}
		}
		$option= JRequest :: getCmd('option', null);
		$controller= JRequest :: getCmd('controller', null);
		if($option == 'com_ose_download') {
			if($pluginParams->get('enable_osedownloadControl', false)) {
				self :: checkOSEDownload();
			}
		}
		if($option == 'com_phocadownload') {
			if($pluginParams->get('enable_phocaControl', false)) {
				self :: checkPhocaDownload();
			}
		}
		if($option == 'com_mtree') {
			if($pluginParams->get('enable_mtreeControl', false)) {
				self :: checkMtree();
			}
		}
		if($option == 'com_hwdvideoshare') {
			if($pluginParams->get('enable_hwdvideoshareControl', false)) {
				self :: checkHWD();
			}
		}
		if($option == 'com_hwdmediashare') {
			if($pluginParams->get('enable_hwdmediashareControl', false)) {
				self :: checkHWDMedia();
			}
		}
		if($option == 'com_sobi2') {
			if($pluginParams->get('enable_sobi2Control', false)) {
				self :: checkSobi2();
			}
		}
		if($option == 'com_sobipro') {
			if($pluginParams->get('enable_sobiproControl', false)) {
				self :: checkSobiPro();
			}
		}
		if($option == 'com_rokdownloads') {
			if($pluginParams->get('enable_rokdownloadControl', false)) {
				self :: checkRokdownload();
			}
		}
		if($option == 'com_jdownloads') {
			if($pluginParams->get('enable_jdownloadsControl', false)) {
				self :: checkJdownloads();
			}
		}
		if($option == 'com_ariquiz') {
			if($pluginParams->get('enable_ariquizControl', false)) {
				self :: checkAriquiz($pluginParams);
			}
		}
		if($option == 'com_community') {
			if($pluginParams->get('enable_jomsocialRegRedirect', false)) {
				self :: jomsocialRegRedirect();
			}
			if($pluginParams->get('enable_JomRegRedirect', false)) {
				self :: jomRegRedirect();
			}
		}
		if($option == 'com_eventbooking') {
			if($pluginParams->get('enable_eventbookingControl', false)) {
				self :: checkEventbooking();
			}
		}
		if ($pluginParams->get('force_ssl', false)) {
			$ssl = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on' OR $_SERVER['HTTPS']==true))?true:false;
			$view= JRequest :: getCmd('view', null);
			if ($ssl==false && $option == 'com_osemsc' && $view=='register')
			{
				$menu = &JSite::getMenu();
				$item = $menu->getActive();
				$currentURL = JURI::current();
				$currentURL = str_replace("http", "https", $currentURL);
				$redirecturl= JRoute :: _($currentURL."?option=com_osemsc&view=register&Itemid=".$item->id);
				$redirecturl= str_replace("&amp;", "&", $redirecturl);
				$mainframe ->redirect($redirecturl);
			}
		}
		if($pluginParams->get('force_user_to_member', false)) {
			$delete_users= $pluginParams->get('delete_users', false);
			self :: forceUsertoMember($delete_users);
		}
	}
	function onAfterRender() {
		$mainframe= JFactory :: getApplication();
		if($mainframe->isAdmin()) {
			return;
		}
	}
	function twocolumnRedirect()
	{
		$mainframe= JFactory :: getApplication();
		$option= JRequest :: getVar('option');
		$view= JRequest :: getVar('view');
		$layout= JRequest :: getVar('layout');
		$task= JRequest :: getVar('task');
		if (empty($layout) && $option=="com_osemsc" && $view=="register" && ($task!="login" && $task!="logout"))
		{
			/*
			$redirecturl= "index.php?option=com_osemsc&view=register&layout=twocolumns";
			$db= & JFactory :: getDBO();
			$query= "SELECT id FROM `#__menu` WHERE `link` LIKE '$redirecturl%'";
			$db->setQuery($query);
			$result= $db->loadResult();
			$Itemid=(!empty($result)) ? "&Itemid=".$result : "";
			*/
			jimport( 'joomla.application.menu' );
			$redirecturl = JMenu::getActive();
			$redirect= JRoute :: _($redirecturl."&layout=twocolumns");
			$redirect= str_replace("&amp;", "&", $redirect);
			$mainframe->redirect($redirect);
		}
	}

	function forceUsertoMember($delete_users= false) {
		$mainframe= JFactory :: getApplication('SITE');
		$user= & JFactory :: getUser();
		$db= & JFactory :: getDBO();
		$u= & JURI :: getInstance();
		$vars= $u->getQuery(true);
		$option= JRequest :: getCmd('option');
		$task= JRequest :: getCmd('task');
		$view= JRequest :: getCmd('view');
		$Itemid = JRequest :: getCmd('Itemid');
		if($option == "com_osemsc" || $option == "com_virtuemart" || strstr((string) $u, "osemsc") || strstr((string) $u, "membership")) {
			return true;
		}
		if (in_array($option, array('com_user', 'com_users')))
		{
			return true;
		}
		$redmenuid = $this->params->def('redmenuid', '0');
		if($redmenuid == $Itemid)
		{
			return true;
		}
		$config= oseRegistry :: call('msc')->getConfig('payment', 'obj');
		if($config->enable_poffline && $option == 'com_content' && $view == 'article')
		{
			$article_id = JRequest :: getCmd('id');
			if($article_id == $config->poffline_art_id)
			{
				return true;
			}
		}

		$redirecturl= JRoute :: _("index.php?option=com_osemsc&view=register");
		if($mainframe->isAdmin()) {
			return; // Dont run in admin
		}
		$groups=$user->get('groups');
		if(in_array('7',$groups) || in_array('8',$groups)) {
			return;
		}
		if(!empty($_POST['payment_id'])) {
			return;
		}
		if(!$user->guest) {
			$results= plgSystemOserouter :: is_member($user->id);
			if(!empty($results))
			{
				foreach($results as $result)
				{
					$status[] = $result->status;
				}
			}
			foreach($results as $result)
			{
				$status[] = $result->status;
			}
			if(!empty($results)) {
				if(!in_array('1',$status)) {
					self::redirect(null, JText :: _("Please renew your membership."));
				}
			} else {
				if($delete_users) {
					// delete user
					$userid= $user->id;
					$user->delete();
					$msg= '';
					JRequest :: setVar('task', 'remove');
					JRequest :: setVar('cid', $userid);
					// delete user acounts active sessions
					$mainframe->logout();
					self::redirect(null, JText :: _("Your membership is not activated."));
				} else {
					self::redirect(null, JText :: _("Your membership is not activated."));
				}
			}
		}
	}
	function is_member($user_id) {
		require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		$member= oseRegistry :: call('member');
		$member->instance($user_id);
		return $member->getOwnMsc(0,'obj');
	}

	function redirect($type=null, $message=null,$redmenuid=null) {
		$mainframe= JFactory :: getApplication('SITE');
		$plugin = &JPluginHelper::getPlugin('system', 'oserouter');
        $pluginParams = $this->params;
		$sefroutemethod= $pluginParams->get('sefroutemethod');

		if (empty($type))
		{
			$redmenuid= empty($redmenuid)?$this->params->def('redmenuid', '0'):$redmenuid;
			$redmessage= $this->params->def('redmessage', 'Member Only!');
			$redmessage=(!empty($message))?$message:$redmessage;
			if (!empty($redmenuid))
			{
				$db= & JFactory :: getDBO();
				$query= "SELECT * FROM `#__menu` WHERE `id` = ". (int)$redmenuid;
				$db->setQuery($query);
				$menu= $db->loadObject();
				require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'helpers'.DS.'oseMscPublic.php');
				$redURL = $uri = oseMscPublic::pointedRedirection($sefroutemethod,$menu);
				/*switch ($sefroutemethod)
				{
					default:
					case 0:
						$redURL= $menu->link."&Itemid=".$menu->id;
					break;
					case 1:
						$redURL= JRoute :: _($menu->link."&Itemid=".$menu->id);
					break;
					case 2:
						$redURL= JRoute :: _($menu->alias);
					break;
				}
				if(strpos($redURL,'http') === false && $sefroutemethod != 1)
				{
					$redURL = JURI::root().$redURL;
				}*/
			}
			else
			{
				$redURL =JURI::root().'index.php?option=com_osemsc&view=register';
			}
			$redirect= str_replace("&amp;", "&", JRoute::_($redURL));
			$mainframe->redirect($redirect, $redmessage);
		}
		elseif ($type=="register")
		{
			$db= & JFactory :: getDBO();
			$redmenuid= $this->params->def('redmenuid', '0');
			$Itemid = JRequest :: getCmd('Itemid');
			if($redmenuid == $Itemid)
			{
				return true;
			}
			if (!empty($redmenuid))
			{
				$query= "SELECT * FROM `#__menu` WHERE `id` = ". (int)$redmenuid;
				$db->setQuery($query);
				$menu= $db->loadObject();
				require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'helpers'.DS.'oseMscPublic.php');
				$redURL = $uri = oseMscPublic::pointedRedirection($sefroutemethod,$menu);
				/*switch ($sefroutemethod)
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

				if(strpos($redURL,'http') === false && $sefroutemethod != 1)
				{
					$redURL = JURI::root().$redURL;
				}*/
			}
			else
			{
				$query= "SELECT id FROM `#__menu` WHERE `link` LIKE 'index.php?option=com_osemsc&view=register%'";
				$db->setQuery($query);
				$result= $db->loadResult();
				$Itemid=(!empty($result)) ? "&Itemid=".$result : "";
				$redirect= JURI::root().JRoute :: _("index.php?option=com_osemsc&view=register".$Itemid);
				$redURL= str_replace("&amp;", "&", $redirect);
			}

			$mainframe->redirect($redURL);
		}
	}

	function checkOSEDownload() {
		$db = oseDB::instance();
		$view= JRequest :: getCmd('view', null);
		if($view == 'category') {
			$id= JRequest :: getInt('id', 0);
		}
		if(JRequest :: getInt('download', 0)) {
			$fileId= JRequest :: getInt('download', 0);
			$query= " SELECT * FROM `#__ose_download` "." WHERE id = ".$db->Quote($fileId);
			$db->setQuery($query);
			$obj= oseDB :: loadItem('obj');
			$id= $obj->catid;
		}
		if(isset($id)){
			$content_ids= oseRegistry :: call('content')->getRestrictedContent('osedownload', 'category');
			if(in_array($id, $content_ids)) {
				self :: redirect();
			}
		}
	}
	function checkPhocaDownload() {
		$db = oseDB::instance();
		$view= JRequest :: getCmd('view', null);
		if($view == 'category') {
			$id= JRequest :: getInt('id', 0);
		}
		if(JRequest :: getInt('download', 0)) {
			$fileId= JRequest :: getInt('download', 0);
			$query= " SELECT * FROM `#__phocadownload` "." WHERE id = ".$db->Quote($fileId);
			$db->setQuery($query);
			$obj= oseDB :: loadItem('obj');
			$id= $obj->catid;
		}
		$content_ids= oseRegistry :: call('content')->getRestrictedContent('phoca', 'category');
		if(in_array($id, $content_ids)) {
			self :: redirect();
		}
	}
	function isComponentControlled() {
		$content_ids= oseRegistry :: call('content')->getRestrictedContent('joomla', 'component');
		$option= JRequest :: getCmd('option', null);
		$db= oseDB :: instance();
		$query= " SELECT e.extension_id FROM `#__extensions` AS e"." WHERE e.`element` = ".$db->Quote(strtolower($option));
		$db->setQuery($query);
		$com_ids= $db->loadResultArray();
		if(count(array_intersect($com_ids, $content_ids))>0) {
			return true;
		} else {
			return false;
		}
	}
	function isMenuControlled() {
		$content_ids= oseRegistry :: call('content')->getRestrictedContent('joomla', 'menu');
		$menu= & JSite :: getMenu();
		$item= $menu->getActive();
		if(!empty($item))
		{
			if(in_array($item->id, $content_ids)) {
				// Ensure that the controlling menu is the same as the current active menu;
				foreach ($item->query as $key => $value)
				{
					$tmp = JRequest::getCmd($key);
					if ($tmp != $value)
					{
						return false;
					}
				}
				return true;
			}
		} else {
			return false;
		}
	}
	function registrationRedirect($mainframe) {
		$option= JRequest :: getCmd('option');
		$task= JRequest :: getCmd('task');
		$view= JRequest :: getCmd('view');
		$regArray= array('register', 'registers', 'registration');
		$optionArray= array('com_community', 'com_user', 'com_users', 'com_comprofiler', 'com_registration');
		if(empty($task) && empty($view))
		{
			return;
		}
		// if task exist then it must be register
		if(!empty($task) && !in_array($task, $regArray)) {
			return;
		}
		// View OR task should be register at least
		if(!empty($view) && !in_array($view, $regArray)) {
			return;
		}
		// If it is in OSE pages, return;
		if($option == 'com_osemsc') {
			return;
		}
		if(in_array($option, $optionArray)) {
			self:: redirect ("register");
		}
	}
	function checkMtree()
	{
		$content_ids= oseRegistry :: call('content')->getRestrictedContent('mtree', 'category');
		$cat_id= JRequest :: getCmd('cat_id', null);
		$link_id = JRequest :: getCmd('link_id', null);
		$redirect = false;
		if(in_array($cat_id, $content_ids))$redirect = true;
		if(empty($cat_id) && !empty($link_id))
		{
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__mt_cl` WHERE `link_id` = '{$link_id}'";
			$db->setQuery($query);
			$objs = $db->loadObjectList();
			foreach($objs as $obj)
			{
				if(in_array($obj->cat_id, $content_ids))$redirect = true;
			}
		}

		if($redirect) {
			self :: redirect();
		}
	}

	function checkHWD()
	{
		$content_ids= oseRegistry :: call('content')->getRestrictedContent('hwdvideo', 'category');
		$cat_id= JRequest :: getCmd('cat_id', null);
		$video_id = JRequest :: getCmd('video_id', null);
		$redirect = false;
		if(in_array($cat_id, $content_ids))$redirect = true;
		if(empty($cat_id) && !empty($video_id))
		{
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__hwdvidsvideos` WHERE `id` = '{$video_id}'";
			$db->setQuery($query);
			$objs = $db->loadObjectList();
			foreach($objs as $obj)
			{
				if(in_array($obj->category_id, $content_ids))$redirect = true;
			}
		}

		if($redirect) {
			self :: redirect();
		}
	}

	function checkHWDMedia()
	{
		$content_ids= oseRegistry :: call('content')->getRestrictedContent('hwdmedia', 'category');
		$view= JRequest :: getCmd('view', null);
		$id = JRequest :: getInt('id', null);
		$redirect = false;
		if($view == 'mediaitem')
		{
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__hwdms_category_map` WHERE `element_id` = '{$id}'";
			$db->setQuery($query);
			$objs = $db->loadObjectList();
			foreach($objs as $obj)
			{
				if(in_array($obj->category_id, $content_ids))$redirect = true;
			}
		}
		if($redirect) {
			self :: redirect();
		}
	}
	
	function checkSobi2()
	{
		$content_ids= oseRegistry :: call('content')->getRestrictedContent('sobi2', 'category');
		$catid= JRequest :: getCmd('catid', null);
		$sobi2Id= JRequest :: getCmd('sobi2Id', null);
		$redirect = false;
		if(in_array($catid, $content_ids))$redirect = true;
		if(empty($catid) && !empty($sobi2Id))
		{
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__sobi2_cat_items_relations` WHERE `itemid` = '{$sobi2Id}'";
			$db->setQuery($query);
			$objs = $db->loadObjectList();
			foreach($objs as $obj)
			{
				if(in_array($obj->catid, $content_ids))$redirect = true;
			}
		}

		if($redirect) {
			self :: redirect();
		}
	}

	function checkSobiPro()
	{
		$content_secids= oseRegistry :: call('content')->getRestrictedContent('sobipro', 'section');
		$content_catids= oseRegistry :: call('content')->getRestrictedContent('sobipro', 'category');
		$content_ids = array_merge($content_secids,$content_catids);
		$sid= JRequest :: getInt('sid', null);
		$redirect = false;
		$db = JFactory::getDBO();
		$query = "SELECT * FROM `#__sobipro_relations` WHERE `id` = '{$sid}' AND `oType` = 'entry'";
		$db->setQuery($query);
		$objs = $db->loadObjectList();
		foreach($objs as $obj)
		{
			if(in_array($obj->pid,$content_ids))
			{
				$redirect = true;
				break;
			}else{
				$catids = self::getSobiProPareCat($obj->pid,array());
				$intersect = array_intersect($catids,$content_catids);
				if(!empty($intersect))
				{
					$redirect = true;
					break;
				}
			}
		}
		if($redirect) {
			self :: redirect();
		}
	}

	function getSobiProPareCat($id,$pids)
	{
		$db = JFactory::getDBO();
		$query = "SELECT pid FROM `#__sobipro_relations` WHERE `id` = '{$id}'";
		$db->setQuery($query);
		$pid = $db->loadResult();
		if(!empty($pid))
		{
			$pids[] = $pid;
			return self::getSobiProPareCat($pid,$pids);
		}
		return $pids;
	}

	function checkRokdownload()
	{
		$content_ids= oseRegistry :: call('content')->getRestrictedContent('rokdownload', 'category');
		$id= (int)JRequest :: getCmd('id', null);
		$view = JRequest :: getCmd('view', null);
		global $mainframe;
		$params = & $mainframe->getParams('com_rokdownloads');
		$redirect = false;
		if($view == 'folder')
		{
			$id = empty($id)?(int)$params->get('top_level_folder'):$id;
			if(in_array($id, $content_ids))$redirect = true;

		}elseif($view == 'file')
		{
			$id = empty($id)?(int)$params->get('filetodisplay'):$id;
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__rokdownloads` WHERE `id` = '{$id}'";
			$db->setQuery($query);
			$node = $db->loadObject();
			$query = "SELECT * FROM `#__rokdownloads` WHERE lft <= '{$node->lft}' and rgt >= '{$node->rgt}' AND `folder` = '1'";
			$db->setQuery($query);
			$objs = $db->loadObjectList();
			foreach($objs as $obj)
			{
				if(in_array($obj->id, $content_ids))$redirect = true;
			}
		}

		if($redirect) {
			self :: redirect();
		}
	}

	function checkEventbooking()
	{
		$event_id= JRequest :: getCmd('event_id', null);
		$task = JRequest :: getCmd('task', null);
		
		if($task == 'individual_registration' || $task == 'group_registration')
		{
			$content_ids= oseRegistry :: call('content')->getRestrictedContent('eventbooking', 'category');
			$user = JFactory::getUser();
			$avalues = array();
			if(!empty($user->id))
			{
				$aobjs = oseRegistry :: call('content')->getInstance('msc')->getMemberAccessContent('eventbooking', 'category', $user->id,'obj');
				if (!empty($aobjs))
				{
					$avalues = array();
					foreach($aobjs as $obj)
					{
						$avalues[] = $obj->content_id;
					}
				}
			}
					
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__eb_events` WHERE `id` = ".$event_id;
			$db->setQuery($query);
			$objs = $db->loadObjectList();
			
			$cats = array();
			foreach($objs as $obj)
			{
				$cats[] = $obj->category_id;
			}
			
			$redirect = false;
			
			if(!empty($cats) && !empty($content_ids))
			{
				$intersect = array_intersect($cats, $content_ids);
				if(!empty($intersect))
				{
					$redirect = true;
				}
			}

			if(!empty($avalues) && !empty($cats))
			{
				$aintersect = array_intersect($cats, $avalues);
				if(!empty($aintersect))
				{
					$redirect = false;
				}
			}
			
			if($redirect)
			{
				self :: redirect();
			}
		}

	}
	
	function checkJdownloads()
	{
		$content_catids= oseRegistry :: call('content')->getRestrictedContent('jdownloads', 'category');
		$content_artids= oseRegistry :: call('content')->getRestrictedContent('jdownloads', 'article');

		//$catid = (int)JRequest :: getCmd('catid', null);
		$artid = (int)JRequest :: getCmd('cid', null);
		$view = JRequest :: getCmd('view', null);

		$db = JFactory::getDBO();
		$query = "SELECT cat_id FROM `#__jdownloads_files` WHERE `file_id` = '{$artid}'";
		$db->setQuery($query);
		$id = $catid = $db->loadResult();

		$catids = array();
		$catids[]=$catid;
		for($i=0;$i<1000;$i++)
		{
			$query = "SELECT parent_id FROM `#__jdownloads_cats` WHERE `cat_id` = '{$id}'";
			$db->setQuery($query);
			$parent_id = $db->loadResult();
			if($parent_id == '0')
			{
				break;
			}
			$catids[] = $id = $parent_id;
		}
		$intersect = array_intersect($catids,$content_catids);
		$redirect = false;
		if(!empty($intersect) && $view == 'finish')
		{
			$redirect = true;
		}
		if(in_array($artid,$content_artids) && $view == 'finish')
		{
			$redirect = true;
		}
		if($redirect)
		{
			self :: redirect();
		}
	}

	function checkAriquiz($pluginParams)
	{
		$task = JRequest::getCmd('task');
		$allowQuizPageView = $pluginParams->get('allowQuizPageView', false);
		$allowCategoryPageView = $pluginParams->get('allowCategoryPageView', false);
		if ($allowQuizPageView && $allowCategoryPageView)
		{
			return ;
		}
		if ($task == 'quiz' && !$allowQuizPageView)
		{
			$quizId = JRequest::getInt('quizId', -1);
			$this->_ACLCheckByQuiz($quizId);
		}else if ($task == 'cat_quiz_list' && !$allowCategoryPageView)
		{
			$categoryId = JRequest::getInt('categoryId', -1);
			$this->_ACLCheckCategory($categoryId);
		}
	}
	
	function onBeforeStartQuiz($params)
	{
		
		$this->_ACLCheckByQuiz($params['QuizId']);
	}

	function _ACLCheckByQuiz($quizId)
	{
		$quizId = @intval($quizId, 10);
		if ($quizId < 1)
		{
			return ;
		}

		$database =& JFactory::getDBO();
		$query = sprintf('SELECT CategoryId FROM `#__ariquizquizcategory` WHERE QuizId = %d LIMIT 0,1',
			$quizId);
		$database->setQuery($query);
		$categoryId = $database->loadResult();

		$this->_ACLCheckCategory($categoryId);
	}
	
	function _ACLCheckCategory($categoryId)
	{
		$categoryId = @intval($categoryId, 10);
		if ($categoryId < 1)
		{
			return ;
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
		$content_ids= oseRegistry :: call('content')->getRestrictedContent('ariquiz', 'category');
		if(in_array($categoryId,$content_ids))
		{
			self::redirect();
			
		}
	}
	
	function jomsocialRegRedirect()
	{
		require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'helpers'.DS.'oseMscPublic.php');
		$uri = &JFactory::getURI();
		$vars['task']	= $uri->getVar( 'task' );
		$vars['view']	= $uri->getVar( 'view' );
		if(empty($vars['task']))
		{
			$vars['task'] = JRequest::getCmd('task');
		}
		if(empty($vars['view']))
		{
			$vars['view'] = JRequest::getCmd('view');
		}
		$session =& JFactory::getSession();
		if(isset($_SESSION['__XIPT']['SELECTED_PROFILETYPE_ID']))
		{
			$session->set('pid',$_SESSION['__XIPT']['SELECTED_PROFILETYPE_ID']);
		}
		$vars['joms_regs'] = ($vars['view'] == 'register' && ( $vars['task'] == 'registerSucess' ));

		if($vars['joms_regs'])
		{
			//$pid = $_SESSION['__XIPT']['SELECTED_PROFILETYPE_ID'];
			$pid = $session->get('pid',null);
			$db= oseDB :: instance();
			$query = "SELECT * FROM `#__osemsc_ext` WHERE `type` = 'jspt'";
			$db->setQuery($query);
			$objs = $db->loadObjectList();
			if(!empty($objs))
			{
				foreach($objs as $obj)
				{
					$data = oseJson::decode($obj->params);
					if($data->enable && $pid == $data->jspt_id)
					{
						$msc_id = $obj->id;
						break;
					}
				}

			}
			$redirect = false;
			if(!empty($msc_id))
			{
				JRequest::setVar('msc_id',$msc_id);
				$msc = oseRegistry::call('msc');
				$opts = $msc->runAddonAction('panel.payment.getOptions',array(),true,true);
				$options = $opts['results'];
				if(!empty($options))
				{
					foreach($options as $option)
					{
						if(!$option['isFree'] && $option['a3']>0)
						{
							$msc_option = $option['id'];
							$redirect =true;
							break;
						}
					}
				}
			}
			if($redirect)
			{
				$cart = oseRegistry::Call('payment')->getInstance('Cart');
				$payment_mode = oseMscPublic::getPaymentMode();

				$item = array('entry_id'=>$msc_id,'entry_type'=>'msc','msc_option'=>$msc_option);
				$cart->addItem($item['entry_id'],$item['entry_type'],$item);
				//oseExit($cart);
				$cart->updateParams('payment_mode',$payment_mode);
				$cart->update();

				$session = JFactory::getSession();
				$session->set('ose_reg_step','cart');
				$app = JFactory::getApplication();
				$app->redirect( 'index.php?option=com_osemsc&view=register');
			}
		}

	}
	function jomRegRedirect()
	{
		//require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		$uri = &JFactory::getURI();
		$vars['task']	= $uri->getVar( 'task' );
		$vars['view']	= $uri->getVar( 'view' );
		if(empty($vars['task']))
		{
			$vars['task'] = JRequest::getCmd('task');
		}
		if(empty($vars['view']))
		{
			$vars['view'] = JRequest::getCmd('view');
		}
		$vars['joms_regs'] = ($vars['view'] == 'register' && ( $vars['task'] == 'registerSucess' ));
		if($vars['joms_regs'])
		{
			$app = JFactory::getApplication();
			$app->redirect( 'index.php?option=com_osemsc&view=register');
		}

	}
}