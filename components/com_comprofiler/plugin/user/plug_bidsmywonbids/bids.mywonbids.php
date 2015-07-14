<?php
/**
 * @package AuctionsFactory
 * @version 1.7.0
 * @copyright www.thefactory.ro
 * @license: commercial
*/

// no direct access
defined('_JEXEC') or die('Restricted access!');

JLoader::register('bidsCbTabHandler', JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'cb'.DS.'plgtabhandler.php');

if(!defined('ITEMS_PER_PAGE'))	define("ITEMS_PER_PAGE", '5');

class getmywonbidsTab extends bidsCbTabHandler {

	function getmywonbidsTab() {

		$this->cbTabHandler();

	}

	function getDisplayTab($tab,$user,$ui){

        $my = &JFactory::getUser();
        $database = &JFactory::getDBO();

        if($my->id!=$user->user_id){
                return null;
        }

        $params = $this->params;

        JHtml::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'htmlelements');

        $where=" where a.userid='$my->id' and accept=1";

        $query="select a.id as parent_message,a.bid_price,a.modified as bid_date,
             a.accept as accept, a.cancel as cancel,
             b.*, b.newmessages as bid_newmessages,
             c.name as currency_name,
             u.name as name, u.username,
             r.rating
            from #__bids a
            left join #__bid_auctions b on a.auction_id=b.id
            left join #__bid_currency c on b.currency=c.id
            left join #__users u on u.id=b.userid
    	    left join #__bid_rate r on r.voter_id='$my->id' and r.auction_id=b.id "
            .$where."  order by id desc";

		$database->setQuery($query);
		$nrbids = $database->loadObjectList();


		$total = count($nrbids);

		$limitstart=0;
		$pagingParams = $this->_getPaging( array(), array( "mywonbids_" ) );
		if (isset($pagingParams["mywonbids_limitstart"])) {
			$limitstart=$pagingParams["mywonbids_limitstart"];
		}

		$database->setQuery($query,$limitstart, ITEMS_PER_PAGE);
		$mybids = $database->loadObjectList();

		$pagingParams["limitstart"] = $limitstart;
		$pagingParams["limit"] = ITEMS_PER_PAGE;

		$return = "<div>";
		$return .="<table width='100%'>";
		$return .='<form name="topForm'.$tab->tabid.'" action="index.php" method="post">';
		$return .="<input type='hidden' name='option' value='com_comprofiler' />";
		$return .="<input type='hidden' name='task' value='userProfile' />";
		$return .="<input type='hidden' name='user' value='".$user->user_id."' />";
		$return .="<input type='hidden' name='tab' value='".$tab->tabid."' />";
		$return .="<input type='hidden' name='act' value='' />";


		if($mybids) {
			$return	.= '<tr>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_bid_title').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_bid_auctioneer').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_end_date').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_bid_price').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_rate').'</th>';
			$return .= '</tr>';
			$k=0;
			foreach ($mybids as $mb){
	            $link_view_details = JRoute::_('index.php?option=com_bids&task=userdetails&id='.$mb->userid);
				$link_view_auction = JHtml::_('auctiondetails.auctionDetailsURL', $mb, false);
				$return .='<tr class="mywatch'.$k.'">';
				$return .='<td>';
				$return .= '<a href="'.$link_view_auction.'">'.$mb->title.'</a>';
				$return .='</td>';
				$return .='<td>';
				$return .= '<a href="'.$link_view_details.'">'.$mb->username.'</a>';
				$return .='</td>';
				$return .='<td>';
				$return .= $mb->close_offer ? $this->printDate($mb->closed_date) : Jtext::_('COM_BIDS_N/A');
				$return .='</td>';
				$return .='<td>';
				$return .= BidsHelperAuction::formatPrice($mb->bid_price)." ".$mb->currency;
				$return .='</td>';
				$return .='<td>';
				if ($mb->rating){
					$return .= $mb->rating;
				}else{
					$return .= '<a href="'.$link_view_auction.'#bid_list">'.Jtext::_('COM_BIDS_rate').'</a>';
				}
				$return .='</td>';
				$return .= "</tr>";
				$k=1-$k;
			}
		} else {
			$return .=	"".JText::_('COM_BIDS_NO_ITEMS')."";
		}


		$return .= "<tr height='20px'>";
		$return .= "<td colspan='3' align='center'>";
		$return .= "</td>";
		$return .= "</tr>";
		$return .= "<tr>";
		$return .= "<td colspan='2' align='center'>";

		$return .= $this->_writePaging($pagingParams,"mywonbids_", ITEMS_PER_PAGE, $total);
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