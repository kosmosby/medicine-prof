<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

class JBidsAdminViewStatistics extends JBidsAdminView {

    function display() {
        $database =  JFactory::getDBO();

        $app = JFactory::getApplication();

        $limit = JRequest::getInt('limit', $app->getCfg('list_limit'));
        $limitstart = JRequest::getInt('limitstart', 0);

        $query = "SELECT COUNT(*)"
                . "\n FROM #__users a where a.usertype!='Super Administrator' and a.usertype!='Administrator'";
        $database->setQuery($query);
        $total = $database->loadResult();
        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);

        $query = "select d.*
			 from #__users d
			 where d.usertype!='Super Administrator' and d.usertype!='Administrator' and d.block!=1
			 ";
        $database->setQuery($query, $pageNav->limitstart, $pageNav->limit);
        $userlist = $database->loadObjectList();

        $totals = array();

        $query = "select count(distinct userid) from #__bid_auctions";
        $database->setQuery($query);
        $totals['a_users'] = $database->loadResult();

        $query = "select count(*) from #__users";
        $database->setQuery($query);
        $totals['r_users'] = $database->loadResult();


        $query = "select count(*) from #__bid_auctions where close_offer !=1 and close_by_admin !=1 and published !=0";
        $database->setQuery($query);
        $totals['a_auctions'] = $database->loadResult();

        $query = "select count(*) from #__bid_auctions where close_offer =1 or close_by_admin =1";
        $database->setQuery($query);
        $totals['c_auctions'] = $database->loadResult();

        $query = "select count(*) from #__bids b
	               left join #__bid_auctions a on b.auction_id=a.id
	       where a.close_offer <>1 and a.close_by_admin <>1";
        $database->setQuery($query);
        $totals['a_bids'] = $database->loadResult();

        $query = "select count(*) from #__bids where accept=1";
        $database->setQuery($query);
        $totals['ac_bids'] = $database->loadResult();

        JHTML::_('behavior.tooltip');

        $this->assignRef('userlist',$userlist);
        $this->assignRef('totals',$totals);
        $this->assignRef('pageNav',$pageNav);

        parent::display();
    }

    function addToolBar() {
        JToolBarHelper::title(JText::_('COM_BIDS_AUCTIONS_FACTORY_STATISTICS'),'bids');
        JToolBarHelper::custom('aboutComponent', 'help', 'help', 'About Component', false);
    }

}
