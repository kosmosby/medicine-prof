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
 * @subpackage: Smarty
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.view');
class BidsSmartyView extends JTheFactorySmartyView 
{
    function __construct()
    {
        JViewLegacy::__construct();
        $this->smarty=new BidsSmarty();
        JHtml::_('behavior.mootools');
        JHTML::script(JUri::root().'components/'.APP_EXTENSION.'/js/auctions.js');
    }
    
}
