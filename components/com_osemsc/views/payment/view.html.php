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
class osemscViewPayment extends osemscView {
	function display($tpl = null) {
		$app = JFactory::getApplication('SITE');
		$config = oseMscConfig::getConfig('register', 'obj');
		$items = oseMscPublic::getCartItems();
		$this->checkUser();
		$this->assignRef('item', $items[0]);
		$model = $this->getModel('payment');
		$items = $model->getMemberships();
		$com = OSECPU_PATH_JS . '/com_ose_cpu/extjs';
		$msc = oseRegistry::call('msc');
		$regForm = empty($config->register_form) ? null : $config->register_form;
		if (!empty($config->payment_system)) {
			$this->setLayout($config->payment_system);
		}
		$tpl = $regForm;
		$this->prepareDocument();
		parent::display($tpl);
	}
	function checkUser() {
		$user = JFactory::getUser();
		if ($user->guest) {
			$app = JFactory::getApplication('SITE');
			$session = &JFactory::getSession();
			$session->set('oseReturnUrl', base64_encode(JRoute::_('index.php?option=com_osemsc&view=payment')));
			$app->redirect(JRoute::_('index.php?option=com_osemsc&view=login'));
		} else {
			$member = oseRegistry::call('member');
			$member->instance($user->id);
			$result = $member->getMemberPanelView('Payment');
			if (!$result) {
				$app = JFactory::getApplication('SITE');
			}
		}
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