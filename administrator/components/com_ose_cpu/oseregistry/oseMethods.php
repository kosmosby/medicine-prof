<?php
/**
 * @version     4.0 +
 * @package     Open Source Excellence Central Processing Units
 * @author      Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author      Created on 17-May-2011
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @Copyright Copyright (C) 2010- Open Source Excellence (R)
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
 */
defined('_JEXEC') or die(";)");
function oseGetIP() {
	if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
		$cip = $_SERVER["HTTP_CLIENT_IP"];
	} else {
		if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} else {
			if (!empty($_SERVER["REMOTE_ADDR"])) {
				$cip = $_SERVER["REMOTE_ADDR"];
			} else {
				$cip = '';
			}
		}
	}
	preg_match("/[\d\.]{7,15}/", $cip, $cips);
	$cip = isset($cips[0]) ? $cips[0] : 'unknown';
	unset($cips);
	return $cip;
}
function oseExit($msg = null, $ajax = true) {
	if (!empty($msg)) {
		if ($ajax) {
			ob_get_clean();
		}
		print_r($msg);
	}
	exit;
}
// for inner call function
function oseCall($class) {
	return oseRegistry::call($class);
}
function oseCheckToken() {
	$tokenCheck = JRequest::checkToken();
	if (empty($tokenCheck)) {
		$result['content'] = 'Token is invalid!';
		$result = oseJson::encode($result);
		oseExit($result);
	}
}
function osePointedRedirection($sefroutemethod, $menu, $isSSL = false) {
	$mainframe = &JFactory::getApplication();
	if ($mainframe->isAdmin()) {
		return; 
	}
	$version = oseHtml::getJoomlaVersion();
	if ($menu->type == 'url') {
		$return = $menu->link;
	} elseif ($menu->type == 'alias') {
		$menuParams = oseJson::decode($menu->params);
		$aMenuId = $menuParams->aliasoptions;
		$aMenu = JSite::getMenu(true)->getItem($aMenuId);
		return osePointedRedirection($sefroutemethod, $aMenu);
	} else {
		switch ($sefroutemethod) {
		default:
		case 0:
			$redURL = $menu->link . "&Itemid=" . $menu->id;
			break;
		case 1:
			$return = $redURL = JRoute::_($menu->link . "&Itemid=" . $menu->id, false);
			break;
		case 2:
			$jConfig = JFactory::getConfig();
			if ($version >= '1.6') {
				if ($jConfig->get('sef_rewrite')) {
					$redURL = JRoute::_(oseGetValue($menu, 'path'));
				} else {
					$redURL = "index.php/" . JRoute::_(oseGetValue($menu, 'path'));
				}
			} else {
				static $menuPath;
				$parent_id = oseGetValue($menu, 'parent');
				if (empty($menuPath)) {
					$menuPath = array();
					array_unshift($menuPath, $menu->alias);
				}
				if ($parent_id != 0) {
					//$aMenu   = JSite::getMenu(true)->getItem($parent_id);
					$db = oseDB::instance();
					$query = " SELECT * FROM `#__menu`" . " WHERE id = '{$parent_id}'";
					$db->setQuery($query);
					$aMenu = oseDB::loadItem('obj');
					array_unshift($menuPath, $aMenu->alias);
					$redURL = osePointedRedirection($sefroutemethod, $aMenu);
					return $redURL;
				} else {
					if (!is_array($menuPath)) {
						$menuPath = array($menuPath);
					}
					$menuPath = implode('/', $menuPath);
					if ($jConfig->getValue('sef_rewrite')) {
						$redURL = JRoute::_($menuPath);
					} else {
						$redURL = "index.php/" . JRoute::_($menuPath);
					}
				}
			}
			break;
		}
	}
	if (strpos($redURL, 'http') === false && $sefroutemethod != 1) {
		$return = JURI::root() . $redURL;
	}
	return $return;
}
function oseDirectCall($folder) {
	return oseRegistry::directCall($folder);
}
function oseGetValue($item, $key, $default = null) {
	$isObj = is_object($item);
	if ($isObj) {
		return empty($item->{$key}) ? $default : $item->{$key};
	} else {
		return empty($item[$key]) ? $default : $item[$key];
	}
}
function oseSetValue($item, $key, $value) {
	$isObj = is_object($item);
	if ($isObj) {
		$item->{$key} = $value;
	} else {
		$item[$key] = $value;
	}
	return $item;
}
function oseGetAppTitle($app, $id, $entry_option = null) {
	$db = oseDB::instance();
	switch ($app) {
	case ('contract'):
		$query = " SELECT * FROM `#__ose_contract_plan`" . " WHERE `id` = '{$id}'";
		break;
	case ('msc'):
		$query = " SELECT * FROM `#__osemsc_acl`" . " WHERE `id` = '{$id}'";
		break;
	}
	$db->setQuery($query);
	$appItem = oseDB::loadItem('obj');
	if (oseGetValue($appItem, 'parent_id', 0) > 1) {
		$title = oseGetAppTitle($app, $appItem->parent_id) . '-' . $appItem->title;
		return $title;
	} elseif ($app == 'msc' && oseGetVersion('com_osemsc') < 6 && !empty($entry_option)) {
		$query = "SELECT * FROM `#__osemsc_ext`" . " WHERE `type` = 'payment' AND `id` = '{$id}'";
		$db->setQuery($query);
		$planPayment = oseDB::loadItem('obj');
		$options = oseJSON::decode($planPayment->params, true);
		$option = oseGetValue($options, $entry_option, array());
		$title = $appItem->title . '-' . oseGetValue($option, 'optionname', '');
		return $title;
	} else {
		return $appItem->title;
	}
}
function oseGetVersion($ext) {
	$folder = JPATH_ADMINISTRATOR . DS . 'components' . DS . $ext;
	if (JFolder::exists($folder)) {
		$xmlFilesInDir = JFolder::files($folder, '.xml$');
	} else {
		$folder = JPATH_SITE . DS . 'components' . DS . $ext;
		if (JFolder::exists($folder)) {
			$xmlFilesInDir = JFolder::files($folder, '.xml$');
		} else {
			$xmlFilesInDir = null;
		}
	}
	$xml_items = '';
	if (count($xmlFilesInDir)) {
		foreach ($xmlFilesInDir as $xmlfile) {
			if ($data = JApplicationHelper::parseXMLInstallFile($folder . DS . $xmlfile)) {
				foreach ($data as $key => $value) {
					$xml_items[$key] = $value;
				}
			}
		}
	}
	if (isset($xml_items['version']) && $xml_items['version'] != '') {
		return $xml_items['version'];
	} else {
		return '';
	}
}
function oseToolbarTitle($title) {
	$html = '<div class="osepagetitle oselogo-labels"><a href="http://www.opensource-excellence.com" target="_blank"><div style="display:block;width:300px;height:48px;float:left"></div></a>'
			. '<h2>' . $title . '</h2></div>';
	$app = JFactory::getApplication();
	$app->set('JComponentTitle', $html);
}

function oseSendMail($from, $fromname, $recipient, $subject, $body, $mode = 0, $cc = null, $bcc = null, $attachment = null, $replyto = null, $replytoname = null)
{
	$mail = JFactory::getMailer();
	$return=$mail->sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
	return $return; 
}
?>