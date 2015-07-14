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
class oseMscControllerActivate extends oseMscController {
	function __construct() {
		parent :: __construct();
	}
	function activate(){
		$user= JRequest::getVar('username');
		$password= JRequest::getVar('password');
		$ext= JRequest::getVar('ext');
		$url = "www.opensource-excellence.com"; 
		$req = "/member/activate.php?task=ativate&username=".urlencode($user).'&password='.urlencode($password).'&ext='.urlencode($ext);
		$fp = fsockopen ($url, 80, $errno, $errstr, 30);
		if (!$fp || $errno) return $errstr;
		
		@fputs($fp, "GET ".$req." HTTP/1.1\r\n");
		@fputs($fp, "HOST: ".$url."\r\n");
		@fputs($fp, "Connection: close\r\n\r\n");
		// read the body data
		$res = '';
		$headerdone = false;
		while (!feof($fp)) {
			$line = fgets ($fp, 1024);
			if (strcmp($line, "\r\n") == 0) {
				// read the header
				$headerdone = true;
			}
			else if ($headerdone)
			{
				// header has been read. now read the contents
				$res .= $line;
			}
		}
		fclose ($fp);
		preg_match('/\"SUCCESS\"/', $res, $matches);
		if ($matches)
		{
			$return = str_replace("}", "", explode('id:', $res)); 
			$this->updateactivation($ext, $return[1]);
			echo $res;exit; 
		}
		else
		{
			echo ($res); exit;
		}
		
	}
	function updateactivation($ext, $id)
	{
		$db = JFactory::getDBO(); 
		$query = "SELECT * FROM #__ose_activation WHERE `ext` = ".$db->Quote($ext, true);
		$db->setQuery($query); 
		$result = $db->loadObject();
		if (empty($result))
		{
			$query = " INSERT INTO `#__ose_activation` (`id` ,`code` ,`ext`) ". 
					 " VALUES (".(int)$id.",".$db->Quote(base64_encode($id),true).", 'osemsc'); ";
			$db->setQuery($query); 
			return $db->query(); 
		}
	}
}