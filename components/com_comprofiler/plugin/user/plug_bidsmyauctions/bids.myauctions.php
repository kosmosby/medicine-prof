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

class getmyauctionsTab extends bidsCbTabHandler {

	function getmyauctionsTab() {
        $this->cbTabHandler();
	}

	function getDisplayTab($tab,$user,$ui){

		$database = &JFactory::getDBO();
		$my = JFactory::getUser();

        $params = $this->params;

        JHtml::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'htmlelements');

        $queryCount = "SELECT COUNT(1) FROM #__bid_auctions WHERE userid=".$user->user_id;
        $database->setQuery($queryCount);
        $total = $database->loadResult();

		$query = "SELECT a.*,b.bid_price AS lastBid,u.username
			FROM #__bid_auctions AS a
			LEFT JOIN #__users u ON a.userid=u.id
			LEFT JOIN #__bids b ON a.id=b.auction_id
			WHERE a.userid = ".$database->quote($user->user_id)."
			GROUP BY a.id
			ORDER BY a.start_date DESC, b.id DESC";

		$limitstart=0;
		$pagingParams = $this->_getPaging( array(), array( "myauctions_" ) );
		if (isset($pagingParams["myauctions_limitstart"])) {
			$limitstart=$pagingParams["myauctions_limitstart"];
		}

		$database->setQuery($query,$limitstart, ITEMS_PER_PAGE);
		$myauctions = $database->loadObjectList();

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

		if($myauctions) {


			$return	.= '<tr>';
			$return .= '<th class="list_ratings_header" width="15%">'.Jtext::_('COM_BIDS_bid_status').'</th>';
			$return .= '<th class="list_ratings_header" width="*%">'.Jtext::_('COM_BIDS_bid_title').'</th>';
			$return .= '<th class="list_ratings_header" width="15%">'.Jtext::_('COM_BIDS_start_date').'</th>';
			$return .= '<th class="list_ratings_header" width="15%">'.Jtext::_('COM_BIDS_end_date').'</th>';
			$return .= '<th class="list_ratings_header" width="10%">'.Jtext::_('COM_BIDS_initial_price').'</th>';

			if($my->id==$user->user_id){
				$return .= '<th class="list_ratings_header" width="10%">'.Jtext::_('COM_BIDS_last_bid').'</th>';
			}

			$return .= '</tr>';
			$k=0;
			foreach ($myauctions as $ma){
			    /*@var $ma JAuctions*/
                //hide unpublished or Banned auctions
                if (!$ma->published || $ma->close_by_admin) {
                    continue;
                }

    	       	$link_view_details = JRoute::_("index.php?option=com_comprofiler&task=userprofile&user=$ma->userid");
    			$link_view_auction = JHtml::_('auctiondetails.auctionDetailsURL', $ma, false);

    			$status="";
    			if ($ma->close_offer){
    			    $status=Jtext::_('COM_BIDS_closed');

    			}elseif(!$ma->published){
    			    $status=Jtext::_('COM_BIDS_unpublished');
    			}elseif(strtotime($ma->end_date) <= gmdate('Y-m-d H:i:s') ){
                    $status=Jtext::_('COM_BIDS_expired');
                }else{
                    $status=Jtext::_('COM_BIDS_published');
                }

    			 $return .='<tr class="mywatch'.$k.'">';
    			 $return .='<td>';
    			 $return .= $status;
    			 $return .='</td>';

    			 $return .='<td>';
    			 $return .= '<a href="'.$link_view_auction.'">'.$ma->title.'</a>';
    			 $return .='</td>';

    			 $return .='<td>';
    			 $return .= $this->printDate($ma->start_date);
    			 $return .='</td>';
    			 $return .='<td>';
    			 $return .= $this->printDate($ma->end_date);
    			 $return .='</td>';
    			 $return .='<td>';
    			 $return .= BidsHelperAuction::formatPrice($ma->initial_price).' '.$ma->currency;
    			 $return .='</td>';
    			 if($my->id==$user->user_id){
    			     if ($ma->lastBid) {
                         $return .= '<td>'.BidsHelperAuction::formatPrice($ma->lastBid).' '.$ma->currency.'</td>';
                     } else {
                         $return .= '<td>-</td>';
                     }

    			 }
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
		$return .= $this->_writePaging($pagingParams,"myauctions_", ITEMS_PER_PAGE, $total);
		$return .= "</td>";
		$return .= "</tr>";
		$return .= "</form>";
		$return .= "</table>";
		$return .= "</div>";

		return $return;
	}
}