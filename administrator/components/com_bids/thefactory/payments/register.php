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
        if ($app->getIniValue('use_payment_gateways'))
        {
            JLoader::register('JTheFactoryPricingHelper',$app->app_path_admin.'payments'.DS.'helper'.DS.'pricing.php');
            JLoader::register('JTheFactoryPaymentsHtmlHelper',$app->app_path_admin.'payments'.DS.'helper'.DS.'paymentshtml.php');
            JLoader::register('JTheFactoryBalancesController',$app->app_path_admin.'payments'.DS.'controllers'.DS.'balances.php');
            JLoader::register('JTheFactoryCurrenciesController',$app->app_path_admin.'payments'.DS.'controllers'.DS.'currencies.php');
            JLoader::register('JTheFactoryGatewaysController',$app->app_path_admin.'payments'.DS.'controllers'.DS.'gateways.php');
            JLoader::register('JTheFactoryOrdersController',$app->app_path_admin.'payments'.DS.'controllers'.DS.'orders.php');
            JLoader::register('JTheFactoryPricingController',$app->app_path_admin.'payments'.DS.'controllers'.DS.'pricing.php');

            if ($app->getIniValue('use_events')){
                JTheFactoryPricingHelper::registerEvents();
                JTheFactoryEventsHelper::registerEvents($app->app_path_admin.'payments'.DS.'events');
            }
        }
   }
}
