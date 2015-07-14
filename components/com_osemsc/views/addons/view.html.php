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
class osemscViewAddons extends osemscView {
	function display($tpl = null) {
		$tpl = null;
		$config = oseMscConfig::getConfig('global', 'obj');
		$com = OSECPU_PATH_JS . '/com_ose_cpu/extjs';
		oseHTML::initScript();
		oseHTML::script($com . '/ose/app.msg.js', '1.5');
		oseHTML::script($com . '/grid/expander.js', '1.5');
		oseHTML::script(OSEMSC_F_URL . '/libraries/init.js', '1.5');
		oseHTML::stylesheet(OSEMSC_F_URL . '/assets/css/' . $config->frontend_style . '.css', (JOOMLA16) ? '1.6' : '1.5');
		$mainframe = JFactory::getApplication('SITE');
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		if ($user->guest) {
			$ItemID = JRequest::getInt("Itemid");
			$return = base64_encode(str_replace("&amp;", "&", JRoute::_("index.php?option=com_osemsc&view=addons&Itemid=" . $ItemID)));
			if (JOOMLA16 == true) {
				$red = str_replace("&amp;", "&", JRoute::_("index.php?option=com_users&view=login&return=" . $return));
			} else {
				$red = str_replace("&amp;", "&", JRoute::_("index.php?option=com_user&view=login&return=" . $return));
			}
			$mainframe->redirect($red, "Please login first.");
		}
		$menu = &JSite::getMenu();
		$item = $menu->getActive();
		$addon_type = '';
		if (!empty($item)) {
			$params = &$menu->getParams($item->id);
			$addon_type = $params->get('addon_type');
		}
		$model = $this->getModel('addons');
		$items = array();
		switch ($addon_type) {
		case 'phoca':
			$items = $model->getAddonCats($addon_type);
			$link = 'index.php?option=com_phocadownload&view=category&id=';
			break;
		case 'roku':
			$items = $model->getAddonInfo($addon_type);
			$link = '';
			break;
		}
		$params = &$mainframe->getParams();
		$page_title = $params->get('page_title');
		$document = &JFactory::getDocument();
		$document->setTitle($params->get('page_title'));
		$this->assignRef('page_title', $page_title);
		$this->assignRef('addon_type', $addon_type);
		$this->assignRef('items', $items);
		$this->assignRef('link', $link);
		$this->prepareDocument();
		parent::display($tpl);
	}
	protected function prepareDocument() {
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menuParams = &$app->getParams();
		$title = null;
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu) {
			$menuParams->def('page_heading', $menuParams->get('page_title', (JOOMLA16 == true) ? $menu->title : $menu->name));
		} else {
			$menuParams->def('page_heading', JText::_('COM_USERS_REGISTRATION'));
		}
		$title = $menuParams->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		} elseif ($app->getCfg('sitename_pagetitles', 0)) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		$this->assign('menuParams', $menuParams);
		$document = &JFactory::getDocument();
		$document->setTitle($title);
	}
}
?>
