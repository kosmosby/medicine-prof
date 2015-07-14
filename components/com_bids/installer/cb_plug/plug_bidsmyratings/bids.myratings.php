<?php

/**
 * @package AuctionsFactory
 * @version 1.7.0
 * @copyright www.thefactory.ro
 * @license: commercial
 */
// no direct access
defined('_JEXEC') or die('Restricted access!');

JLoader::register('bidsCbTabHandler', JPATH_SITE . DS . 'components' . DS . 'com_bids' . DS . 'helpers' . DS . 'cb' . DS . 'plgtabhandler.php');

define(ITEMS_PER_PAGE, '5');

class getmyratingsTab extends bidsCbTabHandler {

    function getmyratingsTab() {
        $this->cbTabHandler();
    }

    function getDisplayTab($tab, $user, $ui) {

        $database = & JFactory::getDBO();
        $my = &JFactory::getUser();

        $params = $this->params;

        JHtml::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'htmlelements');

        $cfg = new BidConfig();

// total ratings
        $query = "select count(*) from #__bid_rate r
			  left join #__users us on r.voter_id = us.id
			  left join #__bid_auctions a on r.auction_id = a.id
		      where r.user_rated_id = ".$user->user_id;
        $database->setQuery($query);
        $total = $database->loadResult();

        $limitstart = 0;
        $pagingParams = $this->_getPaging(array(), array("myrates_"));
        if (isset($pagingParams["myrates_limitstart"])) {
            $limitstart = $pagingParams["myrates_limitstart"];
        }

// my ratings
        $query = "select r.*,us.username,a.title
              from #__bid_rate r
			  left join #__users us on r.voter_id = us.id
			  left join #__bid_auctions a on r.auction_id = a.id
		      where r.user_rated_id = ".$user->user_id;
        $database->setQuery($query, $limitstart, ITEMS_PER_PAGE);

        $pagingParams["limitstart"] = $limitstart;
        $pagingParams["limit"] = ITEMS_PER_PAGE;
        $myratings = $database->loadObjectList();

// all ratings
        $query = "select sum(rating) from #__bid_rate r
			  left join #__users us on r.voter_id = us.id
			  left join #__bid_auctions a on r.auction_id = a.id
		      where r.user_rated_id = ".$user->user_id;
        $database->setQuery($query);
        $all_ratings = $database->loadResult();

// seller ratings
        $query = "select avg(rating) from #__bid_rate r
			  left join #__users us on r.voter_id = us.id
			  left join #__bid_auctions a on r.auction_id = a.id
		      where r.user_rated_id = ".$user->user_id." and rate_type='auctioneer' ";
        $database->setQuery($query);
        $seller_ratings = intval($database->loadResult());

// buyer ratings
        $query = "select avg(rating) from #__bid_rate r
			  left join #__users us on r.voter_id = us.id
			  left join #__bid_auctions a on r.auction_id = a.id
		      where r.user_rated_id = ".$user->user_id." and rate_type!='auctioneer' ";
        $database->setQuery($query);

        $buyer_ratings = intval($database->loadResult());

        $rating = ($total > 0) ? intval($all_ratings / $total) : 0;


        $return = "<style type='text/css'>
						#auction_star{height:12px;margin:0px;padding:0px;}
					</style>";



        $return .="<span class='auction_my_rating'>" . JText::_('COM_BIDS_OVERALL_RATING') . ": <span class='rating_user' rating='" . $rating . "'></span>&nbsp;($rating/10)</span><br>";
        $return .="<span class='auction_my_rating'>" . JText::_('COM_BIDS_RATING_AUCTIONEER') . ": <span class='rating_user' rating='" . $seller_ratings . "'></span>&nbsp;($seller_ratings/10)</span><br>";
        $return .="<span class='auction_my_rating'>" . JText::_('COM_BIDS_RATING_BIDDER') . ": <span class='rating_user' rating='" . $buyer_ratings . "'></span>&nbsp;($buyer_ratings/10)</span>";
        $return .='<form name="topForm' . $tab->tabid . '" action="index.php" method="post">';
        $return .="<table width='100%'>";
        $return .="<input type='hidden' name='option' value='com_comprofiler' />";
        $return .="<input type='hidden' name='task' value='userProfile' />";
        $return .="<input type='hidden' name='user' value='" . $user->user_id . "' />";
        $return .="<input type='hidden' name='tab' value='" . $tab->tabid . "' />";
        $return .="<input type='hidden' name='act' value='' />";

        if ($myratings) {
            $return .= '<tr>';
            $return .='<td colspan=3><hr></td>';
            $return .= '</tr>';
            $k = 0;

            foreach ($myratings as $mr) {
                $link_view_details = JRoute::_("index.php?option=com_bids&task=userdetails&id=".$mr->voter_id);
                $link_view_bids = JRoute::_('index.php?option=com_bids&task=viewbids&id='.$mr->auction_id.':'.$mr->title);
                $utype = ($mr->rate_type == 'auctioneer') ? JText::_('COM_BIDS_BUYER') : JText::_('COM_BIDS_SELLER');


                $return .="<tr class='myrating" . ($k) . "'>";
                $return .="<td colspan=3>";
                $return .="<a href='" . $link_view_bids . "'>$mr->title</a>";
                $return .="</td>";
                $return .= "</tr>";
                $return .='<tr class="myrating' . $k . '">';
                $return .='<td>';
                $return .= "<a href='" . $link_view_details . "'>$mr->username </a>(" . $utype . ") - " . BidsHelperAuction::formatDate($mr->modified);
                $return .= "</td>";
                $return .="<td width='20%' colspan=2 nowrap>";
                $return .="<span class='rating_user' rating='" . $mr->rating . "'></span>";
                $return .="</td>";
                $return .="</tr>";
                $return .="<tr class='myrating" . ($k) . "'>";
                $return .="<td colspan='3' style='border-bottom:1px solid black'>";
                $return .= $mr->review;
                $return .= "</td>";
                $return .= "</tr>";
                $k = 1 - $k;
            }
        } else {

            $return .= "" . JText::_('COM_BIDS_NO_ITEMS') . "";
        }

        $pageslinks = "index.php?option=com_comprofiler&task=userProfile&user=$user->user_id&tab=$tab->tabid";

        $return .= "<tr height='20px'>";
        $return .= "<td colspan='3' align='center'>";
        $return .= "</td>";
        $return .= "</tr>";

        $return .= "<tr>";
        $return .= "<td colspan='2' align='center'>";
        $return .= $this->_writePaging($pagingParams, "myrates_", ITEMS_PER_PAGE, $total);
        $return .= "</td>";
        $return .= "</tr>";

        $return .= "</table>";
        $return .= "</form>";

        JHtml::_('behavior.mootools');
        JHtml::script( JURI::root() . "components/com_bids/js/ratings.js" );
        JHtml::script( JURI::root() . "components/com_bids/js/startup.js" );

        $document = JFactory::getDocument();
        $document->addScriptDeclaration('var JS_ROOT_HOST=\''.JUri::root().'\';');

        return $return;
    }

}

?>