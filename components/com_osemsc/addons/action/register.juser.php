<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterJuser extends oseMscAddonActionRegister
{
	/*
	 *  @params array(); inner means only run by system; field_name means check specific field
	 */
	
	
	protected function validateJuser_username($val)
	{
		$user = JFactory::getUser();
		
		if($user->guest)
		{
			return oseMscPublic::uniqueUserName($val, 0);
		}
		else
		{
			return oseMscPublic::uniqueUserName($val, $user->id);
		}
	}
}
?>