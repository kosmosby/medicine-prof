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

class JBidsAdminViewUserdetails extends JBidsAdminView {

    function display() {

        $app = JFactory::getApplication();
        $db =  JFactory::getDBO();

        $id = JRequest::getInt('id',0,'default','int');
        if(!$id) {
            $cid = JRequest::getVar('cid', array());
            $id = $cid[0];
        }

        $profile = BidsHelperTools::getUserProfileObject($id);

        $lists = array();

        $db->setQuery("SELECT
                            rating,count(*) as nr,
                            auction_id, a.title as auction,
                            voter_id, u.username
                        FROM #__bid_rate
                        LEFT JOIN #__bid_auctions AS a on auction_id = a.id
                        LEFT JOIN #__users AS u on voter_id = u.id
                        WHERE user_rated_id=".$db->quote($id)."
                        GROUP BY rating
                        ORDER BY rating");
        $lists['ratings'] = $db->loadObjectList();

        $db->setQuery("SELECT
                        m.id,
                        auction_id, a.title as auction,
                        u.id as from_id, u.username as fromuser,
                        u2.id as to_id, u2.username as touser,
                        m.modified,
                        m.message,
                        m.published

                    FROM #__bid_messages as m
                    LEFT JOIN #__bid_auctions AS a on m.auction_id = a.id
                    LEFT JOIN #__users AS u on m.userid1 = u.id
                    LEFT JOIN #__users AS u2 on m.userid2 = u2.id
                    WHERE userid1=".$db->quote($id)." OR userid2 = ".$db->quote($id) );
        $lists['messages'] = $db->loadObjectList();

        $query = "SELECT COUNT(*) AS nr_auctions,MAX(modified) AS last_auction_date FROM #__bid_auctions WHERE userid='$id'";
        $db->setQuery($query);
        $res = $db->loadAssocList();
        $lists['nr_auctions'] = $res[0]['nr_auctions'];
        $lists['last_auction_placed'] = $res[0]['last_auction_date'];

        $query = "select count(*) as nr_bids_won from #__bids where userid='$id' and accept=1 ";
        $db->setQuery($query);
        $res = $db->loadAssocList();
        $lists['nr_won_bids'] = $res[0]['nr_bids_won'];

        $query = "select count(*) as nr_bids, max(modified) as last_date from #__bids where userid='$id' ";
        $db->setQuery($query);
        $res = $db->loadAssocList();
        $lists['nr_bids'] = $res[0]['nr_bids'];
        $lists['last_bid_placed'] = $res[0]['last_date'];

        $query = "select c.*, a.title, a.BIN_price, cr.name as currency
		FROM #__bids as c
		LEFT join #__bid_auctions as a on c.auction_id = a.id
		LEFT join #__bid_currency as cr on a.currency = cr.id
		WHERE c.userid = '$id'";
        $db->setQuery($query);
        $lists['bids'] = $db->loadObjectList();

        $query = "SELECT * FROM #__bid_payment_balance WHERE userid=".$db->quote($id);
        $db->setQuery($query);
        $r = $db->loadObject();
        $lists['balance'] = new stdClass();
        $lists['balance']->balance = isset($r->balance) ? $r->balance : 0;
        $lists['balance']->currency = isset($r->currency) ? $r->currency : '';

        $u = JTable::getInstance('user');
        $u->load($profile->id);

        jimport('joomla.html.pane');
	    $pane = JPane::getInstance('sliders', array('allowAllClose' => true));

        JHTML::_('behavior.tooltip');

        $this->assignRef('lists', $lists);
        $this->assignRef('pane', $pane);
        $this->assignRef('u', $u);
        $this->assignRef('user', $profile);

        parent::display();
    }

    function addToolBar() {
        JToolBarHelper::title('');
    }
}
