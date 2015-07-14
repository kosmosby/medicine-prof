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
/**
 * HTML View class for the Registration component
 *
 * @package		Joomla
 * @subpackage	Registration
 * @since 1.0
 */
class osemscViewRegister extends osemscView {
	function display($tpl = null) {
		$user = oseMscPublic::getUser();
		$app = JFactory::getApplication('SITE');
		$cart = oseMscPublic::getCart();
		$osePaymentCurrency = $cart->get('currency');
		// Add Detect
		if ($this->isMobile) {
			$this->setLayout('mobile');
			JRequest::setVar('tmpl', 'component');
		} else {
			$this->loadMultiSelect();
			$this->loadFileUpload();
		}
		$model = $this->getModel('register');
		$msc_id = JRequest::getInt('msc_id', 0);
		$msc_option = JRequest::getCmd('msc_option', null);
		if (!empty($msc_id)) {
			if (empty($msc_option)) {
				$msc = oseRegistry::call('msc');
				$paymentInfos = $msc->getExtInfo($msc_id, 'payment');
				foreach ($paymentInfos as $paymentInfo) {
					$msc_option = oseObject::getValue($paymentInfo, 'id');
					break;
				}
				$model->addToCart($msc_id, $msc_option);
			} else {
				$model->addToCart($msc_id, $msc_option);
			}
		}
		$config = oseMscConfig::getConfig('register', 'obj');
		if (!$isMobile) {
			if (!empty($config->register_form) && $config->register_form != 'default') {
				$layout = JRequest::getCmd("layout");
				if (empty($layout)) {
					$layout = 'default';
				}
				$this->setLayout($layout);
				switch ($layout) {
				case ('onestep'):
				default:
					$headerObjs = $this->getAddons('registerOS_header');
					$bodyObjs = $this->getAddons('registerOS_body');
					$footerObjs = $this->getAddons('registerOS_footer');
					if ($user->guest) {
						$tpl = '';
						$headerObjs = $this->filter($headerObjs, array('welcome'), false);
					} else {
						$tpl = 'payment';
						$headerObjs = $this->filter($headerObjs, array('login'), false);
						$bodyObjs = $this->filter($bodyObjs, array('juser', 'juser_e', 'mailing', 'jomsocial'), false);
					}
					$this->loadAddons($headerObjs, 'registerOS');
					$this->loadAddons($bodyObjs, 'registerOS');
					$this->loadAddons($footerObjs, 'registerOS');
					$this->assignRef('registerOS_header', $headerObjs);
					$this->assignRef('registerOS_body', $bodyObjs);
					$this->assignRef('registerOS_footer', $footerObjs);
					$this->assignRef('enable_fblogin', $config->enable_fblogin);
					$this->assignRef('facebookapiid', $config->facebookapiid);
					break;
				}
			} else {
				$this->setLayout('cart');
				$tpl = "default";
				$headerObjs = $this->getAddons('registerOS_header');
				$bodyObjs = $this->getAddons('registerOS_body');
				$footerObjs = $this->getAddons('registerOS_footer');
				if ($user->guest) {
					$tpl = 'default';
				} else {
					$tpl = 'payment';
					$headerObjs = $this->filter($headerObjs, array('login'), false);
					$bodyObjs = $this->filter($bodyObjs, array('juser', 'juser_e'), false);
				}
				$this->loadAddons($headerObjs, 'registerOS');
				$this->loadAddons($bodyObjs, 'registerOS');
				$this->loadAddons($footerObjs, 'registerOS');
				$this->assignRef('registerOS_header', $headerObjs);
				$this->assignRef('registerOS_body', $bodyObjs);
				$this->assignRef('registerOS_footer', $footerObjs);
			}
		}
		$profiles = $this->getProfileList();
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('var oseGetProfileFields = function()	{return ' . oseJson::encode($profiles) . '};');
		$this->prepareDocument();
		$this->addTemplatePath(JPATH_SITE . DS . 'ose' . DS . 'msc' . DS . 'views' . DS . 'register' . DS . 'tmpl');
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
		$document->setMetaData('Content-Type', 'text/html;charset=utf-8');
		$document->setMetaData('encoding', 'utf-8');
	}
	function getAddons($type) {
		return oseMscAddon::getAddonList($type, false, null, 'obj');
	}
	private function loadAddons($objs, $type) {
		$path = null;
		foreach ($objs as $obj) {
			$this->loadAddon($obj, $type);
		}
	}
	private function loadAddon($obj, $type) {
		$path = null;
		$path = oseMscMethods::getAddonPath("{$obj->name}.js", $type);
		if (!empty($obj->addon_name)) {
			oseHTML::matchScript($path, 'msc');
		}
	}
	public function filter($objs, $addons, $exact = true) {
		foreach ($objs as $key => $obj) {
			if ($exact) {
				if (in_array($obj->name, $addons)) {
					unset($objs[$key]);
				}
			} else {
				foreach ($addons as $addon) {
					if (strpos($obj->name, $addon) === false) {
						continue;
					} else {
						unset($objs[$key]);
					}
				}
			}
		}
		$objs = array_values($objs);
		return $objs;
	}
	public function initJs() {
		$result1 = $this->generateCountryStateData();
		$result2 = $this->generateMscIdOption();
		$result = array_merge($result1, $result2);
		$config = oseRegistry::call('msc')->getConfig('locale', 'obj');
		$jsonCountry = oseObject::getValue($config, 'default_country_json', false);
		if (!$jsonCountry) {
			$jsonCountry = oseJson::encode(array('code3' => 'USA', 'code2' => 'US'));
		} else {
			$jsonCountry = oseObject::getValue($config, 'default_country_json');
		}
		$jsonState = oseObject::getValue($config, 'default_state_json', false);
		$regConfig = oseRegistry::call('msc')->getConfig('register', 'obj');
		$payment_method_note = oseObject::getValue($regConfig, 'payment_method_note', null);
		$hide_billinginfo = oseObject::getValue($regConfig, 'hide_billinginfo', false) ? 'true' : 'false';
		$hide_payment = oseObject::getValue($regConfig, 'hide_payment', false) ? 'true' : 'false';
		$model = $this->getModel('register');
		$item = $model->getBillingInfo();
		$bill = array('total' => 1, 'success' => true, 'result' => $item);
		$js = oseHTML::getInstance('js');
		$js->append('oseMsc.defaultSelectedCountry=Ext.decode(\'' . $jsonCountry . '\');');
		if ($jsonState) {
			$js->append('oseMsc.defaultSelectedState=Ext.decode(\'' . $jsonState . '\');');
		}
		$js->append('oseMsc.payment_method_note="' . $payment_method_note . '";');
		$js->append('oseMsc.hide_billinginfo=' . $hide_billinginfo . ';');
		$js->append('oseMsc.hide_payment=' . $hide_payment . ';');
		$js->append('oseMsc.reg.bill_country="";');
		$js->append('oseMsc.reg.bill_state="";');
		$js->append('oseMsc.reg.bill=' . oseJson::encode($bill) . ';');
		$js->append('oseMsc.mscList = ' . oseJson::encode($result['mscList']) . ';');
		$js->append('oseMsc.mscOptions = ' . oseJson::encode($result['mscOptions']) . ';');
		$js->append('oseMsc.currency = ' . oseJson::encode($result['currency']) . ';');
		$js->append('oseMsc.countryData = ' . oseJson::encode($result['country']) . ';');
		$js->append('oseMsc.stateData = ' . oseJson::encode($result['state']) . ';');
		$js->makeItClosure();
		$js->wrap();
		return $js->toString();
	}
	protected function getProfileList() {
		$db = JFactory::getDBO();
		$query = "SELECT * FROM `#__osemsc_fields` WHERE `published` = '1' ORDER BY `ordering`";
		$db->setQuery($query);
		$items = $db->loadObjectList();
		foreach ($items as $item) {
			if ($item->type == 'radio') {
				$params = explode(',', $item->params);
				foreach ($params as $param) {
					$option = array();
					$option['boxLabel'] = $param;
					$option['inputValue'] = $param;
					$options[] = $option;
				}
				$item->params = $options;
				unset($options);
			}
		}
		$result = array();
		$result['total'] = count($items);
		$result['results'] = $items;
		return $result;
	}
}
