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
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
jimport('joomla.plugin.plugin');

class plgUserOseuser extends JPlugin
{
    function plgUserOseuser(&$subject, $config)
    {
        parent::__construct($subject, $config);
    }
    function onUserAfterSave($user, $isnew, $succes, $msg)
    {
        // Load Jooma and OSE Functions
        $mainframe = JFactory::getApplication();

	    require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
        if ($isnew && !empty($user))
        {
            $db = JFactory::getDBO();
            $params = $this->params;
            $autoUpdate = $params->get('autoUpdate');
            $autoUpdateGroup = $params->get('autoUpdateGroup');

            $comUserParams = JComponentHelper::getParams('com_users');

			$useractivation = $comUserParams->get('useractivation');

			if($useractivation == 1 || $useractivation ==2)
			{
				$session = JFactory::getSession();
				$oseUser = array();
				$oseUser['user_id'] = oseObject::getValue($user,'id');
				$oseUser['block'] = true;
				$oseUser['activation'] = true;
				$session->set('ose_user',$oseUser);
			}
			else
			{
				$memConfig= oseMscConfig :: getConfig('register', 'obj');
				if(!$memConfig->auto_login)
				{
					$session = JFactory::getSession();
					$oseUser = array();
					$oseUser['user_id'] = oseObject::getValue($user,'id');
					$oseUser['block'] = true;
					$oseUser['activation'] = true;
					$session->set('ose_user',$oseUser);
				}
			}

			if ($autoUpdate==true && !empty($autoUpdateGroup))
			{
				if (!class_exists("oseMscPublic"))
				{
					require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'helpers'.DS.'oseMscPublic.php');
				}

	            $cart = oseMscPublic::getCart();
	            $items = $cart->get('items');
				$update = true;
	            if(count($items) >0)
	            {
	             	foreach($items as $item)
	            	{
	            		if($item['entry_id'] == $autoUpdateGroup)
	            		{
	            			$update = false;
	            		}
	            	}
	            }

	        	if (!empty($user) && $update)
	        	{
					// join msc
					if($mainframe->isSite())
	            	{
	            		$cart = oseMscPublic::getCart();
	            		// get current item
	            		$cCart = $cart->cart;
	            		$cart->init();
	            		$cart->__construct();
	            		$cart->updateParams('payment_mode','m');
		            	$paymentInfo = oseRegistry::call('msc')->getPaymentMscInfo($autoUpdateGroup,$cart->get('currency'),0);
						$nItem = array('entry_id'=>$autoUpdateGroup,'entry_type'=>'msc','msc_option'=>oseObject::getValue($paymentInfo,'msc_option'));
						$cart->addItem($nItem['entry_id'],$nItem['entry_type'],$nItem);

						$cart->update();

	            		//$orderPaymentInfo = array();//$cart->output();
	            		oseMscAddon::runAction('register.payment.save',array('member_id'=>$user['id'],'payment_method'=>'none'), true, false);
	            		$order_id = JRequest::getInt('order_id',0);
						oseRegistry::call('payment')->getInstance('Order')->confirmOrder($order_id, array());

	            		$cart->init();
	            		$cart->__construct();


	            		$cart1 = oseMscPublic::getCart();$cart1->__construct();
	            		$cart1->cart = $cCart;
	            		$cart1->update();

	            	}
	            	else
	            	{
	            		$cart = oseMscPublic::getCart();
	            		// get current item
	            		//$cItems = $cart->output();
	            		//$cart->init();
	            		$cart->updateParams('payment_mode','m');
		            	$paymentInfo = oseRegistry::call('msc')->getPaymentMscInfo($autoUpdateGroup,$cart->get('currency'),0);
						$nItem = array('entry_id'=>$autoUpdateGroup,'entry_type'=>'msc','msc_option'=>oseObject::getValue($paymentInfo,'msc_option'));
						$cart->addItem($nItem['entry_id'],$nItem['entry_type'],$nItem);

						$cart->update();

	            		$orderPaymentInfo = array();//$cart->output();
	            		$orderResult = oseMscAddon::runAction('register.payment.save',array('member_id'=>$user['id'],'payment_method'=>'none'), true, false);
	            		$order_id = JRequest::getInt('order_id',0);
						oseRegistry::call('payment')->getInstance('Order')->confirmOrder($order_id, array());
	            		$cart->init();
	            		/*$orderPaymentInfo = $cart->output();
	            		oseMscPublic::generateOrder($user['id'],'none',$orderPaymentInfo);
	            		$order_id = JRequest::getInt('order_id',0);oseExit($order_id);
						oseRegistry::call('payment')->getInstance('Order')->confirmOrder($order_id, array());*/
	            	}
	        	}
			}

			$enableCBUser = $params->get('enableCBUser');
			if ($enableCBUser==true)
			{
				$oseUser['user_id'] = oseObject::getValue($user,'id');
				$query= "SELECT * FROM `#__comprofiler` WHERE `user_id` = ". (int)$oseUser['user_id'];
				$db->setQuery($query);
				$result2= $db->loadResult();
				if(empty($result2)) {
					$query= "INSERT INTO `#__comprofiler` (`id`, `user_id`, `firstname`, `middlename`, `lastname`, `hits`, `message_last_sent`, `message_number_sent`, `avatar`, `avatarapproved`, `approved`, `confirmed`, `lastupdatedate`, `registeripaddr`, `cbactivation`, `banned`, `banneddate`, `unbanneddate`, `bannedby`, `unbannedby`, `bannedreason`, `acceptedterms`) VALUES
							(".(int)$oseUser['user_id'].", ".(int)$oseUser['user_id'].", NULL, NULL, NULL, 0, '0000-00-00 00:00:00', 0, NULL, 1, 1, 1, '0000-00-00 00:00:00', '', '', 0, NULL, NULL, NULL, NULL, NULL, 0)";
					} else {
						$query= "UPDATE `#__comprofiler` SET `approved` = '1', `confirmed` = '1', `banned` = '0' WHERE `id` =".(int)$oseUser['user_id'];
					}
					$db->setQuery($query);
					$db->query();
			}
		}
    }

    function onUserAfterDelete($user, $succes, $msg)
    {
        if (!$succes)
        {
            return false;
        }
		$mainframe = JFactory::getApplication();

        $db = JFactory::getDBO();
        $db->setQuery('DELETE FROM #__session WHERE userid = ' . $db->Quote($user['id']));
        $db->Query();

		$db = JFactory::getDBO();
		$query = "DELETE FROM `#__osemsc_member` WHERE `member_id` = ".(int) $user['id']." LIMIT 1";
		$db->setQuery($query);
		$db->query();

		$query = "DELETE FROM `#__osemsc_member_expired` WHERE `member_id` = ".(int) $user['id']." LIMIT 1";
		$db->setQuery($query);
		$db->query();

		$query = "DELETE FROM `#__osemsc_billinginfo` WHERE `user_id`=".(int) $user['id']." LIMIT 1";
		$db->setQuery($query);
		$db->query();

		$query = "DELETE FROM `#__osemsc_userinfo` WHERE `user_id`=".(int) $user['id']." LIMIT 1";
		$db->setQuery($query);
		$db->query();

		$query= "DELETE FROM `#__osemsc_order` WHERE `user_id` = ".(int) $user['id']." LIMIT 1";
		$db->setQuery($query);
		$db->query();

		$query= "DELETE FROM `#__osemsc_member_history` WHERE `member_id` = ".(int) $user['id']." LIMIT 1";
		$db->setQuery($query);
		$db->query();

        return true;
    }
    /**

     * This method should handle any login logic and report back to the subject

     *

     * @access	public

     * @param   array   holds the user data

     * @param 	array   array holding options (remember, autoregister, group)

     * @return	boolean	True on success

     * @since	1.5

     */
    function onUserLogin($user, $options = array())
    {
    	require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $option=JRequest::getVar("option");
        jimport('joomla.user.helper');
        if ($mainframe->isAdmin() )
        {
            return; // Dont run in admin
        }

        $pluginParams = $this->params;
        //$loginRedirection = $pluginParams->get('loginRedirection');  // Move to OSE Router;
        $singleLogin = $pluginParams->get('singleLogin');
		$loginRedirect= $pluginParams->get('loginRedirect');
		$autoUpdate = $pluginParams->get('autoUpdateOnLogin');
		$autoUpdateGroup = $pluginParams->get('autoUpdateGroup');
		$user = self::getUser($user);

		// Update BillingInfo names;
		$query = " SELECT user_id, firstname FROM #__osemsc_billinginfo WHERE user_id = ".(int)$user->id;
		$db->setQuery($query);
		$objects = $db->loadObject();
		if (!empty($objects->user_id) && empty($objects->firstname))
		{
			$thisUser = JFactory::getUser($objects->user_id);
			$names = explode(' ',$thisUser->name);
			$firstname =  $db->Quote($names[0]);
			$lastname = count($names)>1?$names[1]:'null';
			$lastname =  $db->Quote($lastname);
			$query = "UPDATE `#__osemsc_billinginfo` SET `firstname` = {$firstname},`lastname` = {$lastname}  WHERE `user_id` = ".(int)$user->id;
			$db->setQuery($query);
			$db->Query();
		}

		$query = " SELECT user_id, lastname FROM #__osemsc_billinginfo WHERE user_id = ".(int)$user->id;
		$db->setQuery($query);
		$objects = $db->loadObject();
		if (!empty($objects->user_id) && empty($objects->lastname))
		{
			$query = "UPDATE `#__osemsc_billinginfo` SET `lastname` = (SELECT lastname FROM #__osemsc_userinfo WHERE user_id = ".(int)$user->id.") WHERE `user_id` =".(int)$user->id;
			$db->setQuery($query);
			$db->Query();
		}
		// BillingInfo names;
		$enableVMUser = $pluginParams->get('enableVMUser');
		if ($enableVMUser==true)
			{
				$oseUser['user_id'] = oseObject::getValue($user,'id');
				$query= "SELECT * FROM `#__virtuemart_userinfos` WHERE `address_type` = 'BT' AND `virtuemart_user_id` = ". (int)$user->id;
				$db->setQuery($query);
				$result= $db->loadObject();
				if(!empty($result)) {
					$query = "SELECT `country_3_code` FROM `#__virtuemart_countries` WHERE  `virtuemart_country_id` = ". $db->Quote($result->virtuemart_country_id);
					$db->setQuery($query);
					$country_3_code=$db->loadResult();
					if (!empty($country_3_code))
					{
						$result->country = $country_3_code;
					}
					else
					{
						$result->country = substr($result->country, 0,3);
					}

					$query = "SELECT `state_2_code` FROM `#__virtuemart_states` WHERE  `virtuemart_state_id` = ". $db->Quote($result->virtuemart_state_id);
					$db->setQuery($query);
					$state_2_code=$db->loadResult();
					if (!empty($state_2_code))
					{
						$result->state = $state_2_code;
					}
					else
					{
						$result->state = substr($result->state, 0,2);
					}
					
					$query= "SELECT count(*) FROM `#__osemsc_billinginfo` WHERE `user_id` = ". (int)$user->id;
					$db->setQuery($query);
					$result2= $db->loadResult();
					if (empty($result2))
					{
						$query= "INSERT INTO `#__osemsc_billinginfo` (`user_id`, `firstname`, `lastname`, `company`, `addr1`, `addr2`, `city`, `state`, `country`, `postcode`, `telephone`) VALUES
						(".(int)$user->id.", '{$result->first_name}', '{$result->last_name}', '{$result->company}', '{$result->address_1}', '{$result->address_2}', '{$result->city}', '{$result->state}', '{$result->country}', '{$result->zip}', '{$result->phone_1}');";
					}
					else
					{
						$query = " UPDATE `#__osemsc_billinginfo` " .
								 " SET `firstname` = '{$result->first_name}', " .
								 " `lastname` = '{$result->last_name}', " .
								 " `company` = '{$result->company}', " .
								 " `addr1` = '{$result->address_1}', " .
								 " `addr2` = '{$result->address_2}', " .
								 " `city` = '{$result->city}', " .
								 " `state` = '{$result->state}', " .
								 " `country` = '{$result->country}', " .
								 " `postcode` = '{$result->zip}', " .
								 " `telephone` = '{$result->phone_1}' " .
								 " WHERE `user_id` =".(int)$user->id;
					}
					$db->setQuery($query);
					$db->query();

				}
			}
		if ($autoUpdate==true && !empty($autoUpdateGroup))
		{
			if (!class_exists("oseMscPublic"))
			{
				require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'helpers'.DS.'oseMscPublic.php');
			}
			oseAppConfig::load('msc');
            $cart = oseMscPublic::getCart();
            $items = $cart->get('items');
			$update = true;
            if(count($items) >0)
            {
             	foreach($items as $item)
            	{
            		if($item['entry_id'] == $autoUpdateGroup)
            		{
            			$update = false;
            		}
            	}
            }

            $query = " SELECT count(*) FROM #__osemsc_member WHERE msc_id = ".(int)$autoUpdateGroup." AND member_id=".(int)$user->id;
			$db->setQuery($query);
			$exists = $db->loadResult();
        	if ($update && empty($exists))
        	{
				// join msc
				if($mainframe->isSite())
            	{
            		$cart = oseMscPublic::getCart();
            		// get current item
            		$cCart = $cart->cart;
            		$cart->init();
            		$cart->__construct();
            		$cart->updateParams('payment_mode','m');
	            	$paymentInfo = oseRegistry::call('msc')->getPaymentMscInfo($autoUpdateGroup,$cart->get('currency'),0);
					$nItem = array('entry_id'=>$autoUpdateGroup,'entry_type'=>'msc','msc_option'=>oseObject::getValue($paymentInfo,'msc_option'));
					$cart->addItem($nItem['entry_id'],$nItem['entry_type'],$nItem);

					$cart->update();

            		//$orderPaymentInfo = array();//$cart->output();
            		oseMscAddon::runAction('register.payment.save',array('member_id'=>$user->id,'payment_method'=>'none'), true, false);
            		$order_id = JRequest::getInt('order_id',0);
					oseRegistry::call('payment')->getInstance('Order')->confirmOrder($order_id, array());

            		$cart->init();
            		$cart->__construct();


            		$cart1 = oseMscPublic::getCart();$cart1->__construct();
            		$cart1->cart = $cCart;
            		$cart1->update();

            	}
            	else
            	{
            		$cart = oseMscPublic::getCart();
            		// get current item
            		//$cItems = $cart->output();
            		//$cart->init();
            		$cart->updateParams('payment_mode','m');
	            	$paymentInfo = oseRegistry::call('msc')->getPaymentMscInfo($autoUpdateGroup,$cart->get('currency'),0);
					$nItem = array('entry_id'=>$autoUpdateGroup,'entry_type'=>'msc','msc_option'=>oseObject::getValue($paymentInfo,'msc_option'));
					$cart->addItem($nItem['entry_id'],$nItem['entry_type'],$nItem);

					$cart->update();

            		$orderPaymentInfo = array();//$cart->output();
            		$orderResult = oseMscAddon::runAction('register.payment.save',array('member_id'=>$user->id,'payment_method'=>'none'), true, false);
            		$order_id = JRequest::getInt('order_id',0);
					oseRegistry::call('payment')->getInstance('Order')->confirmOrder($order_id, array());
            		$cart->init();
            		/*$orderPaymentInfo = $cart->output();
            		oseMscPublic::generateOrder($user['id'],'none',$orderPaymentInfo);
            		$order_id = JRequest::getInt('order_id',0);oseExit($order_id);
					oseRegistry::call('payment')->getInstance('Order')->confirmOrder($order_id, array());*/
            	}
        	}
		}
    	$member = oseRegistry::call('member');
		$member->instance($user->id);
		$mscs = $member->getAllOwnedMsc(true,null,'obj');
		$date = oseHTML::getDateTime();
		$date = strtotime($date);
		$updateStatus = false;
		if(!empty($mscs))
		{
			foreach($mscs as $msc)
			{
				$startdate = strtotime($msc->start_date);
				$expdate = strtotime($msc->expired_date);
				$params = oseJson::decode($msc->params);
				
				if($msc->status && ($startdate>$date) && (($date<$expdate) || $msc->eternal == 1 || $msc->expired_date == '0000-00-00 00:00:00'))
				{
					$updateStatus = true;
					$status = 0;
				}elseif(empty($msc->status) && ($startdate < $date) && (($date<$expdate) || $msc->eternal == 1 || $msc->expired_date == '0000-00-00 00:00:00'))
				{
					$updateStatus = true;
					$status = 1;
				}
				
				if($updateStatus)
				{
					$query = "SELECT * FROM `#__osemsc_order_item` WHERE `order_id` = ".$params->order_id;
					$db->setQuery($query);
					$orderItem = $db->loadObject();
					$orderItemParams = oseJson::decode($orderItem->params);
					if($orderItemParams->recurrence_mode == 'fixed')
					{
						$query = "UPDATE `#__osemsc_member` SET `status` = '{$status}' WHERE `id` = ".$msc->id;
						$db->setQuery($query);
						$db->query();
					}
				}
				
			}
		}
 	    if ($singleLogin>0)
	    {
		  self::singleLogin($singleLogin, $db, $user, $mainframe);
	    }
        if ($loginRedirect == true)
        {
        	if (!empty($user))
        	{
				self::loginRedirect($user->id);
        	}
        }

       return true;
    }


    /**
     * This method should handle any logout logic and report back to the subject
     *
     * @access public
     * @param  array	holds the user data
     * @param 	array   array holding options (client, ...)
     * @return object   True on success
     * @since 1.5
     */
    function onUserLogout($user, $options = array())
    {
    	$mainframe= JFactory :: getApplication('SITE');
		if($mainframe->isAdmin()) {
			return true;
		}
        //Make sure we're a valid user first
        if ($user['id'] == 0) return true;
        $my = JFactory::getUser();
        //Check to see if we're deleting the current session
        if ($my->get('id') == $user['id'])
        {
            // Hit the user last visit field
            $my->setLastVisit();
            // Destroy the php session for this user
            $session = JFactory::getSession();
            $session->destroy();
        }
        else
        {
            // Force logout all users with that userid
            $table = &JTable::getInstance('session');
            if(empty($options['clientid'])){
				$table->destroy($user['id']);
			}else{
            	$table->destroy($user['id'], $options['clientid']);
			}
        }
        $pluginParams= $this->params;
		if($pluginParams->get('logoutRedirect', false) == true) {
			self :: redirect('logout');
		}
        return true;
    }
	function redirect($type=null, $message=null) {
		$mainframe= JFactory :: getApplication('SITE');
        $pluginParams = $this->params;
		$sefroutemethod= $pluginParams->get('sefroutemethod');
		if ($type=="logout")
		{
			$option = JRequest::getCmd('option');
			if($option == 'com_osemsc')
			{
				return;
			}
			$db= JFactory :: getDBO();
			$redmenuid= $this->params->def('logoutredmenuid', '0');
			if (!empty($redmenuid))
			{
				$query= "SELECT * FROM `#__menu` WHERE `id` = ". (int)$redmenuid;
				$db->setQuery($query);
				$menu= $db->loadObject();
				require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'helpers'.DS.'oseMscPublic.php');
				$redURL = $uri = oseMscPublic::pointedRedirection($sefroutemethod,$menu);
			}
			else
			{
				$query= "SELECT id, link FROM `#__menu` WHERE `home` = '1'";
				$db->setQuery($query);
				$result= $db->loadObject();
				$Itemid=(!empty($result)) ? "&Itemid=".$result->id : "";
				$redirect= JRoute :: _($result->link.$Itemid);
				$redURL= str_replace("&amp;", "&", $redirect);
			}
			$mainframe->redirect($redURL);
		}
	}
    function getUser($user)
    {
        $instance = new JUser();
        if ($id = intval(JUserHelper::getUserId($user['username'])))
        {
            $instance->load($id);
            return $instance;
        }
        else
        {
        	return false;
        }
    }

    function singleLogin($singleLogin, $db, $user, $mainframe)
    {
		  $session =JFactory::getSession();
 		  $session_id = $session->getId();

	      if ($singleLogin=='1')
	      {
		      $query = "DELETE FROM `#__session` WHERE `session_id` <> ".$db->Quote($session_id)." AND `username` = ".$db->Quote($user->username,true)." AND `client_id` = 0";
		      $db->setQuery($query);
	      	  $db->query();
      	  }
	      elseif ($singleLogin=='2')
	      {
		      $query = "SELECT * FROM `#__session` WHERE `session_id` <> ".$db->Quote($session_id)." AND `username` = ".$db->Quote($user->username,true)." AND `client_id` = 0";
		      $db->setQuery($query);
		      $result = $db->loadObject();
	     	  if (!empty($result))
	      	   {
	      	   	 //$mainframe -> logout ();
	      	   	 $query = "DELETE FROM `#__session` WHERE `session_id` = ".$db->Quote($session_id)." AND `username` = ".$db->Quote($user->username,true)." AND `client_id` = 0";
	      	   	 // $query = "DELETE FROM `#__session` WHERE `session_id` = ".$db->Quote($session_id)." AND `username` = ".$db->Quote($user->username,true)." AND `client_id` = 0";
		      	 $db->setQuery($query);
	      	  	 $db->query();
	      	  	 session_unset();
	      	  	 $session->destroy();
	      	  	 $slredmenuid = $this->params->get('slredmenuid',1);
	      	  	 $sefroutemethod= $this->params->get('sefroutemethod');
	      	  	 $menu = $this->getMenuObj($slredmenuid);
	      	   	 $uri = oseMscPublic::pointedRedirection($sefroutemethod,$menu);
				 $mainframe->redirect($uri, JText::_('Multiple user login is disabled in this website, please wait until this user account logs out.'));
	      	   }
	      	   
	      }
    }
    function getMenuObj($id)
    {
    	$db= JFactory :: getDBO();
    	$query= " SELECT * FROM `#__menu` where `id` = ".(int)$id;
		$db->setQuery($query);
		$menu= $db->loadObject();
		return $menu; 
    }
	function loginRedirect($member_id) {
		$db= JFactory :: getDBO();
		$option= JRequest :: getVar('option');
		$controller= JRequest :: getVar('controller');
		$task= JRequest :: getVar('task');
		if($option == 'com_osemsc' && $controller=="register" && $task=="save")
		{
			return;
		}
		$query= " SELECT a.menuid, m.link, m.id, m.alias,m.path"
				." FROM `#__menu` as m"
				." LEFT JOIN `#__osemsc_acl` as a ON a.menuid = m.id"
				." LEFT JOIN `#__osemsc_member` as b ON b.msc_id = a.id"
				." WHERE b.member_id={$member_id} AND b.status = 1 ORDER BY a.menuid DESC LIMIT 1"
				;
		$db->setQuery($query);
		$menu= $db->loadObject();

		if (!empty($menu))
        {
			$uri= JRoute :: _(JURI::root().$menu->link."&Itemid=".$menu->id);
			$uri= str_replace("&amp;", "&", $uri);
			$mainframe= JFactory :: getApplication();

			if($option == 'com_osemsc' && empty($controller))
			{
				$mainframe->redirect($uri, '');
			}
			elseif($option == 'com_osemsc' && !empty($controller))
			{
				$session =JFactory::getSession();
				$session->set('oseReturnUrl',base64_encode(JURI::root().$menu->link."&Itemid=".$menu->id));
			}
			else
			{
				$pluginParams = $this->params;
				$sefroutemethod= $pluginParams->get('sefroutemethod');
				require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'helpers'.DS.'oseMscPublic.php');
				$uri = oseMscPublic::pointedRedirection($sefroutemethod,$menu);
				$mainframe= JFactory :: getApplication();
				$mainframe->redirect($uri);
			}
        }
        else
        {
			$mainframe= JFactory :: getApplication();

       		$db= JFactory :: getDBO();
			$redmenuid= $this->params->def('redmenuid', '0');
       	 	$query = "SELECT count(*) FROM `#__osemsc_member` WHERE `status` = 0 AND `member_id` = ".$member_id;
			$db->setQuery($query);
			$result = $db->loadResult();
			if(!empty($result))
			{
				$redmenuid= $this->params->def('expmem_redmenuid', $redmenuid);
			}
			$sefroutemethod= $this->params->get('sefroutemethod');
			$option = JRequest::getVar('option');
			if (!empty($redmenuid) && $option != 'com_osemsc')
			{
				$query= "SELECT * FROM `#__menu` WHERE `id` = ". (int)$redmenuid;
				$db->setQuery($query);
				$menu= $db->loadObject();
				require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'helpers'.DS.'oseMscPublic.php');
				$uri = oseMscPublic::pointedRedirection($sefroutemethod,$menu);
				$mainframe->redirect($uri);
			}
			if($option == 'com_osemsc' && empty($controller))
			{
				//$mainframe->redirect('index.php?option=com_osemsc&view=member', '');
			}
			elseif($option == 'com_osemsc' && !empty($controller))
			{
				//$session =JFactory::getSession();
				//$session->set('oseReturnUrl',base64_encode('index.php?option=com_osemsc&view=member'));
			}

        }
	}


}
?>