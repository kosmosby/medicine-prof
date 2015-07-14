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

class bidsViewAuctionEdit extends BidsSmartyView {

    function display($tmpl=null) {

        JHtml::_('behavior.mootools');

        $jsRoot = JURI::root().'components/com_bids/js/jquery/';
        JHTML::script($jsRoot.'jquery.js');
        JHTML::script($jsRoot.'jquery.noconflict.js');
        JHTML::script($jsRoot.'clock/jquery.clock.js');

        $modelAuction = $this->getModel('auction');
        $auction = $modelAuction->get('auction');

        $auction->expired = false;

        $lists = array();

        $lists['title'] = JHTML::_('editauction.inputTitle',$auction);
        $lists['cats'] = JHTML::_('editauction.selectCategory',$auction);
        //$lists['cats']    = JHTML::_('factorycategory.select','cat','',$auction->cat,false,false,true);
        $lists['published'] = JHTML::_('editauction.selectPublished',$auction);
        $lists['tags'] = JHTML::_('editauction.inputTags',$auction);
        $lists['shortDescription'] = JHTML::_('editauction.inputShortDescription',$auction);
        $lists['description'] = JHTML::_('editauction.inputDescription',$auction);
        $lists['auctiontype'] = JHTML::_('editauction.selectAuctionType',$auction);
        $lists['automatic'] = JHTML::_('editauction.inputAutomatic',$auction);
        $lists['binType'] = JHTML::_('editauction.selectBINType',$auction);
        $lists['binPrice'] = JHTML::_('editauction.inputBINPrice',$auction);
        $lists['autoAcceptBIN'] = JHTML::_('editauction.selectAutoAcceptBIN',$auction);
        $lists['quantity'] = JHTML::_('editauction.inputQuantity',$auction);
        $lists['enableSuggestions'] = JHTML::_('editAuction.selectEnableSuggestions',$auction);
        $lists['minNumberSuggestions'] = JHTML::_('editAuction.inputMinNumberSuggestions',$auction);
        $lists['currency'] = JHTML::_('editauction.selectCurrency',$auction);
        $lists['initialPrice'] = JHTML::_('editauction.inputInitialPrice',$auction);
        $lists['showMaxPrice'] = JHTML::_('editauction.selectShowMaxPrice',$auction);
        $lists['showNumberBids'] = JHTML::_('editauction.selectShowNumberBids',$auction);
        $lists['reservePrice'] = JHTML::_('editauction.inputReservePrice',$auction);
        $lists['showReservePrice'] = JHTML::_('editauction.selectShowReservePrice',$auction);
        $lists['minIncrease'] = JHTML::_('editauction.inputMinIncrease',$auction);
        $lists['shippingPrice'] = JHTML::_('editAuction.inputShipmentPrice',$auction);
        $lists['uploadImages'] = JHTML::_('editauction.uploadImages',$auction);
        $lists['paymentInfo'] = JHTML::_('editauction.textPaymentInfo',$auction);
        $lists['shipmentInfo'] = JHTML::_('editauction.textShipmentInfo',$auction);
        //$lists[''] = JHTML::_('editauction.',$auction);


        $lists['currentLocalTime_field'] = JHTML::_('editAuction.currentLocalTime',$auction);
        $lists['startDate_field'] = JHTML::_('editAuction.editStartDate',$auction);
        $lists['endDate_field'] = JHTML::_('editAuction.editEndDate',$auction);
        $lists['editFormTitle'] = JHTML::_('editAuction.formTitle',$auction);

        $auction->links = JHTML::_('auctiondetails.createLinks',$auction);

        $fields = CustomFieldsFactory::getFieldsList("auctions");
        $fields_html = JHtml::_('listauctions.displayfieldshtml',$auction,$fields);
        $custom_fields_with_cat = $modelAuction->getNrFieldsWithFilters();

        JTheFactoryEventsHelper::triggerEvent('onBeforeEditAuction',array($auction));

        $this->assign("custom_fields", $fields );
        $this->assign("custom_fields_html", $fields_html );
        $this->assign("custom_fields_with_cat", $custom_fields_with_cat?1:0);

        $this->assign('lists', $lists);
        $this->assign('auction', $auction);

        parent::display($tmpl);
    }
}
