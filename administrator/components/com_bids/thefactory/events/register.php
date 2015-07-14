<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: events
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');


class JTheFactoryEventsRegister
{
    static function registerModule($app=null)
    {
        if (!$app)
    	   $app= JTheFactoryApplication::getInstance();
        
        if ($app->getIniValue('use_events')){
        	JLoader::register('JTheFactoryEvents',$app->app_path_admin.'events/events.class.php');
            JLoader::register('JTheFactoryEventsHelper',$app->app_path_admin.'events/events.helper.php');
            JLoader::register('JTheFactoryDispatcher',$app->app_path_admin.'events/events.dispatcher.php');
            if ($app->frontpage)
                JTheFactoryEventsHelper::registerEvents(JPATH_SITE.DS.'components'.DS.APP_EXTENSION.DS.'events');
            else
                JTheFactoryEventsHelper::registerEvents(JPATH_ADMINISTRATOR.DS.'components'.DS.APP_EXTENSION.DS.'events');
        }
        
        
    } 
    
}
