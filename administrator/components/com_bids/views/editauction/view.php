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

class JBidsAdminViewEditAuction extends JBidsAdminView
{
    function display($tpl=null) {

        $lang = JFactory::getLanguage();
        $lang->load('com_bids', JPATH_SITE);

        $model = $this->getModel('auction');
        $auction = $model->get('auction');

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
        $lists['startDate_field'] = JHTML::_('editAuction.editStartDate', $auction,1);
        $lists['endDate_field'] = JHTML::_('editAuction.editEndDate', $auction,1);
        $lists['editFormTitle'] = JHTML::_('editAuction.formTitle', $auction);

        $auction->links = JHTML::_('auctiondetails.createLinks', $auction);

        $fields = CustomFieldsFactory::getFieldsList("auctions");
        $fields_html = JHtml::_('listauctions.displayfieldshtml', $auction, $fields);
        $custom_fields_with_cat = $model->getNrFieldsWithFilters();

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

        JHtml::script( JURI::root().'components/com_bids/js/auctions.js' );
        JHtml::script( JURI::root().'components/com_bids/js/date.js' );
        JHtml::script( JURI::root().'components/com_bids/js/multifile.js' );
        JHtml::script( JURI::root().'components/com_bids/js/auction_edit.js' );

        JHtml::script( JURI::root().'components/com_bids/js/jquery/jquery.js' );
        JHtml::script( JURI::root().'components/com_bids/js/jquery/jquery.noconflict.js' );
        JHTML::script( JURI::root().'components/com_bids/js/jquery/clock/jquery.clock.js');

        JHtml::script( JURI::base().'index.php?option=com_bids&task=jsgen&view=editauction&format=raw' );

        JHtml::stylesheet(JURI::root() . 'components/com_bids/templates/default/bid_template.css' );

        parent::display($tpl);
    }

    function addToolBar() {

        JToolBarHelper::title( JText::_('COM_BIDS_AUCTIONS_FACTORY'), 'bids' );
        JToolBarHelper::save('saveauction');
        JToolBarHelper::cancel('canceleditauction');
    }
}


