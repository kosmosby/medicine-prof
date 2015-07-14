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

class JTheFactoryOrdersToolbar
{
    function display($task)
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $appname = $MyApp->getIniValue('name');

        JToolBarHelper::title( JText::_( 'FACTORY_CURRENT_ORDERS_LIST' ),$appname);
        switch($task)
        {
            default:
            break;
            case 'listing':
                JToolBarHelper::custom('orders.confirm','checkin','checkin',JText::_('FACTORY_CONFIRM_ORDER'),true);
                JToolBarHelper::custom('orders.cancel','cancel','cancel',JText::_('FACTORY_CANCEL_ORDER'),true);
            break;
            case 'viewdetails':
                JToolBarHelper::title( JText::_( 'FACTORY_ORDER_DETAIL' ), $appname);
                JToolBarHelper::custom('orders.confirm','checkin','checkin',JText::_('FACTORY_CONFIRM_ORDER'),false);
                JToolBarHelper::custom('orders.cancel','cancel','cancel',JText::_('FACTORY_CANCEL_ORDER'),false);
                JToolBarHelper::custom('orders.listing','back','back',JText::_('FACTORY_BACK_TO_LISTING'),false);
            break;
         }

    }
}
