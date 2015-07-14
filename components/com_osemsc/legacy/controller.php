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
jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');
class osemscController extends JController {
	protected $controller= null, $_c= null;
	function display($cachable = false, $urlparams = false, $tpl= null) {
		$view= JRequest :: getWord('view', null);
		if(empty($view)) {
			JRequest :: setVar('view', 'memberships');
		} else {
			$user= JFactory :: getUser();
			switch($view) {
				case('register') :
					$config= oseMscConfig :: getConfig('register', 'obj');
					if(!empty($config->register_form) && $config->register_form != 'default') {
						switch($config->register_form) {
							case('onestep') :
								if(!$user->guest) {
									//JRequest::setVar('view','payment');
								}
								break;
						}
					} else {
						// Shopping Cart
					}
					break;
				case('payment') :
					JRequest :: setVar('view', 'register');
					$config= oseMscConfig :: getConfig('register', 'obj');
					if(empty($config->register_form) || $config->register_form == 'default') {
						JRequest :: setVar('view', 'register');
					}
					$items= oseMscPublic :: getCartItems();
					if(empty($items[0])) {
						JRequest :: setVar('view', 'memberships');
					}
					break;
				default :
					break;
			}
		}
		parent :: display($tpl);
	}
	function getCountry() {
		$result= oseRegistry :: call('msc')->getInstance('Methods')->getCountry();
		$result= oseJson :: encode($result);
		oseExit($result);
	}
	function action() {
		$result= array();
		$actionName= JRequest :: getString('action');
		$msc= oseRegistry :: call('msc');
		$action= $msc->getInstance('Addon')->parseAction($actionName);
		switch($action->type) {
			case('member') :
				$user= JFactory :: getUser();
				if($user->guest) {
					$result['success']= false;
					$result['title']= JText :: _('Error');
					$result['content']= JText :: _('You do not have access to execute');
				} else {
					$check= oseRegistry :: call('member')->getMemberPanelView('memberAuthor', 'member');
					//oseExit($action->name);
					//$member = oseRegistry::call('member');
					//$member->instance($user->id);
					if($action->name == 'msc_renew')
					{
						$result= $msc->runAddonAction($actionName);
					}
					elseif(count($check['addons']) > 0 )
					{
						foreach($check['addons'] as $aKey => $addon)
						{
							if( ($aKey == $action->name) || $addon->action == $action->name)
							{
								$result= $msc->runAddonAction($actionName);
							}
						}
					}
					else
					{
						$result['success']= false;
						$result['title']= JText :: _('Error');
						$result['content']= JText :: _('You do not have access to execute');
					}
					/*	
					if(!isset($check['addons'][$action->name]) || empty($check['addons'])) {
						$result['success']= false;
						$result['title']= JText :: _('Error');
						$result['content']= JText :: _('You do not have access to execute');
					} else {
						$result= $msc->runAddonAction($actionName);
					}*/
				}
				break;
			default :
				$result= $msc->runAddonAction($actionName);
				break;
		}
		$result= oseJson :: encode($result);
		oseExit($result);
	}
	function initControl() {
		// Require specific controller if requested
		$this->controller= JRequest :: getWord('controller', null);
		// get the Real Controller
		$this->_c= $this->getController();
	}
	/*

	 *  if ajax action, exit;

	 */
	function executeTask($task) {
		$this->_c->execute($task);
		$ajax= JRequest :: getBool('ajax', false);
		if($ajax) {
			//echo 'controller-test';
			exit;
		}
	}
	function getController() {
		$controller= $this->controller;
		if($controller) {
			require_once(OSEMSC_F_CONTROLLER.DS.$controller.'.php');
			$class= 'oseMscController'.$controller;
			return new $class();
		} else {
			return $this;
		}
	}
	function redirectE() {
		$this->_c->redirect();
	}
	function getAddon() {
		$result= array();
		$addon_name= JRequest :: getCmd('addon_name', null);
		$type= JRequest :: getCmd('addon_type', null);
		echo '<script type="text/javascript">'."\r\n";
		require_once(JPATH_SITE.DS.oseMscMethods :: getAddonPath($addon_name.'.js', $type));
		echo "\r\n".'</script>';
		oseExit();
	}
	function getMod() {
		$result= array();
		$addon_name= JRequest :: getCmd('addon_name', null);
		$type= JRequest :: getCmd('addon_type', null);
		echo '<script type="text/javascript">'."\r\n";
		require_once(JPATH_SITE.DS.oseMscMethods :: getJsModPath($addon_name, $type));
		echo "\r\n".'</script>';
		oseExit();
	}
	function getState() {
		$result= oseRegistry :: call('msc')->getInstance('Methods')->getState();
		$result= oseJson :: encode($result);
		oseExit($result);
	}
	
	function generateJs()
	{
		header('Content-Type: text/javascript');
	
		$types = JRequest::getVar('types',array(),'get','array');
	
		if(count($types) < 1)
		{
			exit;
		}
	
		$codes = array();
		foreach($types as $type)
		{
			$func = "generate{$type}Js";
			$codes[] = $this->{$func}();
		}
	
		$codes = implode("\r\n",$codes);
	
		oseExit($codes);
	}
	
	protected function generateAddonsJs()
	{
		header('Content-Type: text/javascript');
	
		$type = JRequest::GetCmd('addontype');
		$db = oseDB::instance();
		$user = JFactory::getUser();
		$array = array();
		$output = '';
		if($type == 'registerOS')
		{
			$headerObjs= oseMscAddon :: getAddonList('registerOS_header', false, null, 'obj');
			$bodyObjs= oseMscAddon :: getAddonList('registerOS_body', false, null, 'obj');
			$footerObjs= oseMscAddon :: getAddonList('registerOS_footer', false, null, 'obj');
			
			if($user->guest)
			{
				$headerObjs = $this->filter($headerObjs,array('welcome','login'),false);
				
			}
			else
			{
				$headerObjs = $this->filter($headerObjs,array('login'),false);
				$bodyObjs = $this->filter($bodyObjs,array('juser','juser_e', 'mailing', 'jomsocial'),false);
			}
			
			$array['header'] = $headerObjs;
			$array['body'] = $bodyObjs;
			$array['footer'] = $footerObjs;
			
			$formItems = array();
			foreach($array as $a)
			{
				foreach($a as $obj)
				{
					if(!empty($obj->addon_name))
					{
						$formItems[] = array('xtype'=>$obj->name);
					}
				}
			}
			$output .= "var getFormItems = function()	{return ".oseJson::encode($formItems)."};";
			$output .= "\r\n";
		}
		elseif ($type == 'member') 
		{
			$user= JFactory :: getUser();
			$member= oseRegistry :: call('member');
			$view= $member->getInstance('PanelView');
			$member->instance($user->id);
			$result= $member->getMemberPanelView('Member', 'member_user');
			
			$columns = array();
			$columns[] = array('type'=>'user','title' => JText::_('MEMBER_USER_ACCOUNT'));
			if(oseObject::getValue($result,'tpl',false))
			{
				switch($result['tpl'])
				{
					case( 'master' ):
					case( 'expired' ):
						$columns[] = array('type'=>'billing','title' => JText::_('BILLING_INFORMATION'));;
						$columns[] = array('type'=>'msc','title' => JText::_('MY_MEMBERSHIP'));;
						break;
					default:
						$columns[] = 'billing';
						break;
				}
			}
			$output .= "var getMemTypes = function()	{return ".oseJson::encode($columns)."};";
			$output .= "\r\n";
			
			foreach($columns as $column)
			{
				$result= $member->getMemberPanelView('Member', 'member_'.$column['type']);
				$array[$column['type']]= array_values($result['addons']);
			}
		}
		
		$output .= "var getAddons = function()	{return ".oseJson::encode($array)."};";
		return $output;
	}
	
	protected function generateLanguageJs()
	{
		$strings = oseMscPublic::text();
		$output = '(function(){var strings='.oseJson::encode($strings).';Joomla.JText.load(strings)})();';
		return $output;
	}
	
	protected function filter($objs,$addons,$exact = true)
	{
		foreach($objs as $key => $obj)
		{
			if($exact)
			{
				if(in_array($obj->name,$addons))
				{
					unset($objs[$key]);
				}
			}
			else
			{
				foreach($addons as $addon)
				{
					if(strpos($obj->name,$addon) === false)
					{
						continue;
					}
					else
					{
						unset($objs[$key]);
					}
				}
			}
		}
		return $objs;
	}
	
	
	protected function generateMscIdOptionJS()
	{
		//$model = $this->getModel('register');
		$list = oseMscPublic::getList();
		$cart = oseMscPublic::getCart();
		$selectedMsc = oseMscAddon::runAction('register.msc.getSelectedMsc',array());
		//oseExit($list);
		$options = array();
		$msc = oseRegistry::call('msc');
		foreach($list as $key => $entry)
		{
			$msc_id = oseObject::getValue($entry,'id');
	
			$node = $msc->getInfo($msc_id,'obj');
			$paymentInfos = $msc->getExtInfo($msc_id,'payment');
	
	
			$osePaymentCurrency = $cart->get('currency');
	
			$option = oseMscPublic::generatePriceOption($node,$paymentInfos,$osePaymentCurrency);
			$options = array_merge($options,$option);
		}
	
		$currency = oseMscPublic::getCurrency();
		$mscList = array('total'=>count($list),'results' => $list);
		$mscOptions = array('total'=>count($options),'results' => $options);
		$currency = array('total'=>count($currency),'results' => $currency);
	
		$output = "var getMscList = function()	{return ".oseJson::encode($mscList)."};";
		$output .= "\r\n";
		$output .= "var getMscOption = function()	{return ".oseJson::encode($mscOptions)."};";
		return $output;
	}
	
	protected function generateCountryStateJS()
	{
		//$model = $this->getModel('register');
		$country = oseMscMethods::getCountry();
		$state = oseMscMethods::getState();

		$output = "\r\n";
		$output .= "var getCountry = function()	{return ".oseJson::encode($country)."};";
		$output .= "\r\n";
		$output .= "var getState = function()	{return ".oseJson::encode($state)."};";
		return $output;
	}
	
	protected function generateTermsJS()
	{
		$member = oseRegistry::call('member');
		$terms = $member->getInstance('Email')->getTerms();
		$total = count($terms);
		
		$result = array();
		$result['total'] = $total;
		
		$terms = str_replace("../", JURI::root(), $terms);
		$result['results'] = $terms;
		
		$output = "\r\n";
		$output .= "var getTerms = function()	{return ".oseJson::encode($terms)."};";
		return $output;
	}
	
	protected function generatePaymentJS()
	{
		$pConfig = oseMscConfig::getConfig('payment','obj');

		$methods = array();

		if(!empty($pConfig->enable_cc))
		{
			$cc_methods = explode(',',$pConfig->cc_methods);

			foreach( $cc_methods as $cc_method)
			{
				switch ($cc_method)
				{
					case('authorize'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' =>  JText::_('Credit_Card'));
					break;

					case('paypal_cc'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;

					case('eway'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;

					case('epay'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;

					case('vpcash_cc'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;

					case('usaepay'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;
					
					case('oospay'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;
					
					case('ebs'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;
					
					default:
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;
				}
			}
		}

		if(!empty($pConfig->enable_paypal))
		{
			$methods[] = array('id'=> 1, 'value'=>'paypal', 'text' =>  JText::_('Paypal'));
		}

		if(!empty($pConfig->enable_gco))
		{
			$methods[] = array('id'=> 2, 'value'=>'gco', 'text' => JText::_('Google_Checkout'));
		}

		if(!empty($pConfig->enable_twoco))
		{
			$methods[] = array('id'=> 3, 'value'=>'2co', 'text' => JText::_('2Checkout'));
		}

		if(!empty($pConfig->enable_poffline))
		{
			$methods[] = array('id'=> 5, 'value'=>'poffline', 'text' => JText::_('Pay_Offline'));
		}

		if(!empty($pConfig->enable_vpcash))
		{
			$methods[] = array('id'=> 6, 'value'=>'vpcash', 'text' => JText::_('VirtualPayCash'));
		}

		if(!empty($pConfig->enable_bbva))
		{
			$methods[] = array('id'=> 7, 'value'=>'bbva', 'text' => JText::_('BBVA'));
		}
		
		if(!empty($pConfig->enable_payfast))
		{
			$methods[] = array('id'=> 8, 'value'=>'payfast', 'text' => JText::_('PayFast'));
		}

		if(!empty($pConfig->enable_clickbank))
		{
			$methods[] = array('id'=> 9, 'value'=>'clickbank', 'text' => JText::_('ClickBank'));
		}
		
		if(!empty($pConfig->enable_ccavenue))
		{
			$methods[] = array('id'=> 10, 'value'=>'ccavenue', 'text' => JText::_('CCAvenue'));
		}
		
		if(!empty($pConfig->enable_icepay))
		{
			$methods[] = array('id'=> 11, 'value'=>'icepay', 'text' => JText::_('ICEPAY'));
		}
		
		if(!empty($pConfig->enable_liqpay))
		{
			$methods[] = array('id'=> 12, 'value'=>'liqpay', 'text' => JText::_('LiqPay'));
		}
		
		if(!empty($pConfig->enable_realex))
		{
			$methods[] = array('id'=> 13, 'value'=>'realex_'.$pConfig->realex_mode, 'text' => JText::_('Realex Payments'));
		}
		
		if(!empty($pConfig->enable_sisow))
		{
			
			$methods[] = array('id'=> 14, 'value'=>'sisow', 'text' => JText::_('Sisow'));
		}
		
		if(!empty($pConfig->enable_pagseguro))
		{
			$methods[] = array('id'=> 15, 'value'=>'pagseguro', 'text' => JText::_('PagSeguro'));
		}
		
		if(!empty($pConfig->enable_paygate))
		{
			$methods[] = array('id'=> 16, 'value'=>'paygate', 'text' => JText::_('PayGate'));
		}
		
		if(!empty($pConfig->enable_quickpay))
		{
			$methods[] = array('id'=> 17, 'value'=>'quickpay', 'text' => JText::_('Quickpay'));
		}
		
		if(!empty($pConfig->enable_sagepay))
		{
			$methods[] = array('id'=> 18, 'value'=>'sagepay', 'text' => JText::_('sagepay'));
		}
		
		$msc_id = JRequest::getInt('msc_id');
		if(!empty($msc_id))
		{
			$db = oseDB::instance();
			$query = "SELECT * FROM `#__osemsc_ext` WHERE `id` = '{$msc_id}' AND `type` = 'hidepayment'";
	        $db->setQuery($query);
	        $data = $db->loadObject();
			$data = oseJson::decode($data->params);
			if(!empty($data->enable) && !empty($data->value))
			{
				$values = explode(",",$data->value);
				foreach($methods as $key => $val)
				{
					if(in_array($val['value'],$values))
					{
						unset($methods[$key]);
					}
				}
			}
		}
		
		$methods = array_values($methods);
		$result = array();

		$result['total'] = count($methods);
		$result['results'] = $methods;

		$output .= "\r\n";
		$output .= "var getPaymentMethod = function()	{return ".oseJson::encode($result)."};";
		return $output;
	}
	
	protected function generateRegInfoJs()
	{
		$initMscPayment= array('msc_id' => 0, 'msc_option' => null);
		$cart= oseMscPublic :: getCart();
		;
		$user= JFactory :: getUser();
		$member= oseRegistry :: call('member');
		$member->instance($user->id);
		$item= $member->getBillingInfo();
		foreach($item as $k => $v)
		{
			$item['bill_'.$k] = $v;
		}
		$cartItems= $cart->get('items');
		$cartItem= $cartItems[0];
		//oseExit($cartItem);
		//$item['msc_id'] = $cartItem['entry_id'];
		//$item['payment_mode'] = $cart->getParams('payment_mode');
		$item['msc_option']= $cartItem['msc_option'];
		
		$output .= "\r\n";
		$output .= "var getRegInfo = function()	{return ".oseJson::encode($item)."};";
		return $output;
	}
	
	protected function generateProfileJS()
	{
		$profile = oseRegistry::call('msc')->runAddonAction('register.profile.getOseProfile');
		$output .= "\r\n";
		$output .= "var getOSEProfile = function()	{return ".oseJson::encode($profile)."};";
		return $output;
	}
	
	function test()
	{
		$result = array();
		$result['success'] = true;
		
		oseExit(oseJson::encode($result));
	}
}
?>