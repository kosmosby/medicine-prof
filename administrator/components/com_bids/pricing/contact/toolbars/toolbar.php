<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: Bids
 * @subpackage: Pay per contact
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JBidAdminContactToolbar
{
    function display($task=null)
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $appname = $MyApp->getIniValue('name');

        JToolBarHelper::title( JText::_('COM_BIDS_PAYPERCONTACT_CONFIGURATION'), $appname);
        switch($task)
        {
            default:
        		JToolBarHelper::apply('pricing.save');
        		JToolBarHelper::cancel('pricing.cancel');
                JSubMenuHelper::addEntry(
              			JText::_('COM_BIDS_PAYMENT_ITEMS'),
              			'index.php?option='.APP_EXTENSION.'&task=pricing.listing',
              			false
              		);
            break;
         }

    }
}
