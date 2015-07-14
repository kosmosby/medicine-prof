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

jimport('joomla.application.component.modelform');

/**
 * @package		Auctions Factory
 * @since 2.0
 */
class bidsModelAuction extends JModelForm {

    protected $auction=null;
    protected $allowedPictureExt = array('JPEG','JPG','GIF','PNG');

    //$arr - array $_REQUEST
    //$files - array $_FILES
    function bind( $arr, $files, $isAdmin = false ) {

        $my = JFactory::getUser();
        $cfg = BidsHelperTools::getConfig();

        $a = JTable::getInstance('auction');
        $a->bind($arr);

        if(!$a->userid) {
            $a->userid = $my->id;
        }

        $original = JTable::getInstance('auction');
        $original->load($a->id);

        if($a->id) { //some fields never change
            $a->auction_nr = $original->auction_nr;
            $a->hits = $original->hits;
            $a->newmessages = $original->newmessages;
            $a->nr_items = $original->nr_items;
            $a->featured = $original->featured;
            $a->extended_counter = $original->extended_counter;
            $a->userid = $original->userid;
        }

        $isPublished = $original->published;
        if ($isPublished && !$isAdmin) {

            $a->title = $original->title;
            $a->cat = $original->cat;
            $a->published = $original->published; //no unpublishing after bids, just cancel
            $a->start_date = $original->start_date;
            $a->end_date = $original->end_date;
            $a->auction_type = $original->auction_type;
            $a->automatic = $original->automatic;
            $a->currency = $original->currency;
            $a->initial_price = $original->initial_price;
            $a->params = $original->params;
            $a->reserve_price = $original->reserve_price;
            $a->min_increase = $original->min_increase;
            $a->BIN_price = $original->BIN_price;
            $a->quantity = $original->quantity;

        } else {

            $_float_fields=array('initial_price','BIN_price','reserve_price','min_increase');
            foreach($_float_fields as $fname) {
                if (isset($arr[$fname]) && substr($arr[$fname],0,1)=='.') {
                    $arr[$fname]='0'.$arr[$fname]; //american style prices like .95 instead of 0.95
                }
            }
            if ($cfg->bid_opt_enable_date) {

                $d = JArrayHelper::getValue($arr,'start_date');
                $h = $m = 0;
                if($cfg->bid_opt_enable_hour) {
                    $h = JArrayHelper::getValue($arr,'start_hour');
                    $m = JArrayHelper::getValue($arr,'start_minutes');
                }
                $a->start_date = BidsHelperDateTime::getUTCDate($d,$h,$m,0);

                $d = JArrayHelper::getValue($arr,'end_date');
                $h = $m = 0;
                if($cfg->bid_opt_enable_hour) {
                    $h = JArrayHelper::getValue($arr,'end_hour');
                    $m = JArrayHelper::getValue($arr,'end_minutes');
                }
                $a->end_date = BidsHelperDateTime::getUTCDate($d,$h,$m,0);

            } else {

                $a->start_date = gmdate('Y-m-d H:i:s');

                $nrDays = 0;
                if (intval($cfg->bid_opt_default_period) > 0) {
                    $nrDays = intval($cfg->bid_opt_default_period);
                }
                if ($cfg->bid_opt_allow_proxy && intval($cfg->bid_opt_proxy_default_period) > 0) {
                    $nrDays = intval($cfg->bid_opt_proxy_default_period);
                }
                if ( ($cfg->bid_opt_global_enable_bin || $cfg->bid_opt_enable_bin_only) && intval($cfg->bid_opt_bin_default_period) > 0 && $a->BIN_price > 0) {
                    $nrDays = intval($cfg->bid_opt_bin_default_period);
                }
                if ($cfg->bid_opt_global_enable_reserve_price && intval($cfg->bid_opt_reserve_price_default_period) > 0 && $a->reserve_price > 0) {
                    $nrDays = intval($cfg->bid_opt_reserve_price_default_period);
                }

                $endTime = strtotime(gmdate('Y-m-d H:i:s', strtotime($a->start_date)) . ' +' . $nrDays . ' days');
                $a->end_date = gmdate('Y-m-d H:i:s',$endTime);
            }

            if(!$isAdmin || !$a->id) {
                $a->userid = $my->id;
                $a->auction_nr = time() . rand(100, 999);
            }

            $a->description = JRequest::getVar('description', '', 'POST', 'none', JREQUEST_ALLOWRAW);

            $a->auction_type = JArrayHelper::getValue($arr,'auction_type', 0,'INT');
            $a->automatic = $cfg->bid_opt_automatic_auction_select ? JArrayHelper::getValue($arr,'automatic', 0,'INT') : $cfg->bid_opt_automatic_auction_default;

            $a->modified = gmdate('Y-m-d H:i:s');

            $a->quantity = JArrayHelper::getValue($arr,'quantity', 0,'INT');
            $a->nr_items = $a->quantity;

            //make sure prices are float
            $a->min_increase = JArrayHelper::getValue($arr,'min_increase',0,'float');
            $a->min_increase = ($cfg->bid_opt_min_increase_select && $a->min_increase) ? $a->min_increase : $cfg->bid_opt_min_increase ;
            $a->initial_price = JArrayHelper::getValue($arr,'initial_price', 0,'float');
            $a->reserve_price = JArrayHelper::getValue($arr,'reserve_price', 0,'float');

            $a->BIN_price = JArrayHelper::getValue($arr,'BIN_price', 0,'float');
            $bin_option= JArrayHelper::getValue($arr,'bin_OPTION',0,'int');
            if (!$bin_option && $a->auction_type != AUCTION_TYPE_BIN_ONLY) {
                $a->BIN_price = 0;
            }

            if($cfg->bid_opt_multiple_shipping) {
                $a->shipZones = isset($arr['shipZones']) ? $arr['shipZones'] : array();
            } else {
                $a->shipZones = array('');
            }
            $a->shipPrices = isset($arr['shipPrices']) ? $arr['shipPrices'] : array();

            jimport('joomla.html.parameter');
            $params = new JParameter(null);
            foreach($a->_defaultParams as $k=>$defParam) {
                $params->set($k,JArrayHelper::getValue($arr,$k,$defParam));
            }
            $a->params = $params->toArray();
        }

        $a->_uploadedFiles = $files;

        $this->auction = $a;

        //put some things in order
        $this->auction->tagNames = $arr['tags'];
        $this->formatTags();
    }

    function save() {

        $id = JRequest::getInt('id', 0);
        $oldid = JRequest::getInt('oldid', 0);
        $isRepost = $oldid && !$id;

        if ( count($errors = $this->ValidateSaveAuction()) ) {
            JError::raiseNotice(1, implode('<br/>', $errors));
            return false;
        }

        //store auction record
        if(!$this->saveAuction()){
            return false;
        }

        //store tags
        $this->saveTags();

        //store images
        $this->saveImages($isRepost ? $oldid : 0);

        //store shipping prices
        if(!$id) { //multiple shipping prices; if there's only one, it is saved on $this->saveAuction()
            $this->saveShippings();
        }

        return true;
    }

    protected function saveAuction() {

        $a = JTable::getInstance('auction');
        $a->bind($this->auction);

        if ( !$a->store() ) {
            return false;
        }
        //if new auction, from now on it has an id
        $this->auction->id = $a->id;

        return true;
    }

    protected function saveTags() {

        $a = $this->auction;
        $db = JFactory::getDbo();

        if($a->id) { //first delete old tags
            $db->setQuery( 'DELETE FROM #__bid_tags WHERE auction_id='.$db->Quote($a->id) );
            $db->query();
        }

        $tags = $a->tagNames;
        $bidtag = JTable::getInstance('bidtag');
        $bidtag->auction_id = $a->id;

        foreach($tags as $t) {
            $tagname = trim($t);
            if($tagname=='') {
                continue;
            }
            $bidtag->id = null;
            $bidtag->tagname = $tagname;
            $bidtag->store();
        }
    }

    protected function saveShippings() {

        $a = $this->auction;
        $db = JFactory::getDbo();

        // Delete old prices
        $db->setQuery('DELETE FROM `#__bid_shipment_prices` WHERE auction='.$db->Quote($a->id));
        $db->query();

        $shipPrice = JTable::getInstance('bidshipprice');
        $shipPrice->auction = $a->id;
        if(is_array($a->shipZones)) {
            foreach($a->shipZones as $k=>$sz) {
                if(!isset($a->shipPrices[$k])) {
                    continue;
                }
                $shipPrice->id = null;
                $shipPrice->zone = $sz;
                $shipPrice->price = floatval( (substr($a->shipPrices[$k],0,1)=='.' ? '0' : ''). $a->shipPrices[$k] );
                $shipPrice->store();
            }
        }
    }

    function ValidateSaveAuction() {

        $db = $this->getDBO();
        $my = JFactory::getUser();

        $cfg = BidsHelperTools::getConfig();

        $errors = array();

        $oldid = JRequest::getInt('oldid', 0);

        $auction = $this->auction;

        $db->setQuery( 'SELECT COUNT(1) FROM #__users WHERE id=' . $db->Quote($auction->userid) );
        if(!$db->loadResult()) {
            $errors[] = JText::_('COM_BIDS_IMPORT_CHECK_USERID');
        }

        if (trim($auction->title)==''){
            $errors[] = JText::_('COM_BIDS_IMPORT_CHECK_TITLE');
        }
        if (trim($auction->shortdescription)==''){
            $errors[] = JText::_('COM_BIDS_IMPORT_CHECK_SHORTDESC');
        }

        if ($auction->auction_type!=AUCTION_TYPE_PRIVATE && $auction->BIN_price>0 && $auction->initial_price>0 && $auction->BIN_price < $auction->initial_price) {
            $errors[] = JText::_('COM_BIDS_IMPORT_CHECK_BIN');
        }

        if( $auction->auction_type!=AUCTION_TYPE_BIN_ONLY && floatval($auction->initial_price)<=0 ) {
            $errors[] = JText::_('COM_BIDS_ERR_INITIAL_PRICE');
        }

        if( AUCTION_TYPE_BIN_ONLY==$auction->auction_type ) {
            if(floatval($auction->BIN_price)<=0) {
                $errors[] = JText::_('COM_BIDS_ERR_BIN_PRICE');
            }
            if(($cfg->bid_opt_quantity_enabled && $auction->quantity<=0)) {
                $errors[] = JText::_('COM_BIDS_ERR_QUANTITY');
            }
        }

        if( AUCTION_TYPE_BIN_ONLY!=$auction->auction_type ) {
            $auction->quantity = 1;
        }

        if(!is_numeric($auction->currency)) {
            if (!$auction->currency){
                $errors[] = JText::_('COM_BIDS_IMPORT_CHECK_CURRENCY');
            } else {
                $db->setQuery('SELECT name FROM #__bid_currency WHERE name='.$db->Quote($auction->currency) );

                if( !$db->loadResult() ) {
                    $errors[] = JText::_('COM_BIDS_IMPORT_CHECK_CURRENCY');
                }
            }
        } else {
            $db->setQuery('SELECT COUNT(1) FROM #__bid_currency WHERE name='.$db->Quote($auction->currency) );
            if( !$db->loadResult() ) {
                $errors[] = JText::_('COM_BIDS_IMPORT_CHECK_CURRENCY');
            }
        }

        if( !in_array( $auction->auction_type, array(AUCTION_TYPE_BIN_ONLY,AUCTION_TYPE_PRIVATE,AUCTION_TYPE_PUBLIC) ) ) {
            $errors[] = JText::_('COM_BIDS_IMPORT_CHECK_AUCTION_TYPE');
        }

        if( !$this->ownsAuction($my->id) ) {
            $errors[]= JText::_('COM_BIDS_NOT_ALLOW_AUCTION');
            return $errors;
        }
        if( !$this->ownsAuction($my->id) ) {
            $errors[]= JText::_('COM_BIDS_NOT_ALLOW_AUCTION');
            return $errors;
        }

        if (!$oldid && $auction->close_offer) {
            $errors[] = JText::_('COM_BIDS_AUCTION_IS_CLOSED');
        }

        if ($cfg->bid_opt_enable_date && (!$auction->id || isset($auction->datechanged) )) {

            if(!$auction->start_date) {
                $errors[] = JText::_('COM_BIDS_ERR_START_DATE_VALID');
            }
            if(!$auction->end_date) {
                $errors[] = JText::_('COM_BIDS_ERR_END_DATE_VALID');
            }

            if($cfg->bid_opt_enable_date && strtotime($auction->start_date)>=strtotime($auction->end_date)) {
                $errors[] = JText::_('COM_BIDS_IMPORT_CHECK_END_DATE');
            }

            if ($auction->start_date && $auction->end_date && !$auction->id) {

                $startDateTime=JFactory::getDate($auction->start_date);
                $endDateTime=JFactory::getDate($auction->end_date);
                $datedif = $endDateTime->toUnix() - $startDateTime->toUnix();

                if ($cfg->bid_opt_availability > 0) {
                    if (floor($datedif / 60 / 60 / 24) >= $cfg->bid_opt_availability * 31) {
                        $errors[] = JText::_('COM_BIDS_NOT_VALID_DATE_INTERVAL') . ": " . $cfg->bid_opt_availability . "\n";
                    }
                }
            }
        }

        //require main image
		if($cfg->bid_opt_require_picture) {

            $has_main_picture = false;

            $oldid = JRequest::getVar('oldid', 0);
            $delete_main_picture = JRequest::getVar('delete_main_picture', '');

            $mainPictureUpload = null;
            $uploadedFiles = $auction->_uploadedFiles;
            foreach($uploadedFiles as $k=>$f) {
                if(strpos($k,'picture')!==false) {
                    $mainPictureUpload = $uploadedFiles[$k];
                    break;
                }
            }

            if( ($oldid || $auction->id) && !$mainPictureUpload ) {

                //repost or edit existing

                $db->setQuery("SELECT COUNT(1) FROM #__bid_pictures WHERE auction_id=". ($oldid?$oldid:$auction->id) );
                $nrExistingPictures = $db->loadResult();


                $has_main_picture = $nrExistingPictures>0 && !$delete_main_picture;

            } else {

                if( !$mainPictureUpload['error'] ) {

                    $has_main_picture=true;
                    if ( !is_uploaded_file($mainPictureUpload['tmp_name']) ) {
                        $errors[]= $mainPictureUpload['name'] . " - " . JText::_("COM_BIDS_ERR_UPLOAD_FAILED");
                        $has_main_picture=false;
                    }

                    if (!$cfg->bid_opt_resize_if_larger && filesize($mainPictureUpload['tmp_name']) > $cfg->bid_opt_max_picture_size * 1024) {
                        $errors[]= $mainPictureUpload['name'] . " - " .JText::_("COM_BIDS_ERR_IMAGESIZE_TOO_BIG");
                        $has_main_picture=false;
                    }
                    if ( $mainPictureUpload['name'] ){
            			$ext = strtolower( JFile::getExt( $mainPictureUpload['name'] ) );
                        if (!$this->isAllowedImage($ext)) {
                            $errors[]= JText::_("COM_BIDS_ERR_NOT_ALLOWED_EXT") . ': ' . $ext;
                            $has_main_picture=false;
                        }
                    } else {
                        $has_main_picture=false;
                    }
                }
            }
            if( !$has_main_picture ) {
                $errors[]= JText::_('COM_BIDS_ERR_PICTURE_IS_REQUIRED');
            }
        }

        return $errors;
    }

    protected function generateNewBid($userid,$amount,$proxyId=0,$quantity=1) {

        $cfg = BidsHelperTools::getConfig();

        $a = $this->auction;

        $newBid = JTable::getInstance('bid');

        $userBids = $this->getUserBids($userid,false);
        $newBid->bind( empty($userBids)?array():reset($userBids) ); //user's CURRENT NOT ACCEPTED BID gets overwritten

        $newBid->userid = $userid;
        $newBid->auction_id = $a->id;
        $newBid->bid_price = $amount;
        $newBid->modified = gmdate('Y-m-d H:i:s');
        if (!$newBid->id) {
            $newBid->initial_bid = $amount;
            $newBid->accept = 0;
            $newBid->cancel = 0;
            $newBid->quantity = $quantity;
        } elseif ($cfg->bid_opt_quantity_enabled && AUCTION_TYPE_BIN_ONLY==$a->auction_type) {
            //increase bid's quantity
            $newBid->quantity += $quantity;
        }

        if($cfg->bid_opt_allow_proxy && $proxyId) {

            $newBid->id_proxy = $proxyId; //this bid is handled by a proxy

            $proxy = JTable::getInstance('bidproxybid');
            $proxy->id = $proxyId;
            $proxy->latest_bid = $amount; // update proxy's latest bid value
            $proxy->store();
        }

        JTheFactoryEventsHelper::triggerEvent('onBeforeSaveBid',array($this->auction,$newBid));

        $newBid->store();

        //log new bid
        $bidLog = JTable::getInstance('bidlog');
        $bidLog->bind($newBid);
        $bidLog->id = null;
        $bidLog->store();

        JTheFactoryEventsHelper::triggerEvent('onAfterSaveBid',array($this->auction,$newBid));

        //VERY IMPORTANT: RELOAD THE AUCTION AT THIS POINT, SO PROPERTIES LIKE "highestBid" REMAIN ACCURATE!!!
        $this->load($a->id);

        //autoextend auction
        if ($cfg->bid_opt_auto_extent) {
            $this->autoExtend();
        }

        return $newBid;
    }

    function bid($userid,$amount,$proxyId=0,$quantity=0) {

        $cfg = BidsHelperTools::getConfig();

        $newBid = $this->generateNewBid($userid,$amount,$proxyId,$quantity);

        if($cfg->bid_opt_allow_proxy) {
            $this->updateProxies();
        }

        return $newBid;
    }

    function proxyBid($userid, $amount ) {

        $a = $this->get('auction');

        $myNewProxy = JTable::getInstance('bidproxybid');
        $myNewProxy->bind( $this->getUserProxy($userid) );
        if (!$myNewProxy->id) {
            $myNewProxy->auction_id = $a->id;
            $myNewProxy->user_id = $userid;
        }
        $myNewProxy->max_proxy_price = max($myNewProxy->max_proxy_price,$amount); //user should not be able to proxy less than he already did

        JTheFactoryEventsHelper::triggerEvent('onBeforeUserProxyBid',array($a,$myNewProxy));
        $myNewProxy->store();
        JTheFactoryEventsHelper::triggerEvent('onAfterUserProxyBid',array($a,$myNewProxy));

        if($myNewProxy->active) {//if I currently own the highest proxy(active), there's no need to go further
            return;
        }

        $this->updateProxies($myNewProxy);
    }

    protected function updateProxies(&$newProxy=null) {

        $db = JFactory::getDBO();
        $a = $this->get('auction');

        if( $newProxy && isset($a->highestBid) && $a->highestBid->userid == $newProxy->user_id ) { //current proxy bidder owns the highest bid -> no more bidding required, just activate the proxy

            $newProxy->active = 1;
            $newProxy->store();

            //assign the current bid to be handled by this proxy
            $highestBid = JTable::getInstance('bid');
            $highestBid->bind($a->highestBid);
            $highestBid->id_proxy = $newProxy->id;
            $highestBid->store();

            return;
        }

        $activeProxy = JTable::getInstance('bidproxybid');
        $db->setQuery('SELECT p.* FROM #__bid_proxy p
                        WHERE p.auction_id='.$db->Quote($a->id).' and p.active=1');
        $result = $db->loadObject();
        $activeProxy->bind($result?$result:array());

        if (!$activeProxy->id) {

            if(!$newProxy) {//just a normal bid, so nothing to do here
                return;
            }

            //$this->generateNewBid($newProxy->user_id, $this->getMinAcceptedPrice(), $newProxy->id);
            $this->proxyGenerateNewBd($newProxy->user_id, $this->getMinAcceptedPrice(), $newProxy);

            $newProxy->active = 1;
            $newProxy->store();

        } else {

            if(!$newProxy) {//just a normal bid

                if($activeProxy->max_proxy_price < $this->getMinAcceptedPrice()) {

                    $proxyBidValue = $activeProxy->max_proxy_price;

                    $activeProxy->active = 0;
                    $activeProxy->store();

                    if($activeProxy->max_proxy_price < $a->highestBid->bid_price) {

                        $loser = JFactory::getUser($activeProxy->user_id);
                        $this->SendMails($loser, 'proxy_ended');

                    }

                } else {

                    $proxyBidValue = $this->getMinAcceptedPrice();

                }

                //$this->generateNewBid($activeProxy->user_id, $proxyBidValue, $activeProxy->id );
                $this->proxyGenerateNewBd($activeProxy->user_id, $proxyBidValue, $activeProxy);

                return;
            }

            //PROXY BATTLE
            if ( $newProxy->max_proxy_price > $activeProxy->max_proxy_price ) {

                $lowerProxy = $activeProxy;
                $higherProxy = $newProxy;

                $lowerProxy->active = 0;
                $lowerProxy->store();

                $higherProxy->active = 1;
                $higherProxy->store();

                $lowBid = $lowerProxy->max_proxy_price;
                $highBid = min( $higherProxy->max_proxy_price, $lowerProxy->max_proxy_price + $this->getMinIncrement() );

            } elseif ( $newProxy->max_proxy_price == $activeProxy->max_proxy_price ) {

                $lowerProxy = $newProxy;
                $higherProxy = $activeProxy;

                $lowBid = max( $lowerProxy->max_proxy_price - $this->getMinIncrement(), 0);
                $highBid = $higherProxy->max_proxy_price;

            } elseif ( $newProxy->max_proxy_price < $activeProxy->max_proxy_price ) {

                $lowerProxy = $newProxy;
                $higherProxy = $activeProxy;

                $lowBid = $lowerProxy->max_proxy_price;
                $highBid = min( $lowerProxy->max_proxy_price + $this->getMinIncrement(), $higherProxy->max_proxy_price );

            }

            //$this->generateNewBid($lowerProxy->user_id, $lowBid, $lowerProxy->id);
            $this->proxyGenerateNewBd($lowerProxy->user_id, $lowBid, $lowerProxy);
            //$this->generateNewBid($higherProxy->user_id, $highBid, $higherProxy->id);
            $this->proxyGenerateNewBd($higherProxy->user_id, $highBid, $higherProxy);

            $loser = JFactory::getUser($lowerProxy->user_id);
            $this->SendMails($loser, 'proxy_ended');
        }
    }

    function proxyGenerateNewBd($userid,$amount,$proxy) {

        $a = $this->get('auction');

        JTheFactoryEventsHelper::triggerEvent('onBeforeProxyBids',array($a,$proxy));
        $this->generateNewBid($userid, $amount, $proxy->id);
        JTheFactoryEventsHelper::triggerEvent('onAfterProxyBids',array($a,$proxy));
    }

    function getUserBids($userid,$accepted=false) {

        $userbids = array();

        foreach($this->auction->bids as $b) {
            $condition = ($b->userid==$userid) && ($accepted ? $b->accept : !$b->accept );
            if($condition) {
                $userbids[] = $b;
            }
        }

        return $userbids;
    }

    function getUserProxy($userid) {

        $db = JFactory::getDBO();

        $db->setQuery('SELECT * FROM #__bid_proxy WHERE auction_id='.$db->Quote($this->auction->id).' AND user_id='.$db->Quote($userid) );

        return $db->loadObject();
    }

    function deleteProxy($userid) {

        $db = JFactory::getDBO();

        $db->setQuery('DELETE FROM #__bid_proxy WHERE auction_id='.$db->Quote($this->auction->id).' AND user_id='.$db->Quote($userid) );

        return $db->query();
    }

    function addWatch($auctionid) {

        $db = $this->getDBO();
        $user = JFactory::getUser();

        if (!$user->id || !$auctionid)
            return false;

        $db->setQuery('select count(1) from #__bid_watchlist where userid = '.$db->quote($user->id).' and auction_id = '.$db->quote($auctionid) );
        if ($db->loadResult()) {
            return false;
        }

        $watchlist = JTable::getInstance('bidwatchlist');
        $watchlist->userid = $user->id;
        $watchlist->auction_id = $auctionid;

        return $watchlist->store();
    }

    function delWatch($auctionid,$userid=null)
    {
        $db = $this->getDBO();
        $user = JFactory::getUser($userid);

        if (!$user->id || !$auctionid)
            return false;

        $db->setQuery('DELETE FROM #__bid_watchlist WHERE userid = '.$db->quote($user->id).' AND auction_id = '.$db->quote($auctionid) );
        $db->query();
        return true;

    }

    function reportAuction($auctionid,$message) {

        $db = $this->getDBO();
        $my = JFactory::getUser();

        // load the row from the db table
        if (!$this->load($auctionid)){
            JError::raiseNotice(100,JText::_('COM_BIDS_ERR_DOES_NOT_EXIST'));
            return false;
        }

        $db->setQuery('INSERT INTO #__bid_report_auctions (auction_id,userid,message,modified)
                        VALUES ('.$db->Quote($auctionid).','.$db->Quote($my->id).','.$db->Quote($message).',UTC_TIMESTAMP() )');
        $db->query();
        JTheFactoryEventsHelper::triggerEvent('onAuctionReported',array($this->auction,$message));
        return true;
    }

    function hitAuction() {

        $a = JTable::getInstance('auction');
        $a->bind($this->auction);

        if (!$a->id) {
            return;
        }
        $a->hit();

        $user= JFactory::getUser();
        if (!$user->id) {
            return;
        }

        $database = $this->getDBO();
        //set Messages read (if it's your auction)
        $database->setQuery("UPDATE #__bid_auctions SET newmessages=0 WHERE userid='{$user->id}' and id=".$a->id);
        $database->query();

        $database->setQuery("UPDATE #__bid_messages set wasread=1 where userid2='{$user->id}' and auction_id=".$a->id);
        $database->query();
    }

    function getAuctionMessages($auctionid)
    {
        $database = $this->getDBO();
        $query = "
    		SELECT m.*,u1.username as fromuser, u2.username as touser
    		FROM #__bid_messages as m
    		LEFT JOIN #__users u1 ON u1.id = m.userid1
            LEFT JOIN #__users u2 ON u2.id = m.userid2
			WHERE m.auction_id={$auctionid}
                AND published=1
		";
        $database->setQuery($query);
        return $database->loadObjectList();
    }

    protected function saveImages($fromId=0) {

        $cfg = BidsHelperTools::getConfig();

        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        $auction = $this->auction;
        $db = $this->getDBO();

        if( $fromId ) { //copy images from another auction

            $fromauction = JTable::getInstance('auction');
            $fromauction->load($fromId);

            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');

            $delete_pictures = JRequest::getVar('delete_pictures', array());

            $pictures = $fromauction->getPicturesList();
            $bidImage = JTable::getInstance('bidimage');
            for ($i = 0; $i < count($pictures); $i++) {
                $bidImage->id = null;
                $ext = substr($pictures[$i]->picture, strpos($pictures[$i]->picture, '.') + 1, strlen($pictures[$i]->picture));
                if ($this->isAllowedImage($ext) && !in_array($pictures[$i]->id, $delete_pictures)) {
                    if (file_exists(AUCTION_PICTURES_PATH.DS.$pictures[$i]->picture)) {

                        $bidImage->auction_id = $auction->id;
                        $bidImage->modified = gmdate('Y-m-d');
                        $bidImage->ordering = $pictures[$i]->ordering;
                        $bidImage->store();
                        $file_name = $auction->id.'_img_'.$bidImage->id.'.'.$ext;
                        $bidImage->picture 	= $file_name;
                        $bidImage->store();

                        JFile::copy(AUCTION_PICTURES_PATH.DS.$pictures[$i]->picture, AUCTION_PICTURES_PATH.DS.$bidImage->picture);
                        JFile::copy(AUCTION_PICTURES_PATH.DS."middle_" . $pictures[$i]->picture, AUCTION_PICTURES_PATH.DS."middle_" . $bidImage->picture);
                        JFile::copy(AUCTION_PICTURES_PATH.DS."resize_" . $pictures[$i]->picture, AUCTION_PICTURES_PATH.DS."resize_" . $bidImage->picture);
                    }
                }
            }

        } else {// save new images

            $files = $auction->_uploadedFiles;

            $delete_pictures = JRequest::getVar('delete_pictures', array());

            foreach ($delete_pictures as $dele_id) {

                $db->setQuery( 'SELECT picture FROM #__bid_pictures WHERE id='.$db->Quote($dele_id).' AND auction_id='.$db->Quote($auction->id) );
                $pic = $db->loadResult();

                $db->setQuery('DELETE FROM #__bid_pictures WHERE id='.$db->Quote($dele_id).' AND auction_id='.$db->Quote($auction->id) );
                $db->query();

                JFile::delete(AUCTION_PICTURES_PATH.DS.$pic);
                JFile::delete(AUCTION_PICTURES_PATH.DS."middle_".$pic);
                JFile::delete(AUCTION_PICTURES_PATH.DS."resize_".$pic);
            }

            $nrfiles = 0;
            $bidImage = JTable::getInstance('bidimage');

            $auction = $this->auction;

            if (!JFolder::exists(AUCTION_PICTURES_PATH)) {
                JFolder::create(AUCTION_PICTURES_PATH);
            }

            $db->setQuery("SELECT MAX(ordering) FROM #__bid_pictures WHERE auction_id=".$auction->id);
            $newOrdering = $db->loadResult() + 1;

            foreach ($files as $k => $file) {

                $bidImage->id = null;

                if (substr($k, 0, 7) != "picture")
                    continue;
                if (!is_uploaded_file(@$file['tmp_name']))
                    continue;
                if (!$cfg->bid_opt_resize_if_larger && filesize($file['tmp_name']) > $cfg->bid_opt_max_picture_size * 1024) {
                    JError::raiseNotice(100,$file['name'] . "- " . JText::_('COM_BIDS_ERR_IMAGESIZE_TOO_BIG'));
                    continue;
                }

                $fname = JFile::makeSafe($file['name']);
                $ext = JFile::getExt($fname);

                if (!$this->isAllowedImage($ext)) {
                    JError::raiseNotice(100,JText::_('COM_BIDS_ERR_NOT_ALLOWED_EXT') . ': ' . $file['name']);
                    continue;
                }

                if ($cfg->bid_opt_maxnr_images && $nrfiles >= $cfg->bid_opt_maxnr_images) {
                    JError::raiseNotice(100,JText::_('COM_BIDS_ERR_IMAGE_TOO_MANY') . ': ' . $file['name']);
                    continue;
                }

                $bidImage->auction_id = $auction->id;
                $bidImage->modified = gmdate('Y-m-d');
                $bidImage->ordering = $newOrdering++;
                $bidImage->store();
                $file_name = $auction->id.'_img_'.$bidImage->id.'.'.$ext;
                $bidImage->picture 	= $file_name;
                $bidImage->store();

                $nrfiles++;

                $path = AUCTION_PICTURES_PATH.DS.$file_name;
                if ($cfg->bid_opt_resize_if_larger && filesize($file['tmp_name']) > $cfg->bid_opt_max_picture_size * 1024) {
                    $res = resize_to_filesize($file['tmp_name'], $path, $cfg->bid_opt_max_picture_size * 1024);
                } else {
                    $res = JFile::upload($file['tmp_name'], $path);
                }

                if ($res) {
                    @chmod($path, 0755);
                    BidsHelperTools::resize_image($file_name, $cfg->bid_opt_thumb_width, $cfg->bid_opt_thumb_height, 'resize');
                    BidsHelperTools::resize_image($file_name, $cfg->bid_opt_medium_width, $cfg->bid_opt_medium_height, 'middle');
                } else {
                    JError::raiseNotice(100,$file['name'] . "- " . JText::_('COM_BIDS_ERR_UPLOAD_FAILED'));
                }
            }
        }
    }

    function load($id) {

        $my = JFactory::getUser();
        $db = JFactory::getDbo();
        $cfg = BidsHelperTools::getConfig();

        $this->auction = JTable::getInstance('auction');
        
        if($id) {
            $q = 'SELECT
                    `a`.*,

                    GROUP_CONCAT(DISTINCT `w`.`userid`) AS followerIds,

                    GROUP_CONCAT(DISTINCT `m`.`id`) AS unreadMessageIds,

                    GROUP_CONCAT(DISTINCT `t`.`id` ORDER BY tagname ASC) AS tagIds,
                    GROUP_CONCAT(DISTINCT `t`.`tagname` ORDER BY tagname ASC) AS tagNames,

                    GROUP_CONCAT(DISTINCT CONCAT(`rate`.`voter_id`,\'#\',`rate`.`user_rated_id`)) AS rates,

                    `c`.`title` AS categoryname

                    FROM `#__bid_auctions` AS a
                    LEFT JOIN `#__categories` AS c
                        ON `a`.`cat`=`c`.`id`
                    LEFT JOIN `#__bid_watchlist` AS w
                        ON `a`.`id`=`w`.`auction_id`
                    LEFT JOIN `#__bid_tags` AS t
                        ON `a`.`id`=`t`.`auction_id`
                    LEFT JOIN `#__bid_messages` AS m
                        ON `a`.`id`=`m`.`auction_id` AND `m`.`userid2`='.$my->id.'
                    LEFT JOIN `#__bid_rate` AS rate
                        ON `a`.`id`=`rate`.`auction_id`
                    WHERE
                        `a`.`id`='.$db->Quote($id).
                    ' GROUP BY `a`.`id`';
            $db->setQuery($q);

            if(!$a = $db->loadObject()) {
                return false;
            }
            $this->auction->bind($a);
            $this->auction->followerIds=$a->followerIds;
            $this->auction->unreadMessageIds=$a->unreadMessageIds;
            $this->auction->tagIds=$a->tagIds;
            $this->auction->tagNames=$a->tagNames;
            $this->auction->rates=$a->rates;
            $this->auction->categoryname=$a->categoryname;

            //extract bids list
            $q = 'SELECT
                    b.id AS bidId,
                    b.*,
                    u.username,
                    p.max_proxy_price,
                    IF(b.bid_price='.$db->Quote($this->auction->BIN_price).',\'bin\',\'bid\') AS bid_type
                  FROM #__bids AS b
                  LEFT JOIN #__bid_proxy AS p
                    ON `b`.`id_proxy`=`p`.`id` AND b.userid=p.user_id AND p.active=1
                  LEFT JOIN #__users AS u
                    ON b.userid=u.id
                  WHERE b.auction_id='.$this->auction->id.'
                  GROUP BY b.id
                  ORDER BY b.modified DESC';
            $db->setQuery($q);
            $this->auction->bids = $db->loadObjectList('bidId');

            $q = 'SELECT
                    b.id AS bidId,
                    b.*,
                    u.username,
                    p.max_proxy_price,
                    IF(b.bid_price=' . $db->Quote($this->auction->BIN_price) . ',\'bin\',\'bid\') AS bid_type
                  FROM #__bid_log AS b
                  LEFT JOIN #__bid_proxy AS p
                    ON `b`.`id_proxy`=`p`.`id` AND b.userid=p.user_id AND p.active=1
                  LEFT JOIN #__users AS u
                    ON b.userid=u.id
                  WHERE b.auction_id=' . $this->auction->id . '
                  GROUP BY b.id
                  ORDER BY b.modified DESC';
            $db->setQuery($q);
            $this->auction->bids_history = $db->loadObjectList('bidId');

            //load suggestions just for the owner
            if($cfg->bin_opt_price_suggestion && $my->id) {

                $db->setQuery("SELECT s.*, ss.userid AS repliedto, u.username, u.email
                                    FROM #__bid_suggestions AS s
                                    LEFT JOIN #__bid_suggestions AS ss
                                        ON s.parent_id=ss.id
                                    LEFT JOIN #__users AS u
                                        ON s.userid = u.id
                                    WHERE
                                        s.auction_id = ".$db->Quote($this->auction->id).
                                    ($this->ownsAuction($my->id) ? '' : ' AND s.userid='.$my->id.' OR ss.userid='.$my->id ).
                                    " ORDER BY status DESC ");

                $this->auction->suggestions = $db->loadObjectList();
            }

            //load shipping prices
            $db->setQuery('SELECT sp.*,sz.name
                            FROM #__bid_shipment_prices AS sp
                            LEFT JOIN #__bid_shipment_zones AS sz
                                ON sp.zone=sz.id
                            WHERE sp.auction='.$this->auction->id);
            $this->auction->shipmentPrices = $db->loadObjectList();

        }

        $this->formatBids();

        $this->formatParams();

        $this->formatTags();

        $this->formatRates();

        $this->formatPayment();

        return true;
    }

    protected function formatBids() {

        $a = $this->auction;

        //initialize ->highestBid
        $a->highestBid = null;
        //initialize ->wonBids
        $a->wonBids = array();

        $a->bidderIds = array();

        $a->winnerIds = array();

        if(!isset($a->bids)) {
            return;
        }

        $maxBidId = $maxBidPrice = 0;

        foreach($a->bids as $bid) {

            if($bid->bid_price > $maxBidPrice && !$bid->cancel) {
                $maxBidPrice = $bid->bid_price;
                $maxBidId = $bid->id;
            }

            if($bid->accept) {
                $a->wonBids[$bid->id] = $bid;
                $a->winnerIds[] = $bid->userid;
            }

            $a->bidderIds[] = $bid->userid;
        }

        $a->highestBid = $maxBidId ? clone $a->bids[$maxBidId] : null;

        //accepted - if the seller can accept any more bids on this auction
        if($a->automatic) {
            $a->accepted = $a->close_offer;
        } else {
            if( AUCTION_TYPE_BIN_ONLY == $a->auction_type) {
                $a->accepted = (count($a->winnerIds)==$a->nr_items);
            } else {
                $a->accepted = (count($a->winnerIds)>0);
            }
        }

    }

    protected function formatTags() {

        $a = $this->auction;

        $a->tagIds = empty($a->tagIds) ? array() : explode(',',$a->tagIds);
        $a->tagNames = empty($a->tagNames) ? array() : explode(',',$a->tagNames);
    }

    protected function formatParams() {

        jimport('joomla.html.parameter');
        $params = new JParameter($this->auction->params);
        $this->auction->params = $params->toArray();

    }

    protected function formatRates() {

        $a = $this->auction;
        $my = JFactory::getUser();

        $a->sellerHasToRate = $a->buyerHasToRate = false;

        if( $my->guest || !count($a->wonBids) ) {
            return;
        }

        $rates = empty($a->rates) ? array() : explode(',',$a->rates);

        $ratedBySeller = array();
        $buyerRaters = array();
        foreach($rates as $r) {
            list($rater,$rated) = explode('#',$r);
            if($a->userid==$rater) { //buyer rated by seller
                $ratedBySeller[] = $rated;
            }
            if($a->userid==$rated) { //seller rated by buyer
                $buyerRaters[] = $rater;
            }
        }

        if( $my->id==$a->userid ) { //i am seller
            $a->buyerHasToRate = false;
            $a->sellerHasToRate = (boolean) count( array_diff($a->winnerIds,$ratedBySeller) );
        } else if ( in_array($my->id,$a->winnerIds) ) { //i am buyer
            $a->sellerHasToRate = false;
            $a->buyerHasToRate = !in_array( $my->id, $buyerRaters );
        } else {
            return;
        }
    }

    protected function formatPayment() {

        $a = $this->auction;

        $a->payment = empty($a->payment) ? array() : explode(',',$a->payment);
    }

    function setCategory($categoryId) {
        $this->auction->cat = (int) $categoryId;
    }

    function saveToSession() {
        $session = JFactory::getSession();
        //var_dump($this->auction);exit;
        $session->set('temp_auction', serialize($this->auction) , APP_EXTENSION );
    }

    function loadAuctionFromSession() {
        $session = JFactory::getSession();
        if (!$session->has( 'temp_auction', APP_EXTENSION)) {
            return false;
        }

        $fooTag = JTable::getInstance('bidtag');
        $fooAuction = JTable::getInstance('auction'); //need this to avoid getting a "__PHP_Incomplete_Class"

		$c = $session->get("temp_auction", null , APP_EXTENSION );
		$c = unserialize($c);

        if (is_object($c)) {
            $fields=get_object_vars($c);

            foreach($fields as $k=>$v) {
                if( !$this->auction->id && in_array($k,array('published')) ) {
                    continue;
                }

                if($k[0]!=='_') {
                    $this->auction->$k=$c->$k;
                }
            }
        }
    }

    function clearSavedSession() {
        $session = JFactory::getSession();
        $session->clear('temp_auction', APP_EXTENSION);
    }

    function mayEditAuction($user,$task=null) {

        //new auction
        if(0==$this->auction->id) {
            return true;
        }

        //override for task REPUBLISH
        if(($this->auction->close_offer || $this->auction->close_by_admin) && $task!='republish') {
            return false;
        }

        return $user->id == $this->auction->userid;
    }

    function prepareRepublish() {

        $this->auction->oldid = $this->auction->id;
        $this->auction->id = null;
        $this->auction->published = 0;
        $this->auction->quantity = $this->auction->nr_items;
    }

    function loadUserDefaults() {

        $my = JFactory::getUser();
        $p = JTable::getInstance('bidusersettings');
        $p->load($my->id);

        if (!count($p->settings)) {
            return;
        }

        $auctionRecord = JTable::getInstance('auction');
        $paramNames = array_keys($auctionRecord->_defaultParams);
        if(isset($p->settings['payment_options'])) {
            $p->settings['payment'] = (array) $p->settings['payment_options'];
        }

        $endHour = $endMinute = '';
        //some user settings go into params, the others go into auction object
        foreach($p->settings as $k=>$setting) {
            if(in_array($k,$paramNames)) {
                $this->auction->params[$k] = $setting;
            } else {
                switch($k) {
                    case 'end_hour':
                        $endHour = $setting;
                        break;
                    case 'end_minute':
                        $endMinute = $setting;
                        break;
                    default:
                        $this->auction->$k = $setting;
                        break;
                }
            }
        }
        $this->auction->end_date = (trim($endHour) && trim($endMinute) ) ? ($endHour.':'.$endMinute) : '';
    }

    function getBidders() {

        $db = JFactory::getDbo();

        $q = "SELECT u.*
              FROM #__users AS u
              LEFT JOIN #__bids AS b
                ON b.userid=u.id
              WHERE
                b.auction_id=".$this->auction->id."
                AND b.cancel=0";
        $db->setQuery($q);

        return $db->loadObjectList();
    }

    function getFollowers($excludeIds=array()) {

        $db = JFactory::getDbo();

        $excludeIds = (array) $excludeIds;

        $where = array();
        $where[] = 'w.auction_id='.$this->auction->id;
        if(!empty($excludeIds)) {
            $where[] = ' u.id NOT IN ('.implode(',',$excludeIds).')';
        }
        $q = "SELECT u.*
                    FROM #__users AS u
                    LEFT JOIN #__bid_watchlist AS w
                        ON u.id=w.userid
                    WHERE ".implode(' AND ',$where) ;
        $db->setQuery($q);

        return $db->loadObjectList();
    }

    function getMinIncrement() {

        $cfg = BidsHelperTools::getConfig();

        $a = $this->auction;

        if($a->auction_type==AUCTION_TYPE_PRIVATE || $a->auction_type==AUCTION_TYPE_BIN_ONLY) {
            return 0;
        }

        $minIncrement = $a->min_increase;

        if(!$cfg->bid_opt_min_increase_select && $cfg->bid_opt_range_increment) {

            $currentPrice = $a->highestBid ? $a->highestBid->bid_price : $a->initial_price;

            //get increase corresponding to increment range
            $db = JFactory::getDbo();
            $db->setQuery('SELECT value FROM `#__bid_increment` WHERE '.$currentPrice.' BETWEEN min_bid AND max_bid ORDER BY min_bid DESC');
            if($res = $db->loadResult()) {
                $minIncrement = $res;
            }
        }

        return (float) $minIncrement;
    }

    function getMinAcceptedPrice() {

        $a = $this->auction;

        if($a->auction_type==AUCTION_TYPE_PRIVATE) {

            $my = JFactory::getUser();
            $myBids = $this->getUserBids($my->id,false);

            //if user already has a bid on this PRIVATE auction, he can't get lower
            return (float) (count($myBids) ? $myBids[0]->bid_price : $a->initial_price);

        } elseif ($a->auction_type==AUCTION_TYPE_BIN_ONLY) {
            return (float) $a->BIN_price;
        }

        if(!$a->highestBid) { //no bid => start with initial price
            return (float) $a->initial_price;
        }

        return (float) $a->highestBid->bid_price + $this->getMinIncrement();
    }

    function cancel() {

        $a = $this->get('auction');
        $a->close_offer = 1;
        $a->closed_date = gmdate('Y-m-d H:i:s');
        $a->published = 0;

        JTheFactoryEventsHelper::triggerEvent('onBeforeCancelAuction',array($this->auction));
        if (!$this->store(true)) {
            return false;
        }
        JTheFactoryEventsHelper::triggerEvent('onAfterCancelAuction',array($this->auction));
        //canceling ends here
        return true;
    }

    function close(JTableBid $bid) {

        $cfg = BidsHelperTools::getConfig();

        $a = $this->auction;

        JTheFactoryEventsHelper::triggerEvent('onBeforeAcceptBid',array($a,$bid));

        $winner = JTable::getInstance('user');
        if ( !$bid->userid || !$winner->load($bid->userid) || $winner->block) {
            JError::raiseWarning(0, JText::_('COM_BIDS_ERR_USER_DOES_NOT_EXIST'));
            return false;
        }

        $seller = JFactory::getUser($a->userid);

        @ignore_user_abort(true);

        //accept bid, close auction, send emails
        JTheFactoryEventsHelper::triggerEvent('onBeforeAcceptBid',array($a,$bid));

        $bid->accept = 1;
        $bid->store();

        //decrease auction's quantity
        $a->quantity -= $bid->quantity;
        $this->store(true);

        //possible scenarios for an auction that is about to close
        $closeAuction = (AUCTION_TYPE_BIN_ONLY!=$a->auction_type) //public or private
                || (AUCTION_TYPE_BIN_ONLY==$a->auction_type && $cfg->bid_opt_quantity_enabled && 0==$a->quantity) //bin only with quantity enabled
                || (AUCTION_TYPE_BIN_ONLY==$a->auction_type && !$cfg->bid_opt_quantity_enabled) //bin only with quantity disabled
        ;
        if( $closeAuction ) {
            $a->published = 1;
            $a->close_offer = 1;
            $a->closed_date = gmdate('Y-m-d H:i:s');
        }

        $this->store(true);
        if($closeAuction) {
            JTheFactoryEventsHelper::triggerEvent('onAfterCloseAuction',array($a,$bid));
        }

        JTheFactoryEventsHelper::triggerEvent('onAfterAcceptBid',array($a,$bid));

        //delete followers
        $db = $this->getDBO();
        $db->setQuery("DELETE FROM #__bid_watchlist WHERE auction_id=".$this->auction->id);
        $db->query();
    }

    function autoExtend() {

        $cfg = BidsHelperTools::getConfig();

        $endTime = BidsHelperDateTime::getTimeStamp($this->auction->end_date);
        $remainingTime = $endTime - time();

        $endingRange = $cfg->bid_opt_auto_extent_limit * BidsHelperDateTime::translateAutoextendPeriod($cfg->bid_opt_auto_extent_limit_type);

        if ( (0 < $remainingTime) && ($remainingTime < $endingRange) ) {

            $extendOffset = $cfg->bid_opt_auto_extent_offset * BidsHelperDateTime::translateAutoextendPeriod($cfg->bid_opt_auto_extent_offset_type);
            $this->auction->end_date = $endTime + $extendOffset;
            $this->auction->end_date = gmdate('Y-m-d H:i:s', $this->auction->end_date);
            $this->auction->extended_counter += 1;

            $this->store(true);
        }
    }

    //wrapper for SendMails method, which is defiend in JTableAuction class (FOR NOW!)
    function sendMails($users,$mayltype) {
        $a = JTable::getInstance('auction');
        $a->bind($this->auction);

        return $a->sendMails($users,$mayltype);
    }

    function store($quick=false) {

        $a = JTable::getInstance('auction');
        $a->bind($this->auction);

        return $a->store($quick);
    }

    function isWinner($userid) {

        $userAcceptedBids = $this->getUserBids($userid,true);

        return count($userAcceptedBids);
    }

    function ownsAuction($userid) {

        $app = JFactory::getApplication();
        if($app->isAdmin()) {
            return true;
        }

        return $this->auction->userid == $userid;
    }

    function getIAmFollower() {

        $my = JFactory::getUser();
        $followerIds = $this->auction->followerIds ? explode(',', $this->auction->followerIds) : array();

        return in_array($my->id,$followerIds);
    }

    function getNrNewMessages() {

        $unreadMessageIds = $this->auction->unreadMessageIds ? explode(',', $this->auction->unreadMessageIds) : array();

        return count($unreadMessageIds);
    }

    function getCategoryName() {
        return $this->auction->categoryname;
    }

    function isAllowedImage($ext) {

        return in_array(strtoupper($ext),$this->allowedPictureExt);
    }

    function getNrFieldsWithFilters() {

        $database = $this->getDBO();
        $database->setQuery("select count(*) from #__bid_fields where page='auctions' and categoryfilter=1");
        return $database->loadResult();
    }

    function getForm($data = array(), $loadData = true) {

    }
}
