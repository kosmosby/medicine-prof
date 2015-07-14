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
 * @subpackage: integration
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryIntegrationRegister
{
    static function registerModule($app=null)
    {
        if (!$app)
    	   $app= JTheFactoryApplication::getInstance();
        if ($app->getIniValue('use_integration')){
            JLoader::register('JTheFactoryIntegration',$app->app_path_admin.'integration'.DS.'integration.php');
            JLoader::register('JTheFactoryIntegrationCBController',$app->app_path_admin.'integration'.DS.'controllers'.DS.'cb.php');
            JLoader::register('JTheFactoryIntegrationLoveController',$app->app_path_admin.'integration'.DS.'controllers'.DS.'love.php');

            $lang=JFactory::getLanguage();
            $lang->load('thefactory.integration',JPATH_ADMINISTRATOR);
        }
   }
}
