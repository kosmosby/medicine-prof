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
 * @subpackage: payments
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryPaymentsRegister
{
    static function registerModule($app=null)
    {
        if (!$app)
    	   $app= JTheFactoryApplication::getInstance();
         if ($app->getIniValue('use_payment_gateways')){
            JLoader::register('JTheFactoryPricingHelper',$app->app_path_admin.'payments'.DS.'helper'.DS.'pricing.php');
            JLoader::register('JTheFactoryBalanceController',$app->app_path_front.'payments'.DS.'controllers'.DS.'balance.php');
            JLoader::register('JTheFactoryOrderProcessorController',$app->app_path_front.'payments'.DS.'controllers'.DS.'processor.php');
            JLoader::register('JTheFactoryOrder',$app->app_path_admin.'payments'.DS.'classes'.DS.'orders.php');
            if ($app->getIniValue('use_events')){
                JTheFactoryPricingHelper::registerEvents();
                JTheFactoryEventsHelper::registerEvents($app->app_path_admin.'payments'.DS.'events');
            }
            $lang=JFactory::getLanguage();
            $lang->load('thefactory.payments',JPATH_ADMINISTRATOR);
        }
   }
}
