<?php
/**
 * Kunena Plugin
 * @package Kunena.Plugins
 * @subpackage Jomwall
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

jimport('joomla.utilities.string');

class KunenaActivityJomwall extends KunenaActivity {
	protected $params = null;

	public function __construct($params) {
		$this->params = $params;
	}

	public function onAfterPost($message) {
	//define('PLG_KUNENA_JOMWALL_ACTIVITY_POST_TITLE', "created a new topic '%s' in the forum.");
	//define('PLG_KUNENA_JOMWALL_ACTIVITY_REPLY_TITLE', "replied to the topic '%s' in the forum.");
	//define('PLG_KUNENA_JOMWALL_ACTIVITY_THANKYOU_TITLE', "thanks user '%s' in the forum.");


		$content = KunenaHtmlParser::plainBBCode($message->message, $this->params->get('activity_stream_limit', 0));
		// Add readmore permalink
		$content .= '<br /><a rel="nofollow" href="'.$message->getTopic()->getPermaUrl().'" class="small profile-newsfeed-item-action">'.JText::_('COM_KUNENA_READMORE').'</a>';

		
			$msg = AwdwallHelperUser::formatUrlInMsg($msg);			
			$wall 				=& JTable::getInstance('Wall', 'Table');						
			$wall->user_id		= $message->userid;
			$wall->group_id		= NULL;
			$wall->type			= 'text';
			$wall->commenter_id	= $message->userid;
			$wall->user_name	= '';
			$wall->avatar		= '';
			$wall->message		= JText::sprintf ( 'PLG_KUNENA_JOMWALL_ACTIVITY_POST_TITLE', ' <a href="' . $message->getTopic()->getUrl() . '">' . $message->subject . '</a>').'<br>'.$content;
			$wall->reply		= 0;
			$wall->is_read		= 0;
			$wall->is_pm		= 0;
			$wall->is_reply		= 0;
			$wall->posted_id	= NULL;
			$wall->wall_date	= time();
			if (!$wall->store()){				

			}
		
	}

	public function onAfterReply($message) {

		$content = KunenaHtmlParser::plainBBCode($message->message, $this->params->get('activity_stream_limit', 0));
		// Add readmore permalink
		$content .= '<br /><a rel="nofollow" href="'.$message->getTopic()->getPermaUrl().'" class="small profile-newsfeed-item-action">'.JText::_('COM_KUNENA_READMORE').'</a>';

		
			$msg = AwdwallHelperUser::formatUrlInMsg($msg);			
			$wall 				=& JTable::getInstance('Wall', 'Table');						
			$wall->user_id		= $message->userid;
			$wall->group_id		= NULL;
			$wall->type			= 'text';
			$wall->commenter_id	= $message->userid;
			$wall->user_name	= '';
			$wall->avatar		= '';
			$wall->message		= JText::sprintf ( 'PLG_KUNENA_JOMWALL_ACTIVITY_REPLY_TITLE', '<a href="' . $message->getTopic()->getUrl() . '">' . $message->subject . '</a>' ).'<br>'.$content;
			$wall->reply		= 0;
			$wall->is_read		= 0;
			$wall->is_pm		= 0;
			$wall->is_reply		= 0;
			$wall->posted_id	= NULL;
			$wall->wall_date	= time();
			if (!$wall->store()){				

			}
		
	}

	public function onAfterThankyou($target, $actor, $message) {
	
			$username = KunenaFactory::getUser($actor)->username;
			$msg = AwdwallHelperUser::formatUrlInMsg($msg);			
			$wall 				=& JTable::getInstance('Wall', 'Table');						
			$wall->user_id		= $target;
			$wall->group_id		= NULL;
			$wall->type			= 'text';
			$wall->commenter_id	= JFactory::getUser()->id;
			$wall->user_name	= '';
			$wall->avatar		= '';
			$wall->message		= JText::sprintf( 'PLG_KUNENA_JOMWALL_ACTIVITY_THANKYOU_TITLE', $username );
			$wall->reply		= 0;
			$wall->is_read		= 0;
			$wall->is_pm		= 0;
			$wall->is_reply		= 0;
			$wall->posted_id	= NULL;
			$wall->wall_date	= time();
			if (!$wall->store()){				

			}
	}


	
}
