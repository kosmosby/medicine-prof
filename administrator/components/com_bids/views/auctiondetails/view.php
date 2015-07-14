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

class JBidsAdminViewAuctionDetails extends JBidsAdminView {

    function display($id) {

        $database = JFactory::getDBO();
        $my = JFactory::getUser();

        $modelAuction = $this->getModel('auction');
        $auction = $modelAuction->get('auction');

        $lists = array();

        $lang = JFactory::getLanguage();
        $lang->load('com_bids', JPATH_SITE);

        $bidCfg = BidsHelperTools::getConfig();

        $lists = array();

        $lists['title'] = JHTML::_('editauction.inputTitle', $auction, 1);
        $lists['cats'] = JHTML::_('editauction.selectCategory', $auction, 1);
        //$lists['cats']    = JHTML::_('factorycategory.select','cat','',$auction->cat,false,false,true);
        $lists['published'] = JHTML::_('editauction.selectPublished', $auction, 1);
        $lists['tags'] = JHTML::_('editauction.inputTags', $auction, 1);
        $lists['shortDescription'] = JHTML::_('editauction.inputShortDescription', $auction, 1);
        $lists['description'] = JHTML::_('editauction.inputDescription', $auction, 1);
        $lists['auctiontype'] = JHTML::_('editauction.selectAuctionType', $auction, 1);
        $lists['automatic'] = JHTML::_('editauction.inputAutomatic', $auction, 1);
        $lists['binType'] = JHTML::_('editauction.selectBINType', $auction, 1);
        $lists['binPrice'] = JHTML::_('editauction.inputBINPrice', $auction, 1);
        $lists['autoAcceptBIN'] = JHTML::_('editauction.selectAutoAcceptBIN', $auction, 1);
        $lists['quantity'] = JHTML::_('editauction.inputQuantity', $auction, 1);
        $lists['enableSuggestions'] = JHTML::_('editAuction.selectEnableSuggestions', $auction, 1);
        $lists['minNumberSuggestions'] = JHTML::_('editAuction.inputMinNumberSuggestions', $auction, 1);
        $lists['currency'] = JHTML::_('editauction.selectCurrency', $auction, 1);
        $lists['initialPrice'] = JHTML::_('editauction.inputInitialPrice', $auction, 1);
        $lists['showMaxPrice'] = JHTML::_('editauction.selectShowMaxPrice', $auction, 1);
        $lists['showNumberBids'] = JHTML::_('editauction.selectShowNumberBids', $auction, 1);
        $lists['reservePrice'] = JHTML::_('editauction.inputReservePrice', $auction, 1);
        $lists['showReservePrice'] = JHTML::_('editauction.selectShowReservePrice', $auction, 1);
        $lists['minIncrease'] = JHTML::_('editauction.inputMinIncrease', $auction, 1);
        $lists['shippingPrice'] = JHTML::_('editAuction.inputShipmentPrice', $auction, 1);
        $lists['uploadImages'] = JHTML::_('editauction.uploadImages', $auction, 1);
        $lists['paymentInfo'] = JHTML::_('editauction.textPaymentInfo', $auction, 1);
        $lists['shipmentInfo'] = JHTML::_('editauction.textShipmentInfo', $auction, 1);
        //$lists[''] = JHTML::_('editauction.',$auction);


        $lists['currentLocalTime_field'] = JHTML::_('editAuction.currentLocalTime', $auction);
        $lists['startDate_field'] = JHTML::_('editAuction.editStartDate', $auction, 1);
        $lists['endDate_field'] = JHTML::_('editAuction.editEndDate', $auction, 1);
        $lists['editFormTitle'] = JHTML::_('editAuction.formTitle', $auction);

        $auction->links = JHTML::_('auctiondetails.createLinks', $auction);

        $fields = CustomFieldsFactory::getFieldsList("auctions");
        $fields_html = JHtml::_('listauctions.displayfieldshtml', $auction, $fields);
        $custom_fields_with_cat = $modelAuction->getNrFieldsWithFilters();

        $this->assign("custom_fields", $fields);
        $this->assign("custom_fields_html", $fields_html);
        $this->assign("custom_fields_with_cat", $custom_fields_with_cat ? 1 : 0);

        $this->assign('lists', $lists);
        $this->assign('auction', $auction);
        $this->assign('bidCfg', $bidCfg);

        JHtml::_('behavior.framework');
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.calendar');
        JHTML::_('behavior.formvalidation');

        JHtml::script(JURI::root() . 'components/com_bids/js/auctions.js');
        JHtml::script(JURI::root() . 'components/com_bids/js/date.js');
        JHtml::script(JURI::root() . 'components/com_bids/js/multifile.js');
        JHtml::script(JURI::root() . 'components/com_bids/js/auction_edit.js');

        JHtml::script(JURI::root() . 'components/com_bids/js/jquery/jquery.js');
        JHtml::script(JURI::root() . 'components/com_bids/js/jquery/jquery.noconflict.js');
        JHTML::script(JURI::root() . 'components/com_bids/js/jquery/clock/jquery.clock.js');

        JHtml::script(JURI::base() . 'index.php?option=com_bids&task=jsgen&view=editauction&format=raw');

        JHtml::stylesheet(JURI::root() . 'components/com_bids/templates/default/bid_template.css');

        $user = BidsHelperTools::getUserProfileObject($auction->userid);

        $auction->userdetails = $user;

        $query = "update #__bid_messages set wasread=1 where userid2='$my->id' and auction_id='$id'";
        $database->setQuery($query);
        $database->query();

        $query = "update #__bid_auctions set newmessages=0 where id='$id'";
        $database->setQuery($query);
        $database->query();

        $feat[] = JHTML::_('select.option', 'none', JText::_('COM_BIDS_NONE'));
        $feat[] = JHTML::_('select.option', 'featured', JText::_('COM_BIDS_PAYMENT_FEATURED'));

        $lists['featured'] = JHTML::_('select.genericlist', $feat, 'featured', 'class="inputbox" id="featured" style="width:120px;"', 'value', 'text', $auction->featured);

        $database = JFactory::getDBO();
        $my = JFactory::getUser();

        $database->setQuery("select max(bid_price) from #__bids where auction_id='$auction->id'");
        $auction->max_bid = $database->loadResult();

        $database->setQuery("select * from #__bid_pictures where auction_id='$auction->id'");
        $photos = $database->loadObjectList();

        $query = "select m.*,u1.username as fromuser, u2.username as touser  from #__bid_messages m
                            left join #__users u1 on u1.id = m.userid1
                            left join #__users u2 on u2.id = m.userid2
                            where m.auction_id='$auction->id'

            ";
        // and (m.userid1 = '$my->id' or m.userid2 = '$my->id') */

        $database->setQuery($query);
        $adminMessages = $database->loadObjectList();

        $query = "
            SELECT a.*,b.username, bp.max_proxy_price
            FROM `#__bids` AS a
            LEFT JOIN `#__users` b
                ON a.userid=b.id
            LEFT JOIN `#__bid_proxy` AS bp
                ON a.id_proxy=bp.id AND bp.active=1
            WHERE a.auction_id=".intval($auction->id);
        $database->setQuery($query);
        $bids = $database->loadObjectList();



        JHTML::_('behavior.modal');
        JHTML::_('behavior.tooltip');

        jimport('joomla.filesystem.file');

        $this->assignRef('lists', $lists);
        $this->assignRef('bids', $bids);
        $this->assignRef('adminMessages', $adminMessages);
        $this->assignRef('photos', $photos);
        $this->assignRef('user', $user);
        $this->assignRef('auction', $auction);

        parent::display();
    }

    function addToolBar() {

        JToolBarHelper::title(JText::_('COM_BIDS_AUCTIONS_FACTORY'), 'bids');
        JToolBarHelper::save('saveauction');
        JToolBarHelper::custom('canceleditauction', 'back', 'back', 'Back to Auctions', false);
    }
}
