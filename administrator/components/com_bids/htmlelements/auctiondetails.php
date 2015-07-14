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



defined('_JEXEC') or die('Restricted access!');

class JHTMLAuctionDetails {

    static function fbLikeButton($auction) {

        $url = '//www.facebook.com/plugins/like.php?href='. urlencode(trim(JUri::root(), '/') . self::auctionDetailsURL($auction)).'&amp;send=false&amp;layout=button_count&amp;width=90&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=50';

        return JHtml::iframe($url,'bidsFBbutton','scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:50px; height:90px;" allowTransparency="true"');
    }

    static function auctionDetailsURL($auction,$xhtml=true) {

        return JRoute::_( 'index.php?option=com_bids&task=viewbids&id='.$auction->id.':'.JFilterOutput::stringURLSafe($auction->title) , $xhtml );
    }

    static function startDateHtml($auction) {

        return BidsHelperAuction::formatDate($auction->start_date);
    }

    static function endDateHtml($auction) {

        return BidsHelperAuction::formatDate($auction->end_date);
    }

    static function createLinks(&$auction) {

        $task = JRequest::getVar('task');

        $ItemIdList = array();
        $ItemIdList['listauctions'] = BidsHelperTools::getMenuItemId(array("task" => "listauctions"));// List Auctions, Auction Details
        $ItemIdList['newauction'] = BidsHelperTools::getMenuItemId(array("task" => "newauction", "task" => "form"));// New Auctions

        $links = array();
        $links['otherauctions'] = JRoute::_('index.php?option=com_bids&task=listauctions&users=' . $auction->userid . '&Itemid=' . $ItemIdList['listauctions']);
        $links['auctiondetails'] = self::auctionDetailsURL($auction);
        $links['bids'] = self::auctionDetailsURL($auction) . '#bid';
        $links['bid_list'] = self::auctionDetailsURL($auction) . '#bid_list';
        $links['messages'] = self::auctionDetailsURL($auction) . '#messages';
        $links['rate_auction'] = self::auctionDetailsURL($auction) . '#bid_list';
        $links['edit'] = JRoute::_('index.php?option=com_bids&task=editauction&id=' . $auction->id . '&Itemid=' . $ItemIdList['listauctions']);
        $links['cancel'] = JRoute::_('index.php?option=com_bids&task=cancelauction&id=' . $auction->id );
        $links['publish'] = JRoute::_('index.php?option=com_bids&task=publish&id=' . $auction->id);
        $links['filter_cat'] = JRoute::_("index.php?option=com_bids&task=listauctions&cat=$auction->cat&Itemid={$ItemIdList['listauctions']}");
        $links['republish'] = JRoute::_('index.php?option=com_bids&task=republish&id=' . $auction->id);
        $links['add_to_watchlist'] = JRoute::_('index.php?option=com_bids&task=watchlist&id=' . $auction->id);
        $links['del_from_watchlist'] = JRoute::_('index.php?option=com_bids&task=delwatch&id=' . $auction->id);
        $links['pagelinks'] = JRoute::_('index.php?option=com_bids&task=' . $task . '&Itemid=' . $ItemIdList['listauctions']);
        $links['auctioneer_profile'] = JRoute::_('index.php?option=com_bids&task=userdetails&id=' . $auction->userid );
        $links['auctioneer_ratings'] = JRoute::_('index.php?option=com_bids&task=userratings&userid=' . $auction->userid );

        $links['report'] = JRoute::_('index.php?option=com_bids&task=report_auction&id=' . $auction->id );
        $links['bin'] = JRoute::_('index.php?option=com_bids&task=bin&id=' . $auction->id . '&Itemid=' . $ItemIdList['listauctions']);

        $links['new_auction'] = JRoute::_('index.php?option=com_bids&task=newauction&Itemid=' . $ItemIdList['newauction']);
        $links['bulkimport'] = JRoute::_('index.php?option=com_bids&task=bulkimport');
        $links['terms'] = JRoute::_('index.php?option=com_bids&task=terms_and_conditions&tmpl=component');

        $links['tags'] = self::taglinks($auction);

        return $links;
    }

    static function countdownHtml($auction) {
        return '<span class="bidCountdown">'.$auction->countdown.'</span>';
    }

    static function follow($auction) {

        if($auction->close_offer) {
            return;
        }

        $my = JFactory::getUser();
        $followerIds = $auction->followerIds ? explode(',', $auction->followerIds) : array();

        //seller cannot follow his own auction
        if($my->guest || $my->id==$auction->userid) {
            $imgSrc = JURI::root().'components/com_bids/images/watchlist_del.jpg';
            $tooltip = JText::_('COM_BIDS_WATCHLIST');

            return '<span class="hasTip actionsTooltip" title=" ::'.$tooltip.'" style="background-image:url(\''
                    .$imgSrc.'\'); background-repeat: no-repeat; "></span>';
        }

        $iAmFollower = in_array($my->id,$followerIds);
        $task = $iAmFollower ? 'delwatch' : 'watchlist';

        $tooltip = JText::_( $iAmFollower ? 'COM_BIDS_REMOVE_FROM_WATCHLIST' : 'COM_BIDS_ADD_TO_WATCHLIST' );
        $imgSrc = JURI::root().'components/com_bids/images/watchlist_' . ($iAmFollower ? 'del' : 'add') . '.jpg';
        $txt = '<span class="hasTip actionsTooltip" title=" ::'.$tooltip.'" style="background-image:url(\''.$imgSrc
                .'\'); background-repeat:no-repeat;"></span>';

        $url = JRoute::_('index.php?option=com_bids&task='.$task.'&id=' . $auction->id);

        return JHTML::link( $url, $txt );
    }

    static function report($auction) {

        $my = JFactory::getUser();

        if($auction->close_offer || $auction->userid==$my->id) {
            return;
        }

        $imgSrc = JURI::root().'components/com_bids/images/report_auction.jpg';
        $tooltip = JText::_('COM_BIDS_REPORT_OFFER');

        $txt = '<span class="hasTip actionsTooltip" title=" ::'.$tooltip.'" style="background-image:url(\''.$imgSrc.'\'); ">&nbsp;</span>';
        $url = JRoute::_('index.php?option='.APP_EXTENSION.'&task=report_auction&id='.$auction->id);

        return JHTML::link( $url, $txt );
    }

    static function chooseWinner($auction) {

        $my = JFactory::getUser();
        if ( !$auction->close_offer || $auction->automatic || $auction->auction_type==AUCTION_TYPE_BIN_ONLY  || $auction->userid!=$my->id || !count($auction->bids) || count($auction->wonBids) ) {
            return;
        }

        $imgSrc = JURI::root().'components/com_bids/images/choose_winner.png';
        $imgAlt = JText::_('COM_BIDS_CHOOSE_A_WINNER');

        return '<span class="hasTip" title=" ::'.$imgAlt.'">'.JHTML::image($imgSrc, $imgAlt).''.JText::_('COM_BIDS_CHOOSE_A_WINNER').'</span>';
    }

    static function rate($auction) {

        $my = JFactory::getUser();

        if( !$auction->sellerHasToRate && !$auction->buyerHasToRate ) { //i'm neither seller, nor buyer who has to rate
            return;
        }

        JHTML::script('star_rate.js',JURI::root().'components/com_bids/js/');

        $imgSrc = JURI::root().'components/com_bids/images/rate_auction.jpg';
        $tooltip = JText::_('COM_BIDS_RATE');

        $txt = '<span class="hasTip actionsTooltip" title=" ::'.$tooltip.'" style="background-image:url(\''.$imgSrc.'\'); ">&nbsp;</span>';

        return JHTML::link('javascript: void(0);',$txt,'onclick="showMessageBox(\'auction_rateit\');"');
    }

    static function selectUserRated($auction) {

        $my = JFactory::getUser();
        if($my->id != $auction->userid) {
            return '<input type="hidden" name="user_rated_id" value="'.$auction->userid.'" />';
        }

        $opts = array();
        $already = array();
        foreach($auction->wonBids as $b) {
            if( in_array($b->userid,$already) ) {
                continue;
            }
            $opts[] = JHTML::_('select.option',$b->userid,$b->username);
            $already[] = $b->userid;
        }

        return JHTML::_('select.genericlist',$opts,'user_rated_id');
    }

    static function computeTotalPrice($auction) {

        $my = JFactory::getUser();
        $totalPrice = array();

        foreach($auction->wonBids as $b ) {
            if($b->userid == $my->id) {
                $totalPrice[] = array(
                    "price" => $b->bid_price,
                    "quantity" => $b->quantity,
                    "total" => $b->bid_price * $b->quantity,
                );
            }
        }

        return $totalPrice;
    }

    static function bidderPaypalButton($auction) {

        $my = JFactory::getUser();
        $cfg = BidsHelperTools::getConfig();
        $tfApp = JTheFactoryApplication::getInstance();

        //build paypal url based on the payment gateway settings
        JModel::addIncludePath($tfApp->app_path_admin.DS.'payments'.DS.'models');
        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $gateways = $model->getGatewayList(true);

        $pgw = null;
        foreach($gateways as $gw) {
            if('pay_paypal'==$gw->classname) {
                $pgw = $gw;
            }
        }
        $paypalURL = JDEBUG ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';

        $sellerProfile = BidsHelperTools::getUserProfileObject($auction->userid);

        $totalPrice = 0;
        $totalItems = 0;
        $priceDetails = array();
        foreach($auction->wonBids as $b ) {
            if($b->userid == $my->id) {
                $totalPrice += $cfg->bid_opt_quantity_enabled ? ($b->bid_price*$b->quantity) : $b->bid_price;
                $totalItems += $cfg->bid_opt_quantity_enabled ? ($b->quantity) : 1;
                $priceDetails[] = $b->quantity.' x '.BidsHelperAuction::formatPrice($b->bid_price).' '.$auction->currency;
            }
        }

        $auction->shipmentPrices = isset($auction->shipmentPrices) ? (array) $auction->shipmentPrices : array();

        if($cfg->bid_opt_multiple_shipping && count($auction->shipmentPrices)>1 ) {//multiple shipment options

            $opts = array();
            $opts[] = JHTML::_('select.option',0,JText::_('COM_BIDS_SELECT_SHIPMENT_ZONE'));
            foreach($auction->shipmentPrices as $sp) {
                $opts[] = JHTML::_('select.option', number_format($sp->price,$cfg->bid_opt_number_decimals), $sp->name.' ('.BidsHelperAuction::formatPrice($sp->price).' '.$auction->currency.')' );
            }
            $shipmentPrice =  '<input type="hidden" id="bidderTotalPrice'.$auction->id.'" value="'.number_format($totalPrice,$cfg->bid_opt_number_decimals).'" />'.//need a hidden input with the total price(witohut shipment)
                        (count($opts)>1 ? JText::_('COM_BIDS_SHIP_TO').JHTML::_('select.genericlist',$opts,'amount','onchange="refreshTotalPrice('.$auction->id.',this.value);"') : '');

        } elseif( count($auction->shipmentPrices)==1 ) {//single shipment zone

            $totalPrice += $auction->shipmentPrices[0]->price;
            $shipmentPrice = JText::_('COM_BIDS_SHIPMENT').': '.BidsHelperAuction::formatPrice($auction->shipmentPrices[0]->price).' '.$auction->currency.' '.
                            ($auction->shipment_info ? JHTML::tooltip($auction->shipment_info) : '');

        } else {
            $shipmentPrice = '';
        }

        $html = ($shipmentPrice)?($shipmentPrice.'<br />'):"";

        $tooltip = ($totalItems > 1 ? ($totalItems.' '.JText::_('COM_BIDS_PAYPAL_BUTTON_ITEMS').'<br />') : '').implode('<br />',$priceDetails);
        $html .= ' <span id="amount_total'.$auction->id.'" class="bids_price">'
                .BidsHelperAuction::formatPrice($totalPrice).'</span> '.$auction->currency.'&nbsp;' . JHTML::tooltip($tooltip);

        if( empty($sellerProfile->paypalemail) ) {
            $html .= '';
        } else {
            $html .=
                '<form name="paypalForm'.$auction->id.'" action="'.$paypalURL.'" method="post">
                    <input type="hidden" name="cmd" value="_xclick" />
                    <input type="hidden" name="business" value="'.$sellerProfile->paypalemail.'" />
                    <input type="hidden" name="item_name" value="'.$auction->title.'" />
                    <input type="hidden" name="item_number" value="'.$auction->id.'" />
                    <input type="hidden" name="invoice" value="'.$auction->auction_nr.'" />
                    <input type="hidden" name="quantity" value="1" />
                    <input type="hidden" name="return" value="'.trim(JUri::root(),'/').self::auctionDetailsURL($auction).'" />
                    <input type="hidden" name="tax" value="0" />
                    <input type="hidden" name="rm" value="2" />
                    <input type="hidden" name="no_note" value="1" />
                    <input type="hidden" name="no_shipping" value="1" />
                    <input type="hidden" name="currency_code" value="'.$auction->currency.'" />
                    <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but06.gif" name="submit" alt="'.JText::_('COM_BIDS_PAYPAL_BUYNOW').'" />
                    <input type="hidden" name="amount" value="'.$totalPrice.'" />
                </form><br />';
        }

        return $html;
    }

    static function taglinks($auction) {

        $tagLinks = array();
        if(isset($auction->tagIds)) {
            foreach($auction->tagIds as $k=>$tagid) {
                if(!isset($auction->tagNames[$k])) {
                    continue;
                }
                $tagname = JFilterOutput::stringURLUnicodeSlug($auction->tagNames[$k]);
                $href = JRoute::_('index.php?option=com_bids&task=tags&tagid='.$tagid.':'.$tagname);
                $tagLinks[] = '<span class="auction_tag">' . JHTML::link($href, $auction->tagNames[$k] ) . '</span>';
            }
        }

        return implode(', ',$tagLinks);
    }

    static function linkRatingsDetails($auction,$ratings) {

        $star_full = JHtml::image(JUri::root().'components/com_bids/images/f_rateit_1.png','stars','style="width:14px;"');
        $star_empty = JHtml::image(JUri::root().'components/com_bids/images/f_rateit_0.png','stars','style="width:14px;"');

        $url = JRoute::_('index.php?option=com_bids&task=userratings&userid=' . $auction->userid . '&tmpl=component' );
        $attribs = array();
        //$attribs['rel'] = '{handler:\'url\', onShow: \'FillRatings\'}';
        $attribs['rel'] = '{handler: \'iframe\', size: {x: 875, y: 550}}';
        $attribs['class'] = 'modal hasTip';

        $tooltipBody = '';
        $sumRating = 0;
        $auctioneer_ratings = array();
        for ($i = 5; $i >= 1; $i--) {

            $auctioneer_ratings[$i] = 0;

            if (isset($ratings[$i * 2])) {
                $auctioneer_ratings[$i] += $ratings[$i * 2]['nr'];
                $sumRating += $i*2*$ratings[$i * 2]['nr'];
            }
            if(isset($ratings[$i * 2 - 1])) {
                $auctioneer_ratings[$i] += $ratings[$i * 2 - 1]['nr'];
                $sumRating += ($i*2-1)*$ratings[$i * 2 - 1 ]['nr'];
            }

            if($i>3) {
                $class = "auction_positive_rating";
            } elseif ($i==3) {
                $class = "auction_neutral_rating";
            } else {
                $class = "auction_negative_rating";
            }

            $tooltipBody .= '<div style="text-align: center;">'.str_repeat($star_full,$i).str_repeat($star_empty,5-$i).'&nbsp;<span class="'.$class.'">'.$auctioneer_ratings[$i].'</span></div>';
        }

        $reputation = array_sum($auctioneer_ratings) ? number_format($sumRating/array_sum($auctioneer_ratings),1) : JText::_('COM_BIDS_N/A');
        $tooltipBody = '<div style="text-align: center; font-weight: bold;">'.$reputation.'</div>'.$tooltipBody;

        $attribs['title'] = JText::_('COM_BIDS_REPUTATION_RATING').'::'.htmlentities($tooltipBody);

        $text1 = array_sum($auctioneer_ratings);
        $text2 = $star_full;

        return JHtml::link($url,$text1,$attribs).' '. JHtml::link($url, $text2, $attribs);
    }
}
