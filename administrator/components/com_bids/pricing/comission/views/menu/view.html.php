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
 * @subpackage: Comission
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');
class JBidPricingViewComissionMenu extends JView
{
    function display($tpl = null)
    {
        $lists = array();
        $lists['selectType'] = JHTML::_('commission.selectType');

        $this->assign('lists',$lists);

        parent::display($tpl);
    }

}

