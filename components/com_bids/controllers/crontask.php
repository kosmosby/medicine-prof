<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 3.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/06/2012
 * @package: Bids
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JBidsControllerCronTask extends JController
{
	var $_name='cronTask';
	var $name='cronTask';
	function __construct($config=array())
    {
        $config['default_task']='cron';
        parent::__construct($config);
    }

    function cron()
    {
        /* cron script Pass and authentication*/
        $cfg=BidsHelperTools::getConfig();
        $config =JFactory::getConfig();
        $pass=JRequest::getVar('pass');
        $debug= JRequest::getVar('debug',0);

        $date=new JDate();
        $nowMysql=$date->toMySQL(false);

        $database = JFactory::getDbo();

        JTheFactoryHelper::modelIncludePath('payments');
        JTheFactoryHelper::tableIncludePath('payments');

        $log= JTable::getInstance('bidcronlog');
        $log->priority='log';
        $log->event='cron';
        $log->logtime=$nowMysql;
        $logtext="";

        if ($cfg->bid_opt_cron_password!==$pass) {
            //Bad Password, log and exit;
            $log->log=JText::_("COM_BIDS_BAD_PASSWORD_USED")." > $pass";
            $log->store();
            die(JText::_("COM_BIDS_ACCESS_DENIED"));
        }
    	@set_time_limit(0);
    	@ignore_user_abort(true);



        // reminder for due to expire suggestions
        $database->setQuery(" SELECT s.auction_id,a.userid
                                FROM #__bid_suggestions AS s
                                LEFT JOIN #__bid_auctions AS a
                                    ON s.auction_id = a.id
                                WHERE
                                    s.status=2
                                    AND UTC_TIMESTAMP() > DATE_ADD( s.modified, INTERVAL 24 HOUR)
                                    AND a.published=1
                                    AND a.close_offer=0
                                    AND a.close_by_admin=0
                                    AND UTC_TIMESTAMP() < a.end_date
                                GROUP BY s.auction_id");
        $curs = $database->loadObjectList();
        
        if(count($curs)>0) {
            $auction =  JTable::getInstance('auction');
            $userIds = array();
        
            foreach($curs as $k=>$val) {
                $userIds[] = $val->userid;
            }
            $database->setQuery('SELECT u.* FROM #__users AS u WHERE u.id IN ( '.implode(',',$userIds).' )');
            $mails = $database->loadObjectList();
            $auction->SendMails($mails,'suggest_reminder'); //Notyfy Sellers abour pending suggestions
        }
        
        // reject expired suggestions
        $database->setQuery(" SELECT a.id AS suggestionId,a.auction_id,b.userid
                                FROM #__bid_suggestions AS a
                                LEFT JOIN #__bid_auctions AS b
                                    ON a.auction_id = b.id
        			WHERE
                                    UTC_TIMESTAMP() > DATE_ADD( a.modified, INTERVAL 48 HOUR)
                                    AND a.status=2");
        $curs = $database->loadObjectList();
        
        if(count($curs)>0) {
            $auction =  JTable::getInstance('auction');
            $userIds = array();
            $suggestionIds = array();
        
            foreach($curs as $k=>$val) {
                $suggestionIds[] = $val->suggestionId;
                $userIds[] = $val->userid;
            }
        
            $database->setQuery('UPDATE #__bid_suggestions SET status=0 WHERE id IN ('.implode(',',$suggestionIds).')');
            $database->query();// reject the suggestions
        
            $database->setQuery('SELECT u.* FROM #__users AS u WHERE u.id IN ( '.implode(',',$userIds).' )');
            $mails = $database->loadObjectList();
            $auction->SendMails($mails,'suggest_rejected'); //Notyfy Bidders abour rejected suggestion
        }
        
        //CLOSE auctions
        $query = "SELECT
                        a.*,
                        a.id as auctionId,
                        GROUP_CONCAT(b.userid) AS bidderIds,
                        GROUP_CONCAT(w.userid) AS watcherIds
                    FROM #__bid_auctions AS a
                    LEFT JOIN #__bids AS b
                        ON a.id=b.auction_id
                    LEFT JOIN #__bid_watchlist AS w
                        ON a.id=w.auction_id
                    WHERE
                        UTC_TIMESTAMP() >= a.end_date
                        AND a.close_offer = 0
                        AND a.published = 1
                        AND a.close_by_admin=0
                    GROUP BY a.id";
        $database->setQuery($query);
        $rows = $database->loadObjectList('auctionId');


        $extendedAuctions = array();
        if($cfg->bid_opt_auto_extent && $cfg->bid_opt_auto_extent_nobids) {
            //these auctions have no bids and will be extended
            foreach($rows as $id=>$r) {
                if(!$r->bidderIds) {
                    $extendedAuctions[] = $id;
                }
            }
        }

        if(count($rows)>0) {
            $auction =  JTable::getInstance('auction');

            //exclude auctions that are going to be extended
            $closeAuctions = array_diff(array_keys($rows),$extendedAuctions);
            if(count($closeAuctions)) {
                $database->setQuery('UPDATE
                                        #__bid_auctions
                                     SET
                                        close_offer=1,
                                        closed_date=UTC_TIMESTAMP()
                                     WHERE
                                        id IN ('.implode(',',  $closeAuctions).')');
                $database->query();
            }
        
            foreach ($rows as $r) {
                //Notify bidders
                $auction->bind($r);
        
                //extend auctions with no bids
                if($cfg->bid_opt_auto_extent && $cfg->bid_opt_auto_extent_nobids>0 && !$r->bidderIds) {
                    $extendedPeriodSeconds = $cfg->bid_opt_auto_extent_nobids * BidsHelperDateTime::translateAutoextendPeriod($cfg->bid_opt_auto_extent_nobids_type);
                    $endTime = BidsHelperDateTime::getTimeStamp($auction->end_date);
                    $newEndTime = $endTime + $extendedPeriodSeconds;

                    $auction->end_date = gmdate('Y-m-d H:i:s',$newEndTime);
                    $auction->store();

                    continue;
                }
        
                jimport('joomla.application.component.model');
                JModelLegacy::addIncludePath(JPATH_COMPONENT_SITE.DS.'models');
                $auctionmodel = JModelLegacy::getInstance('auction','bidsModel');
                $auctionmodel->load($r->auctionId);
        
                $seller = JTable::getInstance('user');
                $seller->load($r->userid);
        
                if($r->automatic) {
        
                    $a = $auctionmodel->get('auction');
                    $highestBid = $a->highestBid;
        
                    $nrHighestBids = 1;
                    if( AUCTION_TYPE_PRIVATE == $a->auction_type ) {
                        if($highestBid) {
                            $query = 'SELECT COUNT(1) AS nrHighestBids
                                        FROM #__bids
                                        WHERE
                                            auction_id='.$r->auctionId.'
                                            AND bid_price='.$highestBid->bid_price;
                            $database->setQuery($query);
                            $nrHighestBids = $database->loadResult();
                        }
                        else {
                            $nrHighestBids = 0;
                        }
                    }
        
                    if($highestBid) {
                        if ($nrHighestBids>1) {
                            //mark auction as NOT automatic and alert owner that 2 or more bids are the same
                            $database->setQuery('UPDATE #__bid_auctions SET automatic=0 WHERE id='.$a->id);
                            $database->query();
        
                            $auctionmodel->SendMails(array($seller),'bid_choose_winner');
                        } else {
                            if ($cfg->bid_opt_global_enable_reserve_price && $a->reserve_price>0 && $a->reserve_price>=$highestBid->bid_price) {
                                //reserve price not met!
                                $auctionmodel->SendMails(array($seller),'bid_reserve_not_met');//reserve price not met
                            } else {
                                $bid = JTable::getInstance('bid');
                                $bid->bind($highestBid);
                                $auctionmodel->close($bid);
                            }
                        }
                    } else {
                        $auctionmodel->SendMails(array($seller),'bid_offer_no_winner_to_owner');
                    }
        
                } else {
                    // Notify owner to choose winner OR that his auction has no winner
                    $auctionmodel->SendMails(array($seller), $r->bidderIds ? 'bid_choose_winner' : 'bid_offer_no_winner_to_owner');
                }
        
                if($r->bidderIds) {
                    $database->setQuery('SELECT u.* FROM #__users AS u WHERE u.id IN ('.$r->bidderIds.')');
                    $mails = $database->loadObjectList();
                    $auctionmodel->SendMails($mails,'bid_closed'); //Notyfy Bidders abour closed Auction
        
                    $database->setQuery('DELETE FROM #__bid_watchlist WHERE auction_id='.$r->id.' AND userid IN ('.$r->bidderIds.')');
                    $database->query();//delete bidders from watchlist, to avoid double notification
                }
                //Notify  Watchlist, Clean Watchlist
                if($r->watcherIds) {
                    $database->setQuery('SELECT u.* FROM #__users AS u WHERE u.id IN ('.$r->watcherIds.')');
                    $mails = $database->loadObjectList();
                    $auctionmodel->SendMails($mails,'bid_watchlist_closed'); //Notify Watchlist
        
                    $database->setQuery('DELETE FROM #__bid_watchlist WHERE auction_id='.$r->id);
                    $database->query();
                }
            }
        }
        
        // END EXPIRED AUCTION
    
    
        //Daily Jobs (things that should run once a day )
        $daily= JRequest::getVar('daily','');
        if ($daily){
            //Notify upcoming expirations
        	$query = "SELECT a.* from #__bid_auctions AS a
        		 	  WHERE UTC_TIMESTAMP() >= DATE_ADD(end_date,INTERVAL -1 DAY) AND a.close_offer != 1 AND published = 1 AND a.close_by_admin!=1";
        	$database->setQuery($query);
        	$rows = $database->loadObjectList();
    
        	$auction =  JTable::getInstance('auction');
  	        
            $logtext.=sprintf("Soon to expire: %d auctions\r\n",count($rows));
    
            foreach ($rows as $row){
                    $auction->load($row->id);
                    $usr=JFactory::getUser($row->userid);
                    $auction->SendMails(array($usr),'bid_your_will_expire'); // Notify Owner that his auction will soon expire
    
                    $query = "SELECT u.* FROM #__users u
                            left join #__bid_watchlist w on u.id = w.userid
                            where w.auction_id = ".$row->id;
    
                    $database->setQuery($query);
                    $watchlist_mails = $database->loadObjectList();
                    $auction->SendMails($watchlist_mails,'bid_watchlist_will_expire'); //Notify Users in watchlist that an auction will expire
            }
    
        	//Close all auctions without a parent user (deleted users?)s
        	$query = "UPDATE
                        #__bid_auctions AS a
                        LEFT JOIN #__users AS b
                            ON a.userid=b.id
                        SET
                            close_by_admin=1,
                            closed_date=UTC_TIMESTAMP()
                        WHERE b.id IS NULL";
        	$database->setQuery($query);
        	$database->query();
            //delete Very old auctions (past Archived time)
        	$interval =  intval($bidCfg->bid_opt_archive);
            $d_opt = ( $bidCfg->bid_opt_archive_type!='') ? $bidCfg->bid_opt_archive_type : 'month';
            $d_opt_sql = strtoupper($d_opt);
        	if ($interval>0){
            	$query = "SELECT id
                                FROM #__bid_auctions
                                WHERE UTC_TIMESTAMP() > DATE_ADD( closed_date, INTERVAL $interval
                                {$d_opt_sql} )
                                AND (close_offer =1
                                or  close_by_admin=1)";
                $database->setQuery($query);
                $idx = $database->loadResultArray(); //select auctions that have to be purged
    
    
                $row =  JTable::getInstance('auction');
                if (count($idx)) {
                    foreach ($idx as $id){
                        $row->delete($id);
                    }
                }
        	}

            $model= JModelLegacy::getInstance('Currency','JTheFactoryModel');
            $currtable= JTable::getInstance('CurrencyTable','JTheFactory');
    
            $currencies=$model->getCurrencyList();
            $default_currency=$model->getDefault();
            $results=array();
            foreach($currencies as $currency){
                if ($currency->name==$default_currency){
                    $currtable->load($currency->id);
                    $currtable->convert=1;
                    $currtable->store();
                    $results[]=$currency->name." ---> ".$default_currency." = 1";
                    continue;
                }
                $conversion=$model->getGoogleCurrency($currency->name,$default_currency);
                if ($conversion===false){
                    $results[]=JText::_("COM_BIDS_ERROR_CONVERTING")." {$currency->name} --> $default_currency";
                    continue;
                }
                $currtable->load($currency->id);
                $currtable->convert=$conversion;
                $currtable->store();
                $results[]=$currency->name." ---> ".$default_currency." = $conversion ";
            }
            $logtext.=implode("\r\n",$results);
            $logtext.="\r\n";
            //some cleanup
        	
        }
        $log->log=$logtext;
        $log->store();
        if ($debug) return;
        ob_clean();
        exit();
        

    }
}
