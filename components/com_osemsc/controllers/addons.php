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
class osemscControllerAddons extends osemscController {
	function __construct() {
		parent::__construct();
	}
	function registerCode() {
		$regCode = JRequest::getString('regCode');
		$mainframe = &JFactory::getApplication();
		$return = array();
		if (empty($regCode)) {
			$return['results'] = 'ERROR';
			$return['text'] = "Registration code is empty";
			echo oseJSON::encode($return);
			exit;
		}
		$user = &JFactory::getUser();
		if ($user->guest) {
			$return['results'] = 'ERROR';
			$return['text'] = "Guest users are not allowed to register codes.";
			echo oseJSON::encode($return);
			exit;
		}
		$model = $this->getModel('addons');
		$items = $model->hasMembership($user->id, 1);
		if (empty($items)) {
			$return['results'] = 'ERROR';
			$Itemid = JRequest::getInt("Itemid");
			$red = str_replace("&amp;", "&", JRoute::_("index.php?option=com_osemsc&view=register&Itemid=" . $Itemid));
			$return['text'] = "Please <a href ='" . $red . "'>subscribe a membership</a> before you register your device.";
			echo oseJSON::encode($return);
			exit;
		}
		if ($user->id) {
			$results = $model->registerCode($regCode, $user->id);
			if ($results == "success") {
				$return['results'] = 'SUCCESS';
				$return['text'] = "Congratulations! Your device is regsitered.";
				echo oseJSON::encode($return);
				exit;
			} elseif ($results == "registered") {
				$return['results'] = 'SUCCESS';
				$return['text'] = "Your device is already regsitered.";
				echo oseJSON::encode($return);
				exit;
			} else {
				$return['results'] = 'ERROR';
				$return['text'] = "Device registration failed, please contact administrators for this issue.";
				echo oseJSON::encode($return);
				exit;
			}
		}
	}
}
?>