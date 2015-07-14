<?php
/**
 * @package AuctionsFactory
 * @version 2.0.2
 * @copyright www.thefactory.ro
 * @license: commercial
*/

// no direct access
defined('_JEXEC') or die('Restricted access!');

JLoader::register('bidsCbTabHandler', JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'cb'.DS.'plgtabhandler.php');

if(!defined('ITEMS_PER_PAGE'))	define("ITEMS_PER_PAGE", '5');

class getmywatchlistTab extends bidsCbTabHandler {

	function getmywatchlistTab() {
		$this->cbTabHandler();
	}

	function getDisplayTab($tab,$user,$ui){

		$database = JFactory::getDBO();
		$my = JFactory::getUser();

		if($my->id!=$user->user_id){
			return null;
		}

		$params = $this->params;

        JHtml::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'htmlelements');

		$query = "SELECT
		            u.username,b.*,
		            c.name as currency_name, MAX(bi.bid_price) AS curr_bid
			FROM #__bid_auctions b
			LEFT JOIN #__bids as bi on bi.auction_id=b.id
			left join #__bid_currency c on b.currency=c.id
			left join #__bid_watchlist w on ".$user->user_id."=w.userid
			left join #__users u on b.userid = u.id
			where b.id=w.auction_id
			group by b.id
			order by id desc
			";

		$database->setQuery($query);
		$nrwatches = $database->loadObjectList();

		$total = count($nrwatches);

		$limitstart=0;
		$pagingParams = $this->_getPaging( array(), array( "mywatch_" ) );
		if (isset($pagingParams["mywatch_limitstart"])) {
			$limitstart=$pagingParams["mywatch_limitstart"];
		}

		$database->setQuery($query,$limitstart, ITEMS_PER_PAGE);
		$mywatches = $database->loadObjectList();

		$pagingParams["limitstart"] = $limitstart;
		$pagingParams["limit"] = ITEMS_PER_PAGE;


		$return = "\t\t<div>\n";
		$return .="<table width='100%'>";
		$return .='<form name="topForm'.$tab->tabid.'" action="index.php" method="post">';
		$return .="<input type='hidden' name='option' value='com_comprofiler' />";
		$return .="<input type='hidden' name='task' value='userProfile' />";
		$return .="<input type='hidden' name='user' value='".$user->user_id."' />";
		$return .="<input type='hidden' name='tab' value='".$tab->tabid."' />";
		$return .="<input type='hidden' name='act' value='' />";


		if($mywatches) {
			$return	.= '<tr>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_bid_title').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_bid_auctioneer').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_start_date').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_end_date').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_initial_price').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_bid_price').'</th>';
			$return .= '</tr>';
			$k=0;
			foreach ($mywatches as $mw){
            $link_view_details = JRoute::_("index.php?option=com_bids&task=userdetails&id=".$mw->userid);
			$link_view_auction = JHtml::_('auctiondetails.auctionDetailsURL', $mw, false);
			//var_dump($myratings);exit;
			 $return .='<tr class="mywatch'.$k.'">';
			 $return .='<td>';
			 $return .= '<a href="'.$link_view_auction.'">'.$mw->title.'</a>';
			 $return .='</td>';
			 $return .='<td>';
			 $return .= '<a href="'.$link_view_details.'">'.$mw->username.'</a>';
			 $return .='</td>';
			 $return .='<td>';
			 $return .= $this->printDate($mw->start_date);
			 $return .='</td>';
			 $return .='<td>';
			 $return .= $this->printDate($mw->end_date);
			 $return .='</td>';
			 $return .='<td>';
			 $return .= BidsHelperAuction::formatPrice($mw->initial_price)." ".$mw->currency;
			 $return .='</td>';
			 $return .='<td>';
			 $return .= BidsHelperAuction::formatPrice($mw->curr_bid)." ".$mw->currency;
			 $return .='</td>';
			 $return .= "</tr>";
			 $k=1-$k;

			 }
		} else {

			$return .=	"".JText::_('COM_BIDS_NO_ITEMS')."";

		}
		$pageslinks = "index.php?option=com_comprofiler&task=userProfile&user=$user->user_id&tab=$tab->tabid";

		$return .= "<tr height='20px'>";
		$return .= "<td colspan='3' align='center'>";
		$return .= "</td>";
		$return .= "</tr>";
		$return .= "<tr>";
		$return .= "<td colspan='2' align='center'>";
		$return .= $this->_writePaging($pagingParams,"mywatch_", ITEMS_PER_PAGE, $total);
		$return .= "</td>";
		$return .= "</tr>";
		$return .= "</form>";
		$return .= "</table>";
		$return .= "</div>";

		//$return ="";
		return $return;
	}
}
?>