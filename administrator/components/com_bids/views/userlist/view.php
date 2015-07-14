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

class JBidsAdminViewUserlist extends JBidsAdminView {

    function display() {

        $db =  JFactory::getDBO();
        $app = JFactory::getApplication();
        $cfg=BidsHelperTools::getConfig();
        $where = array();

        $context = 'com_bids.bidsadminview.users';
        $filter_order = $app->getUserStateFromRequest($context . 'filter_order', 'filter_order', '', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', '', 'word');

        $search = $app->getUserStateFromRequest($context . 'search', 'search', '', 'string');

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($context . 'limitstart', 'limitstart', 0, 'int');
        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ( $limit != 0 ? (floor($limitstart / $limit) * $limit) : 0 );

        if (!$filter_order) {
            $filter_order = 'u.name';
        }
        $order = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . '';

        if ($search)
            $where[] = " username LIKE '%".$db->getEscaped($search)."%' ";

        // Build the where clause of the content record query
        $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

        // Get the total number of records
        $query = "SELECT COUNT(*) " .
                "FROM #__users" . $where;
        $db->setQuery($query);
        $total = $db->loadResult();

        // Create the pagination object
        jimport('joomla.html.pagination');
        $page = new JPagination($total, $limitstart, $limit);

        $profileObject = BidsHelperTools::getUserProfileObject();
        $profileTable = $profileObject->getIntegrationTable();
        $profileKey = $profileObject->getIntegrationKey();

        $profileFields = array('verified','powerseller');
        if($cfg->bid_opt_enable_acl) {
            $profileFields = array_merge($profileFields, array('isBidder','isSeller') );
        }

        $sqlFields = array();
        foreach($profileFields as $pf) {
            $field = $profileObject->getFilterField($pf);
            if($field) {
                $sqlFields[] = 'p.'.$field.' AS '.$pf;
            }
        }

        // Get the users
        $query = "SELECT
                    u.id as userid1, u.username as username,u.name as name, u.email AS email,
                    p.id as profid, ".
                    (count($sqlFields) ? implode(',',$sqlFields).',' : '')."
    			    COUNT(DISTINCT a.id) AS nr_auctions,

    			    COUNT(DISTINCT btbl.id) as nr_closed_bids,

    			    COUNT(DISTINCT IF(a.close_offer,a.id,NULL)) AS nr_closed_offers,
    			    GROUP_CONCAT(DISTINCT IF(a.close_offer,a.id,NULL)) AS closed_offers,

    			    COUNT(DISTINCT IF(a.close_offer,NULL,a.id)) AS nr_open_offers,
    			    GROUP_CONCAT(DISTINCT IF(a.close_offer,NULL,a.id)) AS open_offers,

    			    COUNT(DISTINCT IF(a.featured='none',NULL,a.id)) AS nr_featured_offers,
    			    GROUP_CONCAT(DISTINCT IF(a.featured='none',NULL,a.id)) AS featured_offers,

    			    AVG(urate.rating) AS rating_user,
    			    u.block
                FROM #__users AS u
                LEFT JOIN ".$profileTable." AS p ON u.id=p.".$profileKey."
                LEFT JOIN #__bid_auctions AS a ON u.id=a.userid
                LEFT JOIN #__bid_rate AS urate ON u.id=urate.user_rated_id
                LEFT JOIN #__bids AS btbl ON u.id=btbl.userid AND btbl.accept=1 " .
                $where .
                " GROUP BY u.id " . $order;

        $db->setQuery($query, $page->limitstart, $page->limit);
        $rows = $db->loadObjectList();

        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;
        $lists['search'] = $search;

        JHTML::_('behavior.tooltip');

        $this->assignRef('lists', $lists);
        $this->assignRef('page', $page);
        $this->assignRef('rows', $rows);

        parent::display();
    }

    function addToolBar() {

        JToolBarHelper::title(JText::_('COM_BIDS_AUCTIONS_FACTORY_USER_MANAGEMENT'), 'bids');

        JToolBarHelper::custom('detailuser', 'edit', 'edit', 'Edit', true);
        JToolBarHelper::spacer();
        JToolBarHelper::divider();
        JToolBarHelper::spacer();
        JToolBarHelper::custom('togglepowerseller', 'apply', 'apply', 'Toggle Powerseller', false);
        JToolBarHelper::custom('toggleverify', 'apply', 'apply', 'Toggle Verify', true);

        JToolBarHelper::divider();

        JToolBarHelper::custom('blockuser', 'cancel', 'cancel', 'Block', true);
        JToolBarHelper::spacer();
        JToolBarHelper::custom('unblockuser', 'apply', 'apply', 'Unblock', true);

        JToolBarHelper::spacer();
    }
}
