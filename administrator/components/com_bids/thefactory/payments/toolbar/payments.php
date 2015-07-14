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

class JTheFactoryPaymentsToolbar
{
    function display($task)
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $appname = $MyApp->getIniValue('name');

        JToolBarHelper::title( JText::_( 'FACTORY_RECEIVED_PAYMENTS_LIST' ),$appname);
        switch($task)
        {
            default:
            break;
            case 'listing':
                JToolBarHelper::custom('payments.confirm','checkin','checkin',JText::_('FACTORY_CONFIRM_PAYMENT'),true);
                JToolBarHelper::custom('payments.newpayment','new','new',JText::_('FACTORY_ADD_PAYMENT'),false);
            break;
            case 'viewdetails':
                JToolBarHelper::title( JText::_( 'FACTORY_PAYMENT_DETAILS' ), $appname);
                JToolBarHelper::custom('payments.confirm','checkin','checkin',JText::_('FACTORY_CONFIRM_PAYMENT'),false);
                JToolBarHelper::custom('payments.listing','back','back',JText::_('FACTORY_BACK_TO_LISTING'),false);
            break;
            case 'newpayment':
                JToolBarHelper::custom('payments.savepayment','save','save',JText::_('FACTORY_SAVE_PAYMENT'),false);
                JToolBarHelper::custom('payments.listing','back','back',JText::_('FACTORY_CANCEL'),false);
            break;
         }

    }
}
