<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 3.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: Bids
 * @subpackage: Pay per bid
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');
class JBidPricingViewBidConfig extends JView
{
    function display($tpl = null)
    {
        JHTML::_('behavior.tooltip');
        parent::display($tpl);
    }

}


?>
