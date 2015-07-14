<?php
/**
 * @copyright	Copyright (c) 2014 jomwallpost. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * eventbooking - Events Booking - Jomwall post Plugin
 *
 * @package		Joomla.Plugin
 * @subpakage	jomwallpost.jomwallpost
 */
class plgEventBookingJomwallpost extends JPlugin
{	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);	
		 $this->loadLanguage();	
	}
	
	public function onAfterPaymentSuccess($row) {
		require_once JPATH_ROOT.DS.'components'.DS.'com_eventbooking'.DS.'helper'.DS.'helper.php';
		require_once JPATH_ROOT.DS.'components'.DS.'com_awdwall'.DS.'models'.DS.'wall.php';
		$itemId = EventBookingHelper::getItemid();
		EventBookingHelper::loadLanguage();
		$db = & JFactory::getDBO();
		$user = & JFactory::getUser();
		jimport('joomla.utilities.date');		
		$db	 = & JFactory::getDBO() ;
		$today =& JFactory::getDate();		
		$sql = 'SELECT title FROM #__eb_events WHERE id='.$row->event_id ;
		$db->setQuery($sql);
		$eventTitle = $db->loadResult();
		$url = JRoute::_('index.php?option=com_eventbooking&view=event&id='.$row->event_id.'&Itemid='.$itemId);
		$eventTitle = '<a href="'.$url.'"><strong>'.$eventTitle.'<strong></a>' ;		
		
		$lang = JFactory::getLanguage();
		$tag = $lang->getTag();
		if (!$tag)
			$tag = 'en-GB';
		$lang->load('plg_jomwallpost', JPATH_ROOT, $tag);
		
		if($user->id)
		{
			$wall 				=& JTable::getInstance('Wall', 'Table');						
			$wall->user_id		= $user->id;
			$wall->group_id		= NULL;
			$wall->type			= 'text';
			$wall->commenter_id	=$user->id;
			$wall->message		= JText::sprintf('PLG_REGISTER_FOR_EVENT', 	$eventTitle);
			$wall->reply		= 0;
			$wall->is_read		= 0;
			$wall->is_pm		= 0;
			$wall->is_reply		= 0;
			$wall->posted_id	= NULL;
			$wall->wall_date	= time();
			
			if (!$wall->store()){				
			
			}
				
			$query = 'INSERT INTO #__awd_wall_privacy(wall_id, privacy) VALUES(' . $wall->id . ', 0)';
			$db->setQuery($query);
			$db->query();
		}
		
	} 	
}
