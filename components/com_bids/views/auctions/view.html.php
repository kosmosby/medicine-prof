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



// no direct access
defined('_JEXEC') or die('Restricted access');

class bidsViewAuctions extends BidsSmartyView {

    function display($tmpl) {

        jimport('joomla.html.parameter');

        $cfg = BidsHelperTools::getConfig();

        $model = $this->getModel('auctions');
        $items = $model->get('auctions');

        $lists=array();

		$gallery = BidsHelperGallery::getGalleryPlugin();

        $i=0;
        foreach($items as $k=>&$item) {

            $item->rownr = ++$i;
            $item->params = new JParameter($item->params);

            $gallery->clearImages();
            $gallery->addImageList(explode(',',$item->pictures));
            $item->thumbnail = $gallery->getThumbImage();

            $item->start_date_text = JHTML::_('auctiondetails.startDateHtml', $item);

            $item->description = JFilterOutput::cleanText($item->description);
            $item->shortdescription = JFilterOutput::cleanText($item->shortdescription);

            $item->tagIds = $item->tagIds ? explode(',', $item->tagIds) : array();
            $item->tagNames = $item->tagNames ? explode(',', $item->tagNames) : array();

            $item->links = JHTML::_('auctiondetails.createLinks',$item);
            if (!isset($item->favorite))
                $item->favorite = false;
            $item->del_from_watchlist = (bool) $item->favorite;
            $item->add_from_watchlist = !$item->del_from_watchlist;

            if ($cfg->bid_opt_multiple_shipping) {
                $p = JTable::getInstance('bidshipzone');
                $item->shipping_zones = $p->getPriceList($item->id);
            }

            BidsHelperAuction::renderAuctionTiming($item);
            $item->countdownHtml = JHTML::_('auctiondetails.countdownHtml',$item);

            if ( BidsHelperDateTime::getTimeStamp($item->end_date) <= time() ) {
                $item->expired = true;
            } else {
                $item->expired = false;
            }

            $item->start_date_text = JHTML::_('auctiondetails.startDateHtml', $item);
            $item->end_date_text = JHTML::_('auctiondetails.endDateHtml', $item);

            BidsHelperAuction::setAuctionStatus($item);

            if ($item->params->get('max_price', '1') == 0 || $item->auction_type == AUCTION_TYPE_PRIVATE) {
                $item->highest_bid = null;
            }

            $lists['bidderPaypalButton'][$item->id] = isset($item->wonBids) ? JHTML::_('auctiondetails.bidderPaypalButton',$item) : null;
        }

        $this->assign('positions', array());

        $filter_type = $model->getState('filters.filter_type');
        $filter_bidtype = $model->getState('filters.filter_bidtype');
        $filter_archive = $model->getState('filters.filter_archive');
        $filter_order = $model->getState('filters.filter_order');
        $filter_order_Dir = $model->getState('filters.filter_order_Dir');
        $pagination = $model->get('pagination');

        $lists['archive'] = JHTML::_('listauctions.selectMyFilter', $model->getState('filters.filter_archive'));
        $lists['filter_bidtype'] = JHTML::_('listauctions.selectBidType', $model->getState('filters.filter_bidtype') );
        $lists['orders'] = JHTML::_('listauctions.selectOrder',$filter_order);
        $lists['filter_order_asc'] = JHTML::_('listauctions.selectOrderDir', $filter_order_Dir);
        $lists['filter_cats'] = JHTML::_('listAuctions.selectCategory', $model->getState('filters.cat'));
        $lists['inputKeyword'] = JHTML::_('listAuctions.inputKeyword', $model->getState('filters.keyword'));
        $filters = $model->getFilters();
        $lists['inputsHiddenFilters'] = JHTML::_('listAuctions.inputsHiddenFilters', $filters );
        $lists['htmlLabelFilters'] = JHTML::_('listAuctions.htmlLabelFilters', $filters, false );
        $lists['resetFilters'] = JHTML::_('listAuctions.linkResetFilters', $model->getState('behavior'));

        $this->assign('lists', $lists);

        $uri = JFactory::getURI();
        $this->assign("action", JRoute::_(JFilterOutput::ampReplace($uri->toString())));
        $this->assign("auction_rows", $items);

        $this->assign("filter_type", $filter_type);
        $this->assign("filter_bidtype", $filter_bidtype);
        $this->assign("filter_order_Dir", $filter_order_Dir);
        $this->assign("reverseorder_Dir", $filter_order_Dir=='ASC' ? 'ASC' : 'DESC' );
        $this->assign("filter_order", $filter_order);
        $this->assign("filter_archive", $filter_archive);
        $this->assign("pagination", $pagination);

        JHTML::script( JURI::root().'components/com_bids/js/jquery/jquery.js' );
        JHTML::script( JURI::root().'components/com_bids/js/jquery/jquery.noconflict.js' );

        JHTML::script( JURI::root().'components/com_bids/js/ratings.js' );
        JHTML::script( JURI::root().'components/com_bids/js/startup.js' );

        JHTML::_('behavior.modal');
        JHTML::_('behavior.tooltip');

        BidsHelperHtml::loadCountdownJS();

        parent::display($tmpl);
    }
}
