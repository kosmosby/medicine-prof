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
// No direct access
defined('_JEXEC') or die;
/**
 * Content component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class OSESoftHelper
{
	public static $extension= '';
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	$vName	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function __construct()
	{
		self::$extension= $this->getExtensionName();
		$version= new JVersion();
		$version= substr($version->getShortVersion(), 0, 3);
		if(!defined('JOOMLA16'))
		{
			$value=($version >= '1.6') ? true : false;
			define('JOOMLA16', $value);
		}
	}
	public static function getExtensionName()
	{
		return 'com_osemsc';
	}
	
	public static function showmenu()
	{
		$db= JFactory::getDBO();
		$query= "SELECT * FROM `#__menu` WHERE `alias` =  'OSE Membership™'";
		$db->setQuery($query);
		$results= $db->loadResult();
		if(empty($results))
		{
			$query= "UPDATE `#__menu` SET `alias` =  'OSE Membership™', `path` =  'OSE Membership™', `published`=1, `img` = '\"components/com_osemsc/favicon.ico\"'  WHERE `component_id` = ( SELECT extension_id FROM `#__extensions` WHERE `element` ='com_osemsc')  AND `client_id` = 1 ";
			$db->setQuery($query);
			$db->query();
		}
		self::$extension= self::getExtensionName();
		$view= JRequest :: getVar('view');
		echo '<div class="menu-search">';
		echo '<ul>';
		echo '<li ';
		echo($view == 'memberships') ? 'class="current"' : '';
		echo '><a href="index.php?option='.self::$extension.'&view=memberships">'.JText :: _('MEMBERSHIP_MANAGEMENT').'</a></li>';

		echo '<li ';
		echo($view == 'members') ? 'class="current"' : '';
		echo '><a href="index.php?option='.self::$extension.'&view=members">'.JText :: _('MEMBER_MANAGEMENT').'</a></li>';

		echo '<li ';
		echo($view == 'orders') ? 'class="current"' : '';
		echo '><a href="index.php?option='.self::$extension.'&view=orders">'.JText :: _('ORDER_MANAGEMENT').'</a></li>';

		echo '<li ';
		echo($view == 'coupons') ? 'class="current"' : '';
		echo '><a href="index.php?option='.self::$extension.'&view=coupons">'.JText :: _('COUPON_MANAGEMENT').'</a></li>';

		echo '<li ';
		echo($view == 'emails') ? 'class="current"' : '';
		echo '><a href="index.php?option='.self::$extension.'&view=emails">'.JText :: _('TEMPLATES').'</a></li>';
		
		echo '<li ';
		echo($view == 'config') ? 'class="current"' : '';
		echo '><a href="index.php?option='.self::$extension.'&view=config">'.JText :: _('CONFIGURATION').'</a></li>';

		echo '<li ';
		echo($view == 'profile') ? 'class="current"' : '';
		echo '><a href="index.php?option='.self::$extension.'&view=profile">'.JText :: _('CUSTOM_PROFILE').'</a></li>';
			
		echo '<li ';
		echo($view == 'addons') ? 'class="current"' : '';
		echo '><a href="index.php?option='.self::$extension.'&view=addons">'.JText :: _('ADDONS').'</a></li>';
			
		
		echo '<li ';
		echo($view == 'aboutose') ? 'class="current"' : '';
		echo '><a href="index.php?option='.self::$extension.'&view=aboutose">'.JText :: _('ABOUTOSE').'</a></li>';
				
		echo '</ul></div>';
	}
	function checkAdminAccess()
	{
		$db = &JFactory::getDBO();
		$user = &JFactory::getUser();
		$db->setQuery("SELECT id FROM #__usergroups");
		$groups = $db->loadResultArray();

		$admin_groups = array();
		foreach ($groups as $group_id)
		{
			if (JAccess::checkGroup($group_id, 'core.login.admin'))
			{
				$admin_groups[] = $group_id;
			}
			elseif (JAccess::checkGroup($group_id, 'core.admin'))
			{
				$admin_groups[] = $group_id;
			}
		}
		$admin_groups = array_unique($admin_groups);
		$user_groups = JAccess::getGroupsByUser($user->id);
		if (count(array_intersect($user_groups, $admin_groups))>0)
		{
			$access=  true;
		}
		else
		{
			$access=  false;
		}
		return $access;
	}
	function getVersion()
	{
		$folder= JPATH_ADMINISTRATOR.DS.'components'.DS.self::$extension;
		if(JFolder :: exists($folder))
		{
			$xmlFilesInDir= JFolder :: files($folder, '.xml$');
		}
		else
		{
			$folder= JPATH_SITE.DS.'components'.DS.self::$extension;
			if(JFolder :: exists($folder))
			{
				$xmlFilesInDir= JFolder :: files($folder, '.xml$');
			}
			else
			{
				$xmlFilesInDir= null;
			}
		}
		$xml_items= '';
		if(count($xmlFilesInDir))
		{
			foreach($xmlFilesInDir as $xmlfile)
			{
				if($data= JApplicationHelper :: parseXMLInstallFile($folder.DS.$xmlfile))
				{
					foreach($data as $key => $value)
					{
						$xml_items[$key]= $value;
					}
				}
			}
		}
		if(isset($xml_items['version']) && $xml_items['version'] != '')
		{
			return $xml_items['version'];
		}
		else
		{
			return '';
		}
	}
	function ajaxResponse($status, $message, $data= null, $url= null)
	{
		$return['title']= $status;
		$return['content']= $message;
		$return['data']= $data;
		$return['url']= $url;
		echo oseJSON :: encode($return);
		exit;
	}
	function returnMessages($status, $messages)
	{
		$result= array();
		if($status == true)
		{
			$result['success']= true;
			$result['status']= 'Done';
			$result['result']= $messages;
		}
		else
		{
			$result['success']= false;
			$result['status']= 'Error';
			$result['result']= $messages;
		}
		$result= oseJSON :: encode($result);
		oseExit($result);
	}
	static function renderOSETM()
	{
		$a = base64_decode('PGRpdiBpZCA9ICJvc2Vmb290ZXIiPjxkaXYgY2xhc3M9ImZvb3Rlci10ZXh0Ij5Qb3dlcmVkIGJ5IDxhIGhyZWY9Imh0dHA6Ly93d3cub3BlbnNvdXJjZS1leGNlbGxlbmNlLmNvbS8iIHN0eWxlPSJ0ZXh0LWRlY29yYXRpb246IG5vbmU7IiB0YXJnZXQ9Il9ibGFuayIgdGl0bGU9Ik9TRSBNZW1iZXJzaGlwIj5PU0UgTWVtYmVyc2hpcOKEoiA=');
		$a.= '<small><small>[Version: '.OSEMSCVERSION.']</small></small></a></div></div>'; 
		return $a;
	}
	static function getFolderName($class)
	{
		$classname = explode("View", get_class($class));
		$classname = strtolower($classname[1]);
		return $classname;
	}
	public function loadCats($cats = array())
    {
        if(is_array($cats))
        {
            $i = 0;
            $return = array();
            foreach($cats as $JCatNode)
            {
                $return[$i]->title = $JCatNode->title;
                $return[$i]->cat_id = $JCatNode->id;
                if($JCatNode->hasChildren())
                    $return[$i]->children = self::loadCats($JCatNode->getChildren());
                else
                    $return[$i]->children = false;

                $i++;
            }
            return $return;
        }
        return false;
    }
	public function loadCatTree($cats = array(), $return = array() )
    {
        if(is_array($cats))
        {
            $i = 0;
            $curreturn = array();
            foreach($cats as $JCatNode)
            {
               $curreturn[$i]->title = '['.$JCatNode->id.'] '.str_repeat('-', $JCatNode->level-1).' '.$JCatNode->title;
               $curreturn[$i]->cat_id = $JCatNode->id;

                if($JCatNode->hasChildren())
                {
                	$subreturn = self::loadCatTree($JCatNode->getChildren(), $return);
                }
                $i++;
            }
            $return = array_merge($curreturn, $return);
            //$return = array_unique($return);
            return $return;
        }
        return false;
    }
    function loadOrders($table)
    {
		$db= & JFactory :: getDBO();
		$query= "SELECT CONCAT (`ordering`, ' - ', `title`) as title, `ordering` FROM `{$table}` ORDER BY `ordering` ASC";
		$db->setQuery($query);
		$results= $db->loadObjectList();
		return (!empty($results))?$results:null;
    }

	public static function checkToken($method = 'post')
	{
		$token = self::getOSEToken();
		if (!JRequest::getVar($token, '', $method, 'alnum'))
		{
			$session = JFactory::getSession();
			if ($session->isNew()) {
				// Redirect to login screen.
				$app = JFactory::getApplication();
				$return = JRoute::_('index.php');
				self::ajaxResponse ('ERROR', JText::_('JLIB_ENVIRONMENT_SESSION_EXPIRED'));
			} else {
				self::ajaxResponse ('ERROR', JText::_('Token invalid'));
			}
		} else {
			return true;
		}
	}
	public function getToken()
	{
		$html = '<input type="hidden" value="1" name="'.self::getOSEToken().'">';
		return $html;
	}
	function randStr($length= 32, $chars= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
		// Length of character list
		$chars_length=(strlen($chars) - 1);
		// Start our string
		$string= $chars {
			rand(0, $chars_length)
			};
		// Generate random string
		for($i= 1; $i < $length; $i= strlen($string)) {
			// Grab a random character from our list
			$r= $chars {
				rand(0, $chars_length)
				};
			// Make sure the same two characters don't appear next to each other
			if($r != $string {
				$i -1 })
			$string .= $r;
		}
		// Return the string
		return $string;
	}
	public function getRealIP() {
		$ip= false;
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip= $_SERVER['HTTP_CLIENT_IP'];
		}
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips= explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
			if($ip) {
				array_unshift($ips, $ip);
				$ip= false;
			}
			for($i= 0; $i < count($ips); $i++) {
				if(!preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i])) {
					if(version_compare(phpversion(), "5.0.0", ">=")) {
						if(ip2long($ips[$i]) != false) {
							$ip= $ips[$i];
							break;
						}
					} else {
						if(ip2long($ips[$i]) != -1) {
							$ip= $ips[$i];
							break;
						}
					}
				}
			}
		}
		return($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}
	public static function getOSEToken()
	{
		$session = JFactory::getSession (); 
		$token = $session->getToken();
		return $token; 
	}
	public static function getPreviewMenus()
	{
		$logoutLink = JRoute::_('index.php?option=com_login&task=logout&'. self::getOSEToken() .'=1');
		$hideLinks	= JRequest::getBool('hidemainmenu');
		$output = '<div id="preview_menus">'; 
		
		$output .= '<span class="backtojoomla"><a href="'.JURI::root().'administrator/" >'.JText::_('BACK_TO_JOOMLA').'</a></span>';
		// Print the logout link.
		$output .= '<span class="viewsite"><a href="' . JURI::root() . '" target="_blank">' . JText::_('JGLOBAL_VIEW_SITE') . '</a></span>';
		$output .= '<span class="logout">' .($hideLinks ? '' : '<a href="'.$logoutLink.'">').JText::_('JLOGOUT').($hideLinks ? '' : '</a>').'</span>';
		// Output the items.
		$output .= "</div>"; 
		return $output; 
	}
	function TimerCheck()
	{
		if (!isset($_SESSION['targettime']))
		{
			$_SESSION['targettime'] = new DateTime('+ 10seconds');
		}
		echo $_SESSION['targettime']->format('Y-m-d H:i:s'); 
		$now = new DateTime;
		echo $now->format('Y-m-d H:i:s'); 
		$diff = $_SESSION['targettime']->diff($now);
		
		if ($_SESSION['targettime'] > $now) {
			echo $diff->format('%s seconds to go').'<br />';
			sleep(5);
			self::TimerCheck(); 
		} else {
			unset($_SESSION['targettime']); 
			
			return true; 
		}
	}
}
?>