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

class JBidsAdminViewAuctionlist extends JBidsAdminView {

    function display($tpl=null) {

        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $my = JFactory::getUser();

        $where = array();

        $context = 'com_bids.jbidsdminview.offers.';

        $keyword = $app->getUserStateFromRequest($context . 'keyword', 'keyword', '', 'string');
        $category = $app->getUserStateFromRequest($context . 'category', 'category', '', 'string');
        $filter_authorid = $app->getUserStateFromRequest($context . 'filter_authorid', 'filter_authorid', '', 'string');
        $filter_bidtype = $app->getUserStateFromRequest($context . 'filter_bidtype', 'filter_bidtype', 0, 'int');
        $filter_order = $app->getUserStateFromRequest($context . 'filter_order', 'filter_order', 'start_date', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($context . 'limitstart', 'limitstart', 0, 'int');

        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ( $limit != 0 ? (floor($limitstart / $limit) * $limit) : 0 );

        if (!$filter_order) {
            $filter_order = 'a.title';
        }

        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;

        $order = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . '';

        if ($keyword)
            $where[] = " a.title LIKE '%".$db->escape($keyword)."%' OR a.shortdescription LIKE '%" . $db->escape($keyword) . "%' OR a.description LIKE '%" . $db->escape($keyword) . "%' ";

        if ($filter_authorid) {
            $where[] = " u.username LIKE '%".$db->escape($filter_authorid)."%' ";
        }

        if($category) {
            $where[] = 'a.cat='.$db->quote($category);
        }

        switch ($filter_bidtype) {
            case 1 :
                $where[] = " a.published=1 AND a.close_offer=0 AND a.close_by_admin=0 AND a.end_date>=UTC_TIMESTAMP() ";
                break;
            case 2 :
                $where[] = " a.published=1 AND a.close_offer=0 AND a.close_by_admin=0 AND a.end_date<UTC_TIMESTAMP() ";
                break;

            case 3 :
                $where[] = " a.published=1 AND a.close_offer=1 AND a.close_by_admin=0 ";
                break;

            case 4 :
                $where[] = " a.published=0 AND a.close_by_admin=0 ";
                break;

            case 5 :
                $where[] = " a.close_by_admin=1 ";
                break;
        }

        // Build the where clause of the content record query
        $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

        // Get the total number of records
        $query = 'SELECT COUNT(1)' .
                ' FROM #__bid_auctions AS a' .
                ' LEFT JOIN #__categories AS cc ON cc.id = a.cat ' .
                $where;
        $db->setQuery($query);
        $total = $db->loadResult();

        // Create the pagination object
        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);

        // Get the auctions
        $query = 'SELECT a.*, cc.title AS name,
                 u.username AS username, count(bids.id) as nr_bids, max(bids.bid_price) as min_bid,
                 COUNT(DISTINCT bids.userid) as nr_bidders,
                 COUNT(DISTINCT pix.id ) as nr_pix,
                 1-MIN(msg.wasread) AS newmessages
                 FROM #__bid_auctions AS a
                 LEFT JOIN `#__bids` AS bids ON `bids`.`auction_id`=`a`.`id`
                 LEFT JOIN #__users AS bu ON bu.id = bids.userid
                 LEFT JOIN #__categories AS cc ON cc.id = a.cat
                 LEFT JOIN #__users AS u ON u.id = a.userid
                 LEFT JOIN #__bid_pictures AS pix ON pix.auction_id = a.id
                 LEFT JOIN #__bid_messages AS msg ON msg.auction_id = a.id AND msg.userid2 = \'' . $my->id . '\' '.
                $where .
                ' GROUP BY a.id '.
                $order;
        $db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
        $rows = $db->loadObjectList();

        $filters = array();
        $filters['keyword'] = '<input type="text" name="keyword" value="'.$keyword.'" />';
        $filters['filter_authorid'] = '<input type="text" name="filter_authorid" value="' . $filter_authorid . '" />';
        $filters['category'] = BidsHelperHtml::selectCategory('category',
            array(
                'name'=>'category',
                'select'=>$category)
        );


        $closeid[] = JHTML::_('select.option', '0', JText::_('COM_BIDS_ALL_OFFERS'));
        $closeid[] = JHTML::_('select.option', '1', JText::_('COM_BIDS_ACTIVE'));
        $closeid[] = JHTML::_('select.option', '2', JText::_('COM_BIDS_NR_EXPIRED'));
        $closeid[] = JHTML::_('select.option', '3', JText::_('COM_BIDS_CLOSED'));
        $closeid[] = JHTML::_('select.option', '4', "Cancelled (Unpublished)");
        $closeid[] = JHTML::_('select.option', '5', "Blocked  (Unpublished)");
        $lists['filter_bidtype'] = JHTML::_('select.genericlist', $closeid, 'filter_bidtype', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_bidtype);

        JHTML::_('behavior.tooltip');

        $this->assignRef('lists', $lists);
        $this->assignRef('filters', $filters);
        $this->assignRef('rows', $rows);
        $this->assignRef('pageNav', $pageNav);

        parent::display($tpl);
    }

    function addToolBar() {

        JToolBarHelper::title(JText::_('COM_BIDS_AUCTIONS_FACTORY_MANAGE_LISTINGS'), "bids");

        JToolBarHelper::custom('newauction', 'new', 'new', 'New Auction', false);
        JToolBarHelper::divider();
        JToolBarHelper::custom('closed', 'cancel', 'cancel', 'Block');
        JToolBarHelper::custom('opened', 'apply', 'apply', 'Unblock');

    }

}
