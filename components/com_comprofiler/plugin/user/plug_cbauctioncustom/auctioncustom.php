<?php
/**
* Joomla Community Builder User Plugin: plug_cbhelloworld
* @version $Id$
* @package plug_helloworld
* @subpackage helloworld.php
* @author Nant, JoomlaJoe and Beat
* @copyright (C) Nant, JoomlaJoe and Beat, www.joomlapolis.com
* @license Limited  http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @final 1.0
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


/**
 * Basic tab extender. Any plugin that needs to display a tab in the user profile
 * needs to have such a class. Also, currently, even plugins that do not display tabs (e.g., auto-welcome plugin)
 * need to have such a class if they are to access plugin parameters (see $this->params statement).
 */
class getauctioncustomTab extends cbTabHandler {
	/**
	 * Construnctor
	 */
	function getauctioncustomTab() {
		$this->cbTabHandler();
	}
	
	/**
	* Generates the HTML to display the user profile tab
	* @param object tab reflecting the tab database entry
	* @param object mosUser reflecting the user being displayed
	* @param int 1 for front-end, 2 for back-end
	* @returns mixed : either string HTML for tab content, or false if ErrorMSG generated
	*/
	function getDisplayTab($tab,$user,$ui) {
		$return = null;
        $visitor =& JFactory::getUser();

		$params = $this->params; // get parameters (plugin and related tab)
		
		$is_helloworld_plug_enabled = $params->get('hwPlugEnabled', "1");

		if ($is_helloworld_plug_enabled != "0") {

            $rows = $this->getRecords( $user->id );
            if($rows && count($rows) > 0){
                foreach($rows as $row){
                $dateStr = new JDate($row->end_date.' +5 day', 'GMT');
                $dateEndStr = new JDate($row->end_date, 'GMT');
                $return = '<div style="float:left;color:white;width:290px;padding-top:10px;"> Тема: '
                    .$row->title 
                    .'</div>'
                    .'<div style="color:white;float:right;text-align:right;padding-right:0px;padding-top:0px">Дата и время консультации: '
                    .$dateStr->format('d M Y H:i')
                    .' (МСК)';
                 if($row->active){   
                    $return .='<br/>'
                      .'Время окончания предварительной записи:  '
                      .$dateEndStr->format('d M Y H:i')
                      .' (МСК)';
                  }
                  $return .= '</div>';

                $return .= '
                        <form action="/index.php" method="post" id="bidform">
                        <input type="hidden" name="option" value="com_bids">
                        <input type="hidden" name="max_bid" value="'.$row->maxBid.'" id="max_bid">
                        <input type="hidden" name="task" value="sendbidajax">
                        <input type="hidden" name="id" value="'.$row->id.'">
                        <input type="hidden" name="active" value="'.$row->active.'" id="consultation_active">
                        <input type="hidden" name="proxy" value="0">
                        <input type="hidden" name="amount" id="auctioncustom-amount"/>




                </form>
                <script>

                    function sendBid(value){
                ';
                if($visitor->id){


                $return .='  jQuery("#auctioncustom-amount").val(value);
                        jQuery.ajax({
                            type: "POST",
                            url: "/",
                            data: jQuery("#bidform").serialize(),
                            success: function(data)
                            {
                                var obj = JSON.parse(data);
                                if(obj.success==1){
                                    jQuery("#max_bid_show").html(\'$\'+obj.bid);
                                }else{
                                    window.alert(obj.message);
                                }
                            },
                            error: function(xhr, code){
                                window.alert("Error occured");
                            }
    });
    ';}else{
                $return .= 'window.alert("Вы должны авторизироваться на сайте.")';
                }
                    $return .= '}
</script>';
                }
            }
		}
		
		return $return;
	} // end or getDisplayTab function

    function getRecords( $user_id ){
        $dbo = JFactory::getDBO();
        $dbo->setQuery( $this->getQuery($user_id) );
        $list = $dbo->loadObjectList();
        if( empty($list) ){
          //if there are no open auctions, try to show the recent closed one.
          $dbo->setQuery( $this->getQuery($user_id, true) );
          $list = $dbo->loadObjectList();
        }
        return $list;
    }

    function getQuery( $user_id, $show_expired=false  ){

        $sort_by		=	"start_date";
        $selectCols = array(
            "a.id",
            "a.title",
            "a.userid",
            "a.auction_type",
            "a.initial_price",
            "a.BIN_price",
            "a.currency",
            "u.username as by_user",
            "a.start_date",
            "a.end_date",
            'a.params',
            'a.close_offer',
            'p.picture',
            'MAX(b.bid_price) AS maxBid'
        );

        if($show_expired){
          $selectCols[] = '0 as active';
        }else{
          $selectCols[] = '1 as active';
        }
        $where = array(
            "a.published = 1",
            "a.close_offer = 0",
            "a.close_by_admin = 0"
        );
        if(!$show_expired){
            $where[] = "a.start_date <= UTC_TIMESTAMP()";
            $where[] = "a.end_date >= UTC_TIMESTAMP()";
        }

        $where[] = "a.userid='{$user_id}'";
        if($show_expired){
            $orderings = array("a.end_date DESC");
        }else{
            $orderings = array("a.end_date ASC");
        }

        $JoinList = array("LEFT JOIN `#__users` as u on a.userid=u.id ",
            "LEFT JOIN #__bid_pictures AS p on a.id=p.auction_id");


        $orderings[] = "a.$sort_by DESC, p.ordering";

        $query="SELECT ".implode(",",$selectCols).
            " FROM `#__bid_auctions` as a
            LEFT JOIN `#__bids` as b ON a.id=b.auction_id ".

            implode(" \r\n ",$JoinList)."\r\n".
            "WHERE ".implode(" AND ",$where)." ".PHP_EOL.
            "GROUP BY a.id ".PHP_EOL.
            "ORDER BY ".implode(",",$orderings);
        return $query;
    }
} // end of gethelloworldTab class
?>
