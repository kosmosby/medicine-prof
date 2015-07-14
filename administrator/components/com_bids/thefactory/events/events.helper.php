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

class JTheFactoryEventsHelper
{
    static function registerEvents($eventfiles_path)
    {
        jimport("joomla.filesystem.folder");
        jimport("joomla.filesystem.file");

        $files=JFolder::files($eventfiles_path,'\.php$');
        if (count($files))
            foreach($files as $file)
            {
                $classname='JTheFactoryEvent'.ucfirst(substr($file,0,strlen($file)-4));
                require_once $eventfiles_path.DS.$file;
                if (class_exists($classname))
                    new $classname;
            }
    }
    static function triggerEvent($event, $args=null)
    {
        $dispatcher = JTheFactoryDispatcher::getInstance();

        return $dispatcher->trigger($event, $args);
    }
 }
