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



class bidsViewSuggestions extends BidsSmartyView {

    function display($tmpl) {

        $model = $this->getModel('suggestions');

        $userProfile = BidsHelperTools::getUserProfileObject();

        $filter_bidtype = $model->getState('filters.filter_bidtype');
        $lists['filter_bidtype'] = JHTML::_('listauctions.selectBidType', $filter_bidtype );
        $sfilters['bid_type'] = $filter_bidtype;

        $gallery = BidsHelperGallery::getGalleryPlugin();

        $suggestions = $model->get('suggestions');
        $rownr = 0;
        foreach($suggestions as &$s) {
            $s->rownr = ++$rownr;
            $s->links = JHTML::_('auctiondetails.createLinks',$s);
            BidsHelperAuction::renderAuctionTiming($s);
            $s->countdownHtml = JHTML::_('auctiondetails.countdownHtml',$s);

            $gallery->clearImages();
            $gallery->addImageList(explode(',', $s->pictures));
            $s->thumbnail = $gallery->getThumbImage();
        }

        JHTML::script(JURI::root().'components/com_bids/js/jquery/jquery.js');
        JHTML::script(JURI::root().'components/com_bids/js/jquery/jquery.noconflict.js');

        BidsHelperHtml::loadCountdownJS();

        JHTML::script( JURI::root().'components/com_bids/js/startup.js' );

        $this->assign('auction_rows', $suggestions);
        $this->assign('pagination', $model->get('pagination') );
        $this->assign('lists', $lists);
        $this->assign('sfilters', $sfilters);
        $this->assign('userProfile',$userProfile);

        parent::display($tmpl);
    }
}
