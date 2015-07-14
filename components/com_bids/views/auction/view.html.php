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

class bidsViewAuction extends BidsSmartyView {

    function display($tpl=null) {

        $mainframe = JFactory::getApplication();
        $database = JFactory::getDBO();
        $my = JFactory::getUser();
        $cfg = BidsHelperTools::getConfig();

        $model = $this->getModel();
        $auction = $model->get('auction');

        $document = JFactory::getDocument();
        $document->setTitle($auction->title);
        $document->setMetaData('description', strip_tags($auction->shortdescription));
        $document->setMetaData('abstract', strip_tags($auction->description));
        foreach($auction->tagNames as $tag) {
            $document->setMetaData('keywords', $tag);
        }

        $jsQuantity = 'var has_quantity = ' . ( ($cfg->bid_opt_quantity_enabled && $auction->nr_items > 1) ?
                'true' : 'false' ) . ';';
        $document->addScriptDeclaration($jsQuantity);

        $links_path = JHtml::_('auctiondetails.auctionDetailsURL',$auction);

        $pathway = $mainframe->getPathway();
        $pathway->addItem($auction->title, $links_path);

        /* PLUGINS */
        $database->setQuery("select * from #__bid_pricing where enabled=1 order by ordering");
        $pp = $database->loadObjectList();
        $pricing_plugins = array();
        foreach ($pp as $p) {
            $pricing_plugins[$p->itemname] = $p;
        }
        $this->assign('pricing_plugins', $pricing_plugins);

        $auction->countdownHtml = JHTML::_('auctiondetails.countdownHtml',$auction);

        $auctioneer = JFactory::getUser($auction->userid);
        $profile = BidsHelperTools::getUserProfileObject($auctioneer->id);

        $this->assign('auctioneer', $auctioneer);
        $this->assign('auctioneer_details', $profile);

        $document = JFactory::getDocument();
        $js = 'var must_accept_term = '.((strip_tags($cfg->terms_and_conditions))?"true;":"false;");
        $this->assign('terms_and_conditions',(strip_tags($cfg->terms_and_conditions))?1:0);
        $document->addScriptDeclaration($js);
        $document->addScriptDeclaration('var auction_currency=\''.$auction->currency.'\';');

        /* PLUGINS */
        if ($cfg->bid_opt_enable_captcha) {
            $this->assign("cs", BidsHelperHtml::init_captcha());
        }

        $u = JTable::getInstance('biduser');
        $ratings = $u->getRatingsList($auctioneer->id);

        $list = array();

        $list['auctionWatchlist'] = JHTML::_('auctiondetails.follow',$auction);
        $list['auctionReport'] = JHTML::_('auctiondetails.report',$auction);
        $list['auctionChooseWinner'] = JHTML::_('auctiondetails.chooseWinner',$auction);
        $list['auctionRate'] = JHTML::_('auctiondetails.rate',$auction);
        $list['auctionFbLikeButton'] = JHTML::_('auctionDetails.fbLikeButton',$auction);
        $list['userRated'] = JHTML::_('auctionDetails.selectUserRated',$auction);
        $list['ratingsDetails'] = JHTML::_('auctionDetails.linkRatingsDetails',$auction,$ratings);

        $modelAuctions = JModelLegacy::getInstance('auctions','bidsModel');
        $otherAuctions = $modelAuctions->getOtherAuctionsList($auction->userid,$auction->id,4);

        $gallery = BidsHelperGallery::getGalleryPlugin();
        foreach($otherAuctions as &$oa) {
            BidsHelperAuction::renderAuctionTiming($oa);

            $gallery->clearImages();
            $gallery->addImageList($oa->imagelist);

            $oa->thumbnail = $oa->get('thumbnail');
            $oa->countdownHtml = JHTML::_('auctiondetails.countdownHtml',$oa);
        }
        $list['other_items'] = $otherAuctions;
        $list['tagLinks'] = JHtml::_('auctiondetails.taglinks',$auction);

        $list['ratings'] = BidsHelperHtml::getRatingsSelect();

        if(AUCTION_TYPE_BIN_ONLY==$auction->auction_type && $auction->quantity > 1) {

            $itemsPurchased = 0;

            foreach($auction->wonBids as $wb) {
                if($wb->userid==$my->id) {
                    $itemsPurchased += $wb->quantity;
                }
            }

            $list['myPurchasedItems'] = $itemsPurchased;
        }

        $userProfile = BidsHelperTools::getUserProfileObject();

	    $gallery->writeJS();

        $sellerProfile = BidsHelperTools::getUserProfileObject($auction->userid);

        $list['bidderPaypalButton'] = JHTML::_('auctiondetails.bidderPaypalButton',$auction);

        $auction->payment_name = $auction->payment_method;
        $auction->thumbnail = $auction->get('thumbnail');
        $auction->gallery = $auction->get('gallery');
        $auction->links = JHTML::_('auctiondetails.createLinks',$auction);
        $auction->username = $sellerProfile->username;
        $auction->paypalemail = isset($sellerProfile->paypalemail) ? $sellerProfile->paypalemail : null;
        $auction->start_date_text = JHTML::_('auctiondetails.startDateHtml', $auction);
        $auction->end_date_text = JHTML::_('auctiondetails.endDateHtml', $auction);

        $auction->minIncrement = $model->getMinIncrement();
        $auction->minAcceptedPrice = $model->getMinAcceptedPrice();
        $auction->isMyAuction = $model->ownsAuction($my->id);

        $mybids = $model->getUserBids($my->id);
        $auction->myBid = reset($mybids);

        $auction->my_proxy_bid = $auction->myBid ? $auction->myBid->max_proxy_price : 0;
        $auction->mybid = $auction->myBid ? $auction->myBid->bid_price : 0;

        $auction->winner_list = $auction->wonBids;
        $auction->winBid = 0;

        if($auction->highestBid && $auction->highestBid->accept) {
            $auction->winBid = $auction->highestBid->bid_price;
        }

        $auction->highestBidder = $auction->highestBid ? $auction->highestBid->username : '';
        $auction->highestBidderId = $auction->highestBid ? $auction->highestBid->userid : 0;

        if(!$auction->highestBid) {
            $auction->highestBid = new stdClass();
            $auction->highestBid->bid_price = 0;
        }
        $auction->highestBid->bid_price =
                (
                    $auction->isMyAuction ||
                    (!empty($auction->params['max_price']) && $auction->auction_type!=AUCTION_TYPE_PRIVATE)
                ) ?
                ($auction->highestBid ? $auction->highestBid->bid_price : 0) : 'private';


        $auction->iAmWinner = $model->isWinner($my->id);

        $suggestions = isset($auction->suggestions) ? $auction->suggestions : null;

        JHTML::script(JURI::root().'components/com_bids/js/jquery/jquery.js');
        JHTML::script(JURI::root().'components/com_bids/js/jquery/jquery.noconflict.js');
        JHTML::script(JURI::root().'components/com_bids/js/auctions.js');
        JHTML::script(JURI::root().'components/com_bids/js/startup.js');
        JHTML::_('behavior.modal');

        BidsHelperHtml::loadCountdownJS();

        $this->assign('auction', $auction);
        $this->assign('userProfile',$userProfile);
        $this->assign('message_list', $model->getAuctionMessages($auction->id));
        $this->assign('bid_list', $auction->bids );
        $this->assign('bid_history', $auction->bids_history );
        $this->assign('positions', array());
        $this->assign('suggestions', $suggestions);
        $this->assign('lists', $list);

        parent::display($tpl);
    }
}
