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
class osemscViewMember extends osemscView {
	function display($tpl = null) {
		$tpl = null;
		$this->set('_layout', 'default');
		$user = JFactory::getUser();
		$app = JFactory::getApplication('SITE');
		if ($user->guest) {
			$session = JFactory::getSession();
			$session->set('oseReturnUrl', base64_encode('index.php?option=com_osemsc&view=member'));
			$app->redirect('index.php?option=com_osemsc&view=login');
		} else {
			if (!$this->isMobile) {
				$this->loadViewJs();
				$this->loadGridJs();
				$this->loadMultiSelect();
				$member = oseRegistry::call('member');
				$view = $member->getInstance('PanelView');
				$member->instance($user->id);
				$hasMember = $member->getMemberOwnedMscInfo(true, null, 'obj');
				if ($hasMember > 0) {
					$result = $member->getMemberPanelView('Member');
					if (isset($result['layout'])) {
						$this->set('_layout', $result['layout']);
					}
					if (!empty($result['tpl'])) {
						$tpl = $result['tpl'];
					}
					$companyAddons = $this->getAddons('member_company');
					$this->assignRef('companyAddons', $companyAddons);
				} else {
					$this->set('_layout', 'default');
				}
			} else {
				$this->setLayout('mobile');
				JRequest::setvar('tmpl', 'component');
			}
		}
		$this->prepareDocument();
		parent::display($tpl);
	}
	function getAddons($type) {
		return oseMscAddon::getAddonList($type, false, null, 'obj');
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
?>