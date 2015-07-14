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
jimport('joomla.application.component.view');
class osemscViewMemlist extends osemscView {
	function display($tpl = null) {
		$user = oseMscPublic::getUser();
		$mainframe = JFactory::getApplication();
		$params = clone ($mainframe->getParams('com_osemsc'));
		$msc_id = $params->get('msc');
		$status = $params->get('status', 'all');
		if (empty($msc_id)) {
			$mainframe->redirect('index.php', 'Please choose a membership to show');
		}
		oseHTML::stylesheet(OSEMSC_F_URL . '/assets/css/msc5backend.css', '1.5');
		$com = OSECPU_PATH_JS . '/com_ose_cpu/extjs';
		oseHTML::script($com . '/grid/SearchField.js');
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('var osememlist_msc_id = ' . $msc_id . '; var osememlist_status = \'' . $status . '\';');
		$this->prepareDocument();
		if (!empty($tpl)) {
			parent::display($tpl);
		} else {
			parent::display();
		}
	}
	protected function prepareDocument() {
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menuParams = $app->getParams();
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
		$document = JFactory::getDocument();
		$document->setTitle($title);
	}
}
