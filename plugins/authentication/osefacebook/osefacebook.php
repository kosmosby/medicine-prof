<?php
/**
* Authorization plugin
* @Copyright (C) 2009-2011 Gavick.com
* @ All rights reserved
* @ Joomla! is Free Software
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @version $Revision: GK4 1.0 $
**/
// No direct access
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');
class plgAuthenticationOSEFacebook extends JPlugin
{
	function onUserAuthenticate($credentials, $options, &$response)
	{
		jimport('joomla.user.helper');
		jimport('joomla.user.helper');
		
		$response->type = 'Joomla';
		// Joomla does not like blank passwords
		if (empty($credentials['password'])) {
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');
			return false;
		}
		
		if($credentials['password'] == 'Facebook' && $credentials['username'] == 'Facebook') {
			$oseConfig = $this->getOSEConfig();
			
			require 'sdk/facebook.php';
			
			$facebook = new Facebook(array(
			  'appId'  => $oseConfig->facebookapiid,
			  'secret' => $oseConfig->facebookapisec
			));
			
			// See if there is a user from a cookie
			$user = $facebook->getUser();
			$user_profile = null;
			
			if ($user) {
			  try {
			    // Proceed knowing you have a logged in user who's authenticated.
			    $user_profile = $facebook->api('/me');
			  } catch (FacebookApiException $e) {
			    echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
			    $user = null;
			  }
			}
									
			if($user_profile) {
				// Initialise variables.
				$conditions = '';
				// Get a database object
				$db		= JFactory::getDbo();
				$query	= $db->getQuery(true);
				$query->select('id');
				$query->from('#__users');
				$query->where('email = ' . $db->Quote($user_profile['email']));
				$db->setQuery($query);
				$result = $db->loadObject();
		
				if ($result) {
					$user = JUser::getInstance($result->id); // Bring this in line with the rest of the system
					$response->email = $user->email;
					$response->username = $user->username;
					$response->fullname = $user->name;
					
					if (JFactory::getApplication()->isAdmin()) {
						$response->language = $user->getParam('admin_language');
					} else {
						$response->language = $user->getParam('language');
					}
					
					$response->status = JAuthentication::STATUS_SUCCESS;
					$response->error_message = JText::_('PLG_GK_FACEBOOK_SUCCESS') . '<strong>' . $user->username . '</strong>';
				} else {					
					if($this->params->get('auto_register', false) == 1) {
						$user = $this->createUser($user_profile['email'], $user_profile['username'], $user_profile['name']); 
						if (!empty($user))
						{
							$this->sendEmail($user); 
						}	
						$response->email = $user_profile['email'];
						$response->username = $user_profile['username'];
						$response->fullname = $user_profile['name'];
				
						$response->status = JAuthentication::STATUS_SUCCESS;
						$response->error_message = JText::_('PLG_GK_FACEBOOK_NEW_ACCOUNT') . '<strong>' . $user_profile['name'] . '</strong>';
					} else {
						$response->status = JAuthentication::STATUS_FAILURE;
						$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
					}
				}
			} 
		} else {
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
		}
		
		return $response;		
	}
	private function sendEmail($user)
	{
		if (file_exists(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php') && file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ose_cpu'.DS.'define.php') && !file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'installer.dummy.ini'))
		{
			require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		}
		$emailConfig = oseMscConfig::getConfig('email', 'obj');
		if (!empty($emailConfig->default_reg_email)) {
			$member = oseRegistry::call('member');
			$email = $member->getInstance('email');
			$emailTempDetail = $email->getDoc($emailConfig->default_reg_email, 'obj');
			if (!empty($emailTempDetail)) {
				$variables = $email->getEmailVariablesRegistration($user->id);
				$variables['user'] = oseObject::setValue($variables['user'], 'password', $user->password_clear);
				$emailParams = $email->buildEmailParams($emailTempDetail->type);
				$emailDetail = $email->transEmail($emailTempDetail, $variables, $emailParams);
				$email->sendEmail($emailDetail, $user->email);
				if ($emailConfig->sendReg2Admin) {
					$email->sendToAdminGroup($emailDetail, $emailConfig->admin_group);
				}
			}
		}
	}
	function getOSEConfig()
	{
		$db=JFactory::getDBO();
		$query = " SELECT * FROM `#__osemsc_configuration` "
			    ." WHERE `type` = 'register'"
			    ." AND (`key` ='enable_fblogin' OR `key` ='facebookapiid' OR `key` ='facebookapisec')";
		$db->setQuery($query);
		$objs = $db->loadObjectList();
		$return = new stdClass();
		foreach ($objs as $obj)
		{
			$key = $obj->key; 
			$return->$key = $obj->value; 
		}	
		return $return; 
	}
	private function createUser($email, $username, $name)
	{
		jimport('joomla.application.component.helper');
		$config	= JComponentHelper::getParams('com_users');
		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);
		$password = $this->randStr(8);
		$data = array(
				"name"=>$name,
				"username"=>$username,
				"password"=>$password,
				"password2"=>$password,
				"groups"=>array($defaultUserGroup),
				"email"=>$email
		);
		$user = clone(JFactory::getUser());
		//Write to database
		if(!$user->bind($data)) {
			throw new Exception("Could not bind data. Error: " . $user->getError());
		}
		if (!$user->save()) {
			throw new Exception("Could not save user. Error: " . $user->getError());
		}
		return $user; 
	}
	private function randStr($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
		// Length of character list
		$chars_length = (strlen($chars) - 1);
		// Start our string
		$string = $chars{rand(0, $chars_length)};
		// Generate random string
		for ($i = 1; $i < $length; $i = strlen($string)) {
			// Grab a random character from our list
			$r = $chars{rand(0, $chars_length)};
			// Make sure the same two characters don't appear next to each other
			if ($r != $string{$i - 1})
				$string .= $r;
		}
		// Return the string
		return $string;
	}
}