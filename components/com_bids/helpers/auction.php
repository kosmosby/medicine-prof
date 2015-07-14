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



class BidsHelperAuction {

    static function setAuctionStatus(&$auction) {

        if ($auction->close_offer) {
            $auction->auction_status = JText::_('COM_BIDS_CLOSED');
        } else {
            $auction->auction_status = JText::_('COM_BIDS_OPEN');
            if (BidsHelperDateTime::getTimeStamp($auction->end_date) <= time()) {
                $auction->expired = true;
                $auction->auction_status = JText::_('COM_BIDS_EXPIRED');
            }
        }

        if (!$auction->published) {
            $auction->auction_status = JText::_('COM_BIDS_UNPUBLISHED');
        }

        //2.0.0
        if (!$auction->published && substr($auction->closed_date, 0, 4) !== "0000" && $auction->end_date > $auction->closed_date) {
            $auction->auction_status = JText::_('COM_BIDS_CANCELED');
        }
    }

    static function renderAuctionTiming(&$auction, $forceCountdown=false) {

        $cfg = BidsHelperTools::getConfig();

/*        $startDate = BidsHelperDateTime::getTimeStamp($auction->end_date);
        if( $startDate > time() ) {
            $auction->countdown = JText::_('COM_BIDS_NOT_YET_STARTED');
            return;
        }*/

        $auction->countdown = 0;

        $expiredate = BidsHelperDateTime::getTimeStamp($auction->end_date);

        if($auction->close_offer) {
            $auction->countdown = JText::_('COM_BIDS_CLOSED');
        } else if ($cfg->bid_opt_enable_countdown || $forceCountdown) {

            $diff = $expiredate - time();
            if ($diff > 0) {
                $s = sprintf('%02d', $diff % 60);
                $diff = intval($diff / 60);
                $m = sprintf('%02d', $diff % 60);
                $diff = intval($diff / 60);
                $h = sprintf('%02d', $diff % 24);
                $diff = intval($diff / 24);

                $auction->countdown = ( $diff > 0 ? ( $diff . ' ' . JText::_('COM_BIDS_DAYS') . ', ') : '' ) . $h . ':' . $m . ':' . $s;
            } else {
                $auction->countdown = JText::_('COM_BIDS_EXPIRED');
            }
        }

        $auction->expired = $auction->close_offer ? false : (boolean) ($expiredate <= time() );
    }

    static function formatPrice($price) {

        $cfg = BidsHelperTools::getConfig();

        $numberDecimals = intval($cfg->bid_opt_number_decimals);
        $decimalSeparator = $cfg->bid_opt_decimal_separator ? $cfg->bid_opt_decimal_separator : ',';
        $thousandSeparator = $cfg->bid_opt_thousand_separator ? $cfg->bid_opt_thousand_separator : '';

        return number_format( floatval($price), $numberDecimals, $decimalSeparator, $thousandSeparator);
    }

    static function formatDate($gmdate,$usehour=null) {

        $cfg = BidsHelperTools::getConfig();

        $dateFormat = $cfg->bid_opt_date_format;

        if(($usehour===null && $cfg->bid_opt_enable_hour)||($usehour)) {
            if ($cfg->bid_opt_date_time_format=='h:iA')
                $dateFormat .= ' I:i p';
            elseif ($cfg->bid_opt_date_time_format=='H:i')
                $dateFormat .= ' H:i';
            else
                $dateFormat .= ' H:i';
        }

        return JHTML::date( JFactory::getDate($gmdate)->toUnix() , $dateFormat );
    }

    static function getOrderItemsForAuction($auctionid,$itemname=null)
    {
        $db= JFactory::getDbo();
        $db->setQuery("select oi.*,o.status from `#__bid_payment_orderitems` oi
                        left join `#__bid_payment_orders` o on oi.orderid=o.id
                        where iteminfo='{$auctionid}'
                        ".($itemname?" and itemname='$itemname'":""));
        return $db->loadObjectList();
    }
}
