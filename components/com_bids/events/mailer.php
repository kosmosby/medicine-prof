<?php

defined('_JEXEC') or die('Restricted access');

class JTheFactoryEventMailer extends JTheFactoryEvents
{
    function onAuctionBanned($auction)//
    {
        $seller = JFactory::getUser($auction->userid);
        $auction->sendMails($seller,'bid_auction_banned');
        
    }
    function onUserRating($rateobject)//
    {
        $usr =JFactory::getUser($rateobject->user_rated_id);
        $auction = JTable::getInstance('auction');
        $auction->load($rateobject->auction_id);
        $auction->SendMails($usr , 'bid_rate');
        
    } 
    function onAfterSaveSuggestion($auction,$suggest)//
    {
        if ($suggest->parent_id){
            //counteroffer from auctioneer
            $suggesttable = JTable::getInstance('bidsuggestion');
            $suggesttable->load($suggest->parent_id);
            $usr =JFactory::getUser($suggesttable->userid);
            $auction->SendMails($usr, 'replysuggest_success');
        }
        $usr =JFactory::getUser($suggest->userid);
        $auction->SendMails($usr , 'suggest_success');
    }
    function onRejectSuggestion($auction,$suggest)//
    {
        $usr =JFactory::getUser($suggest->userid);
        $auction->SendMails($usr , 'suggest_rejected');
    }
    function onAcceptSuggestion($auction,$suggest)//
    {
        $usr =JFactory::getUser($suggest->userid);
        $auction->SendMails($usr , 'suggest_accepted');
    }
    
    function onAfterCancelAuction($auction)//
    {
        $auctionmodel = JModelLegacy::getInstance('auction','bidsModel');
        if ( !$auctionmodel->load($auction->id) ) {
            return;
        }
        $bidders = $auctionmodel->getBidders();
        $auction->SendMails($bidders, 'bid_canceled');
        $followers = self::getFollowers($auction->id);
        $auction->SendMails($followers, 'bid_watchlist_canceled');
        
    }

    function onAfterSaveBid($auction,$bid)//
    {

        if ($bid->cancel) retun; //not published yet

		$seller = JFactory::getUser($auction->userid);
        $bidder = JFactory::getUser($bid->userid);
        $watches=null;

		if ($auction->auction_type!=AUCTION_TYPE_PRIVATE) {
            $auctionmodel = JModelLegacy::getInstance('auction','bidsModel');
            if ( !$auctionmodel->load($auction->id) ) {
                return;
            }
            $watches = self::getFollowers($auction->id);
        }
        if($auction->BIN_price>0 && $bid->bid_price>=$auction->BIN_price) 
        {//BIN bid
            if (count($watches))
                $auction->SendMails($watches, 'alert_new_bid_bin');
            $auction->SendMails($seller, 'new_bid_bin');
            if(!$auction->automatic && !$auction->params['auto_accept_bin']) {
                $auction->SendMails($bidder, 'bin_wait_approval');
            } else {
                $auction->SendMails($watches, 'alert_bin_accepted');
            }
        } else {//not BIN bid
            $auction->SendMails($seller, 'new_bid');
            $auction->SendMails($bidder, 'bid_new_mybid');
            if (count($watches))
                $auction->SendMails($watches, 'new_bid_watchlist');

            $db = JFactory::getDBO();
            $db->setQuery("select * from #__bids where auction_id={$auction->id} and id<>{$bid->id} and cancel=0 order by bid_price desc limit 1");
            $secondBid=$db->loadObject();
            if ($secondBid && $secondBid->bid_price<$bid->bid_price && $secondBid->userid<>$bid->userid ){
                $outbidded = JFactory::getUser($secondBid->userid);
                $auction->SendMails(array($outbidded), 'bid_outbid');
            }
        }
    }
    function onAfterSendMessage($auction,$message)//
    {
        $cfg= BidsHelperTools::getConfig();
        $usr=JFactory::getUser($message->userid2);
        
        $app=JFactory::getApplication();
        
        if ($app->isAdmin()) {
            $auction->SendMails(array($usr), 'bid_admin_message');
        } else {
            $auction->SendMails(array($usr),"new_message");
        }
        if ($cfg->bid_opt_uddeim) {
            $comment = str_replace('<br>', "\n", $message->message);
            $comment = JText::sprintf('COM_BIDS_SUBJECT_FORMAT', $auction->title, $auction->auction_nr) . PHP_EOL . $comment;
            BidsHelperUdde::sendMessage($message->userid1, $message->userid2, $comment);
        }
            
    }
    function onAfterAcceptBid($auction,$bid)//
    {
        $db = JFactory::getDBO();
        $winner=JFactory::getUser($bid->userid);
        
        $auction->SendMails($winner,'bid_accepted');

        $auction->sendNewMessage(JText::_('COM_BIDS_ACCEPTED'), null, $bid->userid);

        if ($auction->close_offer)
        {// auction was closed 
            

        }
    }
    function onAfterSaveAuctionSuccess($auction)//
    {
        if ($auction->published){
            $user = JFactory::getUser($auction->userid);
            $auction->SendMails(array($user), 'new_auction');

/*            $watchCategory = JModelLegacy::getInstance('watchlistcategory');
            $followers = $watchCategory->getCategoryFollowers($auction->cat);
            $auctionmodel->SendMails($followers, 'new_auction_watchlist_cat');
*/            
        }

    }
    function onAfterCloseAuction($auction,$bid)
    {
        //mail losers
        $db= JFactory::getDBO();

        $q = "SELECT u.*
                    FROM #__users AS u
                    LEFT JOIN #__bids AS b
                        ON u.id=b.userid
                    WHERE
                        b.cancel=0
                        AND b.accept=0
                        AND u.block=0
                        AND b.auction_id=".$auction->id;
        $db->setQuery($q);
        $losers = $db->loadObjectList();
        $auction->SendMails($losers, 'bid_lost');
        
        //mail rejected suggestions if auction is binolny..
        if(AUCTION_TYPE_BIN_ONLY==$auction->auction_type) {
            $db->setQuery("SELECT u.*
                            FROM #__users AS u
                            LEFT JOIN #__bid_suggestions AS s
                                ON u.id=s.userid
                            WHERE
                                u.id<>".$bid->userid."
                                AND s.status=0
                                AND s.auction_id=".$auction->id."
                            GROUP BY u.id");
            $losers = $db->loadObjectList();
            $auction->SendMails($losers, 'suggest_rejected');
        }

        $auctionmodel = JModelLegacy::getInstance('auction','bidsModel');
        if ( !$auctionmodel->load($auction->id) ) {
            return;
        }
        $followers = self::getFollowers($auction->id);
        $auction->SendMails($followers, 'bid_watchlist_closed');
        
    }
    function onAuctionReported($auction,$message)//
    {
        $app = JFactory::getApplication();

        $adminuser=new StdClass();
        $adminuser->id = null;
        $adminuser->name = $app->getCfg('sitename');
        $adminuser->surname = "";
        $adminuser->email = $app->getCfg('mailfrom');
        $auction->sendMails(array($adminuser), 'report_notify');
    }

    protected function getFollowers($auctionId) {

        $db = JFactory::getDbo();

        static $requests = array();

        if(!isset($requests[$auctionId])) {

            $q = 'SELECT u.*
                        FROM #__users AS u
                        LEFT JOIN #__bid_watchlist AS w
                            ON u.id=w.userid
                        WHERE w.auction_id='.$db->escape($auctionId) ;
            $db->setQuery($q);

            $requests[$auctionId] = $db->loadObjectList();
        }

        return $requests[$auctionId];
    }
}
