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


defined('_JEXEC') or die('Restricted access.');

jimport('joomla.application.component.controller');

class JBidsController extends JControllerLegacy {

    var $_name = 'bids';
    var $name = 'bids';

    function __construct($config = array()) {

        parent::__construct($config);
        $this->registerTask('sendbid', 'saveBid' );
        $this->registerTask('sendbidajax', 'saveBidAjax' );

        $this->registerTask('showsearchresults', 'listauctions' );
        $this->registerTask('mybids', 'listauctions' );
        $this->registerTask('mywonbids', 'listauctions' );
        $this->registerTask('mywatchlist', 'listauctions' );
        $this->registerTask('myauctions', 'listauctions' );
        $this->registerTask('tags', 'listauctions' );

        $this->registerTask('suggestions', 'listsuggestions' );
        $this->registerTask('mysuggestions', 'listsuggestions' );

        $this->registerTask('myratings', 'userratings' );

        $this->registerTask('form', 'editauction' );
        $this->registerTask('newauction', 'editauction' );
        $this->registerTask('republish', 'editauction' );

        $this->registerTask('sendbid', 'saveBid' );
        $this->registerTask('bin', 'saveBid' );

        $this->registerTask('show_profilemodal','editProfile');
        $this->registerTask('registerForm','editProfile');

        $this->registerTask('tree','listcats');

        $this->registerDefaultTask('listauctions');
    }

    function selectcat() {

        $model = $this->getModel('bidscategory');
        $model->loadCategoryTree(1,null,null,true);

        $view = $this->getView('selectcategory', 'html');
        $view->setModel($model,1);
        $view->display('t_choose_category.tpl');
    }

    function editAuction() {

        if( 'newauction'==$this->getTask() && !$this->checkTCAgreed()) {
            $this->setRedirect(JRoute::_('index.php?option=com_bids&task=userdetails', 0), JText::_('COM_BIDS_PLEASE_AGREE_TC') );
            return;
        }

        $id = JRequest::getInt('id');
        $cfg = BidsHelperTools::getConfig();

        $modelAuction = $this->getModel('auction');
        $modelAuction->load($id);

        $user = JFactory::getUser();
        if(!$modelAuction->mayEditAuction($user,$this->getTask())) {
            JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION') );
            return false;
        }

        if(!$id && $cfg->bid_opt_allow_user_settings) {
            $modelAuction->loadUserDefaults();
        }
        $modelAuction->loadAuctionFromSession();

        $category_selected = JRequest::getInt('category_selected', 0);

        if(!$category_selected ) {
            if (('catpage' == $cfg->bid_opt_category_page) && !$id) {
                $tfCatModel = BidsHelperTools::getCategoryModel();
                $nrCats = $tfCatModel->getCategoryCount();
                if ($nrCats) {
                    $this->selectcat();
                    return;
                }
            }
        } else {
            $modelAuction->setCategory($category_selected);
        }


        JTheFactoryEventsHelper::triggerEvent('onBeforeEditAuction',array($modelAuction->get('auction')));

        //this has to be done right BEFORE display, AFTER everything else is processed, because $auction->id becomes 0 after this point
        if('republish'==$this->getTask()) {
            $modelAuction->prepareRepublish();
        }

        $view = $this->getView('auctionedit', 'html');
        $view->setModel($modelAuction);

        $view->display('t_editauction.tpl');
    }

    function cancelEdit() {

        $id = JRequest::getInt('id', 0);
        $oldid = JRequest::getInt('oldid', 0);

        $id = max($id,$oldid);

        $auctionModel = $this->getModel('auction');
        $auctionModel->clearSavedSession();

        $url = $id ? 'index.php?option=com_bids&task=viewbids&id='.$id : 'index.php?option=com_bids&task=listauctions';

        $this->setRedirect( JRoute::_($url,false) );
    }

    function saveAuction() {

        $my = JFactory::getUser();
        $cfg = BidsHelperTools::getConfig();

        $id = JRequest::getInt('id', 0);
        $oldid = JRequest::getInt('oldid', 0);
        $isRepost = $oldid && !$id;

        $auctionmodel = $this->getModel('auction');
        $auctionmodel->bind( JRequest::get('post',JREQUEST_ALLOWHTML), JRequest::get('files') );

        $auction = $auctionmodel->get('auction');

        $extra = '';
        if ($isRepost) {
            $redirectTask = 'republish';
        } elseif ($id) {
            $redirectTask = 'editauction';
            $extra = '&id='.$id;
        } else {
            $redirectTask = 'newauction';
            $extra = ('catpage'==$cfg->bid_opt_category_page) ? '&category_selected='.$auction->cat : '';
        }
        $redirectURL = JRoute::_('index.php?option=com_bids&task='.$redirectTask.$extra,false);

        if(!$auctionmodel->mayEditAuction($my)) {
            JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION') );
            return false;
        }

        JTheFactoryEventsHelper::triggerEvent('onBeforeSaveAuction',array($auction));
        if (!$auctionmodel->save() ) {
            JTheFactoryEventsHelper::triggerEvent('onAfterSaveAuctionError',array($auction));
            $auctionmodel->saveToSession();//for refilling fields on edit
            $this->setRedirect($redirectURL);
            return;
        }
        JTheFactoryEventsHelper::triggerEvent('onAfterSaveAuctionSuccess',array($auction));

        $auctionmodel->clearSavedSession();

        if ($cfg->bid_opt_allow_user_settings && JRequest::getInt('save_user_settings', 0)) {
            $usermodel = $this->getModel('user');
            $auction->payment_options = @$auction->payment; //dirty trick needed for saving default settings
            $usermodel->saveDefaultAuctionSettings($auction);
        }

        $this->setRedirect( JHtml::_('auctiondetails.auctionDetailsURL',$auction,false), JText::_('COM_BIDS_SUCCES'));
    }

    function publish() {

        $my = JFactory::getUser();

        $id = JRequest::getInt('id', 0);

        $redirectURL = JRoute::_('index.php?option=com_bids&task=myauctions', false);

        $auction = JTable::getInstance('auction');
        $auction->load($id);

        if ($my->id != $auction->userid) {
            JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION'));
            $this->setRedirect( $redirectURL );
            return;
        }

        $auction->published = 1;
        JTheFactoryEventsHelper::triggerEvent('onBeforeSaveAuction',array($auction));
        $auction->store($id);
        JTheFactoryEventsHelper::triggerEvent('onAfterSaveAuctionSuccess',array($auction));

        $this->setRedirect( $redirectURL );
    }

    function cancelauction() {

        $my = JFactory::getUser();
        $id = JRequest::getInt('id');
        $auctionmodel = $this->getModel('auction');

        if(!$auctionmodel->load($id)) {
            JError::raiseNotice(100,JText::_('COM_BIDS_ERR_DOES_NOT_EXIST'));
            return false;
        }

        if (!$auctionmodel->ownsAuction($my->id)) {
            return false;
        }

        $auction = $auctionmodel->get('auction');
        JTheFactoryEventsHelper::triggerEvent('onBeforeCancelAuction',array($auction));
        if ($auctionmodel->cancel()) {
            $msg = JText::_('COM_BIDS_AUCTION_WAS_CANCELED');
            JTheFactoryEventsHelper::triggerEvent('onAfterCancelAuction',array($auction));
        } else {
            $msg = JText::_('COM_BIDS_ERR_AUCTION_WAS_CANCELED_FAILED');
        }

        $this->setRedirect( JHtml::_('auctiondetails.auctionDetailsURL',$auction, false), $msg );
    }

    function report_auction() {

        $id = JRequest::getInt('id');

        $auctionmodel = $this->getModel('auction');
        $auctionmodel->load($id);

        $view = $this->getView('reportauction', 'html');
        $view->setModel($auctionmodel);
        $view->display('t_report_auction.tpl');
    }

    function sendReportAuction() {

        $id = JRequest::getInt('id');
        $message = JRequest::getString('message', '');

        $auctionmodel = $this->getModel('auction');
        $auctionmodel->reportAuction($id, $message);

        $auction = JTable::getInstance('auction');
        $auction->load($id);

        $this->setRedirect( JHtml::_('auctiondetails.auctionDetailsURL',$auction, false), JText::_('COM_BIDS_AUCTION_REPORTED') );
    }

    function viewbids() {

        $my = JFactory::getUser();
        $database = JFactory::getDBO();

        $id = JRequest::getInt('id');

        $redirectURL = JRoute::_('index.php?option=com_bids', false);

        $auctionmodel = $this->getModel('auction');
        if (!$auctionmodel->load($id)) {
            $this->setRedirect( $redirectURL, JText::_('COM_BIDS_ERR_DOES_NOT_EXIST')) ;
            return;
        }
        $auction = $auctionmodel->get('auction');
        if (!$auction->published) {
            if ($my->guest || !$auctionmodel->ownsAuction($my->id)) {
                $this->setRedirect( $redirectURL, JText::_('COM_BIDS_ERR_DOES_NOT_EXIST') );
                return;
            }
        } else {
            if( time() < BidsHelperDateTime::getTimeStamp($auction->start_date) && !$auctionmodel->ownsAuction($my->id) ) {
                $this->setRedirect( $redirectURL, JText::_('COM_BIDS_ERR_DOES_NOT_EXIST') );
                return;
            }
        }

        if($auction->close_by_admin) {
            if ($my->guest || !$auctionmodel->ownsAuction($my->id)) {
                $this->setRedirect( $redirectURL, JText::_('COM_BIDS_ERR_DOES_NOT_EXIST'));
                return;
            } else {
                //raise warning for seller
                JError::raiseWarning(1,JText::_('COM_BIDS_ERR_AUCTION_BANNED'));
            }
        }

        $auctionmodel->hitAuction($id);

        BidsHelperAuction::renderAuctionTiming($auction);

        BidsHelperAuction::setAuctionStatus($auction);


//////////// EX-renderAuctionData

        // [+] Rates
        $database->setQuery("SELECT
                AVG(rating) AS all_rates,
                IF(rate_type='auctioneer',AVG(rating),0) AS auctioneer_rates,
                IF(rate_type='bidder',AVG(rating),0) AS bidder_rates
        FROM #__bid_rate
        WHERE user_rated_id=".$auction->userid);

        $rates = $database->loadAssocList();
        if (isset($rates[0]))
            $rates = $rates[0];
        $auction->rating_auctioneer = $rates["auctioneer_rates"];
        $auction->rating_bidder = $rates["bidder_rates"];
        $auction->rating_overall = $rates["all_rates"];

        // [-] Rates

        $view = $this->getView('auction', 'html');
        $view->setModel($auctionmodel,true);

        $view->display('t_auctiondetails.tpl');
    }

    function listcats() {

        $catId = JRequest::getInt('cat', 1);
        $task = $this->getTask();

        $user = JFactory::getUser();

        $depth = null;
        switch($task) {
            case 'listcats':
                $depth = 2;
                $layout = 't_categories.tpl';
                $filter_letter = JRequest::getString('filter_letter', 'all');
                break;
            case 'tree':
                $depth = null;
                $layout = 't_category_tree.tpl';
                $filter_letter = 'all';
                break;
        }

        $model = $this->getModel('bidscategory');
        $model->loadCategoryTree($catId,$depth,$user->id,true,$filter_letter);

        $treeCat = $model->get('categories');
        if(!count($treeCat)) { //no subcategories
            return $this->listauctions();
        }

        $view = $this->getView('categories', 'html');
        $view->setModel($model);

        $view->display($layout);
    }

    function listauctions() {

        $task = strtolower($this->getTask());

        $model = $this->getModel('auctions');
        switch($task) {
            case 'mybids':
            case 'mywonbids':
            case 'mywatchlist':
            case 'myauctions':
                $model->setState('behavior',$task);
                $model->setContext($task);
                $layout = 't_'.$task.'.tpl';
                break;
            case 'showsearchresults':
            default:
                $model->setState('behavior','listauctions');
                $model->setContext('listauctions');
                $layout = 't_listauctions.tpl';
                break;
        }

        $reset = JRequest::getVar('reset', '', 'default', 'string');
        if($reset) {
            $model->setState('filters.reset',$reset);
        }
        $model->setFilters();

        $model->loadAuctions();

        $view = $this->getView('auctions', 'html');
        $view->setModel($model);
        $view->display($layout);
    }

    function listsuggestions() {

        $task = strtolower($this->getTask());

        $model = $this->getModel('suggestions');

        switch($task) {
           case 'suggestions':
               $layout = 't_suggestions.tpl';
               break;
           case 'mysuggestions':
               $model->setState('filters.mysuggestions',1);
               $layout = 't_mysuggestions.tpl';
               break;
        }

        $model->setFilters();
        $model->loadSuggestions();

        $view = $this->getView('suggestions', 'html');
        $view->setModel($model);
        $view->display($layout);
    }

    function search() {

        $model = $this->getModel('auctions');
        $model->setContext('listauctions');
        $model->setFilters();

        $view = $this->getView('searchauctions', 'html');
        $view->setModel($model);
        $view->display();
    }

    function watchlist() {

        $auctionid = (int) JRequest::getVar('id', null);
        $auctionmodel = $this->getModel('auction');
        $auctionmodel->addWatch($auctionid);

        $auction = JTable::getInstance('auction');
        $auction->load($auctionid);

        $this->setRedirect( JHtml::_('auctiondetails.auctionDetailsURL',$auction, false), JText::_('COM_BIDS_ADDED_TO_WATCHLIST'));
    }

    function delwatch() {

        $auctionid = (int) JRequest::getVar('id', null);
        $auctionmodel = $this->getModel('auction');
        $auctionmodel->delWatch($auctionid);

        $auction = JTable::getInstance('auction');
        $auction->load($auctionid);

        $this->setRedirect( JHtml::_('auctiondetails.auctionDetailsURL',$auction, false), JText::_('COM_BIDS_DEL_FROM_WATCHLIST'));
    }

    function addwatchcat() {

        $app = JFactory::getApplication();

        $catid = JRequest::getInt('cat', 1);

        $catmodel = $this->getModel('bidscategory');
        if($catmodel->addWatch($catid)) {
            $app->enqueueMessage( JText::_("COM_BIDS_ADDED_TO_WACHLIST"), 'message' );
        } else {
            $app->enqueueMessage( JText::_("COM_BIDS_ERROR_ADDING_TO_WACHLIST"), 'notice' );
        }

        $parent = $catmodel->getParent($catid);
        $redirectLink = empty($_SERVER['HTTP_REFERER']) ? 'index.php?option=com_bids&task=tree'.($parent->id ? '&cat='.$parent->id : '') : $_SERVER['HTTP_REFERER'];

        $this->setRedirect( JRoute::_($redirectLink, false) );
    }

    function delwatchcat() {

        $app = JFactory::getApplication();

        $catid = JRequest::getInt('cat', 0);

        $catmodel = $this->getModel('bidscategory');
        if($catmodel->delWatch($catid)) {
            $app->enqueueMessage( JText::_("COM_BIDS_REMOVED_FROM_WACHLIST"), 'message' );
        } else {
            $app->enqueueMessage( JText::_("COM_BIDS_ERROR_REMOVING_FROM_WACHLIST"), 'notice' );
        }

        $parent = $catmodel->getParent($catid);
        $redirectLink = empty($_SERVER['HTTP_REFERER']) ? 'index.php?option=com_bids&task=tree'.($parent->id ? '&cat='.$parent->id : '') : $_SERVER['HTTP_REFERER'];

        $this->setRedirect( JRoute::_($redirectLink, false) );
    }

    function userdetails() {

        $my = JFactory::getUser();
        $userid = JRequest::getInt('id', $my->id );

        $r = BidsHelperTools::redirectToProfile($userid);
        if ($r) {
            $this->setRedirect(JRoute::_($r, false) );
            return;
        }

        $model = $this->getModel('user');
        if(!$model->loadUser($userid)) {
            JError::raiseNotice("701",JText::_("COM_BIDS_YOU_NEED_TO_LOGIN_IN_ORDER_TO_ACCESS_THIS_SECTION"));
            $this->setRedirect('index.php?option=com_bids');
            return;
        }

        $view = $this->getView('profile', 'html');
        $view->setModel($model,1);

        $view->display( $my->id==$userid ? 't_myuserdetails.tpl' : 't_userdetails.tpl');
    }

    function editProfile() {

        $my = JFactory::getUser();
        $r = BidsHelperTools::redirectToProfile();
        if ($r && $my->id) {
            $this->setRedirect( JRoute::_($r, false) );
            return;
        }

        $model = $this->getModel('user');
        $view = $this->getView('editprofile', 'html');
        $view->setModel($model,1);

        $view->display('t_editprofile.tpl');
    }

    function saveUserDetails() {

        $model = $this->getModel('user');
        if ($model->saveUserDetails()){
            $return = JRequest::getVar("return", "");
            if($return) {
                $this->setRedirect(base64_decode($return), JText::_("COM_BIDS_DETAILS_SAVED"));
            } else {
                $this->setRedirect( JRoute::_('index.php?option=com_bids&task=userdetails', false),JText::_("COM_BIDS_DETAILS_SAVED"));
            }
        } else {
            $this->setRedirect( JRoute::_('index.php?option=com_bids&task=userdetails', false), JText::_("COM_BIDS_DETAILS_NOT_SAVED"));
        }
    }

    function userratings() {

        $my = JFactory::getUser();

        $input = JFactory::getApplication()->input;
        $userid = $input->getInt('userid', $my->id);

        $user = JFactory::getUser($userid);

        $model = $this->getModel('ratings');
        $model->loadUserRatings($user->id);

        $view = $this->getView('userratings', 'html');
        $view->setModel($model);
        $view->assign('user', $user);

        $view->display('t_userratings.tpl');
    }

    function searchUsers() {

        $view = $this->getView('searchusers', 'html');
        $view->display();
    }

    function showUsers() {

        $model = $this->getModel('users');

        $reset = JRequest::getVar('reset', '', 'default', 'string');
        $model->setState('filters.reset',$reset);
        $model->setFilters();
        $model->loadUsers();

        $view = $this->getView('users', 'html');
        $view->setModel($model);
        $view->display('t_showusers.tpl');
    }

    function terms_and_conditions() {
        $cfg=BidsHelperTools::getConfig();
        echo $cfg->terms_and_conditions;
    }

    function saveMessage() {

        $my = JFactory::getUser();
        $cfg = BidsHelperTools::getConfig();

        $auctionid = JRequest::getInt("id");
        $comment = JRequest::getString('message', '');
        $id_msg = JRequest::getInt('idmsg', 0);
        $bidder_id= JRequest::getInt('bidder_id', null);

        $auctionmodel = $this->getModel('auction');

        if (!$auctionmodel->load($auctionid)) {
            $this->setRedirect( 'index/php?option=com_bids', JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }

        $auction = $auctionmodel->get('auction');

        $redirect_link = JHtml::_('auctiondetails.auctionDetailsURL',$auction, false) . '#messages';

        if (!$cfg->bid_opt_allow_messages) {
            $this->setRedirect($redirect_link, JText::_('COM_BIDS_MESSAGING_WAS_DISALLOWED'));
            return;
        }

        if (!$my->id && $cfg->bid_opt_enable_captcha) {
            // if code not good halt
            if (!BidsHelperHtml::verify_captcha()) {
                $this->setRedirect($redirect_link, JText::_('COM_BIDS_MESSAGE_ERROR_CAPTCHA'));
                return;
            }
        }

        //only seller and buyer can send messages
        if ($auction->close_offer) {
            if ( ($auction->userid!=$my->id) && !in_array($my->id,$auction->winnerIds) ) {
                //allow messages between Winner and auctioneer
                $this->setRedirect($redirect_link, JText::_('COM_BIDS_AUCTION_IS_CLOSED'));
                return;
            }
        }

        if ($auction->published != 1) {
            $this->setRedirect($redirect_link, JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }

        $comment = str_replace("\n", '<br>', $comment);
        $auction->sendNewMessage($comment, $id_msg, $bidder_id);

        $this->setRedirect($redirect_link, JText::_('COM_BIDS_MESSAGE_SUCCESS'));
    }

    function bulkimport() {

        $view = $this->getView('bulkimport', 'html');
        $view->assign('errors', null);
        $view->display();
    }

    function importcsv() {
        $err = BidsHelperTools::ImportFromCSV(0);
        if (count($err) <= 0)
            $this->setRedirect( JRoute::_('index.php?option=com_bids&task=myauctions', false), JText::_('COM_BIDS_AUCTIONS_IMPORTED'));
        else {
            $view = $this->getView('bulkimport', 'html');
            $view->assign('errors', $err);
            $view->display();
        }
    }

    function rss() {

        $database = JFactory::getDBO();
        $cfg = BidsHelperTools::getConfig();

        ob_end_clean();
        $feed = $cfg->bid_opt_RSS_feedtype;
        $cat = JRequest::getInt('cat', '');
        $user = JRequest::getInt('user', '');

        $limit = $cfg->bid_opt_RSS_description ? intval($cfg->bid_opt_RSS_nritems) : intval($cfg->bid_opt_nr_items_per_page);
        if (!$limit)
            $limit = 10;

        require_once (JPATH_COMPONENT_SITE.DS.'libraries'.DS.'feedcreator'.DS.'feedcreator.php');

        $rss = new UniversalFeedCreator();

        $rss->title = $cfg->bid_opt_RSS_title;
        $rss->description = $cfg->bid_opt_RSS_description;
        $rss->link = htmlspecialchars(JURI::root());
        $rss->syndicationURL = htmlspecialchars(JURI::root());
        $rss->cssStyleSheet = null;
        $rss->encoding = 'UTF-8';

        $where = " where a.published=1 and a.close_offer=0 and a.close_by_admin=0 ";

        if ($cat) {
            if (!$cfg->bid_opt_inner_categories) {
                $where .= " and a.cat = '" . $cat . "' ";
            } else {
                $catOb = BidsHelperTools::getCategoryModel();
                $cTree = $catOb->getCategoryTree($cat,true);

                $cat_ids = array();
                if ($cTree) {
                    foreach ($cTree as $cc) {
                        if (!empty($cc->id)) {
                            $cat_ids[] = $cc->id;
                        }
                    }
                }
                if (count($cat_ids))
                    $cat_ids = implode(",", $cat_ids);
                $where .= " and ( a.cat  IN (" . $cat_ids . ") )";
            }
        }

        if ($user) {
            $where .= " AND userid=".$database->quote($user);
        }

        $database->setQuery("select a.* from #__bid_auctions a left join #__categories c on c.id=a.cat ".$where." order by id desc", 0, $limit);
        $rows = $database->loadObjectList();

        for ($i = 0; $i < count($rows); $i++) {

            $auction = $rows[$i];

            $item_link = JHtml::_('auctiondetails.auctionDetailsURL',$auction, false);

            // removes all formating from the intro text for the description text
            $item_description = $auction->description;
            $item_description = JFilterOutput::cleanText($item_description);
            $item_description = html_entity_decode($item_description);

            // load individual item creator class
            $item = new FeedItem();
            // item info
            $item->title = $auction->title;
            $item->link = $item_link;
            $item->description = $item_description;
            $item->source = $rss->link;
            $item->date = date('r', strtotime($auction->start_date));
            $database->setQuery( "select title from #__categories where id=".$database->quote($auction->cat) );
            $item->category = $database->loadResult();

            $rss->addItem($item);
        }

        echo $rss->createFeed($feed);
    }

    function rate() {

        $my = JFactory::getUser();

        $auction_id = JRequest::getInt('id', 0);
        $rating = JRequest::getInt('rate', 0);
        $user_rated_id = JRequest::getInt('user_rated_id', 0);
        $note = JRequest::getString('comment', '');

        $auctionmodel = $this->getModel('auction');
        $auctionmodel->load($auction_id);

        $a = $auctionmodel->get('auction');

        $redirectUrl = JHtml::_('auctiondetails.auctionDetailsURL', $a, false).'#bid_list';

        if (!$rating) { //must have a rating value
            $this->setRedirect( $redirectUrl, JText::_('COM_BIDS_MUST_RATE'));
            return;
        }

        if (
            ($a->userid != $my->id && !in_array($my->id,$a->winnerIds)) // I am not seller, not buyer
            || ($a->userid != $user_rated_id && !in_array($user_rated_id,$a->winnerIds)) // user being rated is not seller, not buyer
        ) {
            $this->setRedirect($redirectUrl, JText::_('COM_BIDS_RATE_NOT_ALLOW'));
            return;
        }

        if($a->userid != $my->id) {
            $user_rated_id = $a->userid; //if I am not the seller, the rated user is
        }

        $ratingsmodel = $this->getModel('ratings');
        if (!$ratingsmodel->canRate($a->id,$my->id,$user_rated_id) ) {
            $this->setRedirect($redirectUrl, JText::_('COM_BIDS_RATE_ALREADY'));
            return;
        }

        $rate = JTable::getInstance('bidrate');
        $rate->voter_id = $my->id;
        $rate->user_rated_id = $user_rated_id;
        $rate->rating = $rating;
        $rate->modified = gmdate('Y-m-d H:i:s');
        $rate->review = $note;
        $rate->auction_id = $a->id;
        $rate->rate_type = ($a->userid == $my->id) ? 'auctioneer' : 'bidder';
        $rate->store();

        JTheFactoryEventsHelper::triggerEvent('onUserRating',array($rate));

        $this->setRedirect($redirectUrl, JText::_('COM_BIDS_RATE_SUCCES'));
    }

    function accept() {

        $cfg = BidsHelperTools::getConfig();
        $my = JFactory::getUser();

        $bid_id = JRequest::getInt('bid', 0);

        $auctionmodel = $this->getModel('auction');

        $bid = JTable::getInstance('bid');
        if (!$bid->load($bid_id) || $bid->cancel || $bid->bid_price <= 0) {
            JError::raiseWarning(0, JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }
        if ($bid->accept) {
            JError::raiseWarning(0, JText::_('COM_BIDS_ALREADY_ACCEPTED'));
            return;
        }

        if (!$auctionmodel->load($bid->auction_id)) {
            JError::raiseWarning(0, JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }
        $auction = $auctionmodel->get('auction');

        if ($auction->published != 1) {
            JError::raiseWarning(0, JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }

        if ($my->id != $auction->userid) {
            JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION'));
            return;
        }

        if ($auction->automatic) {
            JError::raiseWarning(0, JText::_('COM_BIDS_ERR_IS_AUTOMATIC'));
            return;
        }
        if( $auction->auction_type==AUCTION_TYPE_BIN_ONLY && $cfg->bid_opt_quantity_enabled && ($auction->quantity < $bid->quantity) ) {
            JError::raiseWarning(0, JText::_('COM_BIDS_ERR_QUANTITY_LEFT'));
            return;
        }
        if($auction->accepted) {
            JError::raiseWarning(0, JText::_('COM_BIDS_ERR_NO_MORE_BIDS_TO_ACCEPT'));
            return;
        }
        if(!$cfg->bid_opt_manual_accept_before_end) {
            JError::raiseWarning(0, JText::_('COM_BIDS_ERR_NO_ACCEPT_BEFORE_AUCTION_END'));
            return;
        }

        $auctionmodel->close($bid);

        $this->setRedirect( JHtml::_('auctiondetails.auctionDetailsURL', $auction, false) );
    }

    function saveBid() {

        if (!$this->checkTCAgreed()) {
            $this->setRedirect(JRoute::_('index.php?option=com_bids&task=userdetails', 0), JText::_('COM_BIDS_PLEASE_AGREE_TC') );
            return;
        }

        $app = JFactory::getApplication();
        $my = JFactory::getUser();
        $cfg=BidsHelperTools::getConfig();

        $task = strtolower($this->getTask());

        $auction_id = JRequest::getInt('id', -1);
        $proxy = JRequest::getInt('proxy', 0);

        $amount = JRequest::getVar('amount', 0);
        if (substr($amount,0,1)=='.') {
            $amount = (float) '0'.$amount; //american style prices like .95 instead of 0.95
        }

        $quantity_param = JRequest::getInt('quantity', 1);

        $auctionmodel = $this->getModel('auction');
        if ( !$auctionmodel->load($auction_id) ) {
            $this->setRedirect('index.php?option=com_bids', JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }

        $auction = $auctionmodel->get('auction');

        $redirect_link = JHtml::_('auctiondetails.auctionDetailsURL', $auction, false);

        if ($auction->userid == $my->id) {
            $this->setRedirect($redirect_link, JText::_('COM_BIDS_ERR_NO_SELF_BIDDING'));
            return;
        }
        if ($auction->close_offer || $auction->close_by_admin || (BidsHelperDateTime::getTimeStamp($auction->end_date) <= time()) ) {
            $this->setRedirect($redirect_link, JText::_('COM_BIDS_AUCTION_IS_CLOSED'));
            return;
        }
        if ( $auction->published != 1 || BidsHelperDateTime::getTimeStamp($auction->start_date) > time() ) {
            $this->setRedirect($redirect_link, JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }
        if ( 'bin'==$task  ) {

            if($auction->BIN_price <=0) {
                $this->setRedirect($redirect_link, JText::_('COM_BIDS_NO_BIN'));
                return;
            } else {
                $amount = max($amount, $auction->BIN_price);
            }

        }
        if(AUCTION_TYPE_BIN_ONLY==$auction->auction_type) {

            if('bin'!=$task) {
                $this->setRedirect($redirect_link, JText::_('COM_BIDS_ERR_BIN_ONLY'));
                return;
            }

            if($cfg->bid_opt_quantity_enabled && $auction->quantity<$quantity_param) {
                $this->setRedirect($redirect_link, JText::_('COM_BIDS_ERR_QUANTITY_LEFT'));
                return;
            }
        }
        //HERE ENDS ALL SECURITY CHECKS

        //TODO:i think bids greater than BIN price should not be considered BIN
        if
        (
            AUCTION_TYPE_PRIVATE!=$auction->auction_type
            && ( 'bin'==$task
                 || (!$proxy && $auction->BIN_price>0 && $amount>=$auction->BIN_price)
               )
        )
        { // BIN bids

            //for BIN, take amount from field BIN_price
            $newBid = $auctionmodel->bid($my->id,$amount,0,$quantity_param);

            if ( $auction->automatic || $auction->params['auto_accept_bin'] ) {
                $auctionmodel->close($newBid);
            }

            $this->setRedirect($redirect_link);
            return;
        }

        $acceptedPrice = $auctionmodel->getMinAcceptedPrice();
        if( $amount < $acceptedPrice ) {

            switch($auction->auction_type) {
                case AUCTION_TYPE_PUBLIC:
                    $app->enqueueMessage(JText::_('COM_BIDS_ERR_PRICE_MAXBID'));//on public auctions I can't bid lower than min accepted price
                    break;
                case AUCTION_TYPE_PRIVATE:
                    $app->enqueueMessage(JText::_('COM_BIDS_ERR_PRICE_MYBID'));//on private auctions I can't bid lower second time
                    break;
            }
            $this->setRedirect($redirect_link);
            return;
        }

        //reserve price message
        if ($cfg->bid_opt_global_enable_reserve_price && $auction->reserve_price > 0 && $auction->params['show_reserve']) {
            $app->enqueueMessage( JText::_( $amount<$auction->reserve_price ? 'COM_BIDS_RESERVE_NOT_MET' : 'COM_BIDS_RESERVE_MET') );
        }

        if(AUCTION_TYPE_PUBLIC==$auction->auction_type && $cfg->bid_opt_allow_proxy) {

            if($proxy) {
                $auctionmodel->proxyBid($my->id, $amount);//bidding ends here
                $this->setRedirect($redirect_link);
                return;
            } else {
                $auctionmodel->deleteProxy($my->id);
            }
        }

        $newBid = $auctionmodel->bid($my->id,$amount,0,$quantity_param);

        $app->enqueueMessage(JText::_('COM_BIDS_SUCCES'));

        $this->setRedirect( $redirect_link );
    }

    function saveBidAjax() {
        if (!$this->checkTCAgreed()) {
            echo "{\"success\":\"0\",\"message\":\"".JText::_('COM_BIDS_PLEASE_AGREE_TC')."\"}";
            exit;
        }

        $app = JFactory::getApplication();
        $my = JFactory::getUser();
        $cfg=BidsHelperTools::getConfig();

        $task = strtolower($this->getTask());

        $auction_id = JRequest::getInt('id', -1);
        $proxy = JRequest::getInt('proxy', 0);

        $amount = JRequest::getVar('amount', 0);
        if (substr($amount,0,1)=='.') {
            $amount = (float) '0'.$amount; //american style prices like .95 instead of 0.95
        }

        $quantity_param = JRequest::getInt('quantity', 1);
        $auctionmodel = $this->getModel('auction');
        if ( !$auctionmodel->load($auction_id) ) {
            echo "{\"success\":\"0\",\"message\":\"".JText::_('COM_BIDS_DOES_NOT_EXIST')."\"}";
            exit;
        }

        $auction = $auctionmodel->get('auction');

        $redirect_link = JHtml::_('auctiondetails.auctionDetailsURL', $auction, false);

        if ($auction->userid == $my->id) {
            echo "{\"success\":\"0\",\"message\":\"".JText::_('COM_BIDS_ERR_NO_SELF_BIDDING')."\"}";
            exit;
        }
        if ($auction->close_offer || $auction->close_by_admin || (BidsHelperDateTime::getTimeStamp($auction->end_date) <= time()) ) {
            echo "{\"success\":\"0\",\"message\":\"".JText::_('COM_BIDS_AUCTION_IS_CLOSED')."\"}";
            exit;
        }
        if ( $auction->published != 1 || BidsHelperDateTime::getTimeStamp($auction->start_date) > time() ) {
            echo "{\"success\":\"0\",\"message\":\"".JText::_('COM_BIDS_DOES_NOT_EXIST')."\"}";
            exit;
        }
        if ( 'bin'==$task  ) {

            if($auction->BIN_price <=0) {
                echo "{\"success\":\"0\",\"message\":\"".JText::_('COM_BIDS_NO_BIN')."\"}";
                exit;
            } else {
                $amount = max($amount, $auction->BIN_price);
            }

        }
        if(AUCTION_TYPE_BIN_ONLY==$auction->auction_type) {

            if('bin'!=$task) {
                echo "{\"success\":\"0\",\"message\":\"".JText::_('COM_BIDS_ERR_BIN_ONLY')."\"}";
                exit;
            }

            if($cfg->bid_opt_quantity_enabled && $auction->quantity<$quantity_param) {
                echo "{\"success\":\"0\",\"message\":\"".JText::_('COM_BIDS_ERR_QUANTITY_LEFT')."\"}";
                exit;
            }
        }
        //HERE ENDS ALL SECURITY CHECKS

        //TODO:i think bids greater than BIN price should not be considered BIN
        if
        (
            AUCTION_TYPE_PRIVATE!=$auction->auction_type
            && ( 'bin'==$task
                || (!$proxy && $auction->BIN_price>0 && $amount>=$auction->BIN_price)
            )
        )
        { // BIN bids

            //for BIN, take amount from field BIN_price
            $newBid = $auctionmodel->bid($my->id,$amount,0,$quantity_param);

            if ( $auction->automatic || $auction->params['auto_accept_bin'] ) {
                $auctionmodel->close($newBid);
            }

            //$this->setRedirect($redirect_link);
            //return;
            echo "{\"success\":\"1\",\"bid\":\"{$newBid->bid_price}\"}";
            exit;
        }

        $acceptedPrice = $auctionmodel->getMinAcceptedPrice();
        if( $amount < $acceptedPrice ) {

            switch($auction->auction_type) {
                case AUCTION_TYPE_PUBLIC:
                    //$app->enqueueMessage(JText::_('COM_BIDS_ERR_PRICE_MAXBID'));//on public auctions I can't bid lower than min accepted price
                    echo "{\"success\":\"0\",\"message\":\"".JText::_('COM_BIDS_ERR_PRICE_MAXBID')."\"}";
                    exit;
                    break;
                case AUCTION_TYPE_PRIVATE:
                    //$app->enqueueMessage(JText::_('COM_BIDS_ERR_PRICE_MYBID'));//on private auctions I can't bid lower second time
                    echo "{\"success\":\"0\",\"message\":\"".JText::_('COM_BIDS_ERR_PRICE_MYBID')."\"}";
                    exit;
                    break;
            }
            //$this->setRedirect($redirect_link);
            return;
        }

        //reserve price message
        if ($cfg->bid_opt_global_enable_reserve_price && $auction->reserve_price > 0 && $auction->params['show_reserve']) {
            $app->enqueueMessage( JText::_( $amount<$auction->reserve_price ? 'COM_BIDS_RESERVE_NOT_MET' : 'COM_BIDS_RESERVE_MET') );
        }

        if(AUCTION_TYPE_PUBLIC==$auction->auction_type && $cfg->bid_opt_allow_proxy) {

            if($proxy) {
                $auctionmodel->proxyBid($my->id, $amount);//bidding ends here
                $this->setRedirect($redirect_link);
                return;
            } else {
                $auctionmodel->deleteProxy($my->id);
            }
        }

        $newBid = $auctionmodel->bid($my->id,$amount,0,$quantity_param);

        echo "{\"success\":\"1\",\"bid\":\"{$newBid->bid_price}\"}";
        exit;

        //$app->enqueueMessage(JText::_('COM_BIDS_SUCCES'));

        //$this->setRedirect( $redirect_link );
    }

    function suggest() {

        $database = JFactory::getDBO();
        $my = JFactory::getUser();
        $cfg = BidsHelperTools::getConfig();

        $auction_id = JRequest::getInt('id', 0);
        $suggest_id = JRequest::getInt('s_id', 0);
        $suggest = JRequest::getFloat('bid_suggest', 0);
        $q = JRequest::getInt('quantity', 1);

        $bidsSuggest = JTable::getInstance('bidsuggestion');

        $auctionmodel = $this->getModel('auction');
        if (!$auctionmodel->load($auction_id)) {
            $this->setRedirect('index.php?option=com_bids', JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }
        $auction = $auctionmodel->get('auction');

        $redirect_link = JHtml::_('auctiondetails.auctionDetailsURL', $auction, false);

        if (!$suggest_id && $auction->userid == $my->id) {
            $this->setRedirect($redirect_link, JText::_('COM_BIDS_ERR_NO_SELF_BIDDING'));
            return;
        }

        if (!$cfg->bin_opt_price_suggestion
                || $auction->auction_type != AUCTION_TYPE_BIN_ONLY
                || !$auction->params["price_suggest"]) {
            $this->setRedirect($redirect_link, JText::_('COM_BIDS_ERR_NO_PRICE_SUGGEST'));
            return;
        }
        if ($suggest<=0) {
            $this->setRedirect($redirect_link, JText::_('COM_BIDS_ERR_VALID_SUGGEST'));
            return;
        }

        $database->setQuery('SELECT COUNT(1) FROM #__bid_suggestions WHERE userid = '.$database->quote($my->id).' AND auction_id='.$database->quote($auction_id));
        $nrSuggestions = $database->loadResult();

        if ($cfg->bin_opt_limit_suggestions <= $nrSuggestions) {
            $this->setRedirect($redirect_link, JText::_('COM_BIDS_ERR_PRICE_SUGGESTED'));
            return;
        }

        $bidsSuggest->auction_id = $auction_id;
        $bidsSuggest->userid = $my->id;
        $bidsSuggest->modified = gmdate('Y-m-d H:i');
        $bidsSuggest->bid_price = $suggest;
        $bidsSuggest->parent_id = $suggest_id;
        $bidsSuggest->quantity = $q;

        if ($auction->params["price_suggest_min"] <= $suggest) {
            $bidsSuggest->status = 2;
            $msg = JText::_('COM_BIDS_MSG_PRICE_SUGGESTED');
        } else {
            // suggested price must be higher than the lowest suggestion allowed
            $bidsSuggest->status = 0;
            $msg = JText::_('COM_BIDS_ERR_LOW_PRICE_SUGGESTED');
        }
        JTheFactoryEventsHelper::triggerEvent('onBeforeSaveSuggestion',array($auction,$bidsSuggest));
        $bidsSuggest->store();
        JTheFactoryEventsHelper::triggerEvent('onAfterSaveSuggestion',array($auction,$bidsSuggest));
        if ($bidsSuggest->status===0){
            JTheFactoryEventsHelper::triggerEvent('onRejectSuggestion',array($auction,$bidsSuggest));            
        }
        $this->setRedirect($redirect_link, $msg);
    }

    function rejectSuggestion() {

        $my = JFactory::getUser();
        $id = (int) JRequest::getVar("id");
        // suggestion id
        $bid_id = $id;
        $auction = JTable::getInstance('auction');

        $suggestion = JTable::getInstance('bidsuggestion');

        if (!$suggestion->load($bid_id)) {
            echo JText::_('COM_BIDS_DOES_NOT_EXIST');
            return;
        }
        if ($suggestion->bid_price <= 0) {
            echo JText::_('COM_BIDS_DOES_NOT_EXIST');
            return;
        }
        if (!$auction->load($suggestion->auction_id)) {
            echo JText::_('COM_BIDS_DOES_NOT_EXIST');
            return;
        }

        if ($auction->close_offer) {
            echo JText::_('COM_BIDS_AUCTION_IS_CLOSED');
            return;
        }
        if ($auction->published != 1) {
            echo JText::_('COM_BIDS_DOES_NOT_EXIST');
            return;
        }
        if ($auction->automatic == 1) {
            echo JText::_('COM_BIDS_ERR_IS_AUTOMATIC');
            return;
        }

        // Check if I am the Suggestee
        if ($suggestion->parent_id > 0) {

            $bid_reply = JTable::getInstance('bidsuggestion');

            if (!$bid_reply->load($suggestion->parent_id)) {
                JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION'));
                return;
            } else {
                if ($bid_reply->userid != $my->id) {
                    JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION'));
                    return;
                }
            }
        } else {
            if ($auction->userid != $my->id) {
                JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION'));
                return;
            }
        }

        $suggestion->status = 0;
        $suggestion->store();

        JTheFactoryEventsHelper::triggerEvent('onRejectSuggestion',array($auction,$suggestion));

        $this->setRedirect( JHtml::_('auctiondetails.auctionDetailsURL', $auction, false) );
    }

    function acceptSuggestion() {

        $my = JFactory::getUser();
        $id = JRequest::getInt('id');

        $auctionmodel = $this->getModel('auction');

        $suggestion = JTable::getInstance('bidsuggestion');

        if (!$suggestion->load($id)) {
            JError::raiseWarning(0, JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }
        if ($suggestion->bid_price <= 0) {
            JError::raiseWarning(0, JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }
        if (!$auctionmodel->load($suggestion->auction_id)) {
            JError::raiseWarning(0, JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }

        $auction = $auctionmodel->get('auction');

        if ($suggestion->parent_id > 0) {
            $suggestion_reply = JTable::getInstance('bidsuggestion');

            if (!$suggestion_reply->load($suggestion->parent_id)) {
                JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION'));
                return;
            } else {
                if ($suggestion_reply->userid != $my->id) {
                    JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION'));
                    return;
                }
            }
        } else {
            if ($auction->userid != $my->id) {
                JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION'));
                return;
            }
        }

        if(AUCTION_TYPE_BIN_ONLY!=$auction->auction_type) {
            JError::raiseWarning(0, JText::_('COM_BIDS_NOT_ALLOW_AUCTION'));
            return;
        }

        if ($auction->published != 1) {
            JError::raiseWarning(0, JText::_('COM_BIDS_DOES_NOT_EXIST'));
            return;
        }
        if ($auction->automatic == 1) {
            JError::raiseWarning(0, JText::_('COM_BIDS_ERR_IS_AUTOMATIC'));
            return;
        }
        if($auction->quantity < $suggestion->quantity) {
            JError::raiseWarning(0, JText::_('COM_BIDS_ERR_QUANTITY_LEFT'));
            return;
        }
        if($auction->accepted) {
            JError::raiseWarning(0, JText::_('COM_BIDS_ERR_NO_MORE_BIDS_TO_ACCEPT'));
            return;
        }

        $bid = $auctionmodel->bid($suggestion->userid,$suggestion->bid_price,0,$suggestion->quantity);

        //delete the suggestion (all its info has been saved in bid)
        $suggestion->delete();

        JTheFactoryEventsHelper::triggerEvent('onBeforeAcceptSuggestion',array($auction,$bid));
        $auctionmodel->close($bid);
        JTheFactoryEventsHelper::triggerEvent('onAfterAcceptSuggestion',array($auction,$bid));

        $this->setRedirect( JHtml::_('auctiondetails.auctionDetailsURL', $auction, false) );
    }

    function canceluser() {
        $this->setRedirect( JRoute::_('index.php?option=com_bids', false) );
    }

    function saveDefaultAuctionSettings() {

        $cfg = BidsHelperTools::getConfig();

        if(!$cfg->bid_opt_allow_user_settings) {
            $this->setRedirect('index.php');
            return;
        }

        $model = $this->getModel('user');
        $model->saveDefaultAuctionSettings(JRequest::get());

        $this->setRedirect( JRoute::_('index.php?option='.APP_EXTENSION.'&task=userdetails', false), JText::_("COM_BIDS_DETAILS_SAVED") );
    }

    function RefreshCategory()
    {
        $cfg = BidsHelperTools::getConfig();

        $id = JRequest::getInt('id', 0);
        $oldid = JRequest::getInt('oldid', 0);
        $isRepost = $oldid && !$id;

        $auctionmodel = $this->getModel('auction');
        $auctionmodel->bind( JRequest::get('post',JREQUEST_ALLOWHTML), JRequest::get('files') );
        $auctionmodel->saveToSession();

        $auction = $auctionmodel->get('auction');

        $extra = '';
        if ($isRepost) {
            $redirectTask = 'republish';
        } elseif ($id) {
            $redirectTask = 'editauction';
            $extra = '&id='.$id;
        } else {
            $redirectTask = 'newauction';
            $extra = ('catpage'==$cfg->bid_opt_category_page) ? '&category_selected='.$auction->cat : '';
        }
        $redirectURL = JRoute::_('index.php?option=com_bids&task='.$redirectTask.$extra,false);

        $this->setRedirect($redirectURL);
    }

    function redirect() {

        if (JDEBUG && $this->redirect) {
            $app = JFactory::getApplication();
            if($this->message) {
                $app->enqueueMessage( $this->message, $this->messageType);
            }
            echo '<div style="color: #f00;">Debug mode ON. Click the link for redirect: </div>'.JHTML::link($this->redirect,$this->redirect);
        } else {
            return parent::redirect();
        }
    }

    function agreeTC() {

        $redirectUrl = JRoute::_('index.php?option=com_bids&task=userdetails', 0);

        $cfg = BidsHelperTools::getConfig();
        if('component'!=$cfg->bid_opt_profile_mode) {
            $this->setRedirect($redirectUrl);
            return;
        }

        $app = JFactory::getApplication();
        $input = $app->input;

        $agreed = $input->get('agreetc');

        if(!$agreed) {
            $this->setRedirect($redirectUrl);
            return;
        }

        $my = JFactory::getUser();
        $db = JFactory::getDbo();
        $db->setQuery("UPDATE #__bid_users SET agree_tc=1 WHERE userid=".$my->id);
        $db->query();

        $this->setRedirect( $redirectUrl );
    }

    protected function checkTCAgreed() {

        $cfg = BidsHelperTools::getConfig();
        if ('component' != $cfg->bid_opt_profile_mode) {
            return true;
        }

        $my = JFactory::getUser();
        $db = JFactory::getDbo();
        $db->setQuery("SELECT agree_tc FROM #__bid_users WHERE userid=" . $my->id);

        return $db->loadResult();
    }
}
