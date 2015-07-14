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

class getmybidsTab extends bidsCbTabHandler {

	function getmybidsTab() {
		$this->cbTabHandler();
	}

	function getDisplayTab($tab,$user,$ui){

		$my = & JFactory::getUser();
		$database = & JFactory::getDBO();


		if($my->id!=$user->user_id){
			return null;
		}

		$params = $this->params;
        JHtml::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'htmlelements');

		$where = " where ";
		$where .= " a.userid = '$user->user_id' ";
		$where .= " and b.close_offer =0 and  b.close_by_admin=0 and published=1";

		$query="select a.id as parent_message,a.bid_price,a.modified as bid_date, a.auction_id,
            b.newmessages as bid_newmessages,
            a.accept as accept, a.cancel as cancel,
             b.*,c.name as currency_name, u.name as name, u.username
            from #__bids a
            left join #__bid_auctions b on a.auction_id=b.id
       		left join #__bid_currency c on b.currency=c.id
            left join #__users u on u.id=b.userid
            $where order by id desc";

		$database->setQuery($query);
		$nrbids = $database->loadObjectList();


		$total = count($nrbids);

		$limitstart=0;
		$pagingParams = $this->_getPaging( array(), array( "mybids_" ) );
		if (isset($pagingParams["mybids_limitstart"])) {
			$limitstart=$pagingParams["mybids_limitstart"];
		}

		$database->setQuery($query,$limitstart, ITEMS_PER_PAGE);
		$mybids = $database->loadObjectList();

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


		if($mybids) {
			$return	.= '<tr>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_bid_title').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_bid_auctioneer').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_end_date').'</th>';
			$return .= '<th class="list_ratings_header">'.Jtext::_('COM_BIDS_bid_price').'</th>';
			$return .= '</tr>';
			$k=0;
			foreach ($mybids as $mb){
			    /*@var $mb mosBidOffer*/
                $link_view_details = JRoute::_("index.php?option=com_bids&task=userdetails&id=$mb->userid");
    			$link_view_auction = JHtml::_('auctiondetails.auctionDetailsURL', $mb, false);

    			if ($mb->auction_type==AUCTION_TYPE_PUBLIC){
    			    //Outbid?
    			    $database->setQuery("select count(*) from #__bids where id<>'$mb->parent_message' and bid_price+0>$mb->bid_price and auction_id = $mb->auction_id ");
    			    $outbid=$database->loadResult();
    			    $msg = JText::_( $outbid ? 'COM_BIDS_OUTBIDDED' : 'COM_BIDS_HIGHEST_BID' );
    			}elseif ($mb->auction_type==AUCTION_TYPE_PRIVATE) {
    			    $msg=Jtext::_('COM_BIDS_PRIVATE');
    			}elseif ($mb->auction_type==AUCTION_TYPE_BIN_ONLY) {
    			    $msg=Jtext::_('COM_BIDS_BINPRICE');
    			}

    			 $return .='<tr class="mywatch'.$k.'">';
    			 $return .='<td>';
    			 $return .= '<a href="'.$link_view_auction.'">'.$mb->title.'</a>';
    			 $return .='</td>';
    			 $return .='<td>';
    			 $return .= '<a href="'.$link_view_details.'">'.$mb->username.'</a>';
    			 $return .='</td>';
    			 $return .='<td>';
    			 $return .= $this->printDate($mb->end_date);
    			 $return .='</td>';
    			 $return .='<td>';
    			 $return .= BidsHelperAuction::formatPrice($mb->bid_price)." ".$mb->currency;
    			 $return .='</td>';
    			 $return .='<td>';
    			 $return .= $msg;
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
		$return .= $this->_writePaging($pagingParams,"mybids_", ITEMS_PER_PAGE, $total);
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